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

    public function testKeyProcessesUseStructuredFieldsForCsa(): void
    {
        $sections = [];
        foreach (TemplateService::defaultSections() as $section) $sections[$section[0]] = $section;

        $this->assertSame('key_process_table', $sections['key_processes'][2]);
        $this->assertSame(
            ['process' => 'ชื่อกระบวนงาน', 'objective' => 'วัตถุประสงค์'],
            \app\modules\serviceProfile\services\SectionDefinitionService::columns($sections['key_processes'][2])
        );
    }

    public function testReusableSectionsUseStructuredBlockTypes(): void
    {
        $types = [];
        foreach (TemplateService::defaultSections() as $section) $types[$section[0]] = $section[2];

        $this->assertSame([
            'context_scope' => 'service_scope_table',
            'service_recipients' => 'stakeholder_table',
            'team_structure' => 'team_responsibility_table',
            'service_guidelines' => 'service_guideline_table',
            'safety_risk_control' => 'risk_control_table',
            'risk_profile' => 'risk_profile_table',
            'quality_kpi' => 'kpi_table',
            'staff_competency' => 'competency_table',
            'work_instructions' => 'document_reference_table',
            'forms_appendices' => 'document_reference_table',
            'quality_development' => 'development_plan_table',
            'references' => 'reference_table',
        ], array_intersect_key($types, array_flip([
            'context_scope', 'service_recipients', 'team_structure', 'service_guidelines',
            'safety_risk_control', 'risk_profile', 'quality_kpi', 'staff_competency',
            'work_instructions', 'forms_appendices', 'quality_development', 'references',
        ])));
    }

    public function testKpiFieldsSupportAnnualReporting(): void
    {
        $columns = \app\modules\serviceProfile\services\SectionDefinitionService::columns('kpi_table');

        foreach (['indicator', 'definition', 'operator', 'target', 'unit', 'values', 'data_source', 'responsible', 'note'] as $field) {
            $this->assertArrayHasKey($field, $columns);
        }
    }
}
