<?php

namespace app\modules\hr\models;

use yii\db\ActiveRecord;

/**
 * ชุดมาตรวัดที่ผู้ประเมินเลือกเมื่อให้คะแนนข้อพฤติกรรม
 * ส่วนใหญ่ใช้ชุดมาตรฐาน 5 ระดับ (ไม่สังเกตเห็น … เป็นแบบอย่างให้กับผู้อื่น)
 * แต่ข้อวัดสุขภาพในสมรรถนะ "สู่สุขภาวะดี" ใช้มาตรของตัวเอง เช่น BMI หรือผลเดินวิ่ง
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $is_default
 * @property int $sort_order
 * @property CompetencyScaleOption[] $options
 */
class CompetencyScale extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%hr_competency_scale}}';
    }

    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['is_default', 'sort_order'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['code'], 'string', 'max' => 40],
            [['name'], 'string', 'max' => 150],
            [['code'], 'unique'],
            [['is_default'], 'default', 'value' => 0],
            [['sort_order'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => 'รหัส',
            'name' => 'ชื่อชุดมาตรวัด',
            'is_default' => 'เป็นชุดมาตรฐาน',
        ];
    }

    public function getOptions()
    {
        return $this->hasMany(CompetencyScaleOption::class, ['scale_id' => 'id'])
            ->orderBy(['score' => SORT_ASC]);
    }

    public static function defaultScale(): ?self
    {
        return self::find()->where(['is_default' => 1])->orderBy(['sort_order' => SORT_ASC])->one();
    }
}
