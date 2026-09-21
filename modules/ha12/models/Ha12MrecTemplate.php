<?php

namespace app\modules\ha12\models;

/**
 * แม่แบบหัวข้อความสมบูรณ์เวชระเบียน (กิจกรรม 9)
 *
 * 12 หัวข้อมาตรฐานการตรวจ content ของเวชระเบียน (ชุดเริ่มต้น — หน่วยงานแก้/เพิ่ม/ลบได้)
 */
class Ha12MrecTemplate
{
    /** @return string[] 12 หัวข้อมาตรฐาน */
    public static function items(): array
    {
        return [
            'ข้อมูลผู้ป่วย/หน้าปก (Patient Profile)',
            'ประวัติการเจ็บป่วย (History)',
            'การตรวจร่างกาย (Physical Examination)',
            'การวินิจฉัยโรค (Diagnosis)',
            'แผนการรักษา/การดูแล (Treatment Plan)',
            'คำสั่งการรักษา (Physician Order)',
            'บันทึกความก้าวหน้า (Progress Note)',
            'บันทึกทางการพยาบาล (Nurse\'s Note)',
            'ผลตรวจทางห้องปฏิบัติการ/รังสี (Investigation)',
            'ใบยินยอมรับการรักษา (Informed Consent)',
            'สรุปการจำหน่ายผู้ป่วย (Discharge Summary)',
            'การลงลายมือชื่อผู้บันทึก (Authentication)',
        ];
    }
}
