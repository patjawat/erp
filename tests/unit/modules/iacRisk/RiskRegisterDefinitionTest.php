<?php

namespace tests\unit\modules\iacRisk;

use app\modules\iacRisk\models\RiskRegister;
use app\modules\iacRisk\services\RiskMatrixService;
use Codeception\Test\Unit;

class RiskRegisterDefinitionTest extends Unit
{
    public function testSourceTypesSeparateCsaAndManualRisks(): void
    {
        $this->assertSame('csa',RiskRegister::SOURCE_CSA);
        $this->assertSame('manual',RiskRegister::SOURCE_MANUAL);
    }

    public function testFiveByFiveRiskLevelsCoverBoundaryScores(): void
    {
        $this->assertSame('low',RiskMatrixService::evaluate(1,3)['code']);
        $this->assertSame('moderate',RiskMatrixService::evaluate(2,2)['code']);
        $this->assertSame('high',RiskMatrixService::evaluate(2,5)['code']);
        $this->assertSame('very_high',RiskMatrixService::evaluate(5,5)['code']);
        $this->assertNull(RiskMatrixService::evaluate(null,5));
    }
}
