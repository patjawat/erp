<?php

namespace app\modules\medsop\services;

use app\modules\medsop\models\DocumentAssignment;
use app\modules\medsop\models\DocumentReadLog;
use Yii;

class DocumentReadTrackingService
{
    public function recordOpen(DocumentAssignment $assignment, int $employeeId): void
    {
        $this->assertOwner($assignment, $employeeId);
        $now = date('Y-m-d H:i:s');
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($assignment->first_opened_at === null) {
                $assignment->first_opened_at = $now;
            }
            $assignment->last_opened_at = $now;
            $assignment->open_count = (int) $assignment->open_count + 1;
            if ($assignment->status === DocumentAssignment::STATUS_UNREAD) {
                $assignment->status = DocumentAssignment::STATUS_READ;
            }
            if (!$assignment->save()) {
                throw new \RuntimeException('ไม่สามารถบันทึกเวลาเปิดอ่านเอกสารได้');
            }
            $this->writeRequestLog($assignment, DocumentReadLog::EVENT_OPENED);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function acknowledge(DocumentAssignment $assignment, int $employeeId): void
    {
        $this->assertOwner($assignment, $employeeId);
        if ($assignment->first_opened_at === null) {
            throw new \DomainException('กรุณาเปิดอ่านเอกสารก่อนยืนยันรับทราบ');
        }
        if ($assignment->status === DocumentAssignment::STATUS_ACKNOWLEDGED) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $request = Yii::$app->request;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $assignment->status = DocumentAssignment::STATUS_ACKNOWLEDGED;
            $assignment->acknowledged_at = $now;
            $assignment->acknowledged_by = Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id;
            $assignment->acknowledged_ip = $request->userIP;
            $assignment->acknowledged_user_agent = mb_substr((string) $request->userAgent, 0, 500);
            if (!$assignment->save()) {
                throw new \RuntimeException('ไม่สามารถบันทึกการรับทราบเอกสารได้');
            }
            $this->writeRequestLog($assignment, DocumentReadLog::EVENT_ACKNOWLEDGED);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function assertOwner(DocumentAssignment $assignment, int $employeeId): void
    {
        if ((int) $assignment->employee_id !== $employeeId) {
            throw new \DomainException('ไม่มีสิทธิ์บันทึกการอ่านเอกสารรายการนี้');
        }
    }

    private function writeRequestLog(DocumentAssignment $assignment, string $event): void
    {
        $request = Yii::$app->request;
        $log = new DocumentReadLog([
            'assignment_id' => (int) $assignment->id,
            'document_id' => (int) $assignment->document_id,
            'revision_no' => (int) $assignment->revision_no,
            'employee_id' => (int) $assignment->employee_id,
            'user_id' => Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id,
            'event_type' => $event,
            'event_at' => date('Y-m-d H:i:s'),
            'ip_address' => $request->userIP,
            'user_agent' => mb_substr((string) $request->userAgent, 0, 500),
        ]);
        if (!$log->save()) {
            throw new \RuntimeException('ไม่สามารถบันทึกประวัติการอ่านเอกสารได้');
        }
    }
}
