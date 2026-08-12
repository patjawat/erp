<?php

namespace app\modules\hr\models;

use yii\db\ActiveRecord;

/**
 * ตัวเลือกในชุดมาตรวัด 1 บรรทัด = 1 คะแนน
 *
 * @property int $id
 * @property int $scale_id
 * @property int $score
 * @property string $label
 * @property int $sort_order
 * @property CompetencyScale $scale
 */
class CompetencyScaleOption extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%hr_competency_scale_option}}';
    }

    public function rules()
    {
        return [
            [['scale_id', 'score', 'label'], 'required'],
            [['scale_id', 'score', 'sort_order'], 'integer'],
            [['score'], 'integer', 'min' => 1, 'max' => 5],
            [['label'], 'string', 'max' => 255],
            [['sort_order'], 'default', 'value' => 0],
            [['scale_id'], 'exist', 'targetClass' => CompetencyScale::class, 'targetAttribute' => ['scale_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'score' => 'คะแนน',
            'label' => 'ข้อความตัวเลือก',
        ];
    }

    public function getScale()
    {
        return $this->hasOne(CompetencyScale::class, ['id' => 'scale_id']);
    }
}
