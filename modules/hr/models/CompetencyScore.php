<?php

namespace app\modules\hr\models;

use yii\db\ActiveRecord;

/**
 * คะแนนของข้อพฤติกรรมบ่งชี้ 1 ข้อ ในใบประเมิน 1 ใบ
 *
 * @property int $id
 * @property int $evaluation_id
 * @property int $indicator_id
 * @property int $score
 * @property string $scored_by level / item
 * @property CompetencyEvaluation $evaluation
 * @property CompetencyIndicator $indicator
 */
class CompetencyScore extends ActiveRecord
{
    public const BY_LEVEL = 'level'; // ผู้ประเมินให้คะแนนทั้งระดับ ระบบกระจายลงทุกข้อ
    public const BY_ITEM = 'item';   // ผู้ประเมินแยกให้คะแนนข้อนี้เอง

    public static function tableName()
    {
        return '{{%hr_competency_score}}';
    }

    public function rules()
    {
        return [
            [['evaluation_id', 'indicator_id', 'score'], 'required'],
            [['evaluation_id', 'indicator_id'], 'integer'],
            [['score'], 'integer', 'min' => 1, 'max' => 5],
            [['updated_at'], 'safe'],
            [['scored_by'], 'in', 'range' => [self::BY_LEVEL, self::BY_ITEM]],
            [['scored_by'], 'default', 'value' => self::BY_LEVEL],
            [['indicator_id'], 'unique', 'targetAttribute' => ['evaluation_id', 'indicator_id']],
            [['evaluation_id'], 'exist', 'targetClass' => CompetencyEvaluation::class, 'targetAttribute' => ['evaluation_id' => 'id']],
            [['indicator_id'], 'exist', 'targetClass' => CompetencyIndicator::class, 'targetAttribute' => ['indicator_id' => 'id']],
        ];
    }

    public function getEvaluation()
    {
        return $this->hasOne(CompetencyEvaluation::class, ['id' => 'evaluation_id']);
    }

    public function getIndicator()
    {
        return $this->hasOne(CompetencyIndicator::class, ['id' => 'indicator_id']);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->updated_at = date('Y-m-d H:i:s');
        return true;
    }
}
