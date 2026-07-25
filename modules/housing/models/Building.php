<?php

declare(strict_types=1);

namespace app\modules\housing\models;

use yii\db\ActiveQuery;

final class Building extends HousingActiveRecord
{
    public const TYPE_HOUSE = 'house';
    public const TYPE_FLAT = 'flat';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public static function tableName(): string
    {
        return '{{%housing_building}}';
    }

    public function rules(): array
    {
        return [
            [['code', 'name', 'building_type'], 'required'],
            [['description', 'address'], 'string'],
            [['sort_order', 'created_by', 'updated_by'], 'integer'],
            [['code'], 'string', 'max' => 50],
            [['code'], 'unique'],
            [['name'], 'string', 'max' => 255],
            [['building_type'], 'in', 'range' => array_keys(self::typeOptions())],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['sort_order'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'code' => 'รหัส',
            'name' => 'ชื่อบ้านพัก/แฟลต',
            'building_type' => 'ประเภท',
            'address' => 'ที่ตั้ง',
            'description' => 'รายละเอียด',
            'status' => 'สถานะ',
            'sort_order' => 'ลำดับแสดงผล',
        ];
    }

    public function getFloors(): ActiveQuery
    {
        return $this->hasMany(Floor::class, ['building_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'floor_no' => SORT_ASC]);
    }

    public function getUnits(): ActiveQuery
    {
        return $this->hasMany(Unit::class, ['building_id' => 'id']);
    }

    public static function typeOptions(): array
    {
        return [self::TYPE_HOUSE => 'บ้านพัก', self::TYPE_FLAT => 'แฟลต'];
    }

    public static function statusOptions(): array
    {
        return [self::STATUS_ACTIVE => 'ใช้งาน', self::STATUS_INACTIVE => 'งดใช้งาน'];
    }
}
