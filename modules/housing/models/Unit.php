<?php

declare(strict_types=1);

namespace app\modules\housing\models;

use yii\db\ActiveQuery;

final class Unit extends HousingActiveRecord
{
    public const MODE_FAMILY = 'family';
    public const MODE_SHARED = 'shared';
    public const MODE_SINGLE_UNIT = 'single_unit';
    public const STATUS_VACANT = 'vacant';
    public const STATUS_OCCUPIED = 'occupied';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_MOVE_OUT = 'move_out';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_INACTIVE = 'inactive';

    public static function tableName(): string
    {
        return '{{%housing_unit}}';
    }

    public function rules(): array
    {
        return [
            [['building_id', 'code', 'name', 'occupancy_mode'], 'required'],
            [['building_id', 'floor_id', 'capacity', 'sort_order', 'created_by', 'updated_by'], 'integer'],
            [['monthly_base_fee'], 'number'],
            [['description'], 'string'],
            [['code'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 150],
            [['code'], 'unique'],
            [['occupancy_mode'], 'in', 'range' => array_keys(self::modeOptions())],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['status'], 'default', 'value' => self::STATUS_VACANT],
            [['capacity'], 'integer', 'min' => 1, 'skipOnEmpty' => true],
            [['sort_order'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'building_id' => 'อาคาร',
            'floor_id' => 'ชั้น',
            'code' => 'รหัสยูนิต',
            'name' => 'ชื่อยูนิต',
            'occupancy_mode' => 'รูปแบบการพัก',
            'capacity' => 'ความจุ (ถ้ามี)',
            'monthly_base_fee' => 'ค่าพื้นฐานต่อเดือน',
            'description' => 'รายละเอียด',
            'status' => 'สถานะ',
            'sort_order' => 'ลำดับแสดงผล',
        ];
    }

    public function getBuilding(): ActiveQuery
    {
        return $this->hasOne(Building::class, ['id' => 'building_id']);
    }

    public function getFloor(): ActiveQuery
    {
        return $this->hasOne(Floor::class, ['id' => 'floor_id']);
    }

    public function getRooms(): ActiveQuery
    {
        return $this->hasMany(Room::class, ['unit_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'code' => SORT_ASC]);
    }

    public static function modeOptions(): array
    {
        return [
            self::MODE_FAMILY => 'ครอบครัว (ทั้งยูนิต)',
            self::MODE_SHARED => 'แฟลตโสด (แยกห้อง)',
            self::MODE_SINGLE_UNIT => 'บุคคลเดียว (ทั้งยูนิต)',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_VACANT => 'ว่าง',
            self::STATUS_OCCUPIED => 'มีผู้พัก',
            self::STATUS_RESERVED => 'รอเข้าอยู่',
            self::STATUS_MOVE_OUT => 'รอย้ายออก',
            self::STATUS_MAINTENANCE => 'ปิดซ่อม',
            self::STATUS_INACTIVE => 'งดใช้งาน',
        ];
    }
}
