<?php

namespace app\modules\ha12\models;

/**
 * ผลติดตามของรายการทบทวน (หลายครั้งต่อ 1 รายการ)
 *
 * @property int $id
 * @property int $review_id
 * @property string|null $followup_date
 * @property string|null $finding
 * @property string|null $evidence
 * @property string $ref
 */
class Ha12ReviewFollowup extends Ha12ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%ha12_review_followup}}';
    }

    public function rules(): array
    {
        return [
            [['review_id'], 'required'],
            [['review_id'], 'integer'],
            [['followup_date'], 'required'],
            [['followup_date'], 'date', 'format' => 'php:Y-m-d'],
            [['finding', 'evidence'], 'string'],
            [['finding'], 'required'],
            [['review_id'], 'exist', 'targetClass' => Ha12Review::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'followup_date' => 'วันที่ติดตาม',
            'finding' => 'ผลที่พบ',
            'evidence' => 'หลักฐาน',
        ];
    }

    public function getReview()
    {
        return $this->hasOne(Ha12Review::class, ['id' => 'review_id']);
    }
}
