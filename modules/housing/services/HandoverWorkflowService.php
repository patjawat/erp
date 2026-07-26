<?php

declare(strict_types=1);

namespace app\modules\housing\services;

use app\modules\housing\models\Handover;
use app\modules\housing\models\HousingRequest;
use app\modules\housing\models\Occupancy;
use app\modules\housing\models\Resident;
use app\modules\hr\models\Employees;
use Yii;
use yii\db\Expression;

final class HandoverWorkflowService
{
    public function signSender(Handover $handover): void
    {
        $this->assertDraft($handover);
        $this->assertAssetsChecked($handover);
        if ($handover->handed_over_signed_at !== null) {
            throw new \DomainException('ผู้ส่งมอบลงนามแล้ว');
        }
        $handover->handed_over_signed_at = new Expression('NOW()');
        if (!$handover->save(false, ['handed_over_signed_at', 'updated_at', 'updated_by'])) {
            throw new \RuntimeException('ไม่สามารถบันทึกลายมือชื่อผู้ส่งมอบได้');
        }
    }

    public function signReceiver(Handover $handover, int $employeeId): void
    {
        $this->assertDraft($handover);
        if ((int)$handover->received_by_emp_id !== $employeeId) {
            throw new \DomainException('ไม่มีสิทธิ์ลงนามรับมอบเอกสารนี้');
        }
        if ($handover->handed_over_signed_at === null) {
            throw new \DomainException('ผู้ดูแลยังไม่ได้ลงนามส่งมอบ');
        }
        if ($handover->received_signed_at !== null) {
            throw new \DomainException('ผู้รับมอบลงนามแล้ว');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $handover->received_signed_at = new Expression('NOW()');
            if (!$handover->save(false, ['received_signed_at', 'updated_at', 'updated_by'])) {
                throw new \RuntimeException('ไม่สามารถบันทึกลายมือชื่อผู้รับมอบได้');
            }
            $this->activateOccupancy($handover);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function activateOccupancy(Handover $handover): void
    {
        $occupancy = $handover->occupancy;
        $request = $occupancy?->request_id ? HousingRequest::findOne($occupancy->request_id) : null;
        if (!$occupancy || !$request || $request->status !== HousingRequest::STATUS_ALLOCATED) {
            throw new \DomainException('สถานะการจัดสรรไม่พร้อมยืนยันเข้าพัก');
        }

        $now = new Expression('NOW()');
        $handover->status = Handover::STATUS_CONFIRMED;
        $handover->confirmed_at = $now;
        $handover->confirmed_by = Yii::$app->user->id;
        if (!$handover->save(false, ['status', 'confirmed_at', 'confirmed_by', 'updated_at', 'updated_by'])) {
            throw new \RuntimeException('ไม่สามารถยืนยันเอกสารรับมอบได้');
        }

        $occupancy->status = Occupancy::STATUS_ACTIVE;
        $occupancy->start_date = $handover->handover_date;
        if (!$occupancy->save(false, ['status', 'start_date', 'updated_at', 'updated_by'])) {
            throw new \RuntimeException('ไม่สามารถเปิดสถานะเข้าพักได้');
        }
        $this->createEmployeeResident($occupancy);
        (new UnitStatusService())->refresh((int)$occupancy->unit_id);
        (new RequestWorkflowService())->transition($request, HousingRequest::STATUS_ACTIVE, 'ผู้ส่งมอบและผู้รับมอบลงนามครบแล้ว');
    }

    private function assertDraft(Handover $handover): void
    {
        if ($handover->status !== Handover::STATUS_DRAFT) {
            throw new \DomainException('เอกสารนี้ยืนยันรับมอบแล้ว');
        }
    }

    private function assertAssetsChecked(Handover $handover): void
    {
        foreach ($handover->assetItems() as $item) {
            if (empty($item['acknowledged'])) {
                throw new \DomainException('กรุณาตรวจรับอุปกรณ์ให้ครบทุกรายการก่อนลงนามส่งมอบ');
            }
        }
    }

    private function createEmployeeResident(Occupancy $occupancy): void
    {
        if (Resident::find()->where(['occupancy_id' => $occupancy->id, 'resident_type' => 'employee'])->exists()) {
            return;
        }
        $employee = Employees::findOne($occupancy->emp_id);
        if (!$employee) {
            return;
        }
        $resident = new Resident([
            'occupancy_id' => $occupancy->id,
            'resident_type' => 'employee',
            'relationship' => 'self',
            'prefix' => $employee->prefix,
            'first_name' => $employee->fname,
            'last_name' => $employee->lname,
            'citizen_id' => $employee->cid,
            'phone' => $employee->phone,
            'start_date' => $occupancy->start_date,
            'count_for_charge' => true,
        ]);
        if (!$resident->save()) {
            throw new \RuntimeException(implode(' ', $resident->getFirstErrors()));
        }
    }
}
