<?php

namespace app\modules\pm\models;

/** ทะเบียนตอน HA (Part) — Part เดียวต่อตัวชี้วัด */
class KpiHaPart extends StrategyRecord
{
    public static function tableName(): string { return '{{%pm_kpi_ha_part}}'; }

    public function rules(): array
    {
        return [
            [['code', 'name'], 'required'],
            [['sort_order'], 'integer'],
            ['is_active', 'boolean'],
            ['code', 'string', 'max' => 20],
            ['name', 'string', 'max' => 255],
            ['code', 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return ['code' => 'รหัสตอน', 'name' => 'ชื่อตอน', 'sort_order' => 'ลำดับ', 'is_active' => 'ใช้งาน'];
    }

    /** ตอนที่ใช้งาน เรียงตามลำดับ */
    public static function activeParts(): array
    {
        return self::find()->where(['is_active' => true])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
    }
}
