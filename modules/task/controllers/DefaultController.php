<?php

namespace app\modules\task\controllers;

use app\components\UserHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\task\models\Task;
use app\modules\task\models\TaskActivity;
use app\modules\task\services\TaskService;
use app\modules\task\services\TaskTelegramService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * ปฏิทินงานและรายการงาน
 *
 * โครงหน้าเป็นสามคอลัมน์: รายชื่อทีม | ปฏิทิน | รายการงาน
 * งานที่เลยกำหนดจะถูกดึงมารวมไว้บนวันปัจจุบันด้วย
 * เพื่อไม่ให้จมอยู่ในอดีตที่ไม่มีใครเลื่อนกลับไปดู
 */
class DefaultController extends Controller
{
    /** จำนวนวันย้อนหลังที่ยังแสดงงานที่ปิดแล้ว */
    private const DONE_WINDOW_DAYS = 30;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'complete' => ['POST'],
                    'start' => ['POST'],
                    'assign' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $me = $this->currentEmployee();
        $people = $this->visiblePeople($me);
        $selected = $this->selectedEmpIds($me);

        return $this->render('index', [
            'me' => $me,
            'people' => $people,
            'selected' => $selected,
            'lists' => $this->buildLists($selected, null),
        ]);
    }

    /**
     * แหล่งข้อมูลของ FullCalendar — หนึ่งวันได้หนึ่งชิป เป็นจำนวนงาน ไม่ใช่ชื่องาน
     *
     * ถ้าแสดงชื่องานทีละรายการ วันที่มี 5 งานจะกลายเป็นกำแพงข้อความจนอ่านปฏิทินไม่ออก
     * ผู้ใช้ต้องการรู้แค่ว่าวันไหนมีงานค้างกี่ชิ้น แล้วค่อยคลิกดูรายการ
     *
     * งานที่เลยกำหนดไม่แสดงบนวันเดิม เพราะมีชิปรวมบนวันปัจจุบันทำหน้าที่นั้นแทน
     */
    public function actionEvents()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $me = $this->currentEmployee();
        $selected = $this->selectedEmpIds($me);
        $start = (string) Yii::$app->request->get('start');
        $end = (string) Yii::$app->request->get('end');
        $today = date('Y-m-d');

        $query = Task::find()
            ->with(['assignee', 'assigner', 'ownerUnit'])
            ->where(['assignee_emp_id' => $selected])
            ->andWhere(['status' => Task::OPEN_STATUSES])
            ->andWhere(['not', ['due_date' => null]]);

        if ($end !== '') {
            $query->andWhere(['<=', 'due_date', substr($end, 0, 10)]);
        }

        // รวมเป็นรายวัน: นับจำนวน จำว่ามีงานด่วนไหม และมีงานที่ทบมาจากอดีตไหม
        $byDate = [];
        $add = function (string $date, Task $task) use (&$byDate, $today) {
            if (!isset($byDate[$date])) {
                $byDate[$date] = ['count' => 0, 'urgent' => false, 'carried' => false];
            }
            $byDate[$date]['count']++;
            if ($task->priority === Task::PRIORITY_URGENT) {
                $byDate[$date]['urgent'] = true;
            }
            if ($task->due_date < $today) {
                $byDate[$date]['carried'] = true;
            }
        };

        foreach ($query->all() as $task) {
            // งานที่ยังไม่ปิดและเลยกำหนดแล้ว จะทบมารวมอยู่ที่วันนี้
            // ไม่ค้างอยู่ในอดีตที่ไม่มีใครเลื่อนปฏิทินกลับไปดู
            $add($task->due_date < $today ? $today : $task->due_date, $task);
        }

        $events = [];
        foreach ($byDate as $date => $info) {
            $events[] = [
                'id' => 'day-' . $date,
                'title' => $info['count'] . ' งาน',
                'start' => $date,
                'allDay' => true,
                // ไม่ใส่ url เพราะคลิกแล้วต้องเปิด popup ไม่ใช่เปลี่ยนหน้า
                'classNames' => [($info['urgent'] || $info['carried']) ? 'task-ev-urgent' : 'task-ev-normal'],
                'extendedProps' => [
                    'date' => $date,
                    'count' => $info['count'],
                    'carried' => $info['carried'],
                    'dayUrl' => \yii\helpers\Url::to(['/task/default/day', 'date' => $date]),
                ],
            ];
        }

        return $events;
    }

    /**
     * รายการงานของวันที่ระบุ เปิดใน popup เมื่อคลิกชิปบนปฏิทิน
     *
     * ถ้าเป็นวันนี้ จะรวมงานที่ทบมาจากอดีตด้วย ให้ตรงกับตัวเลขบนชิป
     */
    public function actionDay($date)
    {
        $me = $this->currentEmployee();
        $selected = $this->selectedEmpIds($me);
        $date = substr((string) $date, 0, 10);
        $today = date('Y-m-d');

        $query = Task::find()
            ->with(['assignee', 'assigner', 'ownerUnit'])
            ->where(['assignee_emp_id' => $selected])
            ->andWhere(['status' => Task::OPEN_STATUSES]);

        if ($date === $today) {
            $query->andWhere(['<=', 'due_date', $today]);
        } else {
            $query->andWhere(['due_date' => $date]);
        }

        $tasks = $query->orderBy(['due_date' => SORT_ASC, 'priority' => SORT_DESC, 'id' => SORT_ASC])->all();

        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['index']);
        }

        $carried = 0;
        foreach ($tasks as $task) {
            if ($task->due_date < $today) {
                $carried++;
            }
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' => 'งานวันที่ ' . \app\components\ThaiDate::toThaiDate($date, false),
            'count' => count($tasks),
            'content' => $this->renderAjax('_day_popup', [
                'tasks' => $tasks,
                'date' => $date,
                'carried' => $carried,
            ]),
        ];
    }

    /** แผงรายการงานด้านขวา โหลดใหม่เมื่อเปลี่ยนตัวกรองหรือคลิกวันในปฏิทิน */
    public function actionList()
    {
        $me = $this->currentEmployee();
        $selected = $this->selectedEmpIds($me);
        $date = trim((string) Yii::$app->request->get('date')) ?: null;

        return $this->renderPartial('_panel_list', [
            'lists' => $this->buildLists($selected, $date),
            'date' => $date,
        ]);
    }

    /**
     * @param int[] $empIds
     * @param string|null $date กรองเฉพาะวันที่ระบุ (จากการคลิกวันในปฏิทิน)
     */
    private function buildLists(array $empIds, ?string $date): array
    {
        $open = Task::find()
            ->with(['assignee', 'assigner', 'ownerUnit'])
            ->where(['assignee_emp_id' => $empIds])
            ->andWhere(['status' => Task::OPEN_STATUSES]);

        $done = Task::find()
            ->with(['assignee', 'assigner', 'ownerUnit'])
            ->where(['assignee_emp_id' => $empIds])
            ->andWhere(['status' => Task::STATUS_DONE])
            ->andWhere(['>=', 'completed_at', date('Y-m-d 00:00:00', strtotime('-' . self::DONE_WINDOW_DAYS . ' days'))]);

        if ($date !== null) {
            $open->andWhere(['due_date' => $date]);
            $done->andWhere(['due_date' => $date]);
        }

        $openTasks = $open->orderBy(['due_date' => SORT_ASC, 'id' => SORT_ASC])->all();

        // งานที่เลยกำหนดหรือด่วน ขึ้นก่อนเสมอ เพราะเป็นสิ่งที่ระบบควรทัก
        usort($openTasks, function (Task $a, Task $b) {
            $rank = function (Task $t) {
                if ($t->overdueDays() > 0) { return 0; }
                if ($t->priority === Task::PRIORITY_URGENT) { return 1; }
                if ($t->due_date === date('Y-m-d')) { return 2; }
                return 3;
            };
            $ra = $rank($a);
            $rb = $rank($b);
            if ($ra !== $rb) { return $ra <=> $rb; }
            return strcmp((string) $a->due_date, (string) $b->due_date);
        });

        return [
            'open' => $openTasks,
            'done' => $done->orderBy(['completed_at' => SORT_DESC])->all(),
        ];
    }

    /**
     * คนที่ผู้ใช้มีสิทธิ์เห็นงาน = บุคลากรที่ยังปฏิบัติงานในสายหน่วยงานตัวเอง
     * ผู้ที่ย้าย ลาออก เกษียณ หรือพ้นสภาพแล้วไม่แสดงในตัวกรองรายชื่อ
     *
     * @return Employees[]
     */
    private function visiblePeople(Employees $me): array
    {
        if ((string) $me->status !== Employees::STATUS_WORKING) {
            return [];
        }

        $unitIds = TaskService::unitScopeIds($me->department ? (int) $me->department : null);
        if (!$unitIds) {
            return [$me];
        }

        $people = Employees::find()
            ->where(['department' => $unitIds])
            ->andWhere(['status' => Employees::STATUS_WORKING])
            ->andWhere(['not', ['id' => (int) $me->id]])
            ->orderBy(['fname' => SORT_ASC])
            ->all();

        return array_merge([$me], $people);
    }

    /**
     * รหัสพนักงานที่กำลังกรองอยู่ กรองด้วยสิทธิ์เสมอ ไม่เชื่อค่าที่ส่งมาจากหน้าเว็บ
     *
     * @return int[]
     */
    private function selectedEmpIds(Employees $me): array
    {
        $allowed = array_map(function (Employees $e) {
            return (int) $e->id;
        }, $this->visiblePeople($me));

        $requested = Yii::$app->request->get('emp');
        if ($requested === null || $requested === '') {
            return in_array((int) $me->id, $allowed, true) ? [(int) $me->id] : [];
        }

        $ids = array_map('intval', is_array($requested) ? $requested : explode(',', (string) $requested));
        $ids = array_values(array_intersect($ids, $allowed));
        if ($ids) {
            return $ids;
        }

        return in_array((int) $me->id, $allowed, true) ? [(int) $me->id] : [];
    }

    public function actionView($id)
    {
        $task = $this->findTask($id);
        $this->assertCanSee($task);

        return $this->render('view', [
            'task' => $task,
            'activities' => $task->activities,
        ]);
    }

    /**
     * เพิ่มงานเองจากหน้าปฏิทิน — งานส่วนใหญ่มาจากหนังสือ แต่บางเรื่องก็เกิดจากที่ประชุมหรือโทรศัพท์
     *
     * หน่วยงานเจ้าของไม่ต้องเลือก ระบบใช้หน่วยของผู้รับผิดชอบให้เอง
     * ถ้ายังไม่ระบุผู้รับผิดชอบก็ใช้หน่วยของคนสร้าง เพื่อให้กรอกน้อยที่สุด
     */
    public function actionCreate()
    {
        $me = $this->currentEmployee();

        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $this->saveNew($me);
        }

        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['index']);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' => 'เพิ่มงาน',
            'content' => $this->renderAjax('_form_create', [
                'me' => $me,
                'groups' => $this->targetGroups($me),
                'canCrossUnit' => Yii::$app->user->can(TaskService::PERMISSION_CROSS_UNIT),
                'date' => substr((string) Yii::$app->request->get('date'), 0, 10) ?: null,
            ]),
        ];
    }

    /**
     * รายชื่อปลายทางทั้งโรงพยาบาล จัดกลุ่มตามหน่วยงาน แบบเดียวกับที่สารบรรณส่งหนังสือ
     *
     * หน่วยของตัวเองอยู่บนสุด เพราะเป็นปลายทางที่ใช้บ่อยที่สุด
     *
     * @return array<int, array{unit: Organization, people: Employees[], inScope: bool}>
     */
    private function targetGroups(Employees $me): array
    {
        $myScope = TaskService::unitScopeIds($me->department ? (int) $me->department : null);

        $units = Organization::find()
            ->where(['active' => 1])
            ->orderBy(['lft' => SORT_ASC])
            ->all();

        $peopleByUnit = [];
        foreach (Employees::find()->where(['not', ['department' => null]])->orderBy(['fname' => SORT_ASC])->all() as $person) {
            $peopleByUnit[(int) $person->department][] = $person;
        }

        $groups = [];
        foreach ($units as $unit) {
            $id = (int) $unit->id;
            $groups[] = [
                'unit' => $unit,
                'people' => $peopleByUnit[$id] ?? [],
                'inScope' => in_array($id, $myScope, true),
            ];
        }

        // หน่วยของตัวเองขึ้นก่อน แล้วตามด้วยหน่วยในสายเดียวกัน
        usort($groups, function ($a, $b) use ($me) {
            $rank = function ($g) use ($me) {
                if ((int) $g['unit']->id === (int) $me->department) { return 0; }
                return $g['inScope'] ? 1 : 2;
            };
            $ra = $rank($a);
            $rb = $rank($b);
            return $ra === $rb ? 0 : ($ra <=> $rb);
        });

        return $groups;
    }

    private function saveNew(Employees $me): array
    {
        $request = Yii::$app->request;

        $title = trim((string) $request->post('title'));
        if ($title === '') {
            return ['status' => 'error', 'message' => 'กรุณาระบุชื่องาน'];
        }

        // ปลายทางส่งมาเป็น emp:<id> หรือ unit:<id> แบบเดียวกับการ tag หนังสือ
        // เลือกได้หลายปลายทาง แต่ละอันกลายเป็นงานหนึ่งชิ้นตามกติกา 1 งาน = 1 ผู้รับผิดชอบ
        $targets = (array) $request->post('targets', []);
        if (!$targets) {
            return ['status' => 'error', 'message' => 'กรุณาเลือกผู้รับผิดชอบอย่างน้อยหนึ่งราย'];
        }

        // ช่องวันที่กรอกเป็น วว/ดด/พ.ศ. ต้องแปลงกลับเป็น ค.ศ. ก่อนเก็บลงฐานข้อมูลเสมอ
        $dueDate = TaskService::parseDueDate($request->post('due_date'));

        $rows = [];
        $seen = [];
        foreach ($targets as $target) {
            $target = trim((string) $target);
            if ($target === '' || isset($seen[$target])) {
                continue;
            }
            $seen[$target] = true;

            $assignee = null;
            $ownerUnitId = 0;

            if (strpos($target, 'emp:') === 0) {
                $assignee = Employees::findOne((int) substr($target, 4));
                if (!$assignee) {
                    return ['status' => 'error', 'message' => 'ไม่พบผู้รับผิดชอบที่เลือก'];
                }
                $ownerUnitId = (int) $assignee->department;

                // ระบุตัวคนข้ามสายหน่วยงานต้องมีสิทธิ์ ตามกติกาที่ตกลงไว้
                // ส่วนการส่งถึงหน่วยงานเฉย ๆ ไม่ต้องใช้สิทธิ์
                if (!TaskService::canAssignToUnit((int) $me->department, (int) $me->id, $ownerUnitId)) {
                    return [
                        'status' => 'error',
                        'message' => 'คุณไม่มีสิทธิ์ระบุตัวผู้รับผิดชอบข้ามหน่วยงาน — เลือก "ส่งถึงหน่วยงาน" แทน แล้วให้หัวหน้าหน่วยนั้นจ่ายงานเอง',
                    ];
                }
            } elseif (strpos($target, 'unit:') === 0) {
                $ownerUnitId = (int) substr($target, 5);
                if (!Organization::findOne($ownerUnitId)) {
                    return ['status' => 'error', 'message' => 'ไม่พบหน่วยงานที่เลือก'];
                }
            } else {
                continue;
            }

            if (!$ownerUnitId) {
                return ['status' => 'error', 'message' => 'ผู้รับผิดชอบที่เลือกยังไม่ได้ผูกกับหน่วยงาน'];
            }

            $rows[] = [
                'title' => $title,
                'detail' => trim((string) $request->post('detail')) ?: null,
                'owner_unit_id' => $ownerUnitId,
                'assignee_emp_id' => $assignee ? (int) $assignee->id : null,
                'due_date' => $dueDate,
                'priority' => $request->post('priority') === Task::PRIORITY_URGENT
                    ? Task::PRIORITY_URGENT : Task::PRIORITY_NORMAL,
            ];
        }

        if (!$rows) {
            return ['status' => 'error', 'message' => 'กรุณาเลือกผู้รับผิดชอบอย่างน้อยหนึ่งราย'];
        }

        // พัก Telegram ไว้ก่อน แล้วส่งรวมทีเดียว คนเดียวจะได้ไม่โดนเด้งเท่าจำนวนงาน
        $createdTasks = [];
        foreach ($rows as $row) {
            $task = TaskService::create($row, (int) $me->id, true);
            if ($task !== null) {
                $createdTasks[] = $task;
            }
        }
        \app\modules\task\services\TaskTelegramService::notifyBatch($createdTasks, (int) $me->id);
        $created = count($createdTasks);

        if ($created === 0) {
            return ['status' => 'error', 'message' => 'สร้างงานไม่สำเร็จ'];
        }

        return [
            'status' => 'success',
            'message' => $created === 1 ? 'เพิ่มงานเรียบร้อย' : sprintf('เพิ่มงานแล้ว %d รายการ', $created),
            'count' => $created,
        ];
    }

    /**
     * แก้ไขงานใน popup — งานมีข้อมูลไม่มาก ไม่คุ้มที่จะเปลี่ยนหน้า
     *
     * เปิดผ่าน modal กลางของโปรเจกต์ (.open-modal + #main-modal ใน erp.js)
     * และคงทางหนีเป็นหน้าเต็มไว้เมื่อเปิดตรงจาก URL
     */
    public function actionUpdate($id)
    {
        $task = $this->findTask($id);
        $this->assertCanSee($task);
        $canEdit = $this->canEdit($task);

        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if (!$canEdit) {
                return ['status' => 'error', 'message' => 'แก้ไขได้เฉพาะผู้รับผิดชอบหรือหัวหน้าหน่วยงานเจ้าของงาน'];
            }
            return $this->saveFromPost($task);
        }

        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['view', 'id' => $task->id]);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' => $canEdit ? 'แก้ไขงาน' : 'รายละเอียดงาน',
            'content' => $this->renderAjax('_form_modal', [
                'task' => $task,
                'activities' => $task->activities,
                'canEdit' => $canEdit,
            ]),
        ];
    }

    /**
     * บันทึกการแก้ไข พร้อมบันทึกความเคลื่อนไหวเฉพาะสิ่งที่เปลี่ยนจริง
     * ถ้าเลื่อนกำหนดจะนับจำนวนครั้งไว้ เพราะงานที่เลื่อนซ้ำมักกลายเป็นงานร้อน
     */
    private function saveFromPost(Task $task): array
    {
        $request = Yii::$app->request;
        $me = UserHelper::GetEmployee();
        $actorId = $me ? (int) $me->id : null;

        $oldDue = $task->due_date;
        $oldAssignee = (int) $task->assignee_emp_id;
        $wasOpen = $task->isOpen();

        $title = trim((string) $request->post('title'));
        if ($title === '') {
            return ['status' => 'error', 'message' => 'กรุณาระบุชื่องาน'];
        }

        $task->title = $title;
        $task->detail = trim((string) $request->post('detail')) ?: null;
        // ช่องวันที่กรอกเป็น วว/ดด/พ.ศ. ต้องแปลงกลับเป็น ค.ศ. ก่อนเก็บลงฐานข้อมูลเสมอ
        $task->due_date = TaskService::parseDueDate($request->post('due_date'));
        $task->priority = $request->post('priority') === Task::PRIORITY_URGENT
            ? Task::PRIORITY_URGENT : Task::PRIORITY_NORMAL;
        $postData = $request->post();
        if (array_key_exists('is_waiting', $postData)) {
            $task->is_waiting = (bool) $postData['is_waiting'];
        }

        $status = (string) $request->post('status');
        if (array_key_exists($status, Task::statusLabels())) {
            $task->status = $status;
        }

        if ($this->canPickPerson($task) && array_key_exists('assignee_emp_id', $postData)) {
            $newAssignee = (int) $postData['assignee_emp_id'];
            $task->assignee_emp_id = $newAssignee > 0 ? $newAssignee : null;
        }

        // เลื่อนกำหนดนับเป็นการเลื่อน เฉพาะเมื่อเลื่อนออกไปข้างหน้า
        $postponed = $oldDue && $task->due_date && $task->due_date > $oldDue;
        if ($postponed) {
            $task->postpone_count = (int) $task->postpone_count + 1;
        }

        if ($task->status === Task::STATUS_DONE && $wasOpen) {
            $task->completed_at = date('Y-m-d H:i:s');
            $task->completed_by = $actorId;
            $task->is_waiting = false;
        }

        if (!$task->save()) {
            Yii::error(['บันทึกงานไม่สำเร็จ' => $task->getErrors()], __METHOD__);
            return ['status' => 'error', 'message' => 'บันทึกไม่สำเร็จ กรุณาตรวจข้อมูลอีกครั้ง'];
        }

        if ($postponed) {
            TaskService::log($task, TaskActivity::ACTION_POSTPONE, 'เลื่อนจาก ' . $oldDue . ' เป็น ' . $task->due_date, $actorId);
        }
        if ((int) $task->assignee_emp_id !== $oldAssignee) {
            TaskService::log($task, $oldAssignee ? TaskActivity::ACTION_REASSIGN : TaskActivity::ACTION_ASSIGN, null, $actorId);
        }
        if ($task->status === Task::STATUS_DONE && $wasOpen) {
            TaskService::log($task, TaskActivity::ACTION_COMPLETE, trim((string) $request->post('note')) ?: null, $actorId);
            TaskTelegramService::notifyCompleted($task, $actorId);
        } elseif (!$postponed && (int) $task->assignee_emp_id === $oldAssignee) {
            TaskService::log($task, TaskActivity::ACTION_NOTE, 'แก้ไขข้อมูลงาน', $actorId);
        }

        return ['status' => 'success', 'message' => 'บันทึกเรียบร้อย'];
    }

    /** เลือกตัวผู้รับผิดชอบได้เมื่อหน่วยงานเจ้าของอยู่ในสายตัวเอง หรือมีสิทธิ์ข้ามหน่วย */
    private function canPickPerson(Task $task): bool
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            return false;
        }
        return TaskService::canAssignToUnit((int) $me->department, (int) $me->id, (int) $task->owner_unit_id);
    }

    /** ปิดงาน — ออกแบบให้กดปุ่มเดียวจบ บันทึกเป็นตัวเลือก ไม่บังคับ */
    public function actionComplete($id)
    {
        $task = $this->findTask($id);
        $this->assertCanEdit($task);

        $me = UserHelper::GetEmployee();
        $note = trim((string) Yii::$app->request->post('note'));
        $ok = TaskService::complete($task, $me ? (int) $me->id : null, $note !== '' ? $note : null);

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => $ok ? 'success' : 'error'];
        }

        Yii::$app->session->setFlash($ok ? 'success' : 'error', $ok ? 'ปิดงานแล้ว' : 'ปิดงานไม่สำเร็จ');
        return $this->redirect(['index']);
    }

    public function actionStart($id)
    {
        $task = $this->findTask($id);
        $this->assertCanEdit($task);

        $me = UserHelper::GetEmployee();
        $ok = true;
        // กดซ้ำไม่บันทึกซ้ำ ด้วยเหตุผลเดียวกับการปิดงาน
        if ($task->status === Task::STATUS_PENDING) {
            $task->status = Task::STATUS_DOING;
            $ok = $task->save(true, ['status', 'updated_at', 'updated_by']);
            if ($ok) {
                TaskService::log($task, TaskActivity::ACTION_START, null, $me ? (int) $me->id : null);
            }
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => $ok ? 'success' : 'error'];
        }
        return $this->redirect(['index']);
    }

    /** หัวหน้าหน่วยจ่ายงานที่ค้างอยู่ให้คนในหน่วยตัวเอง */
    public function actionAssign($id)
    {
        $task = $this->findTask($id);
        $me = $this->currentEmployee();

        $targetEmpId = (int) Yii::$app->request->post('assignee_emp_id');
        $target = Employees::findOne($targetEmpId);
        if (!$target) {
            throw new NotFoundHttpException('ไม่พบผู้รับผิดชอบที่เลือก');
        }

        if (!TaskService::canAssignToUnit((int) $me->department, (int) $me->id, (int) $target->department)) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์มอบหมายงานให้คนในหน่วยงานอื่น');
        }

        $ok = TaskService::assign($task, $targetEmpId, (int) $me->id);
        Yii::$app->session->setFlash($ok ? 'success' : 'error', $ok ? 'จ่ายงานเรียบร้อย' : 'จ่ายงานไม่สำเร็จ');
        return $this->redirect(['index']);
    }

    private function currentEmployee(): Employees
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            throw new ForbiddenHttpException('ไม่พบข้อมูลพนักงานของบัญชีนี้');
        }
        return $me;
    }

    private function findTask($id): Task
    {
        $task = Task::findOne((int) $id);
        if (!$task) {
            throw new NotFoundHttpException('ไม่พบงานที่ต้องการ');
        }
        return $task;
    }

    /** เห็นงานได้เมื่อเป็นผู้รับผิดชอบ ผู้มอบหมาย หรืออยู่ในสายหน่วยงานเจ้าของ */
    private function assertCanSee(Task $task): void
    {
        $me = $this->currentEmployee();
        $empId = (int) $me->id;
        if ((int) $task->assignee_emp_id === $empId || (int) $task->assigner_emp_id === $empId) {
            return;
        }
        if (in_array((int) $task->owner_unit_id, TaskService::unitScopeIds((int) $me->department), true)) {
            return;
        }
        if (Yii::$app->user->can(TaskService::PERMISSION_CROSS_UNIT)) {
            return;
        }
        throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดูงานนี้');
    }

    /** แก้ไข/ปิดงานได้เมื่อเป็นผู้รับผิดชอบ หรือเป็นหัวหน้าหน่วยเจ้าของงาน */
    private function canEdit(Task $task): bool
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            return false;
        }
        $empId = (int) $me->id;
        return (int) $task->assignee_emp_id === $empId
            || TaskService::isUnitLeader((int) $task->owner_unit_id, $empId)
            || Yii::$app->user->can('admin');
    }

    private function assertCanEdit(Task $task): void
    {
        if (!$this->canEdit($task)) {
            throw new ForbiddenHttpException('แก้ไขได้เฉพาะผู้รับผิดชอบหรือหัวหน้าหน่วยงานเจ้าของงาน');
        }
    }
}
