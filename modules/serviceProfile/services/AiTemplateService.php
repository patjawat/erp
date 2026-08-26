<?php

namespace app\modules\serviceProfile\services;

use app\modules\ai\services\AiProviderFactory;
use app\modules\ai\services\AuditLogger;
use app\modules\serviceProfile\forms\AiTemplateForm;
use app\modules\serviceProfile\models\ServiceProfileTemplate;
use app\modules\serviceProfile\models\ServiceProfileTemplateSection;
use Yii;

class AiTemplateService
{
    public const BLOCK_TYPES = ['rich_text','goal_ha_table','service_scope_table','stakeholder_table','year_series_table','quality_dimension_table','challenge_risk_table','staffing_table','key_process_table','cqi_review_table','kpi_table','pppp_process','development_plan_table','risk_incident_table','risk_control_table','integration_table','attachment','team_responsibility_table','service_guideline_table','risk_profile_table','competency_table','document_reference_table','reference_table'];

    public function generate(AiTemplateForm $form): ServiceProfileTemplate
    {
        if (!$form->validate()) throw new \DomainException(implode(' ', $form->getFirstErrors()));
        $resolved = (new OwnerDirectoryService())->resolveOwner((int) $form->owner_id, (int) $form->effective_fiscal_year);
        $owner = $resolved['unit'];
        $started = microtime(true);
        $request = ['owner' => $owner->name, 'name' => $form->name, 'mission' => $form->mission, 'focus' => $form->focus, 'section_count' => (int) $form->section_count];
        try {
            $provider = (new AiProviderFactory())->create('openrouter');
            $response = $provider->chat([[
                'role' => 'system',
                'content' => 'คุณเป็นผู้เชี่ยวชาญด้าน Hospital Accreditation (HA) และการออกแบบ Service Profile ของโรงพยาบาลไทย สร้างโครง Template ที่กระชับ ไม่ซ้ำกัน ใช้ภาษาไทย และเรียก tool เท่านั้น ห้ามใส่ข้อมูลผู้ป่วยหรือข้อมูลส่วนบุคคล เลือก block type แบบตารางสำหรับข้อมูลที่ต้องบันทึกซ้ำเป็นรายข้อ โดยใช้ key_process_table สำหรับกระบวนการสำคัญ, kpi_table สำหรับตัวชี้วัด, risk_profile_table สำหรับ Risk Profile, competency_table สำหรับสมรรถนะ, development_plan_table สำหรับแผนพัฒนา และ document_reference_table สำหรับ WI หรือแบบฟอร์ม',
            ], [
                'role' => 'user',
                'content' => "หน่วยงาน: {$owner->name}\nชื่อ Template: {$form->name}\nภารกิจและบริการหลัก: {$form->mission}\nจุดเน้น/มาตรฐาน: " . ($form->focus ?: '-') . "\nต้องการประมาณ {$form->section_count} หัวข้อ จัดลำดับจากบริบท กระบวนการ ผลลัพธ์ ความเสี่ยง และแผนพัฒนา",
            ]], [$this->proposalTool()], [
                'temperature' => 0.15, 'max_tokens' => 4000,
                'tool_choice' => ['type' => 'function', 'function' => ['name' => 'propose_service_profile_template']],
            ]);
            $call = $response->getToolCalls()[0] ?? null;
            if (!$call || ($call['name'] ?? '') !== 'propose_service_profile_template') throw new \RuntimeException('AI ไม่ได้ส่งโครง Template ในรูปแบบที่ระบบรองรับ');
            $proposal = $this->validateProposal((array) ($call['arguments'] ?? []));
            $template = $this->saveDraft($form, $resolved, $proposal);
            (new AuditLogger())->log(['provider' => 'openrouter', 'tool_name' => 'propose_service_profile_template', 'action' => 'service_profile_template_generate', 'status' => 'success', 'row_count' => count($proposal['sections']), 'duration_ms' => (int) ((microtime(true)-$started)*1000), 'request' => $request, 'response' => ['template_id' => $template->id, 'model' => $response->getMetadata()['model'] ?? null, 'sections' => $proposal['sections']]]);
            return $template;
        } catch (\Throwable $e) {
            (new AuditLogger())->log(['provider' => 'openrouter', 'tool_name' => 'propose_service_profile_template', 'action' => 'service_profile_template_generate', 'status' => 'error', 'duration_ms' => (int) ((microtime(true)-$started)*1000), 'error_message' => $e->getMessage(), 'request' => $request]);
            throw $e;
        }
    }

    public function review(ServiceProfileTemplate $template): array
    {
        $started = microtime(true);
        $sections = array_map(static fn(ServiceProfileTemplateSection $section) => [
            'code' => $section->section_code, 'title' => $section->title,
            'description' => $section->description, 'block_type' => $section->block_type,
            'is_required' => (bool) $section->is_required, 'is_enabled' => (bool) $section->is_enabled,
        ], $template->sections);
        $request = ['template_id' => $template->id, 'owner' => $template->owner_name_snapshot, 'name' => $template->name, 'sections' => $sections];
        try {
            $provider = (new AiProviderFactory())->create('openrouter');
            $response = $provider->chat([[
                'role' => 'system',
                'content' => 'คุณเป็นผู้ตรวจทบทวน Template Service Profile ตามแนวทาง Hospital Accreditation (HA) วิเคราะห์ความครบถ้วน ความซ้ำ ความชัดเจน ลำดับ และความเหมาะสมของชนิดข้อมูล เรียก tool เท่านั้น ไม่แก้ข้อมูลโดยตรง',
            ], [
                'role' => 'user',
                'content' => 'ตรวจ Template ของหน่วยงาน ' . $template->owner_name_snapshot . "\nชื่อ: " . $template->name . "\nหัวข้อปัจจุบัน:\n" . json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]], [$this->reviewTool()], [
                'temperature' => 0.1, 'max_tokens' => 3500,
                'tool_choice' => ['type' => 'function', 'function' => ['name' => 'review_service_profile_template']],
            ]);
            $call = $response->getToolCalls()[0] ?? null;
            if (!$call || ($call['name'] ?? '') !== 'review_service_profile_template') throw new \RuntimeException('AI ไม่ได้ส่งผลทบทวนในรูปแบบที่ระบบรองรับ');
            $result = $this->validateReview((array) ($call['arguments'] ?? []));
            (new AuditLogger())->log(['provider'=>'openrouter','tool_name'=>'review_service_profile_template','action'=>'service_profile_template_review','status'=>'success','row_count'=>count($result['findings']),'duration_ms'=>(int)((microtime(true)-$started)*1000),'request'=>$request,'response'=>['model'=>$response->getMetadata()['model']??null,'result'=>$result]]);
            return $result;
        } catch (\Throwable $e) {
            (new AuditLogger())->log(['provider'=>'openrouter','tool_name'=>'review_service_profile_template','action'=>'service_profile_template_review','status'=>'error','duration_ms'=>(int)((microtime(true)-$started)*1000),'error_message'=>$e->getMessage(),'request'=>$request]);
            throw $e;
        }
    }

    private function proposalTool(): array
    {
        return ['type' => 'function', 'function' => [
            'name' => 'propose_service_profile_template',
            'description' => 'เสนอหัวข้อ Template Service Profile ที่พร้อมบันทึกเป็นฉบับร่าง',
            'parameters' => ['type' => 'object', 'additionalProperties' => false, 'required' => ['description','sections'], 'properties' => [
                'description' => ['type' => 'string'],
                'sections' => ['type' => 'array', 'minItems' => 6, 'maxItems' => 20, 'items' => ['type' => 'object', 'additionalProperties' => false, 'required' => ['code','title','description','block_type','is_required'], 'properties' => [
                    'code' => ['type' => 'string'], 'title' => ['type' => 'string'], 'description' => ['type' => 'string'],
                    'block_type' => ['type' => 'string', 'enum' => self::BLOCK_TYPES], 'is_required' => ['type' => 'boolean'],
                ]]],
            ]],
        ]];
    }

    private function reviewTool(): array
    {
        return ['type'=>'function','function'=>[
            'name'=>'review_service_profile_template','description'=>'รายงานผลทบทวน Template โดยไม่แก้ข้อมูล',
            'parameters'=>['type'=>'object','additionalProperties'=>false,'required'=>['score','summary','strengths','findings'],'properties'=>[
                'score'=>['type'=>'integer','minimum'=>0,'maximum'=>100], 'summary'=>['type'=>'string'],
                'strengths'=>['type'=>'array','items'=>['type'=>'string'],'maxItems'=>6],
                'findings'=>['type'=>'array','maxItems'=>15,'items'=>['type'=>'object','additionalProperties'=>false,'required'=>['severity','category','title','recommendation','section_codes'],'properties'=>[
                    'severity'=>['type'=>'string','enum'=>['high','medium','low']],
                    'category'=>['type'=>'string','enum'=>['missing','duplicate','clarity','order','block_type','required_setting']],
                    'title'=>['type'=>'string'],'recommendation'=>['type'=>'string'],
                    'section_codes'=>['type'=>'array','items'=>['type'=>'string']],
                ]]],
            ]],
        ]];
    }

    private function validateProposal(array $proposal): array
    {
        $sections = (array) ($proposal['sections'] ?? []);
        if (count($sections) < 6 || count($sections) > 20) throw new \RuntimeException('AI ส่งจำนวนหัวข้อไม่ถูกต้อง');
        $clean = []; $codes = [];
        foreach ($sections as $index => $section) {
            if (!is_array($section)) continue;
            $code = strtolower(trim((string) ($section['code'] ?? '')));
            $code = mb_substr(trim((string) preg_replace('/[^a-z0-9_]+/', '_', $code), '_') ?: 'section_' . ($index + 1), 0, 72);
            if (isset($codes[$code])) $code .= '_' . ($index + 1);
            $type = (string) ($section['block_type'] ?? 'rich_text');
            if (!in_array($type, self::BLOCK_TYPES, true)) $type = 'rich_text';
            $title = trim(strip_tags((string) ($section['title'] ?? '')));
            if ($title === '') continue;
            $codes[$code] = true;
            $clean[] = ['code' => $code, 'title' => mb_substr($title, 0, 255), 'description' => trim(strip_tags((string) ($section['description'] ?? ''))), 'block_type' => $type, 'is_required' => !empty($section['is_required'])];
        }
        if (count($clean) < 6) throw new \RuntimeException('AI ส่งหัวข้อที่ใช้งานได้ไม่เพียงพอ');
        return ['description' => trim(strip_tags((string) ($proposal['description'] ?? ''))), 'sections' => $clean];
    }

    private function validateReview(array $review): array
    {
        $findings = [];
        foreach ((array)($review['findings']??[]) as $finding) {
            if (!is_array($finding)) continue;
            $severity = in_array(($finding['severity']??''), ['high','medium','low'], true) ? $finding['severity'] : 'low';
            $findings[] = ['severity'=>$severity,'category'=>trim(strip_tags((string)($finding['category']??''))),'title'=>mb_substr(trim(strip_tags((string)($finding['title']??''))),0,255),'recommendation'=>trim(strip_tags((string)($finding['recommendation']??''))),'section_codes'=>array_values(array_filter(array_map(static fn($code)=>mb_substr(trim(strip_tags((string)$code)),0,80),(array)($finding['section_codes']??[]))))];
        }
        $priority = ['high'=>0,'medium'=>1,'low'=>2];
        usort($findings, static fn(array $a,array $b):int => ($priority[$a['severity']]??3) <=> ($priority[$b['severity']]??3));
        return ['score'=>max(0,min(100,(int)($review['score']??0))),'summary'=>trim(strip_tags((string)($review['summary']??''))),'strengths'=>array_slice(array_values(array_filter(array_map(static fn($value)=>trim(strip_tags((string)$value)),(array)($review['strengths']??[])))),0,6),'findings'=>$findings];
    }

    private function saveDraft(AiTemplateForm $form, array $resolved, array $proposal): ServiceProfileTemplate
    {
        $owner = $resolved['unit'];
        $ownerType = $resolved['owner_type'];
        $ownerId = $resolved['owner_id'];
        $tx = Yii::$app->db->beginTransaction();
        try {
            $revision = (int) ServiceProfileTemplate::find()->where(['owner_type' => $ownerType, 'owner_id' => $ownerId])->max('revision_no') + 1;
            $template = new ServiceProfileTemplate(['owner_type' => $ownerType, 'owner_id' => $ownerId, 'owner_name_snapshot' => $owner->name, 'name' => $form->name, 'revision_no' => max(1,$revision), 'effective_fiscal_year' => $form->effective_fiscal_year, 'description' => $proposal['description'], 'lifecycle_status' => ServiceProfileTemplate::STATUS_DRAFT, 'is_active' => 0]);
            if (!$template->save()) throw new \RuntimeException(implode(' ', $template->getFirstErrors()));
            foreach ($proposal['sections'] as $index => $section) {
                $row = new ServiceProfileTemplateSection(['template_id' => $template->id, 'section_code' => $section['code'], 'title' => $section['title'], 'description' => $section['description'], 'block_type' => $section['block_type'], 'is_required' => $section['is_required'], 'is_enabled' => 1, 'sort_order' => ($index + 1) * 10]);
                if (!$row->save()) throw new \RuntimeException(implode(' ', $row->getFirstErrors()));
            }
            $tx->commit(); return $template;
        } catch (\Throwable $e) { $tx->rollBack(); throw $e; }
    }
}
