<?php

namespace app\modules\health\models;

/**
 * Facade สำหรับหมวด family_disease ใน health_option
 * ให้โค้ดเดิมที่เรียก HealthFamilyDisease::getActiveList() ยังคงทำงานได้
 */
class HealthFamilyDisease
{
    const CATEGORY = HealthOption::CATEGORY_FAMILY_DISEASE;

    /**
     * คืน [code => title] ของโรคในครอบครัวที่ใช้งานอยู่
     * fallback ค่า hardcode ถ้ายังไม่ได้รัน SQL
     */
    public static function getActiveList(): array
    {
        return HealthOption::getList(self::CATEGORY);
    }

    public static function defaultList(): array
    {
        return [
            'diabetes'     => 'เบาหวาน',
            'hypertension' => 'ความดันสูง',
            'gout'         => 'เก๊าท์',
            'kidney'       => 'ไตวาย',
            'heart'        => 'หัวใจ',
            'stroke'       => 'อัมพาต',
            'emphysema'    => 'ถุงลมโป่งพอง',
            'unknown'      => 'ไม่ทราบ',
        ];
    }
}
