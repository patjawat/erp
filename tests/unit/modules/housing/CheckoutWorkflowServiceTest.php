<?php

namespace tests\unit\modules\housing;

use app\modules\housing\models\Building;
use app\modules\housing\models\Checkout;
use app\modules\housing\models\HousingRequest;
use app\modules\housing\models\Occupancy;
use app\modules\housing\models\Unit as HousingUnit;
use app\modules\housing\services\CheckoutWorkflowService;
use Codeception\Test\Unit;

final class CheckoutWorkflowServiceTest extends Unit
{
    public function testFinalSignatureEndsOccupancyAndReleasesUnit(): void
    {
        $suffix = strtoupper(bin2hex(random_bytes(3)));
        $building = new Building(['code' => 'TEST-CO-B-' . $suffix, 'name' => 'อาคารทดสอบคืนห้อง', 'building_type' => Building::TYPE_HOUSE]);
        $this->assertTrue($building->save(), json_encode($building->errors, JSON_UNESCAPED_UNICODE));
        $unit = new HousingUnit(['building_id' => $building->id, 'code' => 'TEST-CO-U-' . $suffix, 'name' => 'ห้องทดสอบคืน', 'occupancy_mode' => HousingUnit::MODE_FAMILY, 'status' => HousingUnit::STATUS_OCCUPIED]);
        $this->assertTrue($unit->save(), json_encode($unit->errors, JSON_UNESCAPED_UNICODE));
        $request = new HousingRequest(['request_no' => 'TEST-CO-R-' . $suffix, 'request_type' => HousingRequest::TYPE_MOVE_IN, 'emp_id' => 990001, 'status' => HousingRequest::STATUS_ACTIVE]);
        $this->assertTrue($request->save(), json_encode($request->errors, JSON_UNESCAPED_UNICODE));
        $occupancy = new Occupancy(['request_id' => $request->id, 'emp_id' => 990001, 'payer_emp_id' => 990001, 'unit_id' => $unit->id, 'occupancy_type' => HousingUnit::MODE_FAMILY, 'status' => Occupancy::STATUS_ACTIVE, 'start_date' => date('Y-m-d', strtotime('-30 days'))]);
        $this->assertTrue($occupancy->save(), json_encode($occupancy->errors, JSON_UNESCAPED_UNICODE));
        $checkout = new Checkout([
            'checkout_no' => 'TEST-CO-' . $suffix,
            'occupancy_id' => $occupancy->id,
            'requested_date' => date('Y-m-d'),
            'checkout_date' => date('Y-m-d'),
            'move_out_reason' => 'ทดสอบปิดการเข้าพัก',
            'resident_emp_id' => 990001,
            'resident_name' => 'ผู้พักทดสอบ',
            'inspected_by_name' => 'ผู้ตรวจทดสอบ',
            'resident_signed_at' => date('Y-m-d H:i:s'),
            'status' => Checkout::STATUS_AWAITING_STAFF,
        ]);
        $this->assertTrue($checkout->save(), json_encode($checkout->errors, JSON_UNESCAPED_UNICODE));

        try {
            (new CheckoutWorkflowService())->signInspector($checkout);
            $occupancy->refresh();
            $unit->refresh();
            $request->refresh();
            $checkout->refresh();
            $this->assertSame(Checkout::STATUS_COMPLETED, $checkout->status);
            $this->assertSame(Occupancy::STATUS_ENDED, $occupancy->status);
            $this->assertSame(HousingUnit::STATUS_VACANT, $unit->status);
            $this->assertSame(HousingRequest::STATUS_COMPLETED, $request->status);
        } finally {
            $checkout->delete();
            $occupancy->delete();
            $request->delete();
            $unit->delete();
            $building->delete();
        }
    }
}
