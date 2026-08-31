<?php

namespace app\modules\task\controllers;

use app\components\UserHelper;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentsDetail;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
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
 * สร้างงานจากหนังสือใน DMS
 *
 * เป้าหมายของหน้านี้คือ "กด 2 ครั้งจบ" ถ้าผู้ใช้รับค่าที่ระบบเสนอมา
 * ทุกช่องจึงถูกเติมล่วงหน้าจากตัวหนังสือ ผู้ใช้แค่ตรวจแล้วกดมอบหมาย
 */
class DocumentController extends Controller
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
                'actions' => ['create' => ['POST']],
            ],
        ];
    }

    /**
     * ฟอร์มสร้างงาน — เรียกผ่าน AJAX จากฝั่งขวาของหน้าอ่านหนังสือ
     */
    public function actionForm($id)
    {
        $document = $this->findDocument($id);
        $me = UserHelper::GetEmployee();
        if (!$me) {
            throw new ForbiddenHttpException('ไม่พบข้อมูลพนักงานของบัญชีนี้');
        }

        return $this->renderPartial('_form', [
            'document' => $document,
            'targets' => $this->suggestTargets($document, $me),
            'priority' => $this->guessPriority($document),
            'dueDate' => $this->guessDueDate($document),
            'existing' => Task::find()
                ->where(['source_module' => Task::SOURCE_DMS, 'source_id' => (string) $document->id])
                ->count(),
        ]);
    }

    /**
     * สร้างงานจริง รองรับหลายหน่วยงานในครั้งเดียว
     *
     * หนังสือฉบับเดียวส่งถึง 3 หน่วย = 3 งาน แต่ผู้ใช้กดครั้งเดียว
     * และชั้นแจ้งเตือนจะรวมเป็นข้อความเดียว ไม่เด้งเท่าจำนวนงาน
     */
    public function actionCreate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $document = $this->findDocument($id);
        $me = UserHelper::GetEmployee();
        if (!$me) {
            return ['status' => 'error', 'message' => 'ไม่พบข้อมูลพนักงานของบัญชีนี้'];
        }

        $request = Yii::$app->request;
        $title = trim((string) $request->post('title'));
        $detail = trim((string) $request->post('detail'));
        // ช่องวันที่กรอกเป็น วว/ดด/พ.ศ. ต้องแปลงกลับเป็น ค.ศ. ก่อนเก็บลงฐานข้อมูลเสมอ
        $dueDate = (string) TaskService::parseDueDate($request->post('due_date'));
        $priority = $request->post('priority') === Task::PRIORITY_URGENT ? Task::PRIORITY_URGENT : Task::PRIORITY_NORMAL;
        $units = (array) $request->post('units', []);
        $assignees = (array) $request->post('assignees', []);

        if ($title === '') {
            return ['status' => 'error', 'message' => 'กรุณาระบุชื่องาน'];
        }
        if (!$units) {
            return ['status' => 'error', 'message' => 'กรุณาเลือกหน่วยงานที่รับผิดชอบอย่างน้อยหนึ่งหน่วย'];
        }

        $rows = [];
        $blocked = [];
        foreach ($units as $unitId) {
            $unitId = (int) $unitId;
            if ($unitId <= 0) {
                continue;
            }

            // ระบุตัวคนได้เฉพาะเมื่อมีสิทธิ์ ไม่งั้นส่งถึงหน่วยแล้วให้หัวหน้าจ่ายเอง
            $assigneeId = isset($assignees[$unitId]) ? (int) $assignees[$unitId] : 0;
            if ($assigneeId > 0 && !TaskService::canAssignToUnit((int) $me->department, (int) $me->id, $unitId)) {
                $blocked[] = Organization::findOne($unitId)->name ?? ('หน่วย ' . $unitId);
                $assigneeId = 0;
            }

            $rows[] = [
                'title' => $title,
                'detail' => $detail !== '' ? $detail : null,
                'owner_unit_id' => $unitId,
                'assignee_emp_id' => $assigneeId > 0 ? $assigneeId : null,
                'due_date' => $dueDate !== '' ? $dueDate : null,
                'priority' => $priority,
            ];
        }

        $created = TaskService::createBatch(Task::SOURCE_DMS, $document->id, $rows, (int) $me->id);

        if (!$created) {
            return ['status' => 'error', 'message' => 'สร้างงานไม่สำเร็จ'];
        }

        $message = sprintf('สร้างงานแล้ว %d รายการ', count($created));
        if ($blocked) {
            $message .= ' — หน่วย ' . implode(', ', $blocked) . ' ส่งถึงหน่วยงานแทน เพราะไม่มีสิทธิ์ระบุตัวผู้รับผิดชอบข้ามหน่วย';
        }

        return ['status' => 'success', 'message' => $message, 'count' => count($created)];
    }

    /**
     * เสนอหน่วยงานผู้รับผิดชอบจากสายส่งหนังสือ
     *
     * documents_detail แถวที่ name='department' คือหน่วยที่หนังสือถูกส่งถึง
     * (to_id ตรงกับ tree.id 99.97% จากข้อมูลจริง 10,696 แถว)
     * ผู้รับผิดชอบเริ่มต้นคือหัวหน้าหน่วยนั้น อ่านจาก tree.data_json.leader1
     */
    private function suggestTargets(Documents $document, Employees $me): array
    {
        $unitIds = DocumentsDetail::find()
            ->select('to_id')
            ->where(['document_id' => $document->id, 'name' => 'department'])
            ->distinct()
            ->column();

        $unitIds = array_values(array_filter(array_map('intval', $unitIds)));

        // หนังสือที่ไม่มีสายส่ง ให้เสนอหน่วยของผู้ใช้เอง
        if (!$unitIds && $me->department) {
            $unitIds = [(int) $me->department];
        }

        $canCrossUnit = Yii::$app->user->can(TaskService::PERMISSION_CROSS_UNIT);
        $myScope = TaskService::unitScopeIds($me->department ? (int) $me->department : null);

        $targets = [];
        foreach ($unitIds as $unitId) {
            $unit = Organization::findOne($unitId);
            if (!$unit) {
                continue;
            }

            $members = Employees::find()
                ->where(['department' => $unitId])
                ->orderBy(['fname' => SORT_ASC])
                ->all();

            $targets[] = [
                'id' => $unitId,
                'name' => $unit->name,
                'leaderEmpId' => $this->unitLeaderId($unit),
                'members' => $members,
                // เลือกตัวบุคคลได้เฉพาะหน่วยในสายตัวเอง หรือเมื่อมีสิทธิ์ข้ามหน่วย
                'canPickPerson' => $canCrossUnit || in_array($unitId, $myScope, true),
            ];
        }

        return $targets;
    }

    private function unitLeaderId(Organization $unit): ?int
    {
        $dataJson = $unit->data_json;
        if (is_string($dataJson)) {
            $decoded = json_decode($dataJson, true);
            $dataJson = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($dataJson)) {
            return null;
        }
        return isset($dataJson['leader1']) && is_numeric($dataJson['leader1']) ? (int) $dataJson['leader1'] : null;
    }

    /**
     * แปลงชั้นความเร็วของหนังสือเป็นระดับความสำคัญ
     *
     * ข้อมูลจริงมีแค่สองขั้ว ปกติ 91% กับ ด่วนที่สุด 8%
     * ระดับกลางแทบไม่ถูกใช้ จึงแปลงเป็นสองระดับพอ
     */
    private function guessPriority(Documents $document): string
    {
        $speed = trim((string) $document->doc_speed);
        $urgent = ['ด่วน', 'ด่วนมาก', 'ด่วนที่สุด'];
        return in_array($speed, $urgent, true) ? Task::PRIORITY_URGENT : Task::PRIORITY_NORMAL;
    }

    /**
     * เสนอกำหนดเสร็จจากชั้นความเร็วของหนังสือ
     *
     * เคยใช้ doc_expire แต่ตรวจข้อมูลจริงแล้วพบว่าไม่ใช่กำหนดส่งงาน
     * แต่เป็นวันสิ้นอายุการเก็บเอกสาร — 4,529 จาก 4,981 ฉบับ (91%)
     * เป็นวันที่ 1 มกราคมของปีถัดไป เอามาเป็นกำหนดเสร็จไม่ได้
     *
     * จึงเดาจากชั้นความเร็วแทน ซึ่งเป็นข้อมูลที่สะท้อนความเร่งด่วนจริง
     * ผู้ใช้แก้ได้ด้วยปุ่มลัดในฟอร์ม
     */
    private function guessDueDate(Documents $document): ?string
    {
        $date = $this->guessPriority($document) === Task::PRIORITY_URGENT
            ? date('Y-m-d', strtotime('+1 day'))
            : date('Y-m-d', strtotime('+7 days'));

        // ส่งเป็น วว/ดด/พ.ศ. เพราะช่องกรอกใช้ DatepickerThai
        return \app\components\AppHelper::convertToThai($date);
    }

    private function findDocument($id): Documents
    {
        $document = Documents::findOne((int) $id);
        if (!$document) {
            throw new NotFoundHttpException('ไม่พบหนังสือที่ต้องการ');
        }
        return $document;
    }
}
