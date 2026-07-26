<?php

declare(strict_types=1);

namespace app\modules\housing\services;

use app\modules\housing\models\Checkout;
use app\modules\housing\models\HousingRequest;
use app\modules\housing\models\Occupancy;
use app\modules\housing\models\Resident;
use Yii;
use yii\db\Expression;

final class CheckoutWorkflowService
{
    public function submitInspection(Checkout $checkout): void
    {
        if ($checkout->status !== Checkout::STATUS_REQUESTED) {
            throw new \DomainException('รายการนี้ไม่อยู่ในสถานะรอตรวจรับคืน');
        }
        foreach ($checkout->assetItems() as $item) {
            if (empty($item['acknowledged'])) {
                throw new \DomainException('กรุณาตรวจอุปกรณ์ให้ครบทุกรายการก่อนส่งให้ผู้พักลงนาม');
            }
        }
        $checkout->status = Checkout::STATUS_INSPECTION;
        if (!$checkout->save(false, ['status', 'updated_at', 'updated_by'])) {
            throw new \RuntimeException('บันทึกผลการตรวจรับคืนไม่สำเร็จ');
        }
    }

    public function signResident(Checkout $checkout, int $employeeId): void
    {
        if ($checkout->status !== Checkout::STATUS_INSPECTION || (int)$checkout->resident_emp_id !== $employeeId) {
            throw new \DomainException('รายการนี้ยังไม่พร้อมหรือไม่มีสิทธิ์ลงนามส่งคืน');
        }
        $checkout->resident_signed_at = new Expression('NOW()');
        $checkout->status = Checkout::STATUS_AWAITING_STAFF;
        if (!$checkout->save(false, ['resident_signed_at', 'status', 'updated_at', 'updated_by'])) {
            throw new \RuntimeException('บันทึกลายมือชื่อผู้ส่งคืนไม่สำเร็จ');
        }
    }

    public function signInspector(Checkout $checkout): void
    {
        if ($checkout->status !== Checkout::STATUS_AWAITING_STAFF || $checkout->resident_signed_at === null) {
            throw new \DomainException('ผู้พักยังไม่ได้ลงนามส่งคืน');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $occupancy = $checkout->occupancy;
            if (!$occupancy || $occupancy->status !== Occupancy::STATUS_ACTIVE) {
                throw new \DomainException('สถานะผู้พักไม่พร้อมปิดการเข้าพัก');
            }
            $now = new Expression('NOW()');
            $checkout->status = Checkout::STATUS_COMPLETED;
            $checkout->inspector_signed_at = $now;
            $checkout->completed_at = $now;
            $checkout->completed_by = Yii::$app->user->id ?: null;
            if (!$checkout->save(false, ['status', 'inspector_signed_at', 'completed_at', 'completed_by', 'updated_at', 'updated_by'])) {
                throw new \RuntimeException('ยืนยันรับคืนบ้านพักไม่สำเร็จ');
            }
            $occupancy->status = Occupancy::STATUS_ENDED;
            $occupancy->end_date = $checkout->checkout_date;
            $occupancy->move_out_reason = $checkout->move_out_reason;
            if (!$occupancy->save(false, ['status', 'end_date', 'move_out_reason', 'updated_at', 'updated_by'])) {
                throw new \RuntimeException('ปิดสถานะผู้พักไม่สำเร็จ');
            }
            Resident::updateAll(
                ['status' => 'ended', 'end_date' => $checkout->checkout_date, 'updated_at' => new Expression('NOW()')],
                ['occupancy_id' => $occupancy->id, 'status' => 'active']
            );
            $request = $occupancy->request_id ? HousingRequest::findOne($occupancy->request_id) : null;
            if ($request && $request->status === HousingRequest::STATUS_ACTIVE) {
                (new RequestWorkflowService())->transition($request, HousingRequest::STATUS_COMPLETED, 'ลงนามส่งคืนบ้านพักครบทั้งสองฝ่าย');
            }
            (new UnitStatusService())->refresh((int)$occupancy->unit_id);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
