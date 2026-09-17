<?php

namespace tests\unit\modules\laundry;

use app\modules\laundry\services\InventoryAuditReport;
use Codeception\Test\Unit;

class InventoryAuditReportTest extends Unit
{
    public function testTransfersDoNotChangeHospitalTotal(): void
    {
        $in = [
            ['item_id' => 1, 'location' => 'CLEAN', 'department_id' => null, 'qty' => 100],
            ['item_id' => 1, 'location' => 'WARD', 'department_id' => 9, 'qty' => 40],
            ['item_id' => 1, 'location' => 'DIRTY', 'department_id' => null, 'qty' => 12],
            ['item_id' => 1, 'location' => 'DISPOSED', 'department_id' => null, 'qty' => 3],
        ];
        $out = [
            ['item_id' => 1, 'location' => 'EXTERNAL', 'department_id' => null, 'qty' => 100],
            ['item_id' => 1, 'location' => 'CLEAN', 'department_id' => null, 'qty' => 40],
            ['item_id' => 1, 'location' => 'WARD', 'department_id' => 9, 'qty' => 15],
        ];
        $result = InventoryAuditReport::summarize($in, $out)[1];
        $this->assertSame(97, $result['expected_qty']);
        $this->assertSame(97, $result['actual_qty']);
        $this->assertSame(0, $result['difference_qty']);
        $this->assertSame(25, $result['ward_qty']);
        $this->assertSame([], $result['negative_locations']);
    }

    public function testNegativeLocationIsFlaggedEvenWhenOverallTotalBalances(): void
    {
        $in = [
            ['item_id' => 2, 'location' => 'CLEAN', 'department_id' => null, 'qty' => 10],
            ['item_id' => 2, 'location' => 'WARD', 'department_id' => 5, 'qty' => 12],
        ];
        $out = [
            ['item_id' => 2, 'location' => 'EXTERNAL', 'department_id' => null, 'qty' => 10],
            ['item_id' => 2, 'location' => 'CLEAN', 'department_id' => null, 'qty' => 12],
        ];
        $result = InventoryAuditReport::summarize($in, $out)[2];
        $this->assertSame(0, $result['difference_qty']);
        $this->assertSame([['location' => 'CLEAN', 'department_id' => 0, 'qty' => -2]], $result['negative_locations']);
    }

    public function testWarehouseHoldingAndCirculatingTotalAreComparedWithoutMutatingEither(): void
    {
        $rows = InventoryAuditReport::compareWarehouse([
            ['item_code' => 'LIN-1', 'item_name' => 'ผ้าห่ม', 'stock_item_code' => 'MAT-1', 'audit' => ['actual_qty' => 98]],
            ['item_code' => 'LIN-2', 'item_name' => 'ผ้าปู', 'stock_item_code' => null, 'audit' => ['actual_qty' => 25]],
        ], [['item_code' => 'MAT-1', 'qty' => '100.000']]);
        $this->assertCount(1, $rows);
        $this->assertSame(100.0, $rows[0]['warehouse_qty']);
        $this->assertSame(98, $rows[0]['circulating_qty']);
        $this->assertSame(2.0, $rows[0]['difference_qty']);
        $source = file_get_contents(dirname(__DIR__, 4) . '/modules/laundry/services/InventoryAuditReport.php');
        $this->assertStringContainsString("->from('stock_balance')", $source);
        $this->assertStringNotContainsString("->update('stock_balance'", $source);
    }
}
