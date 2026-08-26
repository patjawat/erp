<?php

namespace tests\unit\modules\iacRisk;

use app\modules\iacRisk\models\Csa;
use app\modules\iacRisk\models\CsaRisk;
use Codeception\Test\Unit;

class CsaDefinitionTest extends Unit
{
    public function testCsaWorkflowStatesAreComplete(): void
    {
        $this->assertSame(['draft','author_confirmed','head_pending','head_approved','returned','coordinator_revised'], array_keys(Csa::statusLabels()));
    }

    public function testControlAdequacyOptionsAreComplete(): void
    {
        $this->assertSame(['not_assessed','adequate','inadequate'], array_keys(CsaRisk::adequacyLabels()));
    }
}
