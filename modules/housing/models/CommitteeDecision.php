<?php

declare(strict_types=1);

namespace app\modules\housing\models;

final class CommitteeDecision extends HousingActiveRecord
{
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    public static function tableName(): string
    {
        return '{{%housing_committee_decision}}';
    }

    public function rules(): array
    {
        return [
            [['request_id', 'decision', 'decision_date'], 'required'],
            [['request_id', 'recorded_by', 'created_by', 'updated_by'], 'integer'],
            [['decision_date'], 'date', 'format' => 'php:Y-m-d'],
            [['decision'], 'in', 'range' => [self::APPROVED, self::REJECTED]],
            [['meeting_reference'], 'string', 'max' => 150],
            [['note'], 'string'],
            [['request_id'], 'unique'],
        ];
    }
}
