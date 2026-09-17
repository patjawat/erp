<?php

namespace tests\unit\modules\laundry;

use app\modules\laundry\services\CollectionService;
use Codeception\Test\Unit;
use yii\base\InvalidArgumentException;

class CollectionServiceTest extends Unit
{
    public function testNetWeightSubtractsContainerAtThreeDecimalPlaces(): void
    {
        $this->assertSame('18.250', CollectionService::netKg('19.750', '1.500'));
        $this->assertSame('0.001', CollectionService::netKg('0.001', '0'));
    }

    public function testNonPositiveAndOverPrecisionWeightsAreRejected(): void
    {
        foreach ([['1', '1'], ['1', '2'], ['-1', '0'], ['1.0001', '0'], ['x', '0']] as [$gross, $tare]) {
            try {
                CollectionService::netKg($gross, $tare);
                $this->fail("Expected invalid weight for {$gross}/{$tare}");
            } catch (InvalidArgumentException $e) {
                $this->assertNotSame('', $e->getMessage());
            }
        }
    }

    public function testConfirmedRoundGuardsAndDepartmentScopedReportArePresent(): void
    {
        $root = dirname(__DIR__, 4);
        $service = file_get_contents($root . '/modules/laundry/services/CollectionService.php');
        $report = file_get_contents($root . '/modules/laundry/services/CollectionReport.php');
        $this->assertStringContainsString('FOR UPDATE', $service);
        $this->assertStringContainsString("'status' => 'CONFIRMED'", $service);
        $this->assertStringContainsString("$" . "round['status'] !== 'OPEN'", $service);
        $this->assertStringContainsString("'r.status' => 'CONFIRMED'", $report);
        $this->assertStringContainsString("'s.department_id' => $" . 'departmentId', $report);
        $this->assertStringContainsString("'s.collected_at'", $report);
    }
}
