<?php

namespace app\modules\jd\models;

use yii\db\ActiveRecord;

class JdTemplateBlock extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%jd_template_block}}';
    }

    public function rules()
    {
        return [
            [['template_id', 'section_code', 'title'], 'required'],
            [['template_id', 'sort_order', 'is_enabled'], 'integer'],
            [['data_json', 'updated_at'], 'safe'],
            [['section_code'], 'string', 'max' => 40],
            [['title'], 'string', 'max' => 255],
            [['block_type'], 'string', 'max' => 30],
        ];
    }

    public function getTemplate()
    {
        return $this->hasOne(JdTemplate::class, ['id' => 'template_id']);
    }

    public function getData(): array
    {
        $value = json_decode((string) $this->data_json, true);
        return is_array($value) ? $value : [];
    }

    public function setData(array $value): void
    {
        $this->data_json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function definitions(): array
    {
        return [
            'job_identification' => ['1. ข้อมูลตำแหน่งงาน', 'fields'],
            'job_purpose' => ['2. วัตถุประสงค์หลักของตำแหน่ง', 'prose'],
            'scope_overview' => ['3. ภาพรวมขอบเขตงาน', 'matrix'],
            'responsibilities' => ['4. หน้าที่ความรับผิดชอบหลัก', 'groups'],
            'kpi' => ['5. ตัวชี้วัดผลการปฏิบัติงาน', 'kpi'],
            'qualifications' => ['6. คุณสมบัติประจำตำแหน่ง', 'groups'],
            'competencies' => ['7. สมรรถนะหลักประจำตำแหน่ง (Competencies)', 'competency'],
            'role_boundary' => ['8. ขอบเขตบทบาทและการแบ่งความรับผิดชอบ', 'boundary'],
            'working_conditions' => ['9. สภาพการทำงาน', 'named_items'],
            'approval' => ['10. การอนุมัติเอกสาร', 'approval'],
        ];
    }

    public static function hospitalDefinitions(bool $enabledOnly = true): array
    {
        $defaults = JdStructureDefault::ordered();
        if ($defaults === []) {
            return self::definitions();
        }

        $definitions = [];
        $number = 1;
        foreach ($defaults as $default) {
            if ($enabledOnly && !(int) $default->is_enabled) {
                continue;
            }
            $definitions[$default->section_code] = [
                $number . '. ' . $default->title,
                $default->block_type,
            ];
            $number++;
        }
        return $definitions;
    }

    public static function editorColumns(string $type): array
    {
        $map = [
            'fields' => ['label' => 'หัวข้อ', 'value' => 'รายละเอียด'],
            'prose' => ['content' => 'เนื้อหา'],
            'matrix' => ['area' => 'ด้าน', 'details' => 'ขอบเขตงาน'],
            'groups' => ['group' => 'หมวดย่อย', 'details' => 'รายละเอียด'],
            'authority' => ['subject' => 'เรื่อง', 'can_do' => 'ตัดสินใจ/ดำเนินการได้', 'approval' => 'ต้องประสานหรือขออนุมัติ'],
            'kpi' => ['indicator' => 'ชื่อตัวชี้วัด', 'target' => 'เป้าหมาย', 'expectation' => 'ความคาดหวัง'],
            'competency' => ['competency' => 'สมรรถนะ', 'behavior' => 'พฤติกรรมที่แสดงออก', 'required_level' => 'ระดับที่ต้องการ'],
            'named_items' => ['name' => 'หัวข้อ', 'description' => 'คำอธิบาย'],
            'boundary' => ['responsibility' => 'ทำได้/รับผิดชอบ', 'coordinate' => 'ต้องไม่ทำ/ต้องประสาน'],
            'approval' => ['role' => 'ขั้นตอนการลงนาม', 'employee_id' => 'ผู้ลงนาม'],
        ];
        return $map[$type] ?? $map['named_items'];
    }

    public static function editorType(string $type, ?string $sectionCode = null): string
    {
        return $sectionCode === 'competencies' ? 'competency' : $type;
    }

    public static function isTabularType(string $type): bool
    {
        return in_array($type, [
            'fields',
            'matrix',
            'groups',
            'authority',
            'kpi',
            'competency',
            'named_items',
            'boundary',
        ], true);
    }

    public static function ensureForTemplate(int $templateId): void
    {
        $defaults = JdStructureDefault::ordered();
        if ($defaults !== []) {
            $enabledCodes = [];
            $order = 10;
            $displayNumber = 1;
            foreach ($defaults as $default) {
                $code = (string) $default->section_code;
                $enabledCodes[] = $code;
                $block = self::findOne(['template_id' => $templateId, 'section_code' => $code]);
                if (!$block) {
                    $block = new self(['template_id' => $templateId, 'section_code' => $code]);
                    $block->setData(['intro' => '', 'items' => []]);
                }
                $block->title = $displayNumber . '. ' . $default->title;
                $block->block_type = $default->block_type;
                $block->sort_order = $order;
                $block->is_enabled = $default->is_enabled;
                $block->save(false);
                if ((int) $default->is_enabled === 1) {
                    $displayNumber++;
                }
                $order += 10;
            }
            self::updateAll(['is_enabled' => 0], ['and', ['template_id' => $templateId], ['not in', 'section_code', $enabledCodes]]);
            return;
        }

        $order = 10;
        foreach (self::definitions() as $code => [$title, $type]) {
            $block = self::findOne(['template_id' => $templateId, 'section_code' => $code]);
            if (!$block) {
                $block = new self(['template_id' => $templateId, 'section_code' => $code]);
                $block->title = $title;
                $block->block_type = $type;
                $block->sort_order = $order;
                $block->save(false);
            }
            $order += 10;
        }
        self::updateAll(['is_enabled' => 0], ['template_id' => $templateId, 'section_code' => 'authority']);
    }
}
