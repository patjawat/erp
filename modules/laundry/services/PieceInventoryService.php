<?php

namespace app\modules\laundry\services;

use app\modules\hr\models\Organization;
use Yii;
use yii\base\InvalidArgumentException;
use yii\db\Connection;
use yii\db\Query;

/** Reusable linen is counted in pieces; kilograms never mutate this ledger. */
class PieceInventoryService
{
    private $db;

    public function __construct(?Connection $db = null)
    {
        $this->db = $db ?: Yii::$app->db;
    }

    public function createItem(string $code, string $name, ?string $stockCode, ?int $userId): int
    {
        $code = trim($code);
        $name = trim($name);
        $stockCode = $stockCode === null ? null : trim($stockCode);
        if ($code === '' || $name === '') {
            throw new InvalidArgumentException('กรุณาระบุรหัสและชื่อผ้า');
        }
        if ($stockCode !== null && $stockCode !== ''
            && !(new Query())->from('stock_item')->where(['item_code' => $stockCode])->exists($this->db)) {
            throw new InvalidArgumentException('ไม่พบรหัสพัสดุที่เชื่อม');
        }
        $this->db->createCommand()->insert('laundry_item', [
            'item_code' => $code, 'item_name' => $name,
            'stock_item_code' => $stockCode ?: null, 'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'), 'created_by' => $userId,
        ])->execute();
        return (int) $this->db->getLastInsertID();
    }

    public function setReceivingWarehouse(int $warehouseId, ?int $userId): void
    {
        $warehouse = (new Query())->from('warehouses')->where(['id' => $warehouseId])->one($this->db);
        if (!$warehouse || !in_array($warehouse['warehouse_type'], ['SUB', 'BRANCH'], true)) {
            throw new InvalidArgumentException('กรุณาเลือกคลังย่อยที่ใช้รับผ้าของซักฟอก');
        }
        $tx = $this->db->beginTransaction();
        try {
            $existing = $this->db->createCommand('SELECT * FROM {{%laundry_config}} WHERE id = 1 FOR UPDATE')->queryOne();
            if ($existing && (int) $existing['receiving_warehouse_id'] !== $warehouseId
                && (new Query())->from('laundry_piece_event')->where(['event_type' => 'PROCURE'])->exists($this->db)) {
                throw new InvalidArgumentException('มีรายการรับผ้าจากพัสดุแล้ว เปลี่ยนคลังรับในหน้าจอนี้ไม่ได้');
            }
            $values = ['receiving_warehouse_id' => $warehouseId, 'configured_at' => date('Y-m-d H:i:s'), 'configured_by' => $userId];
            if ($existing) {
                $this->db->createCommand()->update('laundry_config', $values, ['id' => 1])->execute();
            } else {
                $this->db->createCommand()->insert('laundry_config', $values + ['id' => 1])->execute();
            }
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function setPar(int $departmentId, int $itemId, int $target, int $minimum, ?int $userId): void
    {
        if ($target < 0 || $minimum < 0 || $minimum > $target) {
            throw new InvalidArgumentException('ยอดขั้นต่ำต้องไม่เกินยอดเป้าหมาย');
        }
        $this->assertDepartment($departmentId);
        $tx = $this->db->beginTransaction();
        try {
            $this->lockedItem($itemId);
            $row = (new Query())->from('laundry_par')->where(['department_id' => $departmentId, 'item_id' => $itemId])->one($this->db);
            $values = ['target_qty' => $target, 'min_qty' => $minimum, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => $userId];
            if ($row) {
                $this->db->createCommand()->update('laundry_par', $values, ['id' => $row['id']])->execute();
            } else {
                $this->db->createCommand()->insert('laundry_par', $values + ['department_id' => $departmentId, 'item_id' => $itemId])->execute();
            }
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function opening(int $itemId, int $qty, string $location, ?int $departmentId, string $reason, ?int $userId): int
    {
        if (!in_array($location, ['CLEAN', 'WARD'], true) || mb_strlen(trim($reason)) < 5) {
            throw new InvalidArgumentException('ยอดตั้งต้นต้องระบุตำแหน่งและเหตุผล');
        }
        return $this->move('OPENING', $itemId, $qty, 'EXTERNAL', null, $location, $departmentId, $reason, $userId);
    }

    /** Mirrors a confirmed warehouse issue into the separate circulating-piece ledger; never changes warehouse stock. */
    public function receiveFromStockDetail(int $itemId, int $stockDetailId, ?int $userId): int
    {
        $tx = $this->db->beginTransaction();
        try {
            $item = $this->lockedItem($itemId);
            $config = $this->db->createCommand('SELECT receiving_warehouse_id FROM {{%laundry_config}} WHERE id = 1 FOR UPDATE')->queryOne();
            if (!$config) {
                throw new InvalidArgumentException('ต้องกำหนดคลังรับของซักฟอกก่อนรับผ้าจากพัสดุ');
            }
            $detail = $this->db->createCommand(
                'SELECT sd.id, sd.item_code, sd.qty, so.status, so.order_type, so.sub_warehouse_id FROM {{%stock_detail}} sd JOIN {{%stock_order}} so ON so.id = sd.stock_order_id WHERE sd.id = :id FOR UPDATE',
                [':id' => $stockDetailId]
            )->queryOne();
            if (!$detail || $detail['status'] !== 'CONFIRMED' || $detail['order_type'] !== 'OUT'
                || (int) $detail['sub_warehouse_id'] !== (int) $config['receiving_warehouse_id']
                || !$item['stock_item_code'] || $detail['item_code'] !== $item['stock_item_code']) {
                throw new InvalidArgumentException('เอกสารจ่ายพัสดุไม่ตรงชนิดผ้าหรือยังไม่ยืนยัน');
            }
            if (!preg_match('/^\d+(?:\.0+)?$/', (string) $detail['qty']) || (int) $detail['qty'] < 1) {
                throw new InvalidArgumentException('จำนวนผ้าในเอกสารพัสดุต้องเป็นจำนวนเต็มบวก');
            }
            $lotId = $this->createLot($itemId, 'PROCURE', null, $userId);
            $id = $this->insertEvent('PROCURE', $itemId, (int) $detail['qty'], 'EXTERNAL', null, 'CLEAN', null,
                'รับจากพัสดุ', 'CONFIRMED', $userId, $userId, $stockDetailId, null, null, $lotId);
            $tx->commit();
            return $id;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function issueFromLot(int $lotId, int $departmentId, int $qty, ?int $userId): int
    {
        $this->assertQty($qty);
        $this->assertDepartment($departmentId);
        $tx = $this->db->beginTransaction();
        try {
            $candidate = (new Query())->from('laundry_clean_lot')->where(['id' => $lotId])->one($this->db);
            if (!$candidate) {
                throw new InvalidArgumentException('ไม่พบล็อตผ้าสะอาด');
            }
            $itemId = (int) $candidate['item_id'];
            $this->lockedItem($itemId);
            $lot = $this->db->createCommand('SELECT * FROM {{%laundry_clean_lot}} WHERE id = :id FOR UPDATE', [':id' => $lotId])->queryOne();
            if (!$lot || $this->lotBalance($lotId) < $qty || $this->balance($itemId, 'CLEAN', null, true) < $qty) {
                throw new InvalidArgumentException('ยอดผ้าสะอาดในล็อตไม่เพียงพอ');
            }
            $id = $this->insertEvent('ISSUE', $itemId, $qty, 'CLEAN', null, 'WARD', $departmentId,
                null, 'CONFIRMED', $userId, $userId, null, null, null, $lotId);
            $tx->commit();
            return $id;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function recordIroning(int $dryBatchId, int $itemId, int $qty, string $startedAt, string $endedAt, string $evidence, int $userId): int
    {
        $this->assertQty($qty);
        $start = \DateTimeImmutable::createFromFormat('!Y-m-d H:i', $startedAt);
        $end = \DateTimeImmutable::createFromFormat('!Y-m-d H:i', $endedAt);
        if (!$start || !$end || $start->format('Y-m-d H:i') !== $startedAt
            || $end->format('Y-m-d H:i') !== $endedAt || $end <= $start
            || $end > new \DateTimeImmutable() || mb_strlen(trim($evidence)) < 5) {
            throw new InvalidArgumentException('กรุณาระบุเวลาเริ่ม–สิ้นสุดและหลักฐานการรีดให้ถูกต้อง');
        }
        $tx = $this->db->beginTransaction();
        try {
            $this->lockedItem($itemId);
            $count = $this->db->createCommand(
                'SELECT c.qty, c.status, b.stage, b.status AS batch_status FROM {{%laundry_batch_piece_count}} c JOIN {{%laundry_processing_batch}} b ON b.id = c.dry_batch_id WHERE c.dry_batch_id = :batch AND c.item_id = :item FOR UPDATE',
                [':batch' => $dryBatchId, ':item' => $itemId]
            )->queryOne();
            if (!$count || $count['status'] !== 'APPROVED' || $count['stage'] !== 'DRY' || $count['batch_status'] !== 'COMPLETED') {
                throw new InvalidArgumentException('ต้องมีผลนับชิ้นหลังอบที่อนุมัติก่อนบันทึกการรีด');
            }
            $ironed = (int) $this->db->createCommand('SELECT COALESCE(SUM(qty), 0) FROM {{%laundry_ironing}} WHERE dry_batch_id = :batch AND item_id = :item', [':batch' => $dryBatchId, ':item' => $itemId])->queryScalar();
            if ($ironed + $qty > (int) $count['qty']) {
                throw new InvalidArgumentException('จำนวนรีดเกินผลนับชิ้นที่อนุมัติ');
            }
            $this->db->createCommand()->insert('laundry_ironing', [
                'dry_batch_id' => $dryBatchId, 'item_id' => $itemId, 'qty' => $qty,
                'started_at' => $start->format('Y-m-d H:i:s'), 'ended_at' => $end->format('Y-m-d H:i:s'),
                'evidence' => trim($evidence), 'created_at' => date('Y-m-d H:i:s'), 'created_by' => $userId,
            ])->execute();
            $id = (int) $this->db->getLastInsertID();
            $tx->commit();
            return $id;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function returnCounted(int $itemId, int $departmentId, int $qty, ?int $userId): int
    {
        return $this->move('RETURN_COUNTED', $itemId, $qty, 'WARD', $departmentId, 'DIRTY', null,
            'นับคืนเป็นชิ้นโดยทราบหน่วยงานต้นทาง', $userId);
    }

    public function qc(int $itemId, int $qty, string $destination, int $dryBatchId, ?int $userId): int
    {
        if (!in_array($destination, ['CLEAN', 'REWORK', 'REPAIR', 'DISPOSAL_PENDING'], true)) {
            throw new InvalidArgumentException('ผลตรวจผ้าไม่ถูกต้อง');
        }
        $this->assertQty($qty);
        $tx = $this->db->beginTransaction();
        try {
            $this->lockedItem($itemId);
            $batch = $this->db->createCommand('SELECT stage, status FROM {{%laundry_processing_batch}} WHERE id = :id FOR UPDATE', [':id' => $dryBatchId])->queryOne();
            if (!$batch || $batch['stage'] !== 'DRY' || $batch['status'] !== 'COMPLETED') {
                throw new InvalidArgumentException('ต้องอ้างรอบอบที่เสร็จแล้ว');
            }
            $count = $this->db->createCommand('SELECT qty, status FROM {{%laundry_batch_piece_count}} WHERE dry_batch_id = :batch AND item_id = :item FOR UPDATE', [':batch' => $dryBatchId, ':item' => $itemId])->queryOne();
            if (!$count || $count['status'] !== 'APPROVED') {
                throw new InvalidArgumentException('ต้องมีผลนับชิ้นหลังอบที่อนุมัติก่อนตรวจ QC');
            }
            $used = (int) $this->db->createCommand("SELECT COALESCE(SUM(qty), 0) FROM {{%laundry_piece_event}} WHERE processing_batch_id = :batch AND item_id = :item AND event_type = 'QC' AND status = 'CONFIRMED'", [':batch' => $dryBatchId, ':item' => $itemId])->queryScalar();
            $ironed = (int) $this->db->createCommand('SELECT COALESCE(SUM(qty), 0) FROM {{%laundry_ironing}} WHERE dry_batch_id = :batch AND item_id = :item', [':batch' => $dryBatchId, ':item' => $itemId])->queryScalar();
            if ($used + $qty > (int) $count['qty'] || $used + $qty > $ironed || $this->balance($itemId, 'QC_HOLD', null, true) < $qty) {
                throw new InvalidArgumentException('จำนวน QC เกินจำนวนที่รีดแล้วหรือผลนับชิ้นของรอบอบ');
            }
            $lotId = $destination === 'CLEAN' ? $this->productionLot($dryBatchId, $itemId, $userId) : null;
            $id = $this->insertEvent('QC', $itemId, $qty, 'QC_HOLD', null, $destination, null,
                null, 'CONFIRMED', $userId, $userId, null, null, $dryBatchId, $lotId);
            $tx->commit();
            return $id;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function requestDryPieceCount(int $dryBatchId, int $itemId, int $qty, string $evidence, int $userId): int
    {
        $this->assertQty($qty);
        if (mb_strlen(trim($evidence)) < 5) {
            throw new InvalidArgumentException('กรุณาระบุหลักฐานการนับชิ้นหลังอบ');
        }
        $tx = $this->db->beginTransaction();
        try {
            $this->lockedItem($itemId);
            $batch = $this->db->createCommand('SELECT stage, status FROM {{%laundry_processing_batch}} WHERE id = :id FOR UPDATE', [':id' => $dryBatchId])->queryOne();
            if (!$batch || $batch['stage'] !== 'DRY' || $batch['status'] !== 'COMPLETED') {
                throw new InvalidArgumentException('นับชิ้นได้เฉพาะรอบอบที่เสร็จแล้ว');
            }
            $this->db->createCommand()->insert('laundry_batch_piece_count', [
                'dry_batch_id' => $dryBatchId, 'item_id' => $itemId, 'qty' => $qty,
                'status' => 'PENDING', 'evidence' => trim($evidence),
                'created_at' => date('Y-m-d H:i:s'), 'created_by' => $userId,
            ])->execute();
            $id = (int) $this->db->getLastInsertID();
            $tx->commit();
            return $id;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function approveDryPieceCount(int $countId, int $approverId): void
    {
        $tx = $this->db->beginTransaction();
        try {
            $candidate = (new Query())->from('laundry_batch_piece_count')->where(['id' => $countId])->one($this->db);
            if (!$candidate) {
                throw new InvalidArgumentException('ไม่พบผลนับชิ้นหลังอบ');
            }
            $this->lockedItem((int) $candidate['item_id']);
            $count = $this->db->createCommand('SELECT * FROM {{%laundry_batch_piece_count}} WHERE id = :id FOR UPDATE', [':id' => $countId])->queryOne();
            if (!$count || $count['status'] !== 'PENDING' || (int) $count['created_by'] === $approverId) {
                throw new InvalidArgumentException('ผลนับไม่พร้อมอนุมัติหรือผู้อนุมัติเป็นผู้บันทึก');
            }
            if ($this->balance((int) $count['item_id'], 'DIRTY', null, true) < (int) $count['qty']) {
                throw new InvalidArgumentException('ผ้านับคืนในคลังไม่พอสำหรับรอบอบนี้');
            }
            $this->insertEvent('BATCH_COUNT', (int) $count['item_id'], (int) $count['qty'],
                'DIRTY', null, 'QC_HOLD', null, $count['evidence'], 'CONFIRMED',
                (int) $count['created_by'], $approverId, null, null, (int) $count['dry_batch_id']);
            $this->db->createCommand()->update('laundry_batch_piece_count', [
                'status' => 'APPROVED', 'approved_at' => date('Y-m-d H:i:s'), 'approved_by' => $approverId,
            ], ['id' => $countId, 'status' => 'PENDING'])->execute();
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function requestLoss(int $itemId, int $departmentId, int $qty, string $reason, ?int $userId): int
    {
        if (mb_strlen(trim($reason)) < 5) {
            throw new InvalidArgumentException('กรุณาระบุเหตุสูญเสียหรือทำลาย');
        }
        $this->assertDepartment($departmentId);
        $tx = $this->db->beginTransaction();
        try {
            $this->lockedItem($itemId);
            $this->assertQty($qty);
            $id = $this->insertEvent('LOSS', $itemId, $qty, 'WARD', $departmentId, 'DISPOSED', null,
                trim($reason), 'PENDING', $userId, null, null, null);
            $tx->commit();
            return $id;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function approveLoss(int $eventId, int $approverId): void
    {
        $tx = $this->db->beginTransaction();
        try {
            $candidate = (new Query())->from('laundry_piece_event')->where(['id' => $eventId])->one($this->db);
            if (!$candidate) {
                throw new InvalidArgumentException('ไม่พบรายการตัดผ้า');
            }
            $this->lockedItem((int) $candidate['item_id']);
            $event = $this->db->createCommand('SELECT * FROM {{%laundry_piece_event}} WHERE id = :id FOR UPDATE', [':id' => $eventId])->queryOne();
            if (!$event || $event['event_type'] !== 'LOSS' || $event['status'] !== 'PENDING'
                || (int) $event['created_by'] === $approverId) {
                throw new InvalidArgumentException('รายการไม่พร้อมอนุมัติหรือผู้อนุมัติเป็นผู้บันทึก');
            }
            if ($this->balance((int) $event['item_id'], 'WARD', (int) $event['from_department_id'], true) < (int) $event['qty']) {
                throw new InvalidArgumentException('ยอดหน่วยงานไม่พอสำหรับตัดผ้า');
            }
            $this->db->createCommand()->update('laundry_piece_event', [
                'status' => 'CONFIRMED', 'approved_at' => date('Y-m-d H:i:s'), 'approved_by' => $approverId,
            ], ['id' => $eventId, 'status' => 'PENDING'])->execute();
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function returnForRewash(int $itemId, int $qty, string $evidence, ?int $userId): int
    {
        if (mb_strlen(trim($evidence)) < 5) {
            throw new InvalidArgumentException('กรุณาระบุหลักฐานการนำผ้าซักซ้ำ');
        }
        return $this->move('REWASH_RETURN', $itemId, $qty, 'REWORK', null, 'DIRTY', null,
            trim($evidence), $userId);
    }

    public function completeRepair(int $itemId, int $qty, string $evidence, ?int $userId): int
    {
        if (mb_strlen(trim($evidence)) < 5) {
            throw new InvalidArgumentException('กรุณาระบุหลักฐานการซ่อมและตรวจผ้า');
        }
        return $this->move('REPAIR_COMPLETE', $itemId, $qty, 'REPAIR', null, 'CLEAN', null,
            trim($evidence), $userId);
    }

    public function requestQcDisposal(int $itemId, int $qty, string $reason, int $userId): int
    {
        $this->assertQty($qty);
        if (mb_strlen(trim($reason)) < 5) {
            throw new InvalidArgumentException('กรุณาระบุเหตุขอตัดผ้าที่ QC ไม่ผ่าน');
        }
        $tx = $this->db->beginTransaction();
        try {
            $this->lockedItem($itemId);
            $reserved = (int) $this->db->createCommand(
                "SELECT COALESCE(SUM(qty), 0) FROM {{%laundry_piece_event}} WHERE item_id = :item AND event_type = 'QC_DISPOSAL' AND status = 'PENDING'",
                [':item' => $itemId]
            )->queryScalar();
            if ($this->balance($itemId, 'DISPOSAL_PENDING', null, true) - $reserved < $qty) {
                throw new InvalidArgumentException('ผ้ารอตัดจำหน่ายไม่พอ');
            }
            $id = $this->insertEvent('QC_DISPOSAL', $itemId, $qty, 'DISPOSAL_PENDING', null,
                'DISPOSED', null, trim($reason), 'PENDING', $userId, null, null, null);
            $tx->commit();
            return $id;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function approveQcDisposal(int $eventId, int $approverId): void
    {
        $tx = $this->db->beginTransaction();
        try {
            $candidate = (new Query())->from('laundry_piece_event')->where(['id' => $eventId])->one($this->db);
            if (!$candidate) {
                throw new InvalidArgumentException('ไม่พบคำขอตัดผ้า');
            }
            $this->lockedItem((int) $candidate['item_id']);
            $event = $this->db->createCommand('SELECT * FROM {{%laundry_piece_event}} WHERE id = :id FOR UPDATE', [':id' => $eventId])->queryOne();
            if (!$event || $event['event_type'] !== 'QC_DISPOSAL' || $event['status'] !== 'PENDING'
                || (int) $event['created_by'] === $approverId) {
                throw new InvalidArgumentException('คำขอไม่พร้อมอนุมัติหรือผู้อนุมัติเป็นผู้บันทึก');
            }
            if ($this->balance((int) $event['item_id'], 'DISPOSAL_PENDING', null, true) < (int) $event['qty']) {
                throw new InvalidArgumentException('ผ้ารอตัดจำหน่ายไม่พอสำหรับอนุมัติ');
            }
            $this->db->createCommand()->update('laundry_piece_event', [
                'status' => 'CONFIRMED', 'approved_at' => date('Y-m-d H:i:s'), 'approved_by' => $approverId,
            ], ['id' => $eventId, 'status' => 'PENDING'])->execute();
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function balance(int $itemId, string $location, ?int $departmentId = null, bool $currentRead = false): int
    {
        $this->assertLocation($location, $departmentId);
        if ($currentRead) {
            $rows = $this->db->createCommand(
                "SELECT qty, from_location, from_department_id, to_location, to_department_id FROM {{%laundry_piece_event}} WHERE item_id = :item AND status = 'CONFIRMED' FOR UPDATE",
                [':item' => $itemId]
            )->queryAll();
            $balance = 0;
            foreach ($rows as $row) {
                if ($row['to_location'] === $location && ($row['to_department_id'] === null ? null : (int) $row['to_department_id']) === $departmentId) {
                    $balance += (int) $row['qty'];
                }
                if ($row['from_location'] === $location && ($row['from_department_id'] === null ? null : (int) $row['from_department_id']) === $departmentId) {
                    $balance -= (int) $row['qty'];
                }
            }
            return $balance;
        }
        $toCondition = $departmentId === null ? 'IS NULL' : '= :toDepartment';
        $fromCondition = $departmentId === null ? 'IS NULL' : '= :fromDepartment';
        $params = [':item' => $itemId, ':toLocation' => $location, ':fromLocation' => $location];
        if ($departmentId !== null) {
            $params[':toDepartment'] = $departmentId;
            $params[':fromDepartment'] = $departmentId;
        }
        return (int) $this->db->createCommand(
            "SELECT COALESCE(SUM(CASE WHEN to_location = :toLocation AND to_department_id {$toCondition} THEN qty ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN from_location = :fromLocation AND from_department_id {$fromCondition} THEN qty ELSE 0 END), 0) FROM {{%laundry_piece_event}} WHERE item_id = :item AND status = 'CONFIRMED'",
            $params
        )->queryScalar();
    }

    public function lotBalance(int $lotId): int
    {
        return (int) $this->db->createCommand(
            "SELECT COALESCE(SUM(CASE WHEN to_location = 'CLEAN' THEN qty ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN from_location = 'CLEAN' THEN qty ELSE 0 END), 0) FROM {{%laundry_piece_event}} WHERE clean_lot_id = :lot AND status = 'CONFIRMED'",
            [':lot' => $lotId]
        )->queryScalar();
    }

    public function lotBalances(): array
    {
        $rows = $this->db->createCommand(
            "SELECT clean_lot_id, COALESCE(SUM(CASE WHEN to_location = 'CLEAN' THEN qty ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN from_location = 'CLEAN' THEN qty ELSE 0 END), 0) AS available_qty FROM {{%laundry_piece_event}} WHERE clean_lot_id IS NOT NULL AND status = 'CONFIRMED' GROUP BY clean_lot_id"
        )->queryAll();
        $balances = [];
        foreach ($rows as $row) {
            $balances[(int) $row['clean_lot_id']] = (int) $row['available_qty'];
        }
        return $balances;
    }

    private function productionLot(int $dryBatchId, int $itemId, ?int $userId): int
    {
        $existing = (new Query())->from('laundry_clean_lot')->where(['dry_batch_id' => $dryBatchId, 'item_id' => $itemId])->one($this->db);
        return $existing ? (int) $existing['id'] : $this->createLot($itemId, 'PRODUCTION', $dryBatchId, $userId);
    }

    private function createLot(int $itemId, string $sourceType, ?int $dryBatchId, ?int $userId): int
    {
        $this->db->createCommand()->insert('laundry_clean_lot', [
            'lot_no' => 'LL-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(5))),
            'item_id' => $itemId, 'source_type' => $sourceType, 'dry_batch_id' => $dryBatchId,
            'created_at' => date('Y-m-d H:i:s'), 'created_by' => $userId,
        ])->execute();
        return (int) $this->db->getLastInsertID();
    }

    private function move(string $type, int $itemId, int $qty, string $from, ?int $fromDepartment,
        string $to, ?int $toDepartment, ?string $reason, ?int $userId, ?int $batchId = null): int
    {
        $this->assertQty($qty);
        $this->assertLocation($from, $fromDepartment);
        $this->assertLocation($to, $toDepartment);
        if ($from === $to && $fromDepartment === $toDepartment) {
            throw new InvalidArgumentException('ต้นทางและปลายทางต้องต่างกัน');
        }
        $tx = $this->db->beginTransaction();
        try {
            $this->lockedItem($itemId); // serializes movements/count approval for this SKU
            if ($from !== 'EXTERNAL' && $this->balance($itemId, $from, $fromDepartment, true) < $qty) {
                throw new InvalidArgumentException('จำนวนผ้าต้นทางไม่เพียงพอ');
            }
            $lotId = $to === 'CLEAN' ? $this->createLot($itemId, $type, null, $userId) : null;
            $id = $this->insertEvent($type, $itemId, $qty, $from, $fromDepartment, $to, $toDepartment,
                $reason, 'CONFIRMED', $userId, $userId, null, null, $batchId, $lotId);
            $tx->commit();
            return $id;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    private function insertEvent(string $type, int $itemId, int $qty, string $from, ?int $fromDepartment,
        string $to, ?int $toDepartment, ?string $reason, string $status, ?int $userId,
        ?int $approverId, ?int $stockDetailId, ?int $countLineId, ?int $batchId = null, ?int $lotId = null): int
    {
        $this->db->createCommand()->insert('laundry_piece_event', [
            'event_no' => 'LP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4))),
            'event_type' => $type, 'item_id' => $itemId, 'qty' => $qty,
            'from_location' => $from, 'from_department_id' => $fromDepartment,
            'to_location' => $to, 'to_department_id' => $toDepartment,
            'status' => $status, 'source_stock_detail_id' => $stockDetailId,
            'audit_count_line_id' => $countLineId, 'reason' => $reason,
            'processing_batch_id' => $batchId,
            'clean_lot_id' => $lotId,
            'occurred_at' => date('Y-m-d H:i:s'), 'created_by' => $userId,
            'approved_at' => $approverId === null ? null : date('Y-m-d H:i:s'),
            'approved_by' => $approverId,
        ])->execute();
        return (int) $this->db->getLastInsertID();
    }

    private function lockedItem(int $itemId): array
    {
        $row = $this->db->createCommand('SELECT * FROM {{%laundry_item}} WHERE id = :id FOR UPDATE', [':id' => $itemId])->queryOne();
        if (!$row || !(int) $row['is_active']) {
            throw new InvalidArgumentException('ไม่พบชนิดผ้าที่ใช้งาน');
        }
        return $row;
    }

    private function assertDepartment(int $departmentId): void
    {
        if ($departmentId <= 0 || !Organization::find()->where(['id' => $departmentId])->exists()) {
            throw new InvalidArgumentException('ไม่พบหน่วยงาน');
        }
    }

    private function assertLocation(string $location, ?int $departmentId): void
    {
        if (!in_array($location, ['EXTERNAL', 'CLEAN', 'WARD', 'DIRTY', 'QC_HOLD', 'REWORK', 'REPAIR', 'DISPOSAL_PENDING', 'DISPOSED'], true)
            || ($location === 'WARD') !== ($departmentId !== null)) {
            throw new InvalidArgumentException('ตำแหน่งผ้าและหน่วยงานไม่ถูกต้อง');
        }
        if ($departmentId !== null) {
            $this->assertDepartment($departmentId);
        }
    }

    private function assertQty(int $qty): void
    {
        if ($qty < 1) {
            throw new InvalidArgumentException('จำนวนชิ้นต้องมากกว่า 0');
        }
    }
}
