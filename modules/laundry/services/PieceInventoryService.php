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
            $id = $this->insertEvent('PROCURE', $itemId, (int) $detail['qty'], 'EXTERNAL', null, 'CLEAN', null,
                'รับจากพัสดุ', 'CONFIRMED', $userId, $userId, $stockDetailId, null);
            $tx->commit();
            return $id;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function issue(int $itemId, int $departmentId, int $qty, ?int $userId): int
    {
        return $this->move('ISSUE', $itemId, $qty, 'CLEAN', null, 'WARD', $departmentId, null, $userId);
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
        $batch = (new Query())->from('laundry_processing_batch')->where(['id' => $dryBatchId, 'stage' => 'DRY', 'status' => 'COMPLETED'])->one($this->db);
        if (!$batch) {
            throw new InvalidArgumentException('ต้องอ้างรอบอบที่เสร็จแล้ว');
        }
        return $this->move('QC', $itemId, $qty, 'DIRTY', null, $destination, null, null, $userId, $dryBatchId);
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
            $id = $this->insertEvent($type, $itemId, $qty, $from, $fromDepartment, $to, $toDepartment,
                $reason, 'CONFIRMED', $userId, $userId, null, null, $batchId);
            $tx->commit();
            return $id;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    private function insertEvent(string $type, int $itemId, int $qty, string $from, ?int $fromDepartment,
        string $to, ?int $toDepartment, ?string $reason, string $status, ?int $userId,
        ?int $approverId, ?int $stockDetailId, ?int $countLineId, ?int $batchId = null): int
    {
        $this->db->createCommand()->insert('laundry_piece_event', [
            'event_no' => 'LP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4))),
            'event_type' => $type, 'item_id' => $itemId, 'qty' => $qty,
            'from_location' => $from, 'from_department_id' => $fromDepartment,
            'to_location' => $to, 'to_department_id' => $toDepartment,
            'status' => $status, 'source_stock_detail_id' => $stockDetailId,
            'audit_count_line_id' => $countLineId, 'reason' => $reason,
            'processing_batch_id' => $batchId,
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
        if (!in_array($location, ['EXTERNAL', 'CLEAN', 'WARD', 'DIRTY', 'REWORK', 'REPAIR', 'DISPOSAL_PENDING', 'DISPOSED'], true)
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
