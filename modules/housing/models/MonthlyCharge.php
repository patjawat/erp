<?php

declare(strict_types=1);

namespace app\modules\housing\models;

use yii\db\ActiveQuery;

final class MonthlyCharge extends HousingActiveRecord
{
    public static function tableName(): string
    {
        return '{{%housing_monthly_charge}}';
    }

    public function rules(): array
    {
        return [
            [['billing_period_id', 'building_id', 'unit_id', 'charge_type_id'], 'required'],
            [['billing_period_id', 'building_id', 'unit_id', 'charge_type_id', 'is_overridden', 'created_by', 'updated_by'], 'integer'],
            [['previous_value', 'current_value', 'quantity', 'unit_rate', 'calculated_amount', 'actual_amount'], 'number', 'min' => 0],
            [['override_reason'], 'string', 'max' => 255],
            [['note'], 'string'],
            [['status'], 'in', 'range' => ['draft', 'confirmed']],
        ];
    }

    public function getPeriod(): ActiveQuery { return $this->hasOne(BillingPeriod::class, ['id' => 'billing_period_id']); }
    public function getBuilding(): ActiveQuery { return $this->hasOne(Building::class, ['id' => 'building_id']); }
    public function getUnit(): ActiveQuery { return $this->hasOne(Unit::class, ['id' => 'unit_id']); }
    public function getChargeType(): ActiveQuery { return $this->hasOne(ChargeType::class, ['id' => 'charge_type_id']); }
}
