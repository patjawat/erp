<?php

namespace app\modules\ha12\models;

/**
 * นิยามฟิลด์ของแบบฟอร์มทบทวน แยกตามกิจกรรม (อิงบท 6 ของคู่มือ HA12)
 *
 * เก็บเป็น config กลาง (ไม่ใช่ตาราง) เพราะเป็นโครงฟอร์มของระบบ ไม่ใช่ข้อมูลผู้ใช้
 * ค่าที่ผู้ใช้กรอกจะถูกเก็บใน ha12_review.payload_json ตาม key เหล่านี้ + schema_version
 *
 * เฟส 1 รองรับเฉพาะกิจกรรม form_type = general (9 กิจกรรม)
 * กิจกรรม 7 (ยา) / 9 (เวชระเบียน) / 12 (ตัวชี้วัด) มีแบบเฉพาะ — เฟส 2
 */
class Ha12ReviewForm
{
    public const SCHEMA_VERSION = 1;

    /**
     * แผนผังฟิลด์ตามลำดับกิจกรรม (no => รายการฟิลด์)
     * แต่ละฟิลด์: ['key' => ..., 'label' => ..., 'type' => text|textarea|number, 'primary' => true?]
     * ฟิลด์ที่ตั้ง primary=true จะถูกใช้เป็น title ของรายการ
     *
     * @return array<int, array<int, array{key:string,label:string,type:string,primary?:bool}>>
     */
    public static function map(): array
    {
        $ta = 'textarea';
        return [
            1 => [
                ['key' => 'event', 'label' => 'เหตุการณ์', 'type' => $ta, 'primary' => true],
                ['key' => 'method', 'label' => 'วิธีทบทวน', 'type' => 'text'],
                ['key' => 'issue', 'label' => 'ประเด็นที่พบ', 'type' => $ta],
                ['key' => 'fix', 'label' => 'การแก้ไข', 'type' => $ta],
            ],
            2 => [
                ['key' => 'type', 'label' => 'ประเภท', 'type' => 'text'],
                ['key' => 'complaint', 'label' => 'ข้อร้องเรียน/ความคิดเห็น', 'type' => $ta, 'primary' => true],
                ['key' => 'fix', 'label' => 'วิธีแก้ไข', 'type' => $ta],
                ['key' => 'prevent', 'label' => 'ผลและการป้องกันซ้ำ', 'type' => $ta],
            ],
            3 => [
                ['key' => 'reason_main', 'label' => 'เหตุผลหลัก', 'type' => 'text', 'primary' => true],
                ['key' => 'reason_sub', 'label' => 'เหตุผลย่อย', 'type' => $ta],
                ['key' => 'readiness', 'label' => 'ความพร้อม', 'type' => $ta],
                ['key' => 'outcome', 'label' => 'ผลลัพธ์', 'type' => $ta],
                ['key' => 'improve', 'label' => 'การปรับปรุง', 'type' => $ta],
            ],
            4 => [
                ['key' => 'incident', 'label' => 'อุบัติการณ์', 'type' => $ta, 'primary' => true],
                ['key' => 'issue', 'label' => 'ประเด็น', 'type' => $ta],
                ['key' => 'standard', 'label' => 'มาตรฐานที่ควรเป็น', 'type' => $ta],
                ['key' => 'outcome', 'label' => 'ผลลัพธ์', 'type' => $ta],
            ],
            5 => [
                ['key' => 'topic', 'label' => 'เรื่อง', 'type' => 'text', 'primary' => true],
                ['key' => 'count', 'label' => 'จำนวนครั้ง', 'type' => 'number'],
                ['key' => 'category', 'label' => 'ประเภท', 'type' => 'text'],
                ['key' => 'fix', 'label' => 'การแก้ไข', 'type' => $ta],
                ['key' => 'prevent', 'label' => 'การป้องกัน', 'type' => $ta],
                ['key' => 'result', 'label' => 'ผล', 'type' => $ta],
            ],
            6 => [
                ['key' => 'incident', 'label' => 'อุบัติการณ์', 'type' => $ta, 'primary' => true],
                ['key' => 'cause', 'label' => 'ขั้นตอน/สาเหตุ', 'type' => $ta],
                ['key' => 'improve', 'label' => 'การปรับปรุง', 'type' => $ta],
            ],
            8 => [
                ['key' => 'event', 'label' => 'เหตุการณ์สำคัญ', 'type' => $ta, 'primary' => true],
                ['key' => 'cause', 'label' => 'สาเหตุ', 'type' => $ta],
                ['key' => 'prevent', 'label' => 'การป้องกันแก้ไข', 'type' => $ta],
            ],
            10 => [
                ['key' => 'recommendation', 'label' => 'ข้อแนะนำ/องค์ความรู้', 'type' => $ta, 'primary' => true],
                ['key' => 'current', 'label' => 'สภาพปัจจุบัน', 'type' => $ta],
                ['key' => 'need', 'label' => 'สิ่งที่ต้องการ', 'type' => $ta],
                ['key' => 'plan', 'label' => 'แผนดำเนินการ', 'type' => $ta],
            ],
            11 => [
                ['key' => 'topic', 'label' => 'เรื่อง/ทรัพยากร', 'type' => 'text', 'primary' => true],
                ['key' => 'reasonableness', 'label' => 'ความสมเหตุสมผล', 'type' => $ta],
                ['key' => 'fix', 'label' => 'การแก้ไข', 'type' => $ta],
            ],
        ];
    }

    /** ฟิลด์ของกิจกรรมตามลำดับ (no) — [] ถ้าเป็นกิจกรรมแบบเฉพาะ (7/9/12) */
    public static function fieldsFor(int $activityNo): array
    {
        return self::map()[$activityNo] ?? [];
    }

    /** รองรับในเฟส 1 ไหม (กิจกรรมทั่วไปเท่านั้น) */
    public static function isSupported(int $activityNo): bool
    {
        return isset(self::map()[$activityNo]);
    }

    /** key ของฟิลด์หลักที่ใช้เป็น title */
    public static function primaryKey(int $activityNo): ?string
    {
        foreach (self::fieldsFor($activityNo) as $f) {
            if (!empty($f['primary'])) {
                return $f['key'];
            }
        }
        $first = self::fieldsFor($activityNo)[0] ?? null;
        return $first['key'] ?? null;
    }
}
