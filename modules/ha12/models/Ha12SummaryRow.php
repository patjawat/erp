<?php

namespace app\modules\ha12\models;

/**
 * แถวสรุปในตารางสรุป PCT ของกิจกรรม
 *
 * @property int $id
 * @property int $assessment_id
 * @property string|null $unit_name
 * @property string|null $topic
 * @property string|null $improvement
 * @property int $sort
 * @property string $ref
 */
class Ha12SummaryRow extends Ha12ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%ha12_summary_row}}';
    }

    public function rules(): array
    {
        return [
            [['assessment_id'], 'required'],
            [['assessment_id', 'sort'], 'integer'],
            [['unit_name', 'topic'], 'string', 'max' => 500],
            [['improvement'], 'string'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'unit_name' => 'หน่วยงาน',
            'topic' => 'เรื่อง/โรค',
            'improvement' => 'ผลการปรับปรุง',
        ];
    }

    public function getAssessment()
    {
        return $this->hasOne(Ha12Assessment::class, ['id' => 'assessment_id']);
    }

    public function getSources()
    {
        return $this->hasMany(Ha12SummarySource::class, ['summary_row_id' => 'id'])
            ->orderBy(['id' => SORT_ASC]);
    }
}
