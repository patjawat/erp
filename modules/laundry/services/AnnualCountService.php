<?php

namespace app\modules\laundry\services;

use app\modules\hr\models\Organization;
use Yii;
use yii\base\InvalidArgumentException;
use yii\db\Connection;
use yii\db\Query;

/** Audits ward accountability in pieces, not collection weight. */
class AnnualCountService
{
    private $db;
    private $inventory;

    public function __construct(?Connection $db = null)
    {
        $this->db = $db ?: Yii::$app->db;
        $this->inventory = new PieceInventoryService($this->db);
    }

    public function open(int $departmentId, int $year, ?int $userId): int
    {
        if ($year < 2000 || $year > 2200 || $departmentId <= 0
            || !Organization::find()->where(['id' => $departmentId])->exists()) {
            throw new InvalidArgumentException('ปีหรือหน่วยงานไม่ถูกต้อง');
        }
        $tx = $this->db->beginTransaction();
        try {
            $this->db->createCommand('SELECT id FROM {{%tree}} WHERE id = :id FOR UPDATE', [':id' => $departmentId])->queryOne();
            $existing = (new Query())->from('laundry_annual_count')
                ->where(['department_id' => $departmentId, 'count_year' => $year])
                ->andWhere(['status' => ['OPEN', 'APPROVED']])->exists($this->db);
            if ($existing) {
                throw new InvalidArgumentException('หน่วยงานนี้มีรอบสอบยอดที่เปิดอยู่หรืออนุมัติแล้วในปีนี้');
            }
            $items = $this->db->createCommand('SELECT id FROM {{%laundry_item}} ORDER BY id FOR UPDATE')->queryAll();
            if (!$items) {
                throw new InvalidArgumentException('ยังไม่มีทะเบียนชนิดผ้า');
            }
            $cutoffEventId = (int) (new Query())->from('laundry_piece_event')->max('id', $this->db);
            $this->db->createCommand()->insert('laundry_annual_count', [
                'department_id' => $departmentId, 'count_year' => $year,
                'cutoff_at' => date('Y-m-d H:i:s'), 'cutoff_event_id' => $cutoffEventId,
                'status' => 'OPEN', 'created_by' => $userId,
            ])->execute();
            $countId = (int) $this->db->getLastInsertID();
            foreach ($items as $item) {
                $this->db->createCommand()->insert('laundry_annual_count_line', [
                    'count_id' => $countId, 'item_id' => $item['id'],
                    'book_qty' => $this->inventory->balance((int) $item['id'], 'WARD', $departmentId),
                ])->execute();
            }
            $tx->commit();
            return $countId;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function cancel(int $countId, int $userId, string $reason): void
    {
        if (mb_strlen(trim($reason)) < 5) {
            throw new InvalidArgumentException('กรุณาระบุเหตุยกเลิกรอบสอบยอด');
        }
        $tx = $this->db->beginTransaction();
        try {
            $count = $this->lockedCount($countId);
            if ($count['status'] !== 'OPEN') {
                throw new InvalidArgumentException('ยกเลิกได้เฉพาะรอบที่ยังไม่อนุมัติ');
            }
            $this->db->createCommand()->update('laundry_annual_count', [
                'status' => 'CANCELLED', 'cancel_reason' => trim($reason),
                'cancelled_at' => date('Y-m-d H:i:s'), 'cancelled_by' => $userId,
            ], ['id' => $countId, 'status' => 'OPEN'])->execute();
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function record(int $countId, int $itemId, int $onHandQty, int $verifiedInTransitQty, ?string $transitEvidence, ?string $reason): void
    {
        if ($onHandQty < 0 || $verifiedInTransitQty < 0) {
            throw new InvalidArgumentException('ยอดตรวจจริงต้องไม่ติดลบ');
        }
        $actualQty = $onHandQty + $verifiedInTransitQty;
        $transitEvidence = trim((string) $transitEvidence);
        if ($verifiedInTransitQty > 0 && mb_strlen($transitEvidence) < 5) {
            throw new InvalidArgumentException('ผ้าระหว่างซักต้องระบุหลักฐานเลขถุงหรือเอกสารที่นับชิ้นได้');
        }
        $tx = $this->db->beginTransaction();
        try {
            $count = $this->lockedCount($countId);
            if ($count['status'] !== 'OPEN') {
                throw new InvalidArgumentException('รอบสอบยอดนี้ปิดแล้ว');
            }
            $line = (new Query())->from('laundry_annual_count_line')->where(['count_id' => $countId, 'item_id' => $itemId])->one($this->db);
            if (!$line) {
                throw new InvalidArgumentException('ไม่พบชนิดผ้าในรอบสอบยอด');
            }
            $reason = trim((string) $reason);
            if ($actualQty !== (int) $line['book_qty'] && mb_strlen($reason) < 5) {
                throw new InvalidArgumentException('ยอดต่างต้องระบุเหตุหรือลักษณะผลต่างอย่างน้อย 5 ตัวอักษร');
            }
            $this->db->createCommand()->update('laundry_annual_count_line', [
                'on_hand_qty' => $onHandQty, 'verified_in_transit_qty' => $verifiedInTransitQty,
                'transit_evidence' => $transitEvidence ?: null,
                'actual_qty' => $actualQty, 'variance_reason' => $reason ?: null,
            ], ['id' => $line['id']])->execute();
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function approve(int $countId, int $approverId): void
    {
        $tx = $this->db->beginTransaction();
        try {
            $count = $this->lockedCount($countId);
            if ($count['status'] !== 'OPEN' || (int) $count['created_by'] === $approverId) {
                throw new InvalidArgumentException('รอบสอบยอดไม่พร้อมอนุมัติหรือผู้อนุมัติเป็นผู้เปิดรอบ');
            }
            $lines = (new Query())->from('laundry_annual_count_line')->where(['count_id' => $countId])->orderBy(['item_id' => SORT_ASC])->all($this->db);
            if (!$lines) {
                throw new InvalidArgumentException('ไม่มีรายการสอบยอด');
            }
            foreach ($lines as $line) {
                $this->db->createCommand('SELECT id FROM {{%laundry_item}} WHERE id = :id FOR UPDATE', [':id' => $line['item_id']])->queryOne();
            }
            $later = $this->db->createCommand(
                "SELECT id FROM {{%laundry_piece_event}} WHERE id > :cutoff AND status = 'CONFIRMED' AND ((from_location = 'WARD' AND from_department_id = :fromDepartment) OR (to_location = 'WARD' AND to_department_id = :toDepartment)) LIMIT 1 FOR UPDATE",
                [':cutoff' => $count['cutoff_event_id'], ':fromDepartment' => $count['department_id'], ':toDepartment' => $count['department_id']]
            )->queryOne();
            if ($later) {
                throw new InvalidArgumentException('มีการรับ–จ่ายผ้าหลังเวลาเริ่มสอบยอด กรุณาตรวจสอบรอบใหม่');
            }
            foreach ($lines as $line) {
                if ($line['actual_qty'] === null) {
                    throw new InvalidArgumentException('ต้องตรวจนับทุกชนิดผ้าก่อนอนุมัติ');
                }
                $current = $this->inventory->balance((int) $line['item_id'], 'WARD', (int) $count['department_id'], true);
                if ($current !== (int) $line['book_qty']) {
                    throw new InvalidArgumentException('ยอดบัญชีเปลี่ยนหลังเปิดรอบสอบ กรุณาตรวจสอบใหม่');
                }
                $difference = (int) $line['actual_qty'] - (int) $line['book_qty'];
                if ($difference === 0) {
                    continue;
                }
                if (mb_strlen(trim((string) $line['variance_reason'])) < 5) {
                    throw new InvalidArgumentException('ผลต่างยังไม่มีเหตุผล');
                }
                $this->db->createCommand()->insert('laundry_piece_event', [
                    'event_no' => 'LP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4))),
                    'event_type' => $difference > 0 ? 'AUDIT_GAIN' : 'AUDIT_LOSS',
                    'item_id' => $line['item_id'], 'qty' => abs($difference),
                    'from_location' => $difference > 0 ? 'EXTERNAL' : 'WARD',
                    'from_department_id' => $difference > 0 ? null : $count['department_id'],
                    'to_location' => $difference > 0 ? 'WARD' : 'DISPOSED',
                    'to_department_id' => $difference > 0 ? $count['department_id'] : null,
                    'status' => 'CONFIRMED', 'audit_count_line_id' => $line['id'],
                    'reason' => $line['variance_reason'], 'occurred_at' => date('Y-m-d H:i:s'),
                    'created_by' => $count['created_by'], 'approved_at' => date('Y-m-d H:i:s'),
                    'approved_by' => $approverId,
                ])->execute();
            }
            $this->db->createCommand()->update('laundry_annual_count', [
                'status' => 'APPROVED', 'approved_at' => date('Y-m-d H:i:s'), 'approved_by' => $approverId,
            ], ['id' => $countId, 'status' => 'OPEN'])->execute();
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    private function lockedCount(int $countId): array
    {
        $row = $this->db->createCommand('SELECT * FROM {{%laundry_annual_count}} WHERE id = :id FOR UPDATE', [':id' => $countId])->queryOne();
        if (!$row) {
            throw new InvalidArgumentException('ไม่พบรอบสอบยอด');
        }
        return $row;
    }
}
