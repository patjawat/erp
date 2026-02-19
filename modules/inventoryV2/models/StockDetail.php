<?php

namespace app\modules\inventoryV2\models;

use Yii;

/**
 * This is the model class for table "stock_detail".
 *
 * @property int $id
 * @property int $stock_order_id เชื่อมกับ stock_order
 * @property int $item_code เชื่อมกับ stock_item
 * @property float $qty จำนวนที่ทำรายการ
 * @property float|null $remain_qty จำนวนคงเหลือสำหรับ FIFO
 * @property float|null $unit_price ราคาทุนต่อหน่วย
 * @property string|null $lot_number เลขล็อตสินค้า
 * @property string|null $expiry_date วันหมดอายุ
 * @property string|null $ref อ้างอิงอื่นๆ รายบรรทัด
 * @property string|null $data_json
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 *
 * @property StockOrder $stockOrder
 */
class StockDetail extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_detail';
    }

    /**
     * {@inheritdoc}
     */
public function rules()
    {
        return [
            [['unit_price', 'expiry_date', 'ref', 'data_json', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['remain_qty'], 'default', 'value' => 0.00],
            [['stock_order_id', 'item_code', 'qty', 'lot_number'], 'required'],
            
            // 1. ❌ เอา 'item_code' ออกจากกลุ่ม 'integer' เพราะมันเป็น string(50) 
            [['stock_order_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            
            // 2. ✅ เพิ่มกฎสำหรับ item_code ให้เป็น string ตาม Migration
            [['item_code'], 'string', 'max' => 50],
            
            [['qty', 'unit_price', 'remain_qty'], 'number'],
            [['expiry_date', 'data_json'], 'safe'],
            [['lot_number'], 'string', 'max' => 100],
            [['ref'], 'string', 'max' => 255],
            [['stock_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => StockOrder::class, 'targetAttribute' => ['stock_order_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'stock_order_id' => 'Stock Order ID',
            'item_code' => 'Item ID',
            'qty' => 'Qty',
            'unit_price' => 'Unit Price',
            'lot_number' => 'Lot Number',
            'expiry_date' => 'Expiry Date',
            'ref' => 'Ref',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[StockOrder]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockOrder()
    {
        return $this->hasOne(StockOrder::class, ['id' => 'stock_order_id']);
    }

public function getItem()
{
    // เปลี่ยน StockItem เป็นชื่อ Class ของตารางวัสดุของคุณ
    // และ item_code คือฟิลด์ที่ใช้เชื่อมในตาราง stock_detail
    return $this->hasOne(StockItem::class, ['item_code' => 'item_code']);
}

}
