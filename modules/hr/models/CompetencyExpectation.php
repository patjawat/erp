<?php

namespace app\modules\hr\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * ระดับที่คาดหวังของบุคลากร 1 คน ต่อสมรรถนะ 1 ตัว ในรอบประเมินหนึ่ง
 * แยกรายรอบเพราะคนที่เลื่อนตำแหน่งกลางปีจะถูกคาดหวังไม่เท่ากันในสองรอบ
 *
 * @property int $id
 * @property int $emp_id
 * @property int $round_id
 * @property int $competency_year_id
 * @property int $expected_level
 * @property string $source
 * @property string|null $note
 * @property Employees $employee
 * @property CompetencyYear $competencyYear
 */
class CompetencyExpectation extends ActiveRecord
{
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_SUGGESTED = 'suggested';

    public static function tableName()
    {
        return '{{%hr_competency_expectation}}';
    }

    public function rules()
    {
        return [
            [['emp_id', 'round_id', 'competency_year_id', 'expected_level'], 'required'],
            [['emp_id', 'round_id', 'competency_year_id', 'expected_level', 'created_by', 'updated_by'], 'integer'],
            [['expected_level'], 'integer', 'min' => 1, 'max' => 20],
            [['note'], 'string', 'max' => 255],
            [['created_at', 'updated_at'], 'safe'],
            [['source'], 'in', 'range' => [self::SOURCE_MANUAL, self::SOURCE_SUGGESTED]],
            [['source'], 'default', 'value' => self::SOURCE_MANUAL],
            [['emp_id'], 'exist', 'targetClass' => Employees::class, 'targetAttribute' => ['emp_id' => 'id']],
            [['competency_year_id'], 'exist', 'targetClass' => CompetencyYear::class, 'targetAttribute' => ['competency_year_id' => 'id']],
            [['round_id'], 'exist', 'targetClass' => AppraisalRound::class, 'targetAttribute' => ['round_id' => 'id']],
            [['emp_id'], 'unique', 'targetAttribute' => ['round_id', 'emp_id', 'competency_year_id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'expected_level' => 'ระดับที่คาดหวัง',
            'note' => 'หมายเหตุ',
        ];
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getCompetencyYear()
    {
        return $this->hasOne(CompetencyYear::class, ['id' => 'competency_year_id']);
    }

    public function getRound()
    {
        return $this->hasOne(AppraisalRound::class, ['id' => 'round_id']);
    }

    /**
     * ระดับที่ระบบแนะนำตามกติกาในแบบฟอร์มเดิม
     *   อายุงาน 1–2 ปี → ระดับ 1 · 3–5 ปี → ระดับ 2 · 6 ปีขึ้นไป → ระดับ 3
     * ส่วนกรรมการบริหาร (ระดับ 4) และผู้อำนวยการ (ระดับ 5) ยังไม่มีข้อมูลในทะเบียนบุคลากร
     * ให้ HR ปรับเองเมื่อจำเป็น จึงคืนค่าได้สูงสุด 3
     *
     * @return array{level: int|null, years: float|null, reason: string}
     */
    public static function suggestFor(Employees $employee, int $maxLevel): array
    {
        $joinDate = $employee->join_date ?: null;
        if (!$joinDate || $joinDate === '0000-00-00') {
            return ['level' => null, 'years' => null, 'reason' => 'ไม่มีวันที่เริ่มปฏิบัติงานในทะเบียนบุคลากร'];
        }

        $start = strtotime((string) $joinDate);
        if ($start === false) {
            return ['level' => null, 'years' => null, 'reason' => 'วันที่เริ่มปฏิบัติงานไม่ถูกต้อง'];
        }

        $years = (time() - $start) / (365.25 * 24 * 3600);
        if ($years < 0) {
            return ['level' => null, 'years' => null, 'reason' => 'วันที่เริ่มปฏิบัติงานอยู่ในอนาคต'];
        }

        if ($years < 3) {
            $level = 1;
            $reason = 'อายุงาน 1–2 ปี';
        } elseif ($years < 6) {
            $level = 2;
            $reason = 'อายุงาน 3–5 ปี';
        } else {
            $level = 3;
            $reason = 'อายุงาน 6 ปีขึ้นไป';
        }

        return [
            'level' => min($level, max(1, $maxLevel)),
            'years' => round($years, 1),
            'reason' => $reason,
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $uid = (Yii::$app->has('user') && !Yii::$app->user->isGuest) ? (int) Yii::$app->user->id : null;
        if ($insert) {
            $this->created_at = $now;
            $this->created_by = $uid;
        }
        $this->updated_at = $now;
        $this->updated_by = $uid;
        return true;
    }
}
