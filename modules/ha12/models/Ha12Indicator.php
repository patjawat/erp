<?php

namespace app\modules\ha12\models;

use app\modules\hr\models\Organization;

/**
 * ตัวชี้วัดสำคัญ (กิจกรรม 12) — standalone (กรอกค่ารายเดือนเอง)
 *
 * @property int $id
 * @property int|null $owner_unit_id
 * @property int $fiscal_year
 * @property string $name
 * @property string|null $target
 * @property string|null $unit_label
 * @property string|null $risk
 * @property string|null $level
 * @property string|null $fix
 * @property string|null $note
 * @property int $deleted
 * @property string $ref
 */
class Ha12Indicator extends Ha12ActiveRecord
{
    /** ระดับความรุนแรง A-I (บท 9) */
    public const LEVELS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

    /** เดือนในปีงบประมาณ: month_no 1-12 (M01 = ต.ค.) */
    public static function monthLabels(): array
    {
        return [
            1 => 'ต.ค.', 2 => 'พ.ย.', 3 => 'ธ.ค.', 4 => 'ม.ค.', 5 => 'ก.พ.', 6 => 'มี.ค.',
            7 => 'เม.ย.', 8 => 'พ.ค.', 9 => 'มิ.ย.', 10 => 'ก.ค.', 11 => 'ส.ค.', 12 => 'ก.ย.',
        ];
    }

    public static function tableName(): string
    {
        return '{{%ha12_indicator}}';
    }

    public function rules(): array
    {
        return [
            [['fiscal_year', 'name'], 'required'],
            [['fiscal_year', 'owner_unit_id', 'deleted'], 'integer'],
            [['name'], 'string', 'max' => 500],
            [['target'], 'string', 'max' => 255],
            [['unit_label'], 'string', 'max' => 64],
            [['level'], 'in', 'range' => self::LEVELS],
            [['risk', 'fix', 'note'], 'string'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'owner_unit_id' => 'หน่วยงาน',
            'fiscal_year' => 'ปีงบประมาณ',
            'name' => 'ชื่อตัวชี้วัด',
            'target' => 'เป้าหมาย',
            'unit_label' => 'หน่วย',
            'risk' => 'ความเสี่ยง',
            'level' => 'ระดับ (A-I)',
            'fix' => 'การแก้ไข',
            'note' => 'บันทึก',
        ];
    }

    public function getOwnerUnit()
    {
        return $this->hasOne(Organization::class, ['id' => 'owner_unit_id']);
    }

    public function getValues()
    {
        return $this->hasMany(Ha12IndicatorValue::class, ['indicator_id' => 'id'])
            ->orderBy(['month_no' => SORT_ASC]);
    }

    /** ค่ารายเดือน map month_no => value (สำหรับแสดง/กรอก) */
    public function valueMap(): array
    {
        $map = [];
        foreach ($this->values as $v) {
            $map[(int) $v->month_no] = $v->value;
        }
        return $map;
    }
}
