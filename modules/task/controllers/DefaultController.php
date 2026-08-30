<?php

namespace app\modules\task\controllers;

use app\components\UserHelper;
use app\modules\hr\models\Employees;
use app\modules\task\models\Task;
use app\modules\task\services\TaskService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * หน้างานของฉัน และการปิดงาน
 *
 * หลักการของหน้านี้: นำด้วยงานที่กำลังจะมีปัญหา ไม่ใช่งานใหม่
 * เพราะงานใหม่ผู้ใช้จำได้อยู่แล้ว สิ่งที่จำไม่ได้คือของเก่าที่จมอยู่ข้างล่าง
 */
class DefaultController extends Controller
{
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

    /**
     * งานของฉัน — แบ่งเป็นกลุ่มตามความเร่งด่วน ไม่ใช่ตามวันที่สร้าง
     */
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            throw new ForbiddenHttpException('ไม่พบข้อมูลพนักงานของบัญชีนี้');
        }

        $empId = (int) $me->id;
        $unitId = $me->department ? (int) $me->department : null;

        $open = Task::find()
            ->where(['assignee_emp_id' => $empId])
            ->andWhere(['status' => Task::OPEN_STATUSES])
            ->with(['ownerUnit'])
            ->orderBy(['due_date' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        // งานที่ส่งถึงหน่วยแล้วแต่ยังไม่มีผู้รับผิดชอบ — ขึ้นเฉพาะหัวหน้าหน่วย
        $waitingAssign = [];
        if (TaskService::isUnitLeader($unitId, $empId)) {
            $waitingAssign = Task::find()
                ->where(['owner_unit_id' => TaskService::unitScopeIds($unitId)])
                ->andWhere(['assignee_emp_id' => null])
                ->andWhere(['status' => Task::OPEN_STATUSES])
                ->orderBy(['due_date' => SORT_ASC, 'id' => SORT_ASC])
                ->all();
        }

        return $this->render('index', [
            'groups' => $this->groupByUrgency($open),
            'waitingAssign' => $waitingAssign,
            'me' => $me,
            'doneToday' => (int) Task::find()
                ->where(['assignee_emp_id' => $empId, 'status' => Task::STATUS_DONE])
                ->andWhere(['>=', 'completed_at', date('Y-m-d 00:00:00')])
                ->count(),
        ]);
    }

    /**
     * จัดกลุ่มงานตามความเร่งด่วน
     *
     * "ต้องสนใจตอนนี้" คือกลุ่มเดียวที่แสดงเต็มบนหน้าแรก และจำกัดไม่เกิน 3 รายการ
     * เพื่อไม่ให้กลายเป็นรายการยาวที่ไม่มีใครอ่าน
     *
     * @param Task[] $tasks
     */
    private function groupByUrgency(array $tasks): array
    {
        $today = date('Y-m-d');
        $weekEnd = date('Y-m-d', strtotime('+7 days'));

        $attention = [];
        $todayList = [];
        $week = [];
        $later = [];

        foreach ($tasks as $task) {
            if ($this->needsAttention($task, $today)) {
                $attention[] = $task;
                continue;
            }
            if ($task->due_date && $task->due_date <= $today) {
                $todayList[] = $task;
            } elseif ($task->due_date && $task->due_date <= $weekEnd) {
                $week[] = $task;
            } else {
                $later[] = $task;
            }
        }

        return [
            'attention' => array_slice($attention, 0, 3),
            'attentionMore' => max(0, count($attention) - 3),
            'today' => $todayList,
            'week' => $week,
            'later' => $later,
        ];
    }

    /**
     * สัญญาณว่างานกำลังจะกลายเป็นงานร้อน
     *
     * ตั้งใจให้เตือนตอนที่ยังแก้ทัน ไม่ใช่เตือนตอนถึงกำหนดแล้ว
     * งานที่ติดธงรอผู้อื่นไม่นับ เพราะไม่ขยับด้วยเหตุผลที่รู้อยู่แล้ว
     */
    private function needsAttention(Task $task, string $today): bool
    {
        if ($task->is_waiting) {
            return false;
        }
        if ($task->priority === Task::PRIORITY_URGENT && $task->status === Task::STATUS_PENDING) {
            return true;
        }
        // เลยกำหนดแล้ว
        if ($task->due_date && $task->due_date < $today) {
            return true;
        }
        // ใกล้กำหนดภายใน 2 วันแต่ยังไม่เริ่ม
        if ($task->due_date && $task->status === Task::STATUS_PENDING
            && $task->due_date <= date('Y-m-d', strtotime('+2 days'))) {
            return true;
        }
        // เลื่อนมาแล้วสองครั้งขึ้นไป
        if ((int) $task->postpone_count >= 2) {
            return true;
        }
        // ไม่มีความเคลื่อนไหวเกิน 7 วัน
        if ($task->last_activity_at && strtotime($task->last_activity_at) < strtotime('-7 days')) {
            return true;
        }
        return false;
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
     * ปิดงาน — ออกแบบให้กดปุ่มเดียวจบ บันทึกเป็นตัวเลือก ไม่บังคับ
     */
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
        $task->status = Task::STATUS_DOING;
        $ok = $task->save(true, ['status', 'updated_at', 'updated_by']);
        if ($ok) {
            TaskService::log($task, \app\modules\task\models\TaskActivity::ACTION_START, null, $me ? (int) $me->id : null);
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => $ok ? 'success' : 'error'];
        }
        return $this->redirect(['index']);
    }

    /**
     * หัวหน้าหน่วยจ่ายงานที่ค้างอยู่ให้คนในหน่วยตัวเอง
     */
    public function actionAssign($id)
    {
        $task = $this->findTask($id);
        $me = UserHelper::GetEmployee();
        if (!$me) {
            throw new ForbiddenHttpException('ไม่พบข้อมูลพนักงานของบัญชีนี้');
        }

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

    private function findTask($id): Task
    {
        $task = Task::findOne((int) $id);
        if (!$task) {
            throw new NotFoundHttpException('ไม่พบงานที่ต้องการ');
        }
        return $task;
    }

    /**
     * เห็นงานได้เมื่อเป็นผู้รับผิดชอบ ผู้มอบหมาย หรืออยู่ในสายหน่วยงานเจ้าของ
     */
    private function assertCanSee(Task $task): void
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            throw new ForbiddenHttpException('ไม่พบข้อมูลพนักงานของบัญชีนี้');
        }
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

    /**
     * แก้ไข/ปิดงานได้เมื่อเป็นผู้รับผิดชอบ หรือเป็นหัวหน้าหน่วยเจ้าของงาน
     */
    private function assertCanEdit(Task $task): void
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            throw new ForbiddenHttpException('ไม่พบข้อมูลพนักงานของบัญชีนี้');
        }
        $empId = (int) $me->id;
        if ((int) $task->assignee_emp_id === $empId) {
            return;
        }
        if (TaskService::isUnitLeader((int) $task->owner_unit_id, $empId)) {
            return;
        }
        if (Yii::$app->user->can('admin')) {
            return;
        }
        throw new ForbiddenHttpException('แก้ไขได้เฉพาะผู้รับผิดชอบหรือหัวหน้าหน่วยงานเจ้าของงาน');
    }
}
