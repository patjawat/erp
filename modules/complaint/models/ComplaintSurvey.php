<?php

namespace app\modules\complaint\models;

use yii\db\ActiveQuery;

/**
 * แบบสำรวจความพึงพอใจหลังจัดการเรื่องร้องเรียน (Satisfaction_Survey)
 *
 * @property int         $id
 * @property int         $complaint_id
 * @property string|null $survey_date
 * @property int|null    $score1
 * @property int|null    $score2
 * @property int|null    $score3
 * @property int|null    $score4
 * @property int|null    $score5
 * @property string|null $avg_score
 * @property string|null $comment
 */
class ComplaintSurvey extends ComplaintActiveRecord
{
    /** ป้ายชื่อ 5 ด้านที่ประเมิน */
    public const ASPECTS = [
        'score1' => 'การรับฟัง/ใส่ใจ',
        'score2' => 'ความรวดเร็วในการตอบสนอง',
        'score3' => 'ความชัดเจนของการชี้แจง',
        'score4' => 'ผลการแก้ไข/เยียวยา',
        'score5' => 'ความพึงพอใจโดยรวม',
    ];

    public static function tableName(): string
    {
        return '{{%complaint_survey}}';
    }

    public function rules(): array
    {
        return [
            [['complaint_id'], 'required'],
            [['complaint_id', 'score1', 'score2', 'score3', 'score4', 'score5'], 'integer'],
            [['score1', 'score2', 'score3', 'score4', 'score5'], 'in', 'range' => [1, 2, 3, 4, 5], 'skipOnEmpty' => true],
            [['survey_date'], 'safe'],
            [['avg_score'], 'number'],
            [['comment'], 'string'],
        ];
    }

    /** คำนวณคะแนนเฉลี่ยจากด้านที่ให้คะแนน (ข้ามด้านที่ว่าง) */
    public function computeAvg(): void
    {
        $scores = array_filter(
            [$this->score1, $this->score2, $this->score3, $this->score4, $this->score5],
            static fn ($v) => $v !== null && $v !== ''
        );
        $this->avg_score = $scores ? round(array_sum($scores) / count($scores), 2) : null;
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->computeAvg();
        return true;
    }

    public function getComplaint(): ActiveQuery
    {
        return $this->hasOne(Complaint::class, ['id' => 'complaint_id']);
    }
}
