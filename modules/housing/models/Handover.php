<?php

declare(strict_types=1);

namespace app\modules\housing\models;

use yii\db\ActiveQuery;

final class Handover extends HousingActiveRecord
{
    public const TYPE_CHECK_IN = 'check_in';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';

    /** @var array */
    public $condition_photos = [];

    public static function tableName(): string
    {
        return '{{%housing_handover}}';
    }

    public function rules(): array
    {
        return [
            [['handover_no', 'occupancy_id', 'handover_date', 'handed_over_by_name', 'received_by_emp_id', 'received_by_name'], 'required'],
            [['occupancy_id', 'handed_over_by_emp_id', 'received_by_emp_id', 'confirmed_by', 'created_by', 'updated_by'], 'integer'],
            [['handover_date', 'handed_over_signed_at', 'received_signed_at', 'confirmed_at'], 'safe'],
            [['electric_meter_value', 'water_meter_value'], 'number', 'min' => 0],
            [['asset_snapshot', 'condition_note'], 'string'],
            [['handover_no'], 'string', 'max' => 50],
            [['handover_no'], 'unique'],
            [['occupancy_id'], 'unique'],
            [['handover_type'], 'in', 'range' => [self::TYPE_CHECK_IN]],
            [['handover_type'], 'default', 'value' => self::TYPE_CHECK_IN],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_CONFIRMED]],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['handed_over_by_name', 'received_by_name'], 'string', 'max' => 255],
            [['condition_photos'], 'file',
                'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                'maxSize' => 10 * 1024 * 1024,
                'maxFiles' => 10,
                'skipOnEmpty' => true,
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'handover_no' => 'เลขที่เอกสาร',
            'handover_date' => 'วันที่รับมอบ',
            'electric_meter_value' => 'เลขมิเตอร์ไฟฟ้าเริ่มต้น',
            'water_meter_value' => 'เลขมิเตอร์น้ำเริ่มต้น',
            'condition_note' => 'สภาพห้องและหมายเหตุ',
            'handed_over_by_name' => 'ผู้ส่งมอบ',
            'received_by_name' => 'ผู้รับมอบ',
            'condition_photos' => 'รูปถ่ายสภาพห้อง',
        ];
    }

    public function getOccupancy(): ActiveQuery
    {
        return $this->hasOne(Occupancy::class, ['id' => 'occupancy_id']);
    }

    public function assetItems(): array
    {
        $items = json_decode((string)$this->asset_snapshot, true);
        return is_array($items) ? $items : [];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'รอตรวจรับ',
            self::STATUS_CONFIRMED => 'รับมอบแล้ว',
        ];
    }
}
