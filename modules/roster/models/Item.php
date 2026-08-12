<?php

namespace app\modules\roster\models;

use app\modules\hr\models\Employees;

/**
 * ช่องเวร: 1 แถว = 1 คน × 1 วัน × 1 เวร
 *
 * เก็บแนวตั้งแทนคอลัมน์กว้าง 31 ช่อง เพราะ:
 *   - ควบเวร (คนเดียว 2 ผลัดในวันเดียว) เพิ่มแถวได้เลย
 *   - ถามว่า "วันนี้ใครอยู่เวรดึกบ้าง" ได้ตรงๆ ไม่ต้อง unpivot
 *   - join checkin_record เพื่อประเมินสาย (เฟส 3) และคิดค่าตอบแทนรายเวร (เฟส 4) ได้
 *
 * @property int         $id
 * @property int         $period_id
 * @property int         $emp_id
 * @property string      $work_date
 * @property int         $shift_type_id
 * @property int         $is_extra
 * @property string      $status
 *
 * @property Employees   $employee
 * @property ShiftType   $shiftType
 * @property Period      $period
 */
class Item extends RosterActiveRecord
{
    public const STATUS_PLANNED = 'planned';
    public const STATUS_SWAPPED = 'swapped';
    public const STATUS_CANCELLED = 'cancelled';

    public static function tableName()
    {
        return '{{%roster_item}}';
    }

    public function rules()
    {
        return [
            [['period_id', 'emp_id', 'work_date', 'unit_shift_id'], 'required'],
            [['period_id', 'emp_id', 'shift_type_id', 'unit_shift_id', 'is_extra', 'created_by', 'updated_by'], 'integer'],
            [['ot_rate', 'ot_amount'], 'number'],
            [['work_date', 'data_json', 'created_at', 'updated_at'], 'safe'],
            [['note', 'ref'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => [self::STATUS_PLANNED, self::STATUS_SWAPPED, self::STATUS_CANCELLED]],
            [['status'], 'default', 'value' => self::STATUS_PLANNED],
            [['is_extra'], 'default', 'value' => 0],
            [['unit_shift_id'], 'unique', 'targetAttribute' => ['emp_id', 'work_date', 'unit_shift_id'],
                'message' => 'คนนี้ถูกจัดเวรนี้ในวันดังกล่าวอยู่แล้ว'],
        ];
    }

    /** เติมหมวดจากนิยามเวรของหน่วย เพื่อให้รายงานรวมข้ามหน่วยไม่ต้อง join */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($this->unit_shift_id) {
            $unitShift = $this->unitShift ?: UnitShift::findOne((int) $this->unit_shift_id);
            if ($unitShift) {
                $this->shift_type_id = (int) $unitShift->shift_type_id;
            }
        }
        return true;
    }

    public function attributeLabels()
    {
        return [
            'emp_id' => 'เจ้าหน้าที่',
            'work_date' => 'วันที่',
            'shift_type_id' => 'เวร',
            'is_extra' => 'ควบเวร',
            'note' => 'หมายเหตุ',
        ];
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getShiftType()
    {
        return $this->hasOne(ShiftType::class, ['id' => 'shift_type_id']);
    }

    public function getUnitShift()
    {
        return $this->hasOne(UnitShift::class, ['id' => 'unit_shift_id']);
    }

    /** ชื่อ/อักษรย่อที่แสดง — มาจากนิยามของหน่วย ถอยไปหมวดถ้าไม่มี */
    public function shiftName(): string
    {
        return $this->unitShift ? $this->unitShift->displayName()
            : ($this->shiftType ? $this->shiftType->title : 'เวร');
    }

    public function shiftShort(): string
    {
        return $this->unitShift ? $this->unitShift->displayShort()
            : ($this->shiftType ? $this->shiftType->short_name : '?');
    }

    public function shiftCellClass(): string
    {
        return $this->unitShift ? $this->unitShift->cellClass()
            : ($this->shiftType ? $this->shiftType->cellClass() : 'bg-secondary-subtle text-secondary-emphasis');
    }

    public function getPeriod()
    {
        return $this->hasOne(Period::class, ['id' => 'period_id']);
    }

    /**
     * เวรทั้งหมดของรอบ จัดเป็น [emp_id][วันที่ 1..31][] = Item
     * ใช้วาดกริดในครั้งเดียว ไม่ query ต่อช่อง
     */
    public static function gridForPeriod(int $periodId): array
    {
        $rows = static::find()
            ->with(['unitShift', 'shiftType'])
            ->where(['period_id' => $periodId])
            ->andWhere(['<>', 'status', self::STATUS_CANCELLED])
            ->all();
        $grid = [];
        foreach ($rows as $row) {
            $day = (int) date('j', strtotime($row->work_date));
            $grid[(int) $row->emp_id][$day][] = $row;
        }
        return $grid;
    }

    /**
     * นับจำนวนคนที่จัดแล้วต่อ [วัน][unit_shift_id] — ใช้เทียบกับ required_staff ของเวรนั้น
     */
    public static function countByDayShift(int $periodId): array
    {
        $rows = (new \yii\db\Query())
            ->select(['work_date', 'unit_shift_id', 'c' => 'COUNT(*)'])
            ->from(static::tableName())
            ->where(['period_id' => $periodId])
            ->andWhere(['<>', 'status', self::STATUS_CANCELLED])
            ->groupBy(['work_date', 'unit_shift_id'])
            ->all();
        $counts = [];
        foreach ($rows as $row) {
            $day = (int) date('j', strtotime($row['work_date']));
            $counts[$day][(int) $row['unit_shift_id']] = (int) $row['c'];
        }
        return $counts;
    }
}
