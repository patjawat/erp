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
        return HealthOption::getList(self::CATEGORY);
    }

    public static function defaultList(): array
    {
        return [
            'h_diabetes'     => 'เบาหวาน',
            'h_hypertension' => 'ความดันสูง',
            'h_liver'        => 'โรคตับ',
            'h_stroke'       => 'อัมพาต',
            'h_heart'        => 'โรคหัวใจ',
            'h_dyslipidemia' => 'ไขมันเลือดผิดปกติ',
            'h_gastric'      => 'แผลในกระเพาะ',
            'h_birth'        => 'คลอดบุตร > 4kg',
            'h_thirst'       => 'ดื่มน้ำบ่อย',
            'h_nocturia'     => 'ปัสสาวะบ่อยกลางคืน',
            'h_fatigue'      => 'อ่อนเพลีย',
            'h_skin_itch'    => 'คันตามผิวหนัง',
            'h_vision'       => 'ตาพร่ามัว',
            'h_numbness'     => 'ชาปลายมือเท้า',
            'h_constipation' => 'ท้องผูกเรื้อรัง',
            'h_urinary'      => 'ฉี่ขัด/ปนเลือด',
        ];
    }
}
