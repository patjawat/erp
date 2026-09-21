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

    public function attributeLabels(): array
    {
        return [
            'prefix' => 'คำนำหน้า',
            'first_name' => 'ชื่อ',
            'last_name' => 'นามสกุล',
            'relationship' => 'ความสัมพันธ์',
            'citizen_id' => 'เลขบัตรประชาชน',
            'phone' => 'เบอร์โทร',
            'birth_date' => 'วันเดือนปีเกิด',
            'count_for_charge' => 'นับรวมคิดค่าใช้จ่ายรายหัว',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
        ];
    }

    public static function relationshipOptions(): array
    {
        return [
            'spouse' => 'คู่สมรส',
            'child' => 'บุตร',
            'parent' => 'บิดา/มารดา',
            'sibling' => 'พี่น้อง',
            'relative' => 'ญาติ',
            'other' => 'อื่น ๆ',
        ];
    }

    public static function statusOptions(): array
    {
        return ['active' => 'พักอาศัยอยู่', 'ended' => 'ย้ายออกแล้ว'];
    }

    public function isEmployee(): bool
    {
        return $this->resident_type === 'employee';
    }

    public function fullName(): string
    {
        return trim(($this->prefix ? $this->prefix . ' ' : '') . $this->first_name . ' ' . $this->last_name);
    }
}
