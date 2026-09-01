<?php

namespace app\modules\roster\models;

/**
 * นิยามเวรของหน่วยงานหนึ่ง — ชื่อ เวลา จำนวนคนที่ต้องการ และอัตราค่าตอบแทน
 *
 * แต่ละหน่วยเรียกชื่อเวรไม่เหมือนกัน และมีรูปแบบหลากหลายในเดือนเดียว เช่น
 *   "บ่ายดึก"  16 ชม.รวดข้ามเที่ยงคืน — เป็นเวรเดียว ไม่ใช่บ่าย+ดึกต่อกัน
 *   "Refer"    ออกนอกหน่วย ซ้อนกับเวรอื่นในวันเดียวกันได้
 *   "On call"  อยู่บ้านรอเรียก ไม่ใช่ชั่วโมงทำงานจริง → is_standby = 1
 *
 * shift_type_id เป็นเพียง "หมวด" (ช/บ/ด/ควบ) ไว้ทำรายงานรวมข้ามหน่วยงาน
 * ชื่อที่ผู้ใช้เห็นมาจาก name/short_name ของแถวนี้
 *
 * @property int         $id
 * @property int         $unit_id        tree.id
 * @property int         $shift_type_id  หมวดของเวร
 * @property string|null $name
 * @property string|null $short_name
 * @property string|null $start_time
 * @property string|null $end_time
 * @property float|null  $hours
 * @property int         $cross_midnight
 * @property int         $required_staff จำนวนคนที่ต้องการต่อเวร
 * @property int         $is_standby     1 = รอเรียก/ออกนอกหน่วย ยกเว้นจากกฎ
 * @property int         $is_ot          1 = เวรนี้ถูกกำหนดให้นับเป็น OT
 * @property float|null  $pay_rate
 * @property string      $pay_unit       shift | hour
 * @property int         $sort_order
 * @property int         $active
 *
 * @property ShiftType   $shiftType
 */
class UnitShift extends RosterActiveRecord
{
    public const PAY_PER_SHIFT = 'shift';
    public const PAY_PER_HOUR = 'hour';

    public static function tableName()
    {
        return '{{%roster_unit_shift}}';
    }

    public function rules()
    {
        return [
            [['unit_id', 'shift_type_id', 'name'], 'required'],
            [['unit_id', 'shift_type_id', 'position_id', 'cross_midnight', 'required_staff', 'is_standby', 'is_ot',
                'sort_order', 'active', 'created_by', 'updated_by'], 'integer'],
            [['required_staff', 'required_sat', 'required_sun', 'required_holiday'], 'integer', 'min' => 0, 'max' => 99],
            // ปล่อยว่าง = ใช้ค่าวันธรรมดา จึงต้องเก็บเป็น NULL ไม่ใช่ 0
            [['required_sat', 'required_sun', 'required_holiday'], 'default', 'value' => null],
            [['required_sat', 'required_sun', 'required_holiday'], 'filter',
                'filter' => static fn($v) => ($v === '' || $v === null) ? null : (int) $v],
            // บ่ายดึกยาว 16 ชม. จึงเปิดเพดานถึง 24 ไม่ใช่ 12
            [['hours'], 'number', 'min' => 0, 'max' => 24],
            [['pay_rate'], 'number', 'min' => 0],
            [['start_time', 'end_time', 'data_json', 'created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 100],
            // อักษรย่อจำกัด 2 ตัว: ตัวหน้าบอกสาย/หน้าที่ ตัวหลังบอกเวลา ซึ่งก็คืออัตราค่าตอบแทน
            // ยาวกว่านี้ช่องในกริดจะบีบจนอ่านไม่ออกเมื่อคนหนึ่งมีหลายเวรในวันเดียว
            [['short_name'], 'string', 'max' => 2,
                'tooLong' => 'อักษรย่อยาวได้ไม่เกิน 2 ตัว — ใช้ตัวหน้าบอกสาย ตัวหลังบอกเวลา เช่น 1ช = Refer 1 เช้า'],
            [['short_name'], 'filter', 'filter' => static fn($v) => ($v === '' ? null : $v)],
            // ย่อซ้ำในหน่วยเดียวกัน = อ่านตารางไม่ออกว่าช่องนั้นเป็นเวรไหน และคิดเงินผิดโดยไม่มีใครเห็น
            // ไม่ยกเว้นเวรที่ปิดใช้งาน เพราะตารางเดือนเก่ายังอ้างถึงอยู่
            [['short_name'], 'unique', 'targetAttribute' => ['unit_id', 'short_name'],
                'message' => 'หน่วยงานนี้ใช้อักษรย่อนี้กับเวรอื่นแล้ว'],
            [['ref'], 'string', 'max' => 255],
            [['pay_unit'], 'in', 'range' => [self::PAY_PER_SHIFT, self::PAY_PER_HOUR]],
            [['pay_unit'], 'default', 'value' => self::PAY_PER_SHIFT],
            [['active'], 'default', 'value' => 1],
            [['required_staff', 'cross_midnight', 'is_standby', 'is_ot', 'sort_order'], 'default', 'value' => 0],
            [['name'], 'unique', 'targetAttribute' => ['unit_id', 'name'],
                'message' => 'หน่วยงานนี้มีเวรชื่อนี้อยู่แล้ว'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'unit_id' => 'หน่วยงาน',
            'shift_type_id' => 'หมวดเวร',
            'position_id' => 'ตำแหน่ง/วิชาชีพ',
            'name' => 'ชื่อเวร',
            'short_name' => 'อักษรย่อ',
            'start_time' => 'เข้าเวร',
            'end_time' => 'ออกเวร',
            'hours' => 'ชั่วโมง',
            'cross_midnight' => 'ข้ามเที่ยงคืน',
            'required_staff' => 'วันธรรมดา',
            'required_sat' => 'วันเสาร์',
            'required_sun' => 'วันอาทิตย์',
            'required_holiday' => 'วันหยุดนักขัตฤกษ์',
            'is_standby' => 'เวรรอเรียก/นอกหน่วย',
            'is_ot' => 'เป็นเวร OT',
            'pay_rate' => 'ค่าตอบแทน (บาท)',
            'pay_unit' => 'หน่วยค่าตอบแทน',
            'sort_order' => 'ลำดับ',
            'active' => 'ใช้งาน',
        ];
    }

    public static function payUnitLabels(): array
    {
        return [
            self::PAY_PER_SHIFT => 'ต่อเวร',
            self::PAY_PER_HOUR => 'ต่อชั่วโมง',
        ];
    }

    /** ชื่อที่แสดง — ถอยไปใช้ชื่อหมวดถ้าหน่วยยังไม่ได้ตั้งชื่อเอง */
    public function displayName(): string
    {
        if ($this->name) {
            return $this->name;
        }
        return $this->shiftType ? $this->shiftType->title : 'เวร';
    }

    /** อักษรย่อในกริด — ถอยไปใช้ของหมวด แล้วค่อยตัดจากชื่อ */
    public function displayShort(): string
    {
        if ($this->short_name) {
            return $this->short_name;
        }
        if ($this->shiftType && $this->shiftType->short_name) {
            return $this->shiftType->short_name;
        }
        return mb_substr($this->displayName(), 0, 2);
    }

    /** สีของช่องในกริด — ใช้สีของหมวด เพื่อให้เวรหมวดเดียวกันดูเป็นพวกเดียวกันทุกหน่วย */
    public function cellClass(): string
    {
        return $this->shiftType ? $this->shiftType->cellClass() : 'bg-secondary-subtle text-secondary-emphasis';
    }

    public function excelFill(): string
    {
        return $this->shiftType ? $this->shiftType->excelFill() : 'E9ECEF';
    }

    /** ค่าตอบแทนของเวรนี้ 1 ครั้ง */
    public function payAmount(): float
    {
        $rate = (float) ($this->pay_rate ?? 0);
        if ($rate <= 0) {
            return 0.0;
        }
        return $this->pay_unit === self::PAY_PER_HOUR ? $rate * (float) ($this->hours ?? 0) : $rate;
    }

    public function payLabel(): string
    {
        if (!$this->pay_rate) {
            return '-';
        }
        $unit = static::payUnitLabels()[$this->pay_unit] ?? '';
        return number_format((float) $this->pay_rate, 2) . ' บ./' . $unit;
    }

    public function getShiftType()
    {
        return $this->hasOne(ShiftType::class, ['id' => 'shift_type_id']);
    }

    public function getPosition()
    {
        return $this->hasOne(\app\modules\hr\models\EmployeePosition::class, ['id' => 'position_id']);
    }

    /** ชื่อตำแหน่งที่เวรนี้กำหนดไว้ — ว่าง = ไม่จำกัดวิชาชีพ */
    public function positionName(): string
    {
        if (!$this->position_id) {
            return '';
        }
        $row = (new \yii\db\Query())->select('title')->from('employee_position')
            ->where(['id' => $this->position_id])->scalar();
        return (string) $row;
    }

    /**
     * จำนวนคนที่ต้องการของวันนั้น — แยกตามประเภทวัน
     * หอผู้ป่วยใช้คนวันหยุดไม่เท่าวันธรรมดา ถ้าใช้ตัวเลขเดียวตัวนับจะแดงผิดทุกวันหยุด
     *
     * @param bool $isHoliday วันหยุดนักขัตฤกษ์
     * @param int  $dow       0=อาทิตย์ … 6=เสาร์
     */
    public function requiredFor(bool $isHoliday, int $dow): int
    {
        if ($isHoliday && $this->required_holiday !== null) {
            return (int) $this->required_holiday;
        }
        if (!$isHoliday && $dow === 6 && $this->required_sat !== null) {
            return (int) $this->required_sat;
        }
        if (!$isHoliday && $dow === 0 && $this->required_sun !== null) {
            return (int) $this->required_sun;
        }
        return (int) $this->required_staff;
    }

    /**
     * เวรนี้ระบุอัตรากำลังไว้หรือยัง
     * เวรที่ใส่เฉพาะเสาร์/อาทิตย์ (วันธรรมดา 0) ก็ถือว่าระบุแล้ว
     */
    public function hasRequirement(): bool
    {
        return (int) $this->required_staff > 0
            || (int) $this->required_sat > 0
            || (int) $this->required_sun > 0
            || (int) $this->required_holiday > 0;
    }

    /** สรุปอัตรากำลังให้อ่านง่ายในหน้าตั้งค่า เช่น "2 · ส 1 · อา 1" */
    public function requiredLabel(): string
    {
        if (!$this->hasRequirement()) {
            return '';
        }
        $parts = [(string) (int) $this->required_staff];
        foreach ([['ส', $this->required_sat], ['อา', $this->required_sun], ['นักขัตฤกษ์', $this->required_holiday]] as [$label, $value]) {
            if ($value !== null) {
                $parts[] = $label . ' ' . (int) $value;
            }
        }
        return implode(' · ', $parts);
    }

    /** เวรนี้เป็นวันหยุด ไม่ใช่การทำงาน */
    public function isOff(): bool
    {
        return $this->shiftType ? (int) $this->shiftType->is_off === 1 : false;
    }

    /** นับ OT เฉพาะเมื่อเวรของหน่วยงานนี้ถูกกำหนดเป็น OT โดยตรง */
    public function isOt(): bool
    {
        return (int) $this->is_ot === 1;
    }

    /**
     * คำนวณ hours + cross_midnight จากเวลาที่กรอก ถ้าผู้ใช้ไม่ได้ระบุ hours เอง
     * เวรที่เวลาออก <= เวลาเข้า ถือว่าข้ามเที่ยงคืน (เช่น 23:00–07:00)
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($this->start_time && $this->end_time) {
            $start = strtotime('1970-01-01 ' . $this->start_time);
            $end = strtotime('1970-01-01 ' . $this->end_time);
            $this->cross_midnight = $end <= $start ? 1 : 0;
            if ($this->cross_midnight) {
                $end += 86400;
            }
            if ($this->hours === null || $this->hours === '') {
                $this->hours = round(($end - $start) / 3600, 2);
            }
        }
        return true;
    }

    /**
     * @param bool $activeOnly false = รวมเวรที่ปิดใช้งานด้วย ใช้ตอนตรวจอักษรย่อซ้ำ
     *                         เพราะตารางเดือนเก่ายังอ้างถึงเวรที่ปิดไปแล้ว
     * @return self[] เรียงตามลำดับที่ตั้งไว้
     */
    public static function listForUnit(int $unitId, bool $activeOnly = true): array
    {
        $query = static::find()->where(['unit_id' => $unitId]);
        if ($activeOnly) {
            $query->andWhere(['active' => 1]);
        }
        return $query
            ->orderBy(['sort_order' => SORT_ASC, 'start_time' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
    }

    /**
     * @return self[] index ด้วย id ของเวร (ไม่ใช่หมวด) เพราะหน่วยหนึ่งมีหลายเวรในหมวดเดียวกันได้
     */
    public static function mapForUnit(int $unitId): array
    {
        $map = [];
        foreach (static::listForUnit($unitId) as $row) {
            $map[(int) $row->id] = $row;
        }
        return $map;
    }

    /** หน่วยนี้ตั้งค่าเวรไว้หรือยัง — กริดใช้เช็คก่อนแสดงตัวนับความครบ */
    public static function isConfigured(int $unitId): bool
    {
        return static::find()->where(['unit_id' => $unitId, 'active' => 1])->exists();
    }

    /** เวลาแบบไทย: 16:30:00 → "16.30" (ใช้จุด ไม่ใช้ AM/PM) */
    public static function thaiTime(?string $time): string
    {
        if (!$time) {
            return '-';
        }
        return str_replace(':', '.', substr($time, 0, 5));
    }

    /** ช่วงเวลาแบบไทย: "08.00–16.00 น." */
    public function timeRangeLabel(): string
    {
        if (!$this->start_time || !$this->end_time) {
            return '-';
        }
        return static::thaiTime($this->start_time) . '–' . static::thaiTime($this->end_time) . ' น.';
    }
}
