<?php

namespace app\modules\laundry\services;

use app\modules\am\models\Asset;
use Yii;
use yii\base\InvalidArgumentException;
use yii\db\Connection;

/** Machine-cycle ledger: collection kg and wet post-wash kg are distinct measures. */
class ProcessingService
{
    private $db;

    public function __construct(?Connection $db = null)
    {
        $this->db = $db ?: Yii::$app->db;
    }

    public function registerMachine(int $assetId, string $type, $capacityKg, ?int $userId): int
    {
        if (!in_array($type, ['WASH', 'DRY'], true)) {
            throw new InvalidArgumentException('ประเภทเครื่องไม่ถูกต้อง');
        }
        $capacity = CollectionService::netKg($capacityKg, '0');
        $asset = Asset::findOne($assetId);
        if (!$asset || in_array($asset->lifecycle_status, [Asset::LIFECYCLE_REPAIR, Asset::LIFECYCLE_DISPOSED], true)) {
            throw new InvalidArgumentException('ครุภัณฑ์ไม่พร้อมใช้งานหรือไม่พบข้อมูล');
        }
        $this->db->createCommand()->insert('laundry_machine', [
            'asset_id' => $assetId, 'machine_type' => $type, 'capacity_kg' => $capacity,
            'is_active' => 1, 'created_at' => date('Y-m-d H:i:s'), 'created_by' => $userId,
        ])->execute();
        return (int) $this->db->getLastInsertID();
    }

    /** @param array<int,string|float|int> $sources source ID => allocated kilograms */
    public function start(string $stage, int $assetId, string $linenClass, array $sources, ?string $program, ?int $operatorId, string $sourceMode = 'NORMAL'): int
    {
        if (!in_array($stage, ['WASH', 'DRY'], true) || !in_array($linenClass, ['SOILED', 'INFECTIOUS'], true)
            || !in_array($sourceMode, ['NORMAL', 'RECOVERY'], true)) {
            throw new InvalidArgumentException('ขั้นตอนหรือประเภทผ้าไม่ถูกต้อง');
        }
        if (!$sources) {
            throw new InvalidArgumentException('ต้องเลือกแหล่งผ้าก่อนเริ่มรอบ');
        }
        $tx = $this->db->beginTransaction();
        try {
            $machine = $this->db->createCommand('SELECT * FROM {{%laundry_machine}} WHERE asset_id = :id FOR UPDATE', [':id' => $assetId])->queryOne();
            $asset = $this->db->createCommand('SELECT id, lifecycle_status FROM {{%asset}} WHERE id = :id FOR UPDATE', [':id' => $assetId])->queryOne();
            if (!$machine || !$asset || !(int) $machine['is_active'] || $machine['machine_type'] !== $stage
                || in_array($asset['lifecycle_status'], [Asset::LIFECYCLE_REPAIR, Asset::LIFECYCLE_DISPOSED], true)) {
                throw new InvalidArgumentException('เครื่องไม่พร้อมใช้งานหรือชนิดเครื่องไม่ตรงกับรอบ');
            }
            $running = (int) $this->db->createCommand('SELECT COUNT(*) FROM {{%laundry_processing_batch}} WHERE asset_id = :id AND status = :status', [':id' => $assetId, ':status' => 'RUNNING'])->queryScalar();
            if ($running) {
                throw new InvalidArgumentException('เครื่องนี้กำลังทำงานในรอบอื่น');
            }

            $sourceType = $sourceMode === 'RECOVERY' ? 'RECOVERY'
                : ($stage === 'WASH' ? 'COLLECTION_WEIGHT' : 'WASH_BATCH');
            $total = 0.0;
            $allocations = [];
            ksort($sources, SORT_NUMERIC); // deterministic lock order for concurrent allocations
            foreach ($sources as $sourceId => $kg) {
                $sourceId = (int) $sourceId;
                if ($sourceId <= 0) {
                    throw new InvalidArgumentException('แหล่งผ้าไม่ถูกต้อง');
                }
                $amount = CollectionService::netKg($kg, '0');
                $available = $this->sourceKg($sourceType, $sourceId, $linenClass, $stage);
                $used = (float) $this->db->createCommand(
                    'SELECT COALESCE(SUM(i.allocated_kg), 0) FROM {{%laundry_batch_input}} i WHERE i.source_type = :type AND i.source_id = :id',
                    [':type' => $sourceType, ':id' => $sourceId]
                )->queryScalar();
                if (round($used + (float) $amount, 3) > round($available, 3)) {
                    throw new InvalidArgumentException('น้ำหนักที่เลือกเกินน้ำหนักคงเหลือของแหล่งผ้า');
                }
                $total += (float) $amount;
                $allocations[] = [$sourceId, $amount];
            }
            if (round($total, 3) > (float) $machine['capacity_kg']) {
                throw new InvalidArgumentException('น้ำหนักเกินกำลังเครื่อง');
            }
            $now = date('Y-m-d H:i:s');
            $number = 'LB-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $this->db->createCommand()->insert('laundry_processing_batch', [
                'batch_no' => $number, 'stage' => $stage, 'linen_class' => $linenClass,
                'asset_id' => $assetId, 'status' => 'RUNNING', 'program' => $program,
                'input_kg' => number_format($total, 3, '.', ''), 'started_at' => $now,
                'operator_id' => $operatorId, 'created_at' => $now,
            ])->execute();
            $batchId = (int) $this->db->getLastInsertID();
            foreach ($allocations as [$id, $amount]) {
                $this->db->createCommand()->insert('laundry_batch_input', [
                    'batch_id' => $batchId, 'source_type' => $sourceType,
                    'source_id' => $id, 'allocated_kg' => $amount,
                ])->execute();
            }
            $tx->commit();
            return $batchId;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    /** เริ่มรอบแบบง่าย: เครื่อง + กลุ่มผ้า + น้ำหนัก (ไม่ผูกแหล่งผ้า/รอบเก็บ) */
    public function startSimple(string $stage, int $assetId, string $linenClass, $inputKg, ?string $program, ?int $operatorId): int
    {
        if (!in_array($stage, ['WASH', 'DRY'], true) || !in_array($linenClass, ['SOILED', 'INFECTIOUS'], true)) {
            throw new InvalidArgumentException('ขั้นตอนหรือประเภทผ้าไม่ถูกต้อง');
        }
        $input = CollectionService::netKg($inputKg, '0');
        $tx = $this->db->beginTransaction();
        try {
            $machine = $this->db->createCommand('SELECT * FROM {{%laundry_machine}} WHERE asset_id = :id FOR UPDATE', [':id' => $assetId])->queryOne();
            $asset = $this->db->createCommand('SELECT id, lifecycle_status FROM {{%asset}} WHERE id = :id FOR UPDATE', [':id' => $assetId])->queryOne();
            if (!$machine || !$asset || !(int) $machine['is_active'] || $machine['machine_type'] !== $stage
                || in_array($asset['lifecycle_status'], [Asset::LIFECYCLE_REPAIR, Asset::LIFECYCLE_DISPOSED], true)) {
                throw new InvalidArgumentException('เครื่องไม่พร้อมใช้งานหรือชนิดเครื่องไม่ตรงกับรอบ');
            }
            $running = (int) $this->db->createCommand('SELECT COUNT(*) FROM {{%laundry_processing_batch}} WHERE asset_id = :id AND status = :status', [':id' => $assetId, ':status' => 'RUNNING'])->queryScalar();
            if ($running) {
                throw new InvalidArgumentException('เครื่องนี้กำลังทำงานในรอบอื่น');
            }
            if (round((float) $input, 3) > (float) $machine['capacity_kg']) {
                throw new InvalidArgumentException('น้ำหนักเกินกำลังเครื่อง (' . number_format((float) $machine['capacity_kg'], 1) . ' กก.)');
            }
            $now = date('Y-m-d H:i:s');
            $number = 'LB-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $this->db->createCommand()->insert('laundry_processing_batch', [
                'batch_no' => $number, 'stage' => $stage, 'linen_class' => $linenClass,
                'asset_id' => $assetId, 'status' => 'RUNNING', 'program' => $program,
                'input_kg' => $input, 'started_at' => $now,
                'operator_id' => $operatorId, 'created_at' => $now,
            ])->execute();
            $batchId = (int) $this->db->getLastInsertID();
            $tx->commit();
            return $batchId;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    /**
     * ปิดรอบเครื่อง — น้ำหนักออกไม่บังคับ (หน้างานชั่งเฉพาะตอนเข้าเครื่อง ไม่ชั่งตอนออก)
     * ว่าง = ไม่ได้ชั่ง (output_kg = NULL); ขั้นถัดไปที่ต้องใช้น้ำหนักจะใช้น้ำหนักเข้าแทน
     */
    public function finish(int $batchId, $outputKg = null): void
    {
        $output = trim((string) $outputKg) === '' ? null : CollectionService::netKg($outputKg, '0');
        $tx = $this->db->beginTransaction();
        try {
            $batch = $this->lockedBatch($batchId);
            if ($batch['status'] !== 'RUNNING') {
                throw new InvalidArgumentException('รอบนี้ไม่ได้อยู่ระหว่างทำงาน');
            }
            $this->db->createCommand()->update('laundry_processing_batch', [
                'output_kg' => $output, 'ended_at' => date('Y-m-d H:i:s'), 'status' => 'COMPLETED',
            ], ['id' => $batchId, 'status' => 'RUNNING'])->execute();
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function abort(int $batchId, string $reason): void
    {
        if (mb_strlen(trim($reason)) < 5) {
            throw new InvalidArgumentException('กรุณาระบุเหตุหยุดรอบอย่างน้อย 5 ตัวอักษร');
        }
        $tx = $this->db->beginTransaction();
        try {
            $batch = $this->lockedBatch($batchId);
            if ($batch['status'] !== 'RUNNING') {
                throw new InvalidArgumentException('รอบนี้ไม่ได้อยู่ระหว่างทำงาน');
            }
            // Keep source allocations reserved: an aborted load may be damaged or need reprocessing.
            // Releasing it automatically would let the same kilograms be counted twice.
            $this->db->createCommand()->update('laundry_processing_batch', [
                'status' => 'ABORTED', 'ended_at' => date('Y-m-d H:i:s'), 'note' => trim($reason),
            ], ['id' => $batchId, 'status' => 'RUNNING'])->execute();
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function requestRecovery(int $abortedBatchId, string $outcome, $measuredKg, string $evidence, int $userId): int
    {
        if (!in_array($outcome, ['REPROCESS', 'DISCARD'], true) || mb_strlen(trim($evidence)) < 5) {
            throw new InvalidArgumentException('กรุณาระบุผลตรวจผ้าและหลักฐาน');
        }
        $measured = $outcome === 'REPROCESS' ? CollectionService::netKg($measuredKg, '0') : '0.000';
        $tx = $this->db->beginTransaction();
        try {
            $batch = $this->lockedBatch($abortedBatchId);
            if ($batch['status'] !== 'ABORTED') {
                throw new InvalidArgumentException('บันทึกการกู้ผ้าได้เฉพาะรอบที่หยุดกลางคัน');
            }
            $this->db->createCommand()->insert('laundry_batch_recovery', [
                'aborted_batch_id' => $abortedBatchId, 'outcome' => $outcome,
                'measured_kg' => $measured, 'evidence' => trim($evidence),
                'status' => 'PENDING', 'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $userId,
            ])->execute();
            $id = (int) $this->db->getLastInsertID();
            $tx->commit();
            return $id;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function approveRecovery(int $recoveryId, int $approverId): void
    {
        $tx = $this->db->beginTransaction();
        try {
            $recovery = $this->db->createCommand('SELECT * FROM {{%laundry_batch_recovery}} WHERE id = :id FOR UPDATE', [':id' => $recoveryId])->queryOne();
            if (!$recovery || $recovery['status'] !== 'PENDING' || (int) $recovery['created_by'] === $approverId) {
                throw new InvalidArgumentException('รายการกู้ผ้าไม่พร้อมอนุมัติหรือผู้อนุมัติเป็นผู้บันทึก');
            }
            $this->db->createCommand()->update('laundry_batch_recovery', [
                'status' => 'APPROVED', 'approved_at' => date('Y-m-d H:i:s'),
                'approved_by' => $approverId,
            ], ['id' => $recoveryId, 'status' => 'PENDING'])->execute();
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    private function sourceKg(string $type, int $id, string $class, string $stage): float
    {
        if ($type === 'RECOVERY') {
            $row = $this->db->createCommand(
                'SELECT r.measured_kg, r.outcome, r.status, b.stage, b.linen_class FROM {{%laundry_batch_recovery}} r JOIN {{%laundry_processing_batch}} b ON b.id = r.aborted_batch_id WHERE r.id = :id FOR UPDATE',
                [':id' => $id]
            )->queryOne();
            if (!$row || $row['status'] !== 'APPROVED' || $row['outcome'] !== 'REPROCESS'
                || $row['stage'] !== $stage || $row['linen_class'] !== $class) {
                throw new InvalidArgumentException('ผ้าจากรอบหยุดยังไม่อนุมัติหรือชนิดรอบไม่ตรง');
            }
            return (float) $row['measured_kg'];
        }
        if ($type === 'COLLECTION_WEIGHT') {
            $row = $this->db->createCommand(
                'SELECT w.net_kg, w.linen_class, r.status FROM {{%laundry_collection_weight}} w JOIN {{%laundry_collection_stop}} s ON s.id = w.stop_id JOIN {{%laundry_collection_round}} r ON r.id = s.round_id WHERE w.id = :id FOR UPDATE',
                [':id' => $id]
            )->queryOne();
            if (!$row || $row['status'] !== 'CONFIRMED' || $row['linen_class'] !== $class) {
                throw new InvalidArgumentException('น้ำหนักรับผ้ายังไม่ยืนยันหรือประเภทไม่ตรง');
            }
            return (float) $row['net_kg'];
        }
        $row = $this->db->createCommand(
            'SELECT input_kg, output_kg, linen_class, stage, status FROM {{%laundry_processing_batch}} WHERE id = :id FOR UPDATE',
            [':id' => $id]
        )->queryOne();
        if (!$row || $row['stage'] !== 'WASH' || $row['status'] !== 'COMPLETED' || $row['linen_class'] !== $class) {
            throw new InvalidArgumentException('รอบซักต้นทางยังไม่เสร็จหรือประเภทไม่ตรง');
        }
        // ไม่ได้ชั่งตอนออก → ใช้น้ำหนักเข้าเครื่องแทน
        return (float) ($row['output_kg'] ?? $row['input_kg']);
    }

    private function lockedBatch(int $id): array
    {
        $row = $this->db->createCommand('SELECT * FROM {{%laundry_processing_batch}} WHERE id = :id FOR UPDATE', [':id' => $id])->queryOne();
        if (!$row) {
            throw new InvalidArgumentException('ไม่พบรอบเครื่อง');
        }
        return $row;
    }
}
