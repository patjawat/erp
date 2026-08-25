<?php

namespace tests\unit\modules\iacRisk;

use app\modules\iacRisk\models\ServiceProcessVersion;
use app\modules\serviceProfile\services\SectionDefinitionService;
use Codeception\Test\Unit;

class ProcessDefinitionTest extends Unit
{
    public function testServiceProfileProcessFieldsContainOnlyNameAndObjective(): void
    {
        $this->assertSame([
            'process' => 'ชื่อกระบวนงาน',
            'objective' => 'วัตถุประสงค์',
        ], SectionDefinitionService::columns('key_process_table'));
    }

    public function testReviewWorkflowContainsEveryAnnualDecision(): void
    {
        $this->assertSame([
            ServiceProcessVersion::REVIEW_PENDING,
            ServiceProcessVersion::REVIEW_RETAINED,
            ServiceProcessVersion::REVIEW_MODIFIED,
            ServiceProcessVersion::REVIEW_NEW,
            ServiceProcessVersion::REVIEW_RETIRED,
        ], array_keys(ServiceProcessVersion::reviewLabels()));
    }
}
