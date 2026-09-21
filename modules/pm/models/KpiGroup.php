<?php

namespace app\modules\pm\models;

/**
 * กลุ่มตัวชี้วัด (KPI group)
 * kind=strategy → กลุ่มที่ดึงข้อมูลจากทะเบียนยุทธศาสตร์ (pm_strategy_indicator)
 * kind=standalone → กลุ่มที่เก็บใน pm_kpi_indicator
 */
class KpiGroup extends StrategyRecord
{
    public const KIND_STRATEGY = 'strategy';
    public const KIND_STANDALONE = 'standalone';

    public static function tableName(): string { return '{{%pm_kpi_group}}'; }

    public function rules(): array
    {
        return [
            [['code', 'name'], 'required'],
            [['sort_order'], 'integer'],
            ['is_active', 'boolean'],
            [['code'], 'string', 'max' => 50],
            [['name', 'name_en'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['icon', 'color'], 'string', 'max' => 50],
            ['kind', 'in', 'range' => array_keys(self::kindList())],
            ['kind', 'default', 'value' => self::KIND_STANDALONE],
            [['code'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'code' => 'รหัสกลุ่ม', 'name' => 'ชื่อกลุ่ม (ไทย)', 'name_en' => 'ชื่อกลุ่ม (English)',
            'description' => 'คำอธิบาย', 'kind' => 'ชนิด',
            'icon' => 'ไอคอน', 'color' => 'สี', 'sort_order' => 'ลำดับ', 'is_active' => 'ใช้งาน',
        ];
    }

    public static function kindList(): array
    {
        return [self::KIND_STRATEGY => 'ยุทธศาสตร์ (ดึงจากแผน)', self::KIND_STANDALONE => 'นอกแผน (คีย์เอง)'];
    }

    public function getIndicators() { return $this->hasMany(KpiIndicator::class, ['group_id' => 'id'])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]); }

    public function isStrategy(): bool { return $this->kind === self::KIND_STRATEGY; }

    /** กลุ่มที่ใช้งาน เรียงตามลำดับ */
    public static function activeGroups(): array
    {
        return self::find()->where(['is_active' => true])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
    }
}
