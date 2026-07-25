<?php

declare(strict_types=1);

namespace app\modules\housing\models;

final class GuestRequest extends HousingActiveRecord
{
    public static function tableName(): string
    {
        return '{{%housing_guest_request}}';
    }

    public function rules(): array
    {
        return [
            [['request_no', 'occupancy_id', 'requested_by_emp_id', 'guest_name', 'reason', 'start_date', 'end_date'], 'required'],
            [['occupancy_id', 'requested_by_emp_id', 'decided_by', 'created_by', 'updated_by'], 'integer'],
            [['reason', 'decision_note'], 'string'],
            [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
            [['end_date'], 'compare', 'compareAttribute' => 'start_date', 'operator' => '>=', 'type' => 'date'],
            [['decided_at', 'checked_in_at', 'checked_out_at'], 'safe'],
            [['request_no'], 'string', 'max' => 50],
            [['request_no'], 'unique'],
            [['guest_name'], 'string', 'max' => 255],
            [['citizen_id'], 'string', 'max' => 20],
            [['relationship'], 'string', 'max' => 100],
            [['phone'], 'string', 'max' => 50],
            [['status'], 'in', 'range' => ['pending', 'approved', 'rejected', 'active', 'completed', 'cancelled']],
            [['status'], 'default', 'value' => 'pending'],
        ];
    }

    public function getOccupancy()
    {
        return $this->hasOne(Occupancy::class, ['id' => 'occupancy_id']);
    }
}
