<?php

namespace app\modules\hr\models;

use yii\db\ActiveRecord;

/**
 * ระดับสมรรถนะ (ระดับที่ 1..N) ของสมรรถนะในปีงบประมาณหนึ่ง
 * ผู้ประเมินจะให้คะแนนเฉพาะระดับ 1 ถึงระดับที่คาดหวังของผู้ถูกประเมิน
 *
 * @property int $id
 * @property int $competency_year_id
 * @property int $level_no
 * @property string|null $description
 * @property int $sort_order
 * @property CompetencyYear $competencyYear
 * @property CompetencyIndicator[] $indicators
 */
class CompetencyLevel extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%hr_competency_level}}';
    }

    public function rules()
    {
        return [
            [['competency_year_id', 'level_no'], 'required'],
            [['competency_year_id', 'level_no', 'sort_order'], 'integer'],
            [['description'], 'string'],
            [['sort_order'], 'default', 'value' => 0],
            [['level_no'], 'unique',
                'targetAttribute' => ['competency_year_id', 'level_no'],
                'message' => 'มีระดับนี้อยู่แล้วในสมรรถนะเดียวกัน',
            ],
            [['competency_year_id'], 'exist', 'targetClass' => CompetencyYear::class, 'targetAttribute' => ['competency_year_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'level_no' => 'ระดับที่',
            'description' => 'คำอธิบายระดับ',
        ];
    }

    public function getCompetencyYear()
    {
        return $this->hasOne(CompetencyYear::class, ['id' => 'competency_year_id']);
    }

    public function getIndicators()
    {
        return $this->hasMany(CompetencyIndicator::class, ['level_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }
}
