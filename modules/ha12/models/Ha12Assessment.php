<?php

namespace app\modules\ha12\models;

/**
 * ผลประเมินต่อกิจกรรม ต่อรอบ (เลือกได้หลายระดับ)
 *
 * @property int $id
 * @property int $round_id
 * @property int $activity_id
 * @property string|null $levels  ระดับที่เลือก คั่นด้วย , เช่น "3,4"
 * @property string|null $reason
 * @property string|null $summary_text
 * @property string $status  draft|published
 * @property string|null $published_at
 * @property string $ref
 */
class Ha12Assessment extends Ha12ActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    public static function tableName(): string
    {
        return '{{%ha12_assessment}}';
    }

    public function rules(): array
    {
        return [
            [['round_id', 'activity_id'], 'required'],
            [['round_id', 'activity_id'], 'integer'],
            [['levels'], 'string', 'max' => 32],
            [['reason', 'summary_text'], 'string'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLISHED]],
        ];
    }

    /** ระดับที่เลือกเป็น array<int> */
    public function levelArray(): array
    {
        if (!$this->levels) {
            return [];
        }
        return array_values(array_filter(array_map('intval', explode(',', $this->levels))));
    }

    public function setLevelArray(array $levels): void
    {
        $clean = array_values(array_unique(array_filter(array_map('intval', $levels), static fn ($v) => $v >= 1 && $v <= 5)));
        sort($clean);
        $this->levels = $clean ? implode(',', $clean) : null;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function getRound()
    {
        return $this->hasOne(Ha12Round::class, ['id' => 'round_id']);
    }

    public function getActivity()
    {
        return $this->hasOne(Ha12Activity::class, ['id' => 'activity_id']);
    }

    public function getRows()
    {
        return $this->hasMany(Ha12SummaryRow::class, ['assessment_id' => 'id'])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
    }
}
