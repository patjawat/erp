<?php

namespace app\modules\roster\helpers;

use app\modules\roster\models\Item;
use app\modules\roster\models\Period;
use app\modules\roster\models\Swap;
use Yii;

/**
 * ดำเนินการเปลี่ยนตัวเวรหลังตารางประกาศแล้ว
 *
 * หลักการ: ไม่ลบ/สร้าง roster_item ใหม่ แต่สลับ emp_id ในแถวเดิม แล้วให้ roster_swap
 * เป็นหลักฐานว่าใครเปลี่ยนเป็นใคร เมื่อไร ใครอนุมัติ — ตรวจย้อนหลังได้ครบโดยที่กริดยังเรียบง่าย
 *
 * ตรวจกฎซ้ำทุกครั้งก่อนอนุมัติ เพราะคนที่มารับเวรอาจกลายเป็นดึกติดเช้าหรือทำงานติดกันเกินกำหนด
 * ผลการตรวจถูกบันทึกลง roster_swap.warnings เพื่อให้ผู้ตรวจสอบเห็นย้อนหลังว่าอนุมัติทั้งที่รู้
 */
class RosterSwapService
{
    /**
     * ตรวจว่าถ้าให้ $toEmpId มารับเวรนี้ จะผิดกฎอะไรบ้าง
     * @return string[] ข้อความเตือน (ว่าง = ไม่ผิดกฎ)
     */
    public static function previewWarnings(Item $item, int $toEmpId): array
    {
        $period = $item->period;
        if (!$period) {
            return [];
        }
        $checker = new RuleChecker((int) $period->unit_id);
        // ไม่นับเวรใบนี้ เพราะกำลังจะเปลี่ยนมือ
        $shifts = RuleChecker::shiftsOfEmployee($toEmpId, $period->firstDate(), $period->lastDate(), $item->id);
        return $checker->checkAssignment($item->work_date, (int) $item->unit_shift_id, $shifts);
    }

    /**
     * คนคนหนึ่งถือเวรนี้ (วันเดียวกัน เวรเดียวกัน) อยู่แล้วหรือยัง
     *
     * ฐานข้อมูลมี unique index (emp_id, work_date, unit_shift_id) กันไว้อยู่แล้ว
     * แต่ถ้าปล่อยให้ไปชนตอน UPDATE ผู้ใช้จะเห็นข้อความ SQL ดิบ ๆ ที่อ่านไม่รู้เรื่อง
     *
     * @param int|null $ignoreItemId เวรที่กำลังจะย้ายออกไป จึงไม่ถือว่าชน
     */
    private static function heldBy(Item $item, int $empId, ?int $ignoreItemId = null): ?Item
    {
        $query = Item::find()
            ->where([
                'emp_id' => $empId,
                'work_date' => $item->work_date,
                'unit_shift_id' => $item->unit_shift_id,
            ])
            ->andWhere(['<>', 'id', $item->id]);
        if ($ignoreItemId) {
            $query->andWhere(['<>', 'id', $ignoreItemId]);
        }
        return $query->one();
    }

    /**
     * เหตุผลที่เปลี่ยนตัวไม่ได้เลย (คนละเรื่องกับคำเตือนจากกฎ ซึ่งแค่เตือนแล้วไปต่อได้)
     * @return string|null null = ทำได้
     */
    private static function blockingReason(Item $item, int $toEmpId, ?Item $counterItem): ?string
    {
        $nameOf = static function (int $empId): string {
            $emp = \app\modules\hr\models\Employees::findOne($empId);
            return $emp ? trim(($emp->prefix ?? '') . $emp->fname . ' ' . $emp->lname) : 'เจ้าหน้าที่';
        };
        $when = static fn(Item $i): string => date('j/n', strtotime($i->work_date));

        // แลกเวรชนิดเดียวกันในวันเดียวกัน = ตารางเหมือนเดิมทุกประการ ไม่มีประโยชน์
        if ($counterItem
            && $counterItem->work_date === $item->work_date
            && (int) $counterItem->unit_shift_id === (int) $item->unit_shift_id) {
            return sprintf('ทั้งสองคนอยู่%sวันที่ %s เหมือนกันอยู่แล้ว แลกแล้วตารางไม่เปลี่ยน',
                $item->shiftName(), $when($item));
        }

        if (static::heldBy($item, $toEmpId, $counterItem ? (int) $counterItem->id : null)) {
            return sprintf('%s อยู่%sวันที่ %s อยู่แล้ว รับเพิ่มอีกเวรเดียวกันไม่ได้',
                $nameOf($toEmpId), $item->shiftName(), $when($item));
        }

        if ($counterItem
            && static::heldBy($counterItem, (int) $item->emp_id, (int) $item->id)) {
            return sprintf('%s อยู่%sวันที่ %s อยู่แล้ว รับเพิ่มอีกเวรเดียวกันไม่ได้',
                $nameOf((int) $item->emp_id), $counterItem->shiftName(), $when($counterItem));
        }

        return null;
    }

    /**
     * ยื่นใบขอแลก/ยกเวรให้ (เจ้าหน้าที่เป็นผู้ยื่น)
     * @throws \RuntimeException
     */
    public static function request(Item $item, int $toEmpId, string $type, ?string $reason, ?int $counterItemId = null): Swap
    {
        $period = $item->period;
        if (!$period || !$period->allowsSwap()) {
            throw new \RuntimeException('รอบเวรนี้ยังไม่ประกาศ หรือปิดรอบแล้ว จึงเปลี่ยนตัวไม่ได้');
        }
        if ($item->work_date < date('Y-m-d')) {
            throw new \RuntimeException('เปลี่ยนตัวเวรย้อนหลังไม่ได้');
        }
        if (static::hasOpenRequest((int) $item->id)) {
            throw new \RuntimeException('เวรนี้มีใบขอเปลี่ยนตัวที่ยังไม่จบอยู่แล้ว');
        }

        $counterItem = null;
        if ($type === Swap::TYPE_SWAP) {
            if (!$counterItemId) {
                throw new \RuntimeException('การแลกเวรต้องระบุเวรของอีกฝ่ายที่จะแลก');
            }
            $counterItem = Item::findOne($counterItemId);
            if (!$counterItem || (int) $counterItem->emp_id !== $toEmpId) {
                throw new \RuntimeException('เวรที่จะแลกไม่ใช่ของคนที่เลือก');
            }
            if ((int) $counterItem->period_id !== (int) $period->id) {
                throw new \RuntimeException('แลกข้ามรอบเวรไม่ได้');
            }
        }

        $blocked = static::blockingReason($item, $toEmpId, $counterItem);
        if ($blocked !== null) {
            throw new \RuntimeException($blocked);
        }

        $swap = new Swap([
            'period_id' => (int) $period->id,
            'item_id' => (int) $item->id,
            'counter_item_id' => $counterItem ? (int) $counterItem->id : null,
            'type' => $type,
            'from_emp_id' => (int) $item->emp_id,
            'to_emp_id' => $toEmpId,
            'reason' => $reason,
            'status' => Swap::STATUS_PENDING,
            'requested_by' => (int) $item->emp_id,
        ]);
        if (!$swap->save()) {
            throw new \RuntimeException(implode(' ', array_merge(...array_values($swap->getErrors()))));
        }
        return $swap;
    }

    /**
     * หัวหน้าหน่วยเปลี่ยนตัวเองกรณีฉุกเฉิน — ข้ามขั้นรอคู่กรณีตอบรับและอนุมัติทันที
     * @throws \RuntimeException
     */
    public static function replace(Item $item, int $toEmpId, string $reason, int $actorEmpId): Swap
    {
        $period = $item->period;
        if (!$period || !$period->allowsSwap()) {
            throw new \RuntimeException('รอบเวรนี้ยังไม่ประกาศ หรือปิดรอบแล้ว จึงเปลี่ยนตัวไม่ได้');
        }
        if (trim($reason) === '') {
            throw new \RuntimeException('กรุณาระบุเหตุผลในการเปลี่ยนตัว');
        }

        $swap = new Swap([
            'period_id' => (int) $period->id,
            'item_id' => (int) $item->id,
            'type' => Swap::TYPE_REPLACE,
            'from_emp_id' => (int) $item->emp_id,
            'to_emp_id' => $toEmpId,
            'reason' => $reason,
            'status' => Swap::STATUS_ACCEPTED, // ไม่ต้องรอคู่กรณี
            'requested_by' => $actorEmpId,
            'responded_at' => date('Y-m-d H:i:s'),
            'responded_by' => $actorEmpId,
        ]);
        if (!$swap->save()) {
            throw new \RuntimeException(implode(' ', array_merge(...array_values($swap->getErrors()))));
        }
        return static::approve($swap, $actorEmpId);
    }

    /** คู่กรณีตอบรับ */
    public static function accept(Swap $swap, int $actorEmpId): Swap
    {
        if ($swap->status !== Swap::STATUS_PENDING) {
            throw new \RuntimeException('ใบขอนี้ไม่ได้อยู่ระหว่างรอตอบรับ');
        }
        if ((int) $swap->to_emp_id !== $actorEmpId) {
            throw new \RuntimeException('คุณไม่ใช่ผู้ที่ถูกขอให้รับเวรนี้');
        }
        $swap->status = Swap::STATUS_ACCEPTED;
        $swap->responded_at = date('Y-m-d H:i:s');
        $swap->responded_by = $actorEmpId;
        $swap->save(false);
        return $swap;
    }

    public static function reject(Swap $swap, int $actorEmpId): Swap
    {
        if (!$swap->isOpen()) {
            throw new \RuntimeException('ใบขอนี้จบไปแล้ว');
        }
        $swap->status = Swap::STATUS_REJECTED;
        $swap->responded_at = date('Y-m-d H:i:s');
        $swap->responded_by = $actorEmpId;
        $swap->save(false);
        return $swap;
    }

    /**
     * หัวหน้าหน่วยอนุมัติ — จุดเดียวที่ roster_item ถูกแก้หลังประกาศ
     * @throws \RuntimeException
     */
    public static function approve(Swap $swap, int $actorEmpId): Swap
    {
        if ($swap->status !== Swap::STATUS_ACCEPTED) {
            throw new \RuntimeException('ใบขอนี้ยังไม่ผ่านการตอบรับจากคู่กรณี');
        }
        $item = $swap->item;
        if (!$item) {
            throw new \RuntimeException('ไม่พบเวรต้นทาง');
        }
        $period = $swap->period;
        if (!$period || !$period->allowsSwap()) {
            throw new \RuntimeException('รอบเวรนี้ปิดแล้ว เปลี่ยนตัวไม่ได้');
        }

        $counterItem = $swap->counter_item_id ? $swap->counterItem : null;

        // ตรวจซ้ำก่อนเขียนจริง — ระหว่างรออนุมัติ ตารางอาจเปลี่ยนไปจนชนกันได้
        $blocked = static::blockingReason($item, (int) $swap->to_emp_id, $counterItem);
        if ($blocked !== null) {
            throw new \RuntimeException($blocked);
        }

        // เก็บผลตรวจกฎไว้เป็นหลักฐานว่าอนุมัติทั้งที่รู้ว่าผิดกฎอะไร
        $warnings = static::previewWarnings($item, (int) $swap->to_emp_id);
        if ($counterItem) {
            foreach (static::previewWarnings($counterItem, (int) $swap->from_emp_id) as $warning) {
                $warnings[] = $warning;
            }
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $fromEmpId = (int) $swap->from_emp_id;
            $toEmpId = (int) $swap->to_emp_id;

            $item->emp_id = $toEmpId;
            $item->status = Item::STATUS_SWAPPED;
            $item->note = trim(($item->note ? $item->note . ' · ' : '') . 'เปลี่ยนตัวตามใบ #' . $swap->id);
            if (!$item->save(false)) {
                throw new \RuntimeException('บันทึกเวรต้นทางไม่สำเร็จ');
            }

            if ($counterItem) {
                $counterItem->emp_id = $fromEmpId;
                $counterItem->status = Item::STATUS_SWAPPED;
                $counterItem->note = trim(($counterItem->note ? $counterItem->note . ' · ' : '') . 'เปลี่ยนตัวตามใบ #' . $swap->id);
                if (!$counterItem->save(false)) {
                    throw new \RuntimeException('บันทึกเวรที่แลกไม่สำเร็จ');
                }
            }

            $swap->status = Swap::STATUS_APPROVED;
            $swap->approved_at = date('Y-m-d H:i:s');
            $swap->approved_by = $actorEmpId;
            $swap->warnings = $warnings ?: null;
            $swap->save(false);

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw new \RuntimeException('เปลี่ยนตัวไม่สำเร็จ: ' . $e->getMessage());
        }

        return $swap;
    }

    public static function cancel(Swap $swap, int $actorEmpId): Swap
    {
        if (!$swap->isOpen()) {
            throw new \RuntimeException('ใบขอนี้จบไปแล้ว ยกเลิกไม่ได้');
        }
        if ((int) $swap->requested_by !== $actorEmpId) {
            throw new \RuntimeException('ยกเลิกได้เฉพาะผู้ยื่นเท่านั้น');
        }
        $swap->status = Swap::STATUS_CANCELLED;
        $swap->save(false);
        return $swap;
    }

    public static function hasOpenRequest(int $itemId): bool
    {
        return Swap::find()
            ->where(['item_id' => $itemId])
            ->andWhere(['status' => [Swap::STATUS_PENDING, Swap::STATUS_ACCEPTED]])
            ->exists();
    }

    /** ใบขอที่รอ "ฉัน" ตอบรับ */
    public static function pendingForEmployee(int $empId): array
    {
        return Swap::find()
            ->where(['to_emp_id' => $empId, 'status' => Swap::STATUS_PENDING])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
    }

    /**
     * ใบขอที่รอหัวหน้าหน่วยอนุมัติ
     * @param int[]|null $unitIds null = ทุกหน่วย (admin)
     */
    public static function awaitingApproval(?array $unitIds): array
    {
        if ($unitIds !== null && empty($unitIds)) {
            return [];
        }
        $query = Swap::find()
            ->alias('s')
            ->innerJoin(['p' => Period::tableName()], 'p.id = s.period_id')
            ->where(['s.status' => Swap::STATUS_ACCEPTED])
            ->orderBy(['s.responded_at' => SORT_ASC]);
        if ($unitIds !== null) {
            $query->andWhere(['p.unit_id' => $unitIds]);
        }
        return $query->all();
    }
}
