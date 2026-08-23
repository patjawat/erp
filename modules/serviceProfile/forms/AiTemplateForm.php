<?php

namespace app\modules\serviceProfile\forms;

use yii\base\Model;

class AiTemplateForm extends Model
{
    public $owner_id;
    public $name;
    public $mission;
    public $focus;
    public $section_count = 12;
    public $effective_fiscal_year;

    public function rules(): array
    {
        return [
            [['owner_id', 'name', 'mission', 'effective_fiscal_year'], 'required'],
            [['owner_id', 'section_count', 'effective_fiscal_year'], 'integer'],
            [['section_count'], 'integer', 'min' => 6, 'max' => 20],
            [['effective_fiscal_year'], 'integer', 'min' => 2500, 'max' => 2700],
            [['name'], 'string', 'max' => 255],
            [['mission', 'focus'], 'string', 'max' => 4000],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'owner_id' => 'หน่วยงาน / ทีมประสาน', 'name' => 'ชื่อ Template',
            'mission' => 'ภารกิจและบริการหลัก', 'focus' => 'จุดเน้นหรือมาตรฐานที่ต้องการ',
            'section_count' => 'จำนวนหัวข้อโดยประมาณ', 'effective_fiscal_year' => 'เริ่มใช้ปีงบประมาณ',
        ];
    }
}
