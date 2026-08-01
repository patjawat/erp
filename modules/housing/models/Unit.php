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
            [['electric_account_no'], 'string', 'max' => 100],
            [['code'], 'unique'],
            [['occupancy_mode'], 'in', 'range' => array_keys(self::modeOptions())],
            [['occupancy_mode'], 'validateModeChange'],
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
            'code' => 'รหัสห้อง',
            'name' => 'ชื่อห้อง',
            'electric_account_no' => 'หมายเลขผู้ใช้ไฟฟ้า',
            'occupancy_mode' => 'รูปแบบการพัก',
            'capacity' => 'ความจุ (ถ้ามี)',
            'monthly_base_fee' => 'ค่าพื้นฐานต่อเดือน',
            'description' => 'รายละเอียด',
            'status' => 'สถานะ',
            'sort_order' => 'ลำดับแสดงผล',
        ];
    }

    /**
     * ห้ามสลับ "รูปแบบการพัก" ข้ามเส้นแบ่ง แยกห้องย่อย (shared) ↔ ทั้งห้อง ขณะที่ห้อง
     * ยังมีการจัดสรรหรือมีผู้พักอยู่ เพราะโครงสร้าง occupancy (ระดับห้องย่อย vs ทั้งห้อง)
     * จะไม่สอดคล้องกับ mode ทันที
     */
    public function validateModeChange(string $attribute): void
    {
        if ($this->isNewRecord || !$this->isAttributeChanged($attribute)) {
            return;
        }
        $wasShared = $this->getOldAttribute($attribute) === self::MODE_SHARED;
        $isShared = $this->{$attribute} === self::MODE_SHARED;
        if ($wasShared === $isShared) {
            return;
        }
        $hasOccupancy = Occupancy::find()->where([
            'unit_id' => $this->id,
            'status' => [Occupancy::STATUS_ALLOCATED, Occupancy::STATUS_ACTIVE],
        ])->exists();
        if ($hasOccupancy) {
            $this->addError($attribute, 'ไม่สามารถเปลี่ยนรูปแบบการพักระหว่าง "แยกห้องย่อย" กับ "ทั้งห้อง" ได้ เนื่องจากห้องนี้มีการจัดสรรหรือมีผู้พักอยู่ กรุณาย้ายออกหรือยกเลิกการจัดสรรก่อน');
        }
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

    public function getAssets(): ActiveQuery
    {
        return $this->hasMany(AssetAssignment::class, ['unit_id' => 'id'])
            ->andWhere(['room_id' => null, 'is_active' => 1])
            ->orderBy(['item_name' => SORT_ASC]);
    }

    public function getPhotos(): ActiveQuery
    {
        return $this->hasMany(LocationPhoto::class, ['unit_id' => 'id'])
            ->andWhere(['room_id' => null])
            ->orderBy(['is_primary' => SORT_DESC, 'sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public static function modeOptions(): array
    {
        return [
            self::MODE_FAMILY => 'ครอบครัว (ทั้งห้อง)',
            self::MODE_SHARED => 'แฟลตโสด (แยกห้องย่อย)',
            self::MODE_SINGLE_UNIT => 'บุคคลเดียว (ทั้งห้อง)',
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
