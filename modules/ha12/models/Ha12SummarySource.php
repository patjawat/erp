<?php

namespace app\modules\ha12\models;

/**
 * หลักฐานของแถวสรุป (polymorphic) + snapshot ณ รุ่นที่ใช้ประเมิน
 *
 * @property int $id
 * @property int $summary_row_id
 * @property string $source_type  review|med|mrec|indicator
 * @property int $source_id
 * @property int|null $source_rev
 * @property string|null $label
 * @property string|null $snapshot_json
 * @property string $ref
 */
class Ha12SummarySource extends Ha12ActiveRecord
{
    public const TYPE_REVIEW = 'review';
    public const TYPE_MED = 'med';
    public const TYPE_MREC = 'mrec';
    public const TYPE_INDICATOR = 'indicator';

    public static function tableName(): string
    {
        return '{{%ha12_summary_source}}';
    }

    public function rules(): array
    {
        return [
            [['summary_row_id', 'source_type', 'source_id'], 'required'],
            [['summary_row_id', 'source_id', 'source_rev'], 'integer'],
            [['source_type'], 'in', 'range' => [self::TYPE_REVIEW, self::TYPE_MED, self::TYPE_MREC, self::TYPE_INDICATOR]],
            [['label'], 'string', 'max' => 500],
            [['snapshot_json'], 'string'],
        ];
    }

    public function snapshot(): array
    {
        $d = $this->snapshot_json ? json_decode($this->snapshot_json, true) : [];
        return is_array($d) ? $d : [];
    }
}
