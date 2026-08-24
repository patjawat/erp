<?php

namespace tests\unit\modules\serviceProfile;

use app\modules\serviceProfile\models\ServiceProfile;
use app\modules\serviceProfile\models\ServiceProfileApproval;
use app\modules\serviceProfile\models\ServiceProfileTemplateSection;
use app\modules\serviceProfile\services\SectionDefinitionService;
use Codeception\Test\Unit;

class SectionDefinitionServiceTest extends Unit
{
    public function testEveryStructuredBlockHasColumns(): void
    {
        foreach (ServiceProfileTemplateSection::blockTypeLabels() as $type => $label) {
            if (in_array($type, ['rich_text', 'attachment'], true)) continue;
            $columns = SectionDefinitionService::columns($type);
            $this->assertNotEmpty($columns, $type . ' must define editor columns');
            $this->assertSame(array_keys($columns), array_values(array_unique(array_keys($columns))));
        }
    }

    public function testWorkflowStatusesAndStagesRemainDistinct(): void
    {
        $statuses = array_keys(ServiceProfile::statusLabels());
        $this->assertCount(count(array_unique($statuses)), $statuses);
        $this->assertContains(ServiceProfile::STATUS_REVIEW_PENDING, $statuses);
        $this->assertContains(ServiceProfile::STATUS_APPROVAL_PENDING, $statuses);
        $this->assertContains(ServiceProfile::STATUS_ACKNOWLEDGEMENT_PENDING, $statuses);
        $this->assertNotSame(ServiceProfileApproval::STAGE_DIRECTOR, ServiceProfileApproval::STAGE_HEAD);
    }
}
