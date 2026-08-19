<?php

namespace app\modules\hr\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * กรอบที่ใช้จริงรายปี — เก็บเฉพาะตัวเลขที่คนใส่เข้ามา
 *
 * ค่าที่คำนวณจากสูตรได้เองไม่ต้องเก็บ เพราะคำนวณใหม่ได้เสมอจากเกณฑ์ + โปรไฟล์
 * ตารางนี้จึงมีแต่ FTE ที่กรอกเอง กับค่าที่กรอกทับพร้อมเหตุผล
 *
 * scope = hospital (org_unit_id เป็น NULL) หรือ unit (ระบุหน่วยงาน)
 *
 * @property int $id
 * @property int $thai_year
 * @property string $scope
 * @property int|null $org_unit_id
 * @property int|null $line_id
 * @property string|null $frame_qty
 * @property string $source
 * @property array|null $calc_json
 * @property string|null $override_reason
 */
class WorkforceFrame extends \yii\db\ActiveRecord
{
    public const SCOPE_HOSPITAL = 'hospital';
    public const SCOPE_UNIT = 'unit';

    public const SOURCE_MANUAL_FTE = 'manual_fte';
    public const SOURCE_OVERRIDE = 'override';
    public const SOURCE_NONE = 'none';

    public static function tableName()
    {
        return '{{%workforce_frame}}';
    }

    public function behaviors()
    {
        return [
            ['class' => TimestampBehavior::class, 'value' => new Expression('NOW()')],
            ['class' => BlameableBehavior::class],
        ];
    }

    public function rules()
    {
        return [
            [['thai_year'], 'required'],
            [['thai_year', 'org_unit_id', 'line_id', 'employee_position_id'], 'integer'],
            [['frame_qty', 'frame_min', 'frame_max'], 'number', 'min' => 0, 'max' => 9999],
            [['scope'], 'in', 'range' => [self::SCOPE_HOSPITAL, self::SCOPE_UNIT]],
            [['source'], 'in', 'range' => [self::SOURCE_MANUAL_FTE, self::SOURCE_OVERRIDE, self::SOURCE_NONE]],
            [['override_reason', 'note'], 'string'],
            [['calc_json'], 'safe'],
            // กรอกทับค่าที่ระบบคำนวณได้ ต้องบอกเหตุผลเสมอ ไม่งั้นอธิบายกับ สสจ. ไม่ได้
            [
                ['override_reason'],
                'required',
                'when' => fn ($model) => $model->source === self::SOURCE_OVERRIDE,
                'whenClient' => false,
                'message' => 'ต้องระบุเหตุผลเมื่อกรอกทับค่าที่ระบบคำนวณ',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'frame_qty' => 'กรอบ',
            'override_reason' => 'เหตุผลที่กรอกทับ',
            'note' => 'หมายเหตุ',
        ];
    }

    public function getLine(): ActiveQuery
    {
        return $this->hasOne(WorkforceStandardLine::class, ['id' => 'line_id']);
    }

    /**
     * กรอบระดับโรงพยาบาลของปี จัดคีย์ตาม line_id
     *
     * @return array<int,static>
     */
    public static function hospitalMap(int $thaiYear): array
    {
        $rows = static::find()
            ->where(['thai_year' => $thaiYear, 'scope' => self::SCOPE_HOSPITAL])
            ->andWhere(['not', ['line_id' => null]])
            ->all();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->line_id] = $row;
        }

        return $map;
    }

    /** หาแถวของสายงานนี้ หรือสร้างใหม่ถ้ายังไม่มี */
    public static function forLine(int $thaiYear, int $lineId): self
    {
        $model = static::findOne([
            'thai_year' => $thaiYear,
            'scope' => self::SCOPE_HOSPITAL,
            'line_id' => $lineId,
        ]);

        return $model ?? new static([
            'thai_year' => $thaiYear,
            'scope' => self::SCOPE_HOSPITAL,
            'line_id' => $lineId,
            'org_unit_id' => null,
            'source' => self::SOURCE_NONE,
        ]);
    }

    public function hasValue(): bool
    {
        return $this->frame_qty !== null && $this->frame_qty !== '';
    }
}
