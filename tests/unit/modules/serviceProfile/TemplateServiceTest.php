<?php

namespace tests\unit\modules\serviceProfile;

use app\modules\serviceProfile\models\ServiceProfileTemplateSection;
use app\modules\serviceProfile\services\TemplateService;
use Codeception\Test\Unit;

class TemplateServiceTest extends Unit
{
    public function testDefaultSectionsHaveUniqueCodesAndSupportedTypes(): void
    {
        $sections = TemplateService::defaultSections();
        $codes = array_column($sections, 0);
        $supportedTypes = ServiceProfileTemplateSection::blockTypeLabels();

        $this->assertNotEmpty($sections);
        $this->assertSame($codes, array_values(array_unique($codes)));

        foreach ($sections as [$code, $title, $type, $required]) {
            $this->assertNotSame('', trim($code));
            $this->assertNotSame('', trim($title));
            $this->assertArrayHasKey($type, $supportedTypes);
            $this->assertIsBool($required);
        }
    }

    public function testDefaultTemplateCoversSourceDocumentSections(): void
    {
        $codes = array_column(TemplateService::defaultSections(), 0);

        foreach (['introduction_objectives', 'context_scope', 'service_recipients', 'team_structure', 'key_processes', 'service_guidelines', 'safety_risk_control', 'risk_profile', 'quality_kpi', 'staff_competency', 'work_instructions', 'forms_appendices', 'quality_development', 'references'] as $requiredCode) {
            $this->assertContains($requiredCode, $codes);
        }
        $this->assertCount(14, $codes);
    }
}
