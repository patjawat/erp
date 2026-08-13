<?php

namespace app\modules\roster\models;

/**
 * กฎการจัดเวรของหน่วยงาน
 *
 * ทุกกฎเป็น "เตือน" ไม่ใช่ "ห้ามบันทึก" — หน้างานจริงมีวันที่คนไม่พอจนต้องฝืนกฎ
 * ระบบที่บล็อกไม่ให้บันทึกจะถูกเลิกใช้ แล้วกลับไปใช้ Excel
 *
 * เก็บแบบ key-value เพื่อเพิ่มกฎใหม่ได้โดยไม่ต้อง migrate
 *
 * @property int         $id
 * @property int         $unit_id
 * @property string      $rule_key
 * @property int|null    $shift_type_id
 * @property int|null    $int_value
 * @property array|null  $data_json
 * @property int         $active
 */
class UnitRule extends RosterActiveRecord
{
    /** พักระหว่างเวรอย่างน้อยกี่ชั่วโมง */
    public const KEY_MIN_REST_HOURS = 'min_rest_hours';
    /** ยอมให้พักน้อยกว่าขั้นต่ำได้กี่ครั้งต่อรอบ */
    public const KEY_MAX_REST_VIOLATIONS = 'max_rest_violations';
    /** ห้ามเวร 2 ชนิดนี้ในวันเดียวกัน — data_json {"a":id,"b":id} */
    public const KEY_FORBID_SAME_DAY = 'forbid_same_day';
    /** ห้ามเวร a วันนี้ ต่อด้วยเวร b วันถัดไป — data_json {"a":id,"b":id} */
    public const KEY_FORBID_NEXT_DAY = 'forbid_next_day';
    /** เวรชนิดนี้ติดกันได้ไม่เกิน int_value วัน */
    public const KEY_MAX_CONSECUTIVE_SHIFT = 'max_consecutive_shift';
    /** ทำงานติดต่อกันได้ไม่เกิน int_value วัน (ไม่แยกชนิดเวร) */
    public const KEY_MAX_CONSECUTIVE_WORKDAYS = 'max_consecutive_workdays';
    /**
     * ชั่วโมงทำงานรวมต่อสัปดาห์ปฏิทิน ไม่เกิน int_value
     * จำเป็นเพราะระบบรองรับเวร 16 ชม. (บ่ายดึก) — อยู่ 3 วันติดก็ 48 ชม. แล้ว
     * ซึ่งกฎ "วันทำงานติดต่อกัน" จับไม่ได้เลย
     */
    public const KEY_MAX_HOURS_PER_WEEK = 'max_hours_per_week';

    public static function tableName()
    {
        return '{{%roster_unit_rule}}';
    }

    public function rules()
    {
        return [
            [['unit_id', 'rule_key'], 'required'],
            [['unit_id', 'shift_type_id', 'int_value', 'active', 'created_by', 'updated_by'], 'integer'],
            [['data_json', 'created_at', 'updated_at'], 'safe'],
            [['rule_key'], 'string', 'max' => 60],
            [['ref'], 'string', 'max' => 255],
            [['active'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'rule_key' => 'กฎ',
            'shift_type_id' => 'ประเภทเวร',
            'int_value' => 'ค่า',
            'active' => 'เปิดใช้',
        ];
    }

    public static function keyLabels(): array
    {
        return [
            self::KEY_MIN_REST_HOURS => 'พักระหว่างเวรอย่างน้อย (ชั่วโมง)',
            self::KEY_MAX_REST_VIOLATIONS => 'ยอมให้พักน้อยกว่าขั้นต่ำได้ (ครั้ง/รอบ)',
            self::KEY_FORBID_SAME_DAY => 'ห้ามอยู่ 2 เวรนี้ในวันเดียวกัน',
            self::KEY_FORBID_NEXT_DAY => 'ห้ามอยู่เวรนี้แล้วต่อเวรนี้วันถัดไป',
            self::KEY_MAX_CONSECUTIVE_SHIFT => 'เวรชนิดนี้ติดกันสูงสุด (วัน)',
            self::KEY_MAX_CONSECUTIVE_WORKDAYS => 'ทำงานติดต่อกันสูงสุด (วัน)',
            self::KEY_MAX_HOURS_PER_WEEK => 'ชั่วโมงทำงานสูงสุดต่อสัปดาห์',
        ];
    }

    /**
     * กฎที่ใช้งานอยู่ของหน่วย จัดกลุ่มตาม rule_key
     * @return array<string, self[]>
     */
    public static function groupedForUnit(int $unitId): array
    {
        $rows = static::find()->where(['unit_id' => $unitId, 'active' => 1])->all();
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row->rule_key][] = $row;
        }
        return $grouped;
    }

    /**
     * ชุดกฎแนะนำ — อิงแนวปฏิบัติการจัดเวรพยาบาล ยังไม่บันทึกลงฐาน
     * ห้ามดึกติดเช้าวันถัดไป, ห้ามเช้าคู่บ่าย/บ่ายคู่ดึกวันเดียวกัน, ดึกติดกันไม่เกิน 3 วัน
     *
     * @return self[]
     */
    public static function defaultSet(int $unitId): array
    {
        $types = [];
        foreach (ShiftType::activeList() as $type) {
            $types[$type->code] = (int) $type->id;
        }

        $set = [];
        $add = function (string $key, ?int $intValue, ?array $json, ?int $shiftTypeId) use ($unitId, &$set) {
            $set[] = new static([
                'unit_id' => $unitId,
                'rule_key' => $key,
                'int_value' => $intValue,
                'data_json' => $json,
                'shift_type_id' => $shiftTypeId,
                'active' => 1,
            ]);
        };

        $add(self::KEY_MIN_REST_HOURS, 8, null, null);
        $add(self::KEY_MAX_REST_VIOLATIONS, 3, null, null);
        $add(self::KEY_MAX_CONSECUTIVE_WORKDAYS, 6, null, null);
        // 48 ชม./สัปดาห์ ตามมาตรฐาน ILO C001
        $add(self::KEY_MAX_HOURS_PER_WEEK, 48, null, null);
        if (isset($types['M'], $types['A'])) {
            $add(self::KEY_FORBID_SAME_DAY, null, ['a' => $types['M'], 'b' => $types['A']], null);
        }
        if (isset($types['A'], $types['N'])) {
            $add(self::KEY_FORBID_SAME_DAY, null, ['a' => $types['A'], 'b' => $types['N']], null);
        }
        if (isset($types['N'], $types['M'])) {
            $add(self::KEY_FORBID_NEXT_DAY, null, ['a' => $types['N'], 'b' => $types['M']], null);
        }
        if (isset($types['N'])) {
            $add(self::KEY_MAX_CONSECUTIVE_SHIFT, 3, null, $types['N']);
        }
        return $set;
    }

    /** บันทึกชุดกฎแนะนำให้หน่วยที่ยังไม่เคยตั้ง */
    public static function seedDefaults(int $unitId): int
    {
        if (static::find()->where(['unit_id' => $unitId])->exists()) {
            return 0;
        }
        $created = 0;
        foreach (self::defaultSet($unitId) as $rule) {
            if ($rule->save()) {
                $created++;
            }
        }
        return $created;
    }
}
