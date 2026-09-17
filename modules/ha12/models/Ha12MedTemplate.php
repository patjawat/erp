<?php

namespace app\modules\ha12\models;

/**
 * แม่แบบความคลาดเคลื่อนทางยา (กิจกรรม 7) — อิงบท 7-8 ของคู่มือ HA12
 *
 * เก็บเป็น config กลาง (ไม่ใช่ตาราง): 5 หัวข้อหลัก + ความเสี่ยงย่อยมาตรฐาน
 * + หน่วยตัวหารที่ใช้ได้ + ระดับความรุนแรง ใช้ตอนสร้างรายงานใหม่ (auto-gen แถวย่อย)
 */
class Ha12MedTemplate
{
    /** ระดับความรุนแรง (คอลัมน์นับ) — key => ป้าย */
    public const SEVERITY = [
        'c_no_harm' => 'No Harm',
        'c_e' => 'E',
        'c_f' => 'F',
        'c_g' => 'G',
        'c_h' => 'H',
        'c_i' => 'I',
    ];

    /** ทีมผู้บันทึก */
    public const TEAMS = [
        'pharmacy' => 'เภสัชกรรม',
        'nursing' => 'พยาบาล',
        'joint' => 'ร่วม (เภสัช+พยาบาล)',
    ];

    /** หน่วยตัวหาร */
    public const DIVISOR_UNITS = [
        'prescription' => 'ใบสั่งยา',
        'patient_day' => 'วันนอน',
        'patient' => 'รายผู้ป่วย',
    ];

    /** ฐานอัตราที่เลือกได้ */
    public const RATE_BASES = [100, 1000, 10000];

    /**
     * 5 หัวข้อหลัก + ความเสี่ยงย่อยมาตรฐาน + หน่วยตัวหารเริ่มต้น
     * บท 8: หัวข้อ 1-4 ใช้ใบสั่งยา ; หัวข้อ 5 เลือกวันนอน/รายผู้ป่วย
     *
     * @return array<int, array{no:int,name:string,default_unit:string,units:string[],subrisks:string[]}>
     */
    public static function groups(): array
    {
        return [
            1 => [
                'no' => 1, 'name' => 'การสั่งใช้ยา (สั่งยาผิด)',
                'default_unit' => 'prescription', 'units' => ['prescription'],
                'subrisks' => [
                    'สั่งผิดขนาด', 'เลือกยาที่มีข้อห้ามใช้', 'สั่งยาที่ผู้ป่วยแพ้',
                    'ความถี่ผิด', 'เขียนชื่อยาผิด/ผิดชนิด', 'สั่งให้ยาผิดวิธี',
                ],
            ],
            2 => [
                'no' => 2, 'name' => 'การคัดลอกคำสั่งใช้ยา',
                'default_unit' => 'prescription', 'units' => ['prescription'],
                'subrisks' => [
                    'คัดลอกคลาดเคลื่อน (Ward)', 'คัดลอกคลาดเคลื่อน (เภสัชกรรม)',
                ],
            ],
            3 => [
                'no' => 3, 'name' => 'การจัดยา',
                'default_unit' => 'prescription', 'units' => ['prescription'],
                'subrisks' => [
                    'จัดผิดชนิด', 'จัดผิดคน', 'จัดผิดจำนวน', 'จัดผิดรูปแบบ',
                    'จัดผิดความแรง', 'จัดยาไม่ครบรายการ',
                ],
            ],
            4 => [
                'no' => 4, 'name' => 'การจ่ายยา',
                'default_unit' => 'prescription', 'units' => ['prescription'],
                'subrisks' => [
                    'จ่ายผิดคน', 'จ่ายผิดชนิด', 'จ่ายผิดความแรง', 'จ่ายผิดรูปแบบ',
                    'จ่ายผิดขนาด', 'จ่ายผิดจำนวน', 'จ่ายยาที่แพ้ซ้ำ', 'จ่ายยาหมดอายุ',
                    'จ่ายยาที่แพทย์ OFF', 'จ่ายยาที่มีข้อห้ามใช้',
                ],
            ],
            5 => [
                'no' => 5, 'name' => 'การบริหารยา',
                'default_unit' => 'patient_day', 'units' => ['patient_day', 'patient'],
                'subrisks' => [
                    'ให้ยาผิดเวลา', 'ไม่ได้ให้ยา', 'ให้ยาผิดชนิด', 'ความเข้มข้นผิด',
                    'ให้ยาผิดคน', 'ให้ยาผิดจำนวน', 'ให้ยาผิดวิธี', 'ให้ยาที่ไม่ควรได้',
                    'รูปแบบผิด', 'ยา stat ล่าช้า',
                ],
            ],
        ];
    }

    /** ป้ายหัวข้อหลัก (no => name) */
    public static function groupName(int $no): string
    {
        return self::groups()[$no]['name'] ?? ('หัวข้อ ' . $no);
    }

    public static function teamLabel(?string $team): string
    {
        return $team ? (self::TEAMS[$team] ?? $team) : '—';
    }

    public static function unitLabel(?string $unit): string
    {
        return $unit ? (self::DIVISOR_UNITS[$unit] ?? $unit) : '—';
    }
}
