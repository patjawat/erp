<?php

namespace app\modules\serviceProfile\services;

use app\modules\serviceProfile\models\ServiceProfileTemplate;
use app\modules\serviceProfile\models\ServiceProfileTemplateSection;
use Yii;

class TemplateService
{
    public static function defaultSections(): array
    {
        return [
            ['introduction_objectives', '1. บทนำและวัตถุประสงค์', 'rich_text', true],
            ['context_scope', '2. บริบทและขอบเขตบริการ', 'service_scope_table', true],
            ['service_recipients', '3. กลุ่มผู้รับบริการและความต้องการสำคัญ', 'stakeholder_table', true],
            ['team_structure', '4. โครงสร้างทีมและหน้าที่รับผิดชอบ', 'team_responsibility_table', true],
            ['key_processes', '5. กระบวนการสำคัญของหน่วยงาน', 'key_process_table', true],
            ['service_guidelines', '6. แนวทางบริการตามกลุ่มผู้รับบริการและสถานการณ์สำคัญ', 'service_guideline_table', true],
            ['safety_risk_control', '7. มาตรฐานความปลอดภัยและการควบคุมความเสี่ยง', 'risk_control_table', true],
            ['risk_profile', '8. Risk Profile และมาตรการป้องกัน', 'risk_profile_table', true],
            ['quality_kpi', '9. ตัวชี้วัดคุณภาพและ KPI Dashboard', 'kpi_table', true],
            ['staff_competency', '10. สมรรถนะบุคลากร', 'competency_table', true],
            ['work_instructions', '11. WI และแนวทางปฏิบัติงานที่สำคัญ', 'document_reference_table', true],
            ['forms_appendices', '12. แบบฟอร์มและภาคผนวก', 'document_reference_table', false],
            ['quality_development', '13. แผนพัฒนาคุณภาพและการทบทวนผลลัพธ์', 'development_plan_table', true],
            ['references', '14. เอกสารอ้างอิง', 'reference_table', false],
        ];
    }

    public function seedDefaultSections(ServiceProfileTemplate $template): void
    {
        $order = 10;
        foreach (self::defaultSections() as [$code, $title, $type, $required]) {
            $section = new ServiceProfileTemplateSection([
                'template_id' => $template->id, 'section_code' => $code, 'title' => $title,
                'block_type' => $type, 'is_required' => $required, 'is_enabled' => 1, 'sort_order' => $order,
            ]);
            if (!$section->save()) {
                throw new \RuntimeException(implode(' ', $section->getFirstErrors()));
            }
            $order += 10;
        }
    }

    public function publish(ServiceProfileTemplate $template): void
    {
        $tx = Yii::$app->db->beginTransaction();
        try {
            ServiceProfileTemplate::updateAll([
                'is_active' => 0, 'lifecycle_status' => ServiceProfileTemplate::STATUS_RETIRED,
            ], ['owner_type' => $template->owner_type, 'owner_id' => $template->owner_id, 'is_active' => 1]);
            $template->is_active = 1;
            $template->lifecycle_status = ServiceProfileTemplate::STATUS_ACTIVE;
            if (!$template->save()) throw new \RuntimeException(implode(' ', $template->getFirstErrors()));
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function cloneRevision(ServiceProfileTemplate $source): ServiceProfileTemplate
    {
        $tx = Yii::$app->db->beginTransaction();
        try {
            $revision = (int) ServiceProfileTemplate::find()->where(['owner_type' => $source->owner_type, 'owner_id' => $source->owner_id])->max('revision_no') + 1;
            $copy = new ServiceProfileTemplate([
                'owner_type' => $source->owner_type, 'owner_id' => $source->owner_id,
                'owner_name_snapshot' => $source->owner_name_snapshot, 'name' => $source->name,
                'revision_no' => $revision, 'effective_fiscal_year' => $source->effective_fiscal_year,
                'parent_template_id' => $source->id, 'description' => $source->description,
                'lifecycle_status' => ServiceProfileTemplate::STATUS_DRAFT, 'is_active' => 0,
            ]);
            if (!$copy->save()) throw new \RuntimeException(implode(' ', $copy->getFirstErrors()));
            foreach ($source->sections as $section) {
                $item = new ServiceProfileTemplateSection([
                    'template_id' => $copy->id, 'section_code' => $section->section_code,
                    'title' => $section->title, 'description' => $section->description,
                    'block_type' => $section->block_type, 'config_json' => $section->config_json,
                    'is_required' => $section->is_required, 'is_enabled' => $section->is_enabled,
                    'sort_order' => $section->sort_order,
                ]);
                if (!$item->save()) throw new \RuntimeException(implode(' ', $item->getFirstErrors()));
            }
            $tx->commit();
            return $copy;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }
}
