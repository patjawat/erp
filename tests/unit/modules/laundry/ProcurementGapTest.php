<?php

namespace tests\unit\modules\laundry;

use app\modules\laundry\services\ProcurementGapService;
use Codeception\Test\Unit;

class ProcurementGapTest extends Unit
{
    public function testCompleteCountsOffsetCentralCleanOnlyOnce(): void
    {
        $result = ProcurementGapService::calculate([
            ['target' => 100, 'actual' => 94],
            ['target' => 80, 'actual' => 70],
            ['target' => 20, 'actual' => 30],
        ], 5);
        $this->assertSame(200, $result['target_qty']);
        $this->assertSame(16, $result['ward_deficit']);
        $this->assertSame(11, $result['preliminary_gap']);
    }

    public function testMissingCountSuppressesGap(): void
    {
        $result = ProcurementGapService::calculate([
            ['target' => 100, 'actual' => 90],
            ['target' => 80, 'actual' => null],
        ], 3);
        $this->assertSame(1, $result['missing_departments']);
        $this->assertNull($result['preliminary_gap']);
    }

    public function testNoParDoesNotGeneratePurchaseNeed(): void
    {
        $this->assertNull(ProcurementGapService::calculate([], 0)['preliminary_gap']);
    }
}
