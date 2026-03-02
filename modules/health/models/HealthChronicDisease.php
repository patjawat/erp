<?php

namespace app\modules\health\models;

/**
 * Facade สำหรับหมวด chronic_disease ใน health_option
 * ให้โค้ดเดิมที่เรียก HealthChronicDisease::getActiveList() ยังคงทำงานได้
 */
class HealthChronicDisease
{
    const CATEGORY = HealthOption::CATEGORY_CHRONIC_DISEASE;

    /**
     * คืน [code => title] ของโรคประจำตัวที่ใช้งานอยู่
     * fallback ค่า hardcode ถ้ายังไม่ได้รัน SQL
     */
    public static function getActiveList(): array
    {
        $list = HealthOption::getList(self::CATEGORY);
        return !empty($list) ? $list : self::defaultList();
    }

    public static function defaultList(): array
    {
        return [
            'DM'     => 'DM (เบาหวาน)',
            'HT'     => 'HT (ความดันโลหิตสูง)',
            'DLP'    => 'DLP (ไขมันในเลือด)',
            'Heart'  => 'โรคหัวใจ',
            'Kidney' => 'โรคไต',
            'other'  => 'อื่นๆ',
        ];
    }
}
