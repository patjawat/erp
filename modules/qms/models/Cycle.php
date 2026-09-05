<?php

namespace app\modules\qms\models;

/**
 * รอบปีงบประมาณของมาตรฐานหนึ่ง (standard × fiscal_year)
 *
 * @property int $id
 * @property int $standard_id
 * @property int $fiscal_year
 * @property string $status
 * @property string|null $next_review_date
 * @property string|null $note
 */
class Cycle extends QmsActiveRecord
{
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    public static function tableName(): string
    {
        return '{{%qms_cycle}}';
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_OPEN => 'เปิดอยู่',
            self::STATUS_CLOSED => 'ปิดรอบแล้ว',
        ];
    }

    public function rules(): array
    {
        return [
            [['standard_id', 'fiscal_year'], 'required'],
            [['standard_id', 'fiscal_year'], 'integer'],
            [['status'], 'in', 'range' => [self::STATUS_OPEN, self::STATUS_CLOSED]],
            [['status'], 'default', 'value' => self::STATUS_OPEN],
            [['next_review_date'], 'date', 'format' => 'php:Y-m-d'],
            [['note'], 'string'],
            [['standard_id', 'fiscal_year'], 'unique', 'targetAttribute' => ['standard_id', 'fiscal_year']],
            [['standard_id'], 'exist', 'targetClass' => Standard::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'standard_id' => 'มาตรฐาน',
            'fiscal_year' => 'ปีงบประมาณ',
            'status' => 'สถานะรอบ',
            'next_review_date' => 'ทบทวนครั้งถัดไป',
            'note' => 'หมายเหตุ',
        ];
    }

    public function getStandard()
    {
        return $this->hasOne(Standard::class, ['id' => 'standard_id']);
    }

    public function getItems()
    {
        return $this->hasMany(CycleItem::class, ['cycle_id' => 'id']);
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }
}
