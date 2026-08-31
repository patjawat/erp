<?php

namespace app\modules\task\services;

use app\components\AppHelper;
use app\modules\hr\models\Organization;
use app\modules\notify\models\Notify;
use app\modules\task\models\Task;
use app\modules\task\models\TaskActivity;
use Yii;
use yii\db\Query;

/**
 * ทางเข้าเดียวสำหรับการสร้างและเปลี่ยนสถานะงาน
 *
 * โมดูลอื่น (dms, pm) ต้องเรียกผ่านคลาสนี้เท่านั้น ห้ามเขียนตาราง task ตรง ๆ
 * เพื่อให้ทุกงานมีความเคลื่อนไหวบันทึกไว้ และอ้างกลับไปหาต้นเรื่องได้เสมอ
 */
class TaskService
{
    public const PERMISSION_CROSS_UNIT = 'taskAssignCrossUnit';

    /**
     * สร้างงานจากต้นเรื่องในโมดูลอื่น
     *
     * ตัวอย่างการเรียกจาก DMS:
     *   TaskService::createFromSource(Task::SOURCE_DMS, $document->id, [
     *       'title'           => $document->topic,
     *       'owner_unit_id'   => $departmentId,
     *       'assignee_emp_id' => $empId,        // ว่างได้ ถ้าส่งถึงหน่วยเฉย ๆ
     *       'due_date'        => '2026-09-05',
     *       'priority'        => Task::PRIORITY_URGENT,
     *   ], $actorEmpId);
     *
     * @return Task|null คืน null เมื่อบันทึกไม่ผ่าน (อ่านสาเหตุจาก $task->errors ที่ log ไว้)
     */
    public static function createFromSource(string $sourceModule, $sourceId, array $data, ?int $actorEmpId = null): ?Task
    {
        $data['source_module'] = $sourceModule;
        $data['source_id'] = $sourceId === null ? null : (string) $sourceId;

        return self::create($data, $actorEmpId);
    }

    /**
     * สร้างงานหนึ่งชิ้น พร้อมบันทึกความเคลื่อนไหวแรก
     *
     * @param bool $deferTelegram ให้ผู้เรียกส่ง Telegram เองทีหลัง
     *                            ใช้ตอนสร้างหลายงานพร้อมกัน จะได้รวมเป็นข้อความเดียว
     */
    public static function create(array $data, ?int $actorEmpId = null, bool $deferTelegram = false): ?Task
    {
        $task = new Task();
        $task->source_module = $data['source_module'] ?? Task::SOURCE_MANUAL;
        $task->load($data, '');

        if ($actorEmpId !== null && $task->assigner_emp_id === null) {
            $task->assigner_emp_id = $actorEmpId;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$task->save()) {
                $transaction->rollBack();
                Yii::error(['สร้างงานไม่สำเร็จ' => $task->getErrors()], __METHOD__);
                return null;
            }

            $note = $task->assignee_emp_id === null
                ? 'ส่งงานถึงหน่วยงาน ยังไม่ระบุผู้รับผิดชอบ'
                : null;
            self::log($task, TaskActivity::ACTION_CREATE, $note, $actorEmpId, false);

            $transaction->commit();
            self::notifyAssignee($task, $actorEmpId, $deferTelegram);
            return $task;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * สร้างหลายงานจากต้นเรื่องเดียว เช่น หนังสือส่งถึง 3 หน่วยงาน
     *
     * ตั้งใจให้เรียกครั้งเดียวเพื่อให้ชั้นแจ้งเตือนรวมเป็นข้อความเดียวได้
     * ไม่ใช่เด้งเตือนเท่าจำนวนงาน
     *
     * @param array $rows แต่ละแถวคือ $data ของงานหนึ่งชิ้น
     * @return Task[]
     */
    public static function createBatch(string $sourceModule, $sourceId, array $rows, ?int $actorEmpId = null): array
    {
        $created = [];
        foreach ($rows as $row) {
            $row['source_module'] = $sourceModule;
            $row['source_id'] = $sourceId === null ? null : (string) $sourceId;
            // พัก Telegram ไว้ก่อน แล้วส่งรวมทีเดียวตอนท้าย
            $task = self::create($row, $actorEmpId, true);
            if ($task !== null) {
                $created[] = $task;
            }
        }

        TaskTelegramService::notifyBatch($created, $actorEmpId);
        return $created;
    }

    /**
     * จ่ายงานให้ผู้รับผิดชอบ หรือเปลี่ยนตัวผู้รับผิดชอบ
     */
    public static function assign(Task $task, ?int $empId, ?int $actorEmpId = null, ?string $note = null): bool
    {
        $wasAssigned = $task->assignee_emp_id !== null;
        $task->assignee_emp_id = $empId;

        if (!$task->save(true, ['assignee_emp_id', 'updated_at', 'updated_by'])) {
            Yii::error(['จ่ายงานไม่สำเร็จ' => $task->getErrors()], __METHOD__);
            return false;
        }

        self::log(
            $task,
            $wasAssigned ? TaskActivity::ACTION_REASSIGN : TaskActivity::ACTION_ASSIGN,
            $note,
            $actorEmpId
        );
        self::notifyAssignee($task, $actorEmpId);
        return true;
    }

    /**
     * แจ้งผู้รับผิดชอบ
     *
     * กระดิ่งในระบบได้ทุกงาน ส่วน Telegram เด้งเฉพาะงานที่ต้องรู้เดี๋ยวนี้
     * เพราะคำสัญญาของระบบนี้คือทำให้ถูกรบกวนน้อยลง ไม่ใช่มากขึ้น
     * และไม่ให้การแจ้งเตือนล้มเหลวไปทำให้การบันทึกงานพัง
     */
    private static function notifyAssignee(Task $task, ?int $actorEmpId, bool $deferTelegram = false): void
    {
        if (!$task->assignee_emp_id) {
            return;
        }
        try {
            // กระดิ่งได้ทุกงาน หนึ่งงานหนึ่งรายการ ไม่รวมกัน
            Notify::createForTaskAssigned($task, $actorEmpId);
        } catch (\Throwable $e) {
            Yii::warning('แจ้งเตือนงานไม่สำเร็จ: ' . $e->getMessage(), __METHOD__);
        }

        if (!$deferTelegram) {
            TaskTelegramService::notifyAssigned($task, $actorEmpId);
        }
    }

    /**
     * ปิดงาน — ออกแบบให้เรียกได้จากปุ่มเดียว บันทึกเป็นตัวเลือก ไม่บังคับกรอก
     *
     * กดซ้ำแล้วไม่บันทึกซ้ำ เพราะผู้ใช้กดรัวได้ง่ายบนมือถือ
     * และประวัติที่มีรายการ "ปิดงาน" ซ้ำ ๆ ทำให้อ่านไม่รู้เรื่อง
     */
    public static function complete(Task $task, ?int $actorEmpId = null, ?string $note = null): bool
    {
        if ($task->status === Task::STATUS_DONE) {
            return true;
        }

        $task->status = Task::STATUS_DONE;
        $task->is_waiting = false;
        $task->completed_at = date('Y-m-d H:i:s');
        $task->completed_by = $actorEmpId;

        if (!$task->save(true, ['status', 'is_waiting', 'completed_at', 'completed_by', 'updated_at', 'updated_by'])) {
            Yii::error(['ปิดงานไม่สำเร็จ' => $task->getErrors()], __METHOD__);
            return false;
        }

        self::log($task, TaskActivity::ACTION_COMPLETE, $note, $actorEmpId);
        return true;
    }

    /**
     * เลื่อนกำหนดส่ง — นับจำนวนครั้งไว้ เพราะงานที่เลื่อนซ้ำมักกลายเป็นงานร้อน
     */
    public static function postpone(Task $task, string $newDueDate, ?int $actorEmpId = null, ?string $note = null): bool
    {
        $oldDue = $task->due_date;
        $task->due_date = $newDueDate;
        $task->postpone_count = (int) $task->postpone_count + 1;

        if (!$task->save(true, ['due_date', 'postpone_count', 'updated_at', 'updated_by'])) {
            Yii::error(['เลื่อนกำหนดไม่สำเร็จ' => $task->getErrors()], __METHOD__);
            return false;
        }

        $detail = trim(sprintf('เลื่อนจาก %s เป็น %s. %s', $oldDue ?: '-', $newDueDate, (string) $note));
        self::log($task, TaskActivity::ACTION_POSTPONE, $detail, $actorEmpId);
        return true;
    }

    /**
     * บันทึกความเคลื่อนไหว และอัปเดต last_activity_at ของงาน
     *
     * @param bool $touchTask ตั้งเป็น false ตอนสร้างงานใหม่ เพราะยังอยู่ใน transaction เดียวกัน
     */
    public static function log(Task $task, string $action, ?string $note = null, ?int $empId = null, bool $touchTask = true): ?TaskActivity
    {
        $activity = new TaskActivity();
        $activity->task_id = $task->id;
        $activity->emp_id = $empId;
        $activity->action = $action;
        $activity->note = ($note !== null && trim($note) !== '') ? trim($note) : null;

        if (!$activity->save()) {
            Yii::error(['บันทึกความเคลื่อนไหวไม่สำเร็จ' => $activity->getErrors()], __METHOD__);
            return null;
        }

        $task->last_activity_at = $activity->created_at;
        if ($touchTask) {
            $task->updateAttributes(['last_activity_at' => $task->last_activity_at]);
        } else {
            Task::updateAll(['last_activity_at' => $task->last_activity_at], ['id' => $task->id]);
        }

        return $activity;
    }

    /**
     * แปลงวันที่จากฟอร์มเป็น ค.ศ. รูปแบบ Y-m-d
     *
     * ปกติช่องกรอกใช้ DatepickerThai จึงได้ วว/ดด/พ.ศ. มา
     * แต่รับ Y-m-d ไว้ด้วย เผื่อค่ามาจากหน้าที่ค้างใน cache หรือผู้เรียกอื่น
     * ถ้าไม่รับไว้ AppHelper::convertToGregorian จะคืน null แล้วกำหนดเสร็จถูกล้างทิ้งเงียบ ๆ
     */
    public static function parseDueDate(?string $input): ?string
    {
        $input = trim((string) $input);
        if ($input === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $input)) {
            $ts = strtotime($input);
            return $ts ? date('Y-m-d', $ts) : null;
        }

        $converted = AppHelper::convertToGregorian($input);
        if (!$converted) {
            return null;
        }
        $ts = strtotime($converted);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    // ------------------------------------------------------------------
    // สิทธิ์การมอบหมาย
    // ------------------------------------------------------------------

    /**
     * คืนรายการ id หน่วยงานในสายของหน่วยที่ระบุ (ตัวเอง + ลูกทุกชั้น)
     *
     * ใช้ nested set (lft / rgt / root) ของตาราง tree
     * หัวหน้ากลุ่มระดับบนจึงมอบหมายให้คนในกลุ่มงานย่อยใต้ตัวเองได้ โดยไม่นับว่าข้ามหน่วย
     *
     * @return int[]
     */
    public static function unitScopeIds(?int $unitId): array
    {
        if (!$unitId) {
            return [];
        }

        $node = (new Query())
            ->select(['root', 'lft', 'rgt'])
            ->from(Organization::tableName())
            ->where(['id' => $unitId])
            ->one();

        if (!$node) {
            return [(int) $unitId];
        }

        $ids = (new Query())
            ->select('id')
            ->from(Organization::tableName())
            ->where(['root' => $node['root']])
            ->andWhere(['between', 'lft', (int) $node['lft'], (int) $node['rgt']])
            ->column();

        return array_map('intval', $ids ?: [(int) $unitId]);
    }

    /**
     * เป็นหัวหน้าหรือรองหัวหน้าของหน่วยนี้หรือไม่
     *
     * หัวหน้าเก็บไว้ใน tree.data_json (leader1 / leader2) ไม่ใช่คอลัมน์ tree.leader ซึ่งว่างทั้งตาราง
     * ใช้กติกาเดียวกับที่ DMS ใช้อยู่แล้วใน DocumentsController::isDeptHeadOrDeputy()
     */
    public static function isUnitLeader(?int $unitId, ?int $empId): bool
    {
        if (!$unitId || !$empId) {
            return false;
        }

        $org = Organization::findOne((int) $unitId);
        if (!$org) {
            return false;
        }

        $dataJson = $org->data_json;
        if (is_string($dataJson)) {
            $decoded = json_decode($dataJson, true);
            $dataJson = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($dataJson)) {
            $dataJson = [];
        }

        $leaders = [];
        foreach (['leader1', 'leader2'] as $key) {
            if (isset($dataJson[$key]) && is_numeric($dataJson[$key])) {
                $leaders[] = (int) $dataJson[$key];
            }
        }

        return in_array((int) $empId, $leaders, true);
    }

    /**
     * มอบหมายงานให้หน่วยงานนี้แบบระบุตัวบุคคลได้หรือไม่
     *
     * กติกา (ตกลงกับผู้ใช้ 2026-08-29):
     * - ในสายหน่วยงานตัวเอง ทุกคนทำได้ ไม่ต้องมีสิทธิ์พิเศษ
     * - ข้ามสาย ต้องมีสิทธิ์ taskAssignCrossUnit หรือเป็นหัวหน้าหน่วยปลายทาง
     * - ส่วนการส่งงานถึง "หน่วยงาน" เฉย ๆ โดยไม่ระบุตัวคน ไม่ต้องผ่านการตรวจนี้
     */
    public static function canAssignToUnit(?int $actorUnitId, ?int $actorEmpId, int $targetUnitId): bool
    {
        if (in_array($targetUnitId, self::unitScopeIds($actorUnitId), true)) {
            return true;
        }

        if (self::isUnitLeader($targetUnitId, $actorEmpId)) {
            return true;
        }

        try {
            return Yii::$app->user->can(self::PERMISSION_CROSS_UNIT);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
