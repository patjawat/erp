<?php

use yii\db\Migration;

/** Converts reusable SP content into structured items for reporting and review. */
class m260826_130000_structure_service_profile_sections extends Migration
{
    private array $sectionTypes = [
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
    ];

    public function safeUp()
    {
        foreach ($this->sectionTypes as $sectionCode => $blockType) {
            $condition = ['section_code' => $sectionCode, 'block_type' => 'rich_text'];
            $this->update('{{%service_profile_template_section}}', ['block_type' => $blockType], $condition);
            $this->update('{{%service_profile_section}}', ['block_type' => $blockType], $condition);
        }
    }

    public function safeDown()
    {
        foreach ($this->sectionTypes as $sectionCode => $blockType) {
            $condition = ['section_code' => $sectionCode, 'block_type' => $blockType];
            $this->update('{{%service_profile_template_section}}', ['block_type' => 'rich_text'], $condition);
            $this->update('{{%service_profile_section}}', ['block_type' => 'rich_text'], $condition);
        }
    }
}
