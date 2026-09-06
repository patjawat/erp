<?php

namespace app\modules\qms\models;

/**
 * การเชื่อมโยงข้อกำหนดข้ามมาตรฐาน (Requirement Mapping / Shared Control)
 * requirement_id = ข้อเจ้าของ (ที่มีหลักฐานจริง) → ไปสนอง standard_id ปลายทาง
 *
 * @property int $id
 * @property int $requirement_id
 * @property int $standard_id
 * @property string $relation
 * @property string|null $note
 */
class RequirementLink extends QmsActiveRecord
{
    public const RELATION_DIRECT = 'direct';
    public const RELATION_PARTIAL = 'partial';

    public static function tableName(): string
    {
        return '{{%qms_requirement_link}}';
    }

    public static function relationLabels(): array
    {
        return [
            self::RELATION_DIRECT => 'เชื่อมโยงโดยตรง',
            self::RELATION_PARTIAL => 'เชื่อมโยงบางส่วน',
        ];
    }

    public function rules(): array
    {
        return [
            [['requirement_id', 'standard_id'], 'required'],
            [['requirement_id', 'standard_id'], 'integer'],
            [['relation'], 'in', 'range' => [self::RELATION_DIRECT, self::RELATION_PARTIAL]],
            [['relation'], 'default', 'value' => self::RELATION_DIRECT],
            [['note'], 'string', 'max' => 255],
            [['requirement_id', 'standard_id'], 'unique', 'targetAttribute' => ['requirement_id', 'standard_id']],
            [['requirement_id'], 'exist', 'targetClass' => Requirement::class, 'targetAttribute' => 'id'],
            [['standard_id'], 'exist', 'targetClass' => Standard::class, 'targetAttribute' => 'id'],
        ];
    }

    public function getRequirement()
    {
        return $this->hasOne(Requirement::class, ['id' => 'requirement_id']);
    }

    public function getStandard()
    {
        return $this->hasOne(Standard::class, ['id' => 'standard_id']);
    }

    public function relationLabel(): string
    {
        return self::relationLabels()[$this->relation] ?? $this->relation;
    }
}
