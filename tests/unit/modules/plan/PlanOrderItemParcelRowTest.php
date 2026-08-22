<?php

namespace tests\unit\modules\plan;

use app\modules\plan\models\PlanOrderItem;
use Codeception\Test\Unit;

class PlanOrderItemParcelRowTest extends Unit
{
    public function testParcelRowKeepsTheMaterialCodeSoThePlanLinksBackToStock(): void
    {
        $row = PlanOrderItem::parcelRowAttributes(7, [
            'code' => '07-00104',
            'item_name' => 'Leukocyte Poor Packed Red Cells',
            'qty' => '669',
            'unit_price' => '550.00',
        ]);

        $this->assertSame(7, $row['plan_order_id']);
        $this->assertSame('07-00104', $row['item_id']);
        $this->assertSame('Leukocyte Poor Packed Red Cells', $row['item_name']);
        $this->assertSame('Leukocyte Poor Packed Red Cells', $row['title']);
    }

    public function testParcelRowComputesTheLineTotal(): void
    {
        $row = PlanOrderItem::parcelRowAttributes(7, [
            'code' => '07-00104',
            'item_name' => 'ทดสอบ',
            'qty' => '669',
            'unit_price' => '550.00',
        ]);

        $this->assertSame(367950.0, $row['total_price']);
    }

    public function testHandTypedRowWithoutACodeStillSaves(): void
    {
        $row = PlanOrderItem::parcelRowAttributes(7, [
            'item_name' => 'ถุงมือยาง',
            'qty' => '10',
            'unit_price' => '25.5',
        ]);

        $this->assertSame('', $row['item_id']);
        $this->assertSame(255.0, $row['total_price']);
    }

    public function testBlankRowFromTheFormIsSkipped(): void
    {
        $this->assertNull(PlanOrderItem::parcelRowAttributes(7, ['item_name' => '   ', 'qty' => 5]));
        $this->assertNull(PlanOrderItem::parcelRowAttributes(7, ['qty' => 5, 'unit_price' => 10]));
    }

    public function testMissingQuantityOrPriceYieldsZeroTotalRatherThanAnError(): void
    {
        $row = PlanOrderItem::parcelRowAttributes(7, ['item_name' => 'ไม่ระบุจำนวน']);

        $this->assertSame(0, $row['qty']);
        $this->assertSame(0.0, $row['total_price']);
    }
}
