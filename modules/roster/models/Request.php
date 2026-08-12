<?php

namespace app\modules\roster\models;

use app\modules\hr\models\Employees;
use Yii;

/**
 * คำขอหยุด / ขออยู่เวร ที่เจ้าหน้าที่ยื่นก่อนหัวหน้าจัดเวร
 *
 * ตรงกับวิธีทำงานจริงในหอผู้ป่วย — เขียนขอหยุดไว้ก่อน แล้วหัวหน้าค่อยจัดเวรโดยดูคำขอประกอบ
 * period_id เป็น NULL ได้ เพราะเจ้าหน้าที่มักยื่นล่วงหน้าก่อนหัวหน้าเปิดรอบของเดือนนั้น
 *
 * @property int         $id
 * @property int|null    $period_id
 * @property int         $unit_id
 * @property int         $emp_id
 * @property string      $work_date
 * @property string      $type       off | on
 * @property int|null    $shift_type_id
 * @property string      $status
 */
class Request extends RosterActiveRecord
{
    public const TYPE_OFF = 'off';
    public const TYPE_ON = 'on';

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    public static function tableName()
    {
        return '{{%roster_request}}';
    }

    public function rules()
    {
        return [
            [['unit_id', 'emp_id', 'work_date', 'type'], 'required'],
            [['period_id', 'unit_id', 'emp_id', 'shift_type_id', 'responded_by', 'created_by', 'updated_by'], 'integer'],
            [['work_date', 'responded_at', 'data_json', 'created_at', 'updated_at'], 'safe'],
            [['reason', 'ref'], 'string', 'max' => 255],
            [['type'], 'in', 'range' => [self::TYPE_OFF, self::TYPE_ON]],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_ACCEPTED, self::STATUS_REJECTED]],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['type'], 'unique', 'targetAttribute' => ['emp_id', 'work_date', 'type'],
                'message' => 'ยื่นคำขอชนิดนี้ของวันดังกล่าวไว้แล้ว'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'work_date' => 'วันที่',
            'type' => 'ประเภทคำขอ',
            'shift_type_id' => 'เวรที่ขอ',
            'reason' => 'เหตุผล',
            'status' => 'สถานะ',
        ];
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_OFF => 'ขอหยุด',
            self::TYPE_ON => 'ขออยู่เวร',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'รอหัวหน้าพิจารณา',
            self::STATUS_ACCEPTED => 'รับคำขอ',
            self::STATUS_REJECTED => 'ไม่รับคำขอ',
        ];
    }

    public function getTypeLabel(): string
    {
        return self::typeLabels()[$this->type] ?? (string) $this->type;
    }

    public function getStatusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? (string) $this->status;
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getShiftType()
    {
        return $this->hasOne(ShiftType::class, ['id' => 'shift_type_id']);
    }

    /**
     * คำขอในช่วงวันที่ของหน่วย จัดเป็น [emp_id][วัน][] = Request
     * หัวหน้าเห็นบนกริดตอนจัดเวร จะได้ไม่จัดทับวันที่มีคนขอหยุด
     */
    public static function gridForUnit(int $unitId, string $fromDate, string $toDate): array
    {
        $rows = static::find()
            ->where(['unit_id' => $unitId])
            ->andWhere(['<>', 'status', self::STATUS_REJECTED])
            ->andWhere(['between', 'work_date', $fromDate, $toDate])
            ->all();
        $grid = [];
        foreach ($rows as $row) {
            $day = (int) date('j', strtotime($row->work_date));
            $grid[(int) $row->emp_id][$day][] = $row;
        }
        return $grid;
    }

    /** ผูกคำขอที่ยื่นล่วงหน้าเข้ากับรอบ เมื่อหัวหน้าเปิดรอบของเดือนนั้น */
    public static function attachToPeriod(Period $period): int
    {
        return Yii::$app->db->createCommand()->update(
            static::tableName(),
            ['period_id' => $period->id],
            [
                'and',
                ['unit_id' => $period->unit_id, 'period_id' => null],
                ['between', 'work_date', $period->firstDate(), $period->lastDate()],
            ]
        )->execute();
    }
}
