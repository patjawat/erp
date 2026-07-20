<?php

namespace app\modules\medsop\services;

use app\modules\approveV2\models\Approve;
use app\modules\medsop\models\Document;
use Yii;

class ApprovalIntegrationService
{
    public const APPROVE_NAME = 'medsop';

    public function rows(Document $document): array
    {
        return Approve::find()
            ->where(['name' => self::APPROVE_NAME, 'from_id' => (string) $document->id])
            ->orderBy(['level' => SORT_ASC])
            ->all();
    }

    public function start(Document $document, array $approverEmployeeIds): void
    {
        if (!$document->isEditable()) {
            throw new \DomainException('เอกสารสถานะนี้ไม่สามารถส่งอนุมัติได้');
        }
        $approverEmployeeIds = array_values(array_unique(array_filter(array_map('intval', $approverEmployeeIds))));
        if ($approverEmployeeIds === []) {
            throw new \DomainException('ยังไม่ได้กำหนดผู้อนุมัติ');
        }

        Approve::deleteAll(['name' => self::APPROVE_NAME, 'from_id' => (string) $document->id]);
        foreach ($approverEmployeeIds as $index => $employeeId) {
            $row = new Approve([
                'name' => self::APPROVE_NAME,
                'from_id' => (string) $document->id,
                'title' => $document->document_no . ' ' . $document->title,
                'emp_id' => $employeeId,
                'level' => $index + 1,
                'status' => $index === 0 ? 'Pending' : 'None',
                'data_json' => [
                    'label' => 'อนุมัติเอกสาร SOP/WI',
                    'document_no' => $document->document_no,
                    'revision_no' => $document->current_revision,
                ],
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Yii::$app->user->isGuest ? null : Yii::$app->user->id,
            ]);
            if (!$row->save()) {
                throw new \RuntimeException('ไม่สามารถสร้างรายการอนุมัติได้');
            }
        }
    }

    public function resolveDocumentStatus(Document $document): string
    {
        $rows = $this->rows($document);
        if ($rows === []) {
            return $document->status;
        }
        foreach ($rows as $row) {
            if ($row->status === 'Reject') {
                return Document::STATUS_REJECTED;
            }
            if ($row->status !== 'Pass') {
                return Document::STATUS_PENDING;
            }
        }
        return Document::STATUS_PUBLISHED;
    }
}
