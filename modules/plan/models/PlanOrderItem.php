<?php

namespace app\modules\plan\models;

use Yii;

/**
 * This is the model class for table "plan_item".
 *
 * @property int $id
 * @property int $plan_order_id รหัสแผน
 * @property string $title ชื่อวัสดุ/บุคลากร/ค่าใช้สอย
 * @property string|null $asset_code รหัสครุภัณฑ์
 * @property string $item_id รหัสที่ใช้เชื่อมกัน (วัสดุ = รหัสวัสดุ, บุคลากร = รหัสพนักงาน)
 * @property string $item_name ชื่อการเชื่อมต่อ
 * @property int|null $qty จำนวน
 * @property float|null $unit_price ราคาต่อหน่วย
 * @property float|null $total_price ราคารวม
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class PlanOrderItem extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plan_order_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_json', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['qty'], 'default', 'value' => 1],
            [['total_price'], 'default', 'value' => 0.00],
            [['plan_order_id', 'title', 'item_id', 'item_name'], 'required'],
            [['plan_order_id', 'qty', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['unit_price', 'total_price'], 'number'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['title', 'item_id', 'item_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * สร้างรายการวัสดุของแผนจากแถวที่ส่งมาจากฟอร์ม
     *
     * รวมไว้ที่เดียวเพราะมี 3 ทางที่บันทึกรายการวัสดุ (plan/parcel สร้าง, plan/parcel แก้ไข,
     * me/plan ของหัวหน้าหน่วยงาน) ซึ่งเคยเขียนแยกกันแล้วตกหล่นไม่เหมือนกัน
     *
     * item_id เก็บรหัสวัสดุไว้โยงกลับไปทะเบียนวัสดุ ให้สอดคล้องกับแผนบุคลากรที่เก็บ emp_id ไว้ช่องเดียวกัน
     * แถวที่ผู้ใช้พิมพ์ชื่อเองจะไม่มีรหัส เก็บเป็นค่าว่างได้
     *
     * @param array $row คีย์ที่รับ: item_name, qty, unit_price, code
     * @return array|null null เมื่อแถวไม่มีชื่อรายการ (แถวว่างจากฟอร์ม)
     */
    public static function parcelRowAttributes(int $planOrderId, array $row): ?array
    {
        $itemName = trim((string) ($row['item_name'] ?? ''));
        if ($itemName === '') {
            return null;
        }

        $qty = (int) ($row['qty'] ?? 0);
        $unitPrice = (float) ($row['unit_price'] ?? 0);

        return [
            'plan_order_id' => $planOrderId,
            'title' => $itemName,
            'item_name' => $itemName,
            'item_id' => trim((string) ($row['code'] ?? '')),
            'qty' => $qty,
            'unit_price' => $unitPrice,
            'total_price' => round($qty * $unitPrice, 2),
        ];
    }

    /**
     * บันทึกรายการวัสดุหนึ่งแถวจากฟอร์ม คืน false เมื่อเป็นแถวว่างที่ข้ามไป
     */
    public static function saveParcelRow(int $planOrderId, array $row): bool
    {
        $attributes = self::parcelRowAttributes($planOrderId, $row);
        if ($attributes === null) {
            return false;
        }

        $item = new self();
        $item->setAttributes($attributes, false);

        return $item->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'plan_order_id' => 'Plan Order ID',
            'title' => 'Title',
            'item_id' => 'Item ID',
            'item_name' => 'Item Name',
            'qty' => 'Qty',
            'unit_price' => 'Unit Price',
            'total_price' => 'Total Price',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
        ];
    }

}
