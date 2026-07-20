<?php

namespace app\modules\am\models;

use yii\db\Expression;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * อัตราค่าเสื่อมหลายช่วงต่อ 1 เกณฑ์
 *
 * @property int $id
 * @property int $depreciation_profile_id
 * @property int $start_month
 * @property int|null $end_month
 * @property string $rate_percent
 * @property int $sequence
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property DepreciationProfile $profile
 */
class DepreciationProfileRate extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%depreciation_profile_rates}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['depreciation_profile_id', 'start_month', 'rate_percent'], 'required'],
            [['depreciation_profile_id', 'start_month', 'end_month', 'sequence'], 'integer'],
            [['start_month'], 'integer', 'min' => 1],
            [['rate_percent'], 'number', 'min' => 0],
            [['sequence'], 'default', 'value' => 0],
            [['end_month'], 'validateEndMonth'],
            [['end_month'], 'validateNoOverlap'],
            [
                ['depreciation_profile_id'],
                'exist',
                'targetClass' => DepreciationProfile::class,
                'targetAttribute' => 'id',
            ],
        ];
    }

    /**
     * end_month ต้องไม่น้อยกว่า start_month (null = เปิดปลายช่วง)
     */
    public function validateEndMonth($attribute)
    {
        if ($this->end_month !== null && (int) $this->end_month < (int) $this->start_month) {
            $this->addError($attribute, 'เดือนสิ้นสุดต้องไม่น้อยกว่าเดือนเริ่มต้น');
        }
    }

    /**
     * ห้ามช่วงเดือนซ้อนกันภายใน profile เดียวกัน
     */
    public function validateNoOverlap($attribute)
    {
        if ($this->hasErrors() || empty($this->depreciation_profile_id)) {
            return;
        }
        $start = (int) $this->start_month;
        $end = $this->end_month !== null ? (int) $this->end_month : PHP_INT_MAX;

        $others = self::find()
            ->where(['depreciation_profile_id' => $this->depreciation_profile_id])
            ->andFilterWhere(['not', ['id' => $this->id]])
            ->all();

        foreach ($others as $r) {
            $oStart = (int) $r->start_month;
            $oEnd = $r->end_month !== null ? (int) $r->end_month : PHP_INT_MAX;
            if ($start <= $oEnd && $oStart <= $end) {
                $this->addError($attribute, "ช่วงเดือน {$start}-" . ($this->end_month ?? '∞') . " ซ้อนทับกับช่วงที่มีอยู่ ({$oStart}-" . ($r->end_month ?? '∞') . ')');
                return;
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'id' => 'รหัส',
            'depreciation_profile_id' => 'เกณฑ์ค่าเสื่อม',
            'start_month' => 'เดือนเริ่มต้น',
            'end_month' => 'เดือนสิ้นสุด',
            'rate_percent' => 'อัตรา (%)',
            'sequence' => 'ลำดับ',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
        ];
    }

    public function getProfile()
    {
        return $this->hasOne(DepreciationProfile::class, ['id' => 'depreciation_profile_id']);
    }
}
