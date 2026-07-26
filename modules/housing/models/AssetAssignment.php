<?php

declare(strict_types=1);

namespace app\modules\housing\models;

use yii\db\ActiveQuery;

final class AssetAssignment extends HousingActiveRecord
{
    public const CONDITION_NORMAL = 'normal';
    public const CONDITION_REPAIR = 'repair';
    public const CONDITION_DAMAGED = 'damaged';
    public const CONDITION_LOST = 'lost';

    public $image_file;

    public static function tableName(): string
    {
        return '{{%housing_asset_assignment}}';
    }

    public function rules(): array
    {
        return [
            [['unit_id', 'item_name', 'quantity', 'unit_name'], 'required'],
            [['unit_id', 'room_id', 'asset_id', 'is_active', 'created_by', 'updated_by'], 'integer'],
            [['quantity', 'unit_price', 'monthly_rent'], 'number', 'min' => 0],
            [['assigned_at', 'returned_at'], 'safe'],
            [['description'], 'string'],
            [['item_name'], 'string', 'max' => 255],
            [['category'], 'string', 'max' => 100],
            [['unit_name'], 'string', 'max' => 50],
            [['condition_status'], 'in', 'range' => array_keys(self::conditionOptions())],
            [['condition_status'], 'default', 'value' => self::CONDITION_NORMAL],
            [['quantity'], 'default', 'value' => 1],
            [['unit_name'], 'default', 'value' => 'ชิ้น'],
            [['unit_price', 'monthly_rent'], 'default', 'value' => 0],
            [['is_active'], 'default', 'value' => 1],
            [['image_file'], 'file',
                'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                'maxSize' => 10 * 1024 * 1024,
                'skipOnEmpty' => true,
            ],
            [['room_id'], 'validateRoom'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'item_name' => 'ชื่ออุปกรณ์หรือของใช้',
            'category' => 'หมวด',
            'quantity' => 'จำนวน',
            'unit_name' => 'หน่วยนับ',
            'unit_price' => 'ราคาต่อหน่วย',
            'monthly_rent' => 'ค่าเช่ารายเดือน',
            'condition_status' => 'สภาพ',
            'assigned_at' => 'วันที่ติดตั้ง/นำเข้า',
            'description' => 'หมายเหตุ',
            'is_active' => 'ใช้งานอยู่',
            'image_file' => 'รูปภาพอุปกรณ์',
        ];
    }

    public function validateRoom(string $attribute): void
    {
        if ($this->room_id && !Room::find()->where(['id' => $this->room_id, 'unit_id' => $this->unit_id])->exists()) {
            $this->addError($attribute, 'ห้องที่เลือกไม่ได้อยู่ในยูนิตนี้');
        }
    }

    public function getUnit(): ActiveQuery
    {
        return $this->hasOne(Unit::class, ['id' => 'unit_id']);
    }

    public function getRoom(): ActiveQuery
    {
        return $this->hasOne(Room::class, ['id' => 'room_id']);
    }

    public function totalValue(): float
    {
        return (float) $this->quantity * (float) $this->unit_price;
    }

    public function totalMonthlyRent(): float
    {
        return (float) $this->quantity * (float) $this->monthly_rent;
    }

    public static function conditionOptions(): array
    {
        return [
            self::CONDITION_NORMAL => 'สภาพดี',
            self::CONDITION_REPAIR => 'ต้องซ่อม',
            self::CONDITION_DAMAGED => 'ชำรุด/ใช้งานไม่ได้',
            self::CONDITION_LOST => 'สูญหาย',
        ];
    }
}
