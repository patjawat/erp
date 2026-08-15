<?php

namespace app\modules\finance\services;

use app\modules\finance\models\FinanceInbox;

/**
 * Receives immutable snapshots without changing the source system.
 */
class FinanceInboxService
{
    /**
     * @throws \DomainException when the same source version already exists
     * @throws \RuntimeException when the snapshot cannot be saved
     */
    public function receive(array $source, array $payload): FinanceInbox
    {
        $sourceSystem = trim((string) ($source['source_system'] ?? ''));
        $sourceType = trim((string) ($source['source_type'] ?? ''));
        $sourceId = trim((string) ($source['source_id'] ?? ''));
        $sourceVersion = max(1, (int) ($source['source_version'] ?? 1));

        if ($sourceSystem === '' || $sourceType === '' || $sourceId === '') {
            throw new \InvalidArgumentException('ต้องระบุระบบ ประเภท และรหัสเอกสารต้นทาง');
        }

        $duplicate = FinanceInbox::find()->where([
            'source_system' => $sourceSystem,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'source_version' => $sourceVersion,
        ])->exists();
        if ($duplicate) {
            throw new \DomainException('เอกสารต้นทางรุ่นนี้ถูกส่งเข้ากล่องรับแล้ว');
        }

        $validation = $this->validatePayload($payload);
        $model = new FinanceInbox([
            'source_system' => $sourceSystem,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'source_version' => $sourceVersion,
            'source_document_no' => $source['source_document_no'] ?? null,
            'vendor_id' => $source['vendor_id'] ?? null,
            'vendor_code_snapshot' => $source['vendor_code_snapshot'] ?? null,
            'vendor_name_snapshot' => $source['vendor_name_snapshot'] ?? null,
            'document_date' => $source['document_date'] ?? null,
            'amount' => $source['amount'] ?? null,
            'status' => FinanceInbox::STATUS_PENDING_REVIEW,
            'payload_json' => $payload,
            'validation_json' => $validation ?: null,
            'received_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$model->save()) {
            throw new \RuntimeException('สร้างรายการกล่องรับไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        }

        return $model;
    }

    public function validatePayload(array $payload): array
    {
        $messages = [];
        foreach ([
            'document_no' => 'ไม่พบเลขที่เอกสาร',
            'document_date' => 'ไม่พบวันที่เอกสาร',
            'vendor' => 'ไม่พบข้อมูลผู้แทนจำหน่าย',
            'items' => 'ไม่พบรายการที่ตรวจรับ',
            'amount' => 'ไม่พบยอดเงินรวม',
        ] as $key => $message) {
            if (!array_key_exists($key, $payload) || $payload[$key] === null || $payload[$key] === '' || $payload[$key] === []) {
                $messages[] = $message;
            }
        }
        return $messages;
    }
}
