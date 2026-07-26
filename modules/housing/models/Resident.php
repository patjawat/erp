<?php

declare(strict_types=1);

namespace app\modules\housing\models;

final class Resident extends HousingActiveRecord
{
    public static function tableName(): string
    {
        return '{{%housing_resident}}';
    }

    public function rules(): array
    {
        return [
            [['occupancy_id', 'resident_type', 'first_name', 'last_name', 'start_date'], 'required'],
            [['occupancy_id', 'created_by', 'updated_by'], 'integer'],
            [['birth_date', 'start_date', 'end_date'], 'safe'],
            [['count_for_charge'], 'boolean'],
            [['count_for_charge'], 'default', 'value' => true],
            [['note'], 'string'],
            [['resident_type', 'relationship', 'prefix', 'phone'], 'string', 'max' => 50],
            [['first_name', 'last_name'], 'string', 'max' => 150],
            [['citizen_id'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => ['active', 'ended']],
            [['status'], 'default', 'value' => 'active'],
        ];
    }
}
