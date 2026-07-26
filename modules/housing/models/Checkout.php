<?php

declare(strict_types=1);

namespace app\modules\housing\models;

use yii\db\ActiveQuery;

final class Checkout extends HousingActiveRecord
{
    public const STATUS_REQUESTED = 'requested';
    public const STATUS_INSPECTION = 'inspection';
    public const STATUS_AWAITING_STAFF = 'awaiting_staff';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public $condition_photos = [];

    public static function tableName(): string
    {
        return '{{%housing_checkout}}';
    }

    public function rules(): array
    {
        return [
            [['checkout_no', 'occupancy_id', 'requested_date', 'move_out_reason', 'resident_emp_id', 'resident_name'], 'required'],
            [['occupancy_id', 'resident_emp_id', 'inspected_by_emp_id', 'completed_by', 'created_by', 'updated_by'], 'integer'],
            [['requested_date', 'checkout_date', 'resident_signed_at', 'inspector_signed_at', 'completed_at'], 'safe'],
            [['move_out_reason', 'asset_snapshot', 'condition_note'], 'string'],
            [['electric_meter_value', 'water_meter_value', 'outstanding_amount'], 'number', 'min' => 0],
            [['checkout_no'], 'string', 'max' => 50],
            [['checkout_no', 'occupancy_id'], 'unique'],
            [['resident_name', 'inspected_by_name'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['status'], 'default', 'value' => self::STATUS_REQUESTED],
            [['condition_photos'], 'file', 'extensions' => ['jpg', 'jpeg', 'png', 'webp'], 'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'], 'maxSize' => 10 * 1024 * 1024, 'maxFiles' => 10, 'skipOnEmpty' => true],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'requested_date' => 'วันที่ต้องการย้ายออก',
            'checkout_date' => 'วันที่ตรวจรับคืน',
            'move_out_reason' => 'เหตุผลการย้ายออก',
            'electric_meter_value' => 'เลขมิเตอร์ไฟฟ้าสุดท้าย',
            'water_meter_value' => 'เลขมิเตอร์น้ำสุดท้าย',
            'outstanding_amount' => 'ยอดค้างชำระ ณ วันตรวจ',
            'condition_note' => 'สภาพบ้านพักและหมายเหตุ',
            'condition_photos' => 'รูปถ่ายสภาพวันส่งคืน',
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
            self::STATUS_REQUESTED => 'รอผู้ดูแลนัดตรวจ',
            self::STATUS_INSPECTION => 'รอผู้พักลงนามส่งคืน',
            self::STATUS_AWAITING_STAFF => 'รอผู้ดูแลรับคืน',
            self::STATUS_COMPLETED => 'ส่งคืนเรียบร้อย',
            self::STATUS_CANCELLED => 'ยกเลิก',
        ];
    }
}
