<?php

namespace app\modules\ha12\models;

/**
 * ค่ารายเดือนของตัวชี้วัด (M01-M12 ตามปีงบ)
 *
 * @property int $id
 * @property int $indicator_id
 * @property int $month_no
 * @property string|null $value
 * @property string $ref
 */
class Ha12IndicatorValue extends Ha12ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%ha12_indicator_value}}';
    }

    public function rules(): array
    {
        return [
            [['indicator_id', 'month_no'], 'required'],
            [['indicator_id', 'month_no'], 'integer'],
            [['month_no'], 'in', 'range' => range(1, 12)],
            [['value'], 'number'],
        ];
    }

    public function getIndicator()
    {
        return $this->hasOne(Ha12Indicator::class, ['id' => 'indicator_id']);
    }
}
