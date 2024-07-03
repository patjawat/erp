<?php

namespace app\modules\warehouse\models;

use Yii;

/**
 * This is the model class for table "stock_order".
 *
 * @property int $id รหัสการเคลื่อนไหวสินค้า
 * @property string|null $name ชื่อการเก็บของข้อมูล เช่น stock_order, stock_item
 * @property string|null $po_number รหัสใบสั่งซื้อ
 * @property string|null $rc_number รหัสใบรับสินค้า
 * @property int|null $product_id รหัสสินค้า
 * @property int|null $from_warehouse_id รหัสคลังสินค้าต้นทาง
 * @property int|null $to_warehouse_id รหัสคลังสินค้าปลายทาง
 * @property int|null $qty จำนวนสินค้าที่เคลื่อนย้าย
 * @property string $movement_type ประเภทการเคลื่อนไหว (IN = รับเข้า, OUT = จ่ายออก, TRANSFER = โอนย้าย)
 * @property string $movement_date วันที่และเวลาที่เกิดการเคลื่อนไหว
 * @property string|null $lot_number หมายเลข Lot
 * @property string|null $expiry_date วันหมดอายุ
 * @property string|null $category_id หมวดหมูหลักที่เก็บ
 * @property string|null $ref
 * @property string|null $data_json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class StockOrder extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $qty_check;

    public static function tableName()
    {
        return 'stock_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'from_warehouse_id', 'to_warehouse_id', 'qty', 'created_by', 'updated_by', 'lot_number'], 'integer'],
            [['movement_type'], 'required'],
            [['movement_type'], 'string'],
            [['movement_date', 'expiry_date', 'data_json', 'created_at', 'updated_at', 'qty_check'], 'safe'],
            [['name', 'po_number', 'rc_number', 'lot_number'], 'string', 'max' => 50],
            [['category_id', 'ref'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'po_number' => 'Po Number',
            'rc_number' => 'Rc Number',
            'product_id' => 'Product ID',
            'from_warehouse_id' => 'From Warehouse ID',
            'to_warehouse_id' => 'To Warehouse ID',
            'qty' => 'Qty',
            'movement_type' => 'Movement Type',
            'movement_date' => 'Movement Date',
            'lot_number' => 'Lot Number',
            'expiry_date' => 'Expiry Date',
            'category_id' => 'Category ID',
            'ref' => 'Ref',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }
}
