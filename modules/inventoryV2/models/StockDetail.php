<?php

namespace app\modules\inventoryV2\models;

use Yii;

/**
 * This is the model class for table "stock_detail".
 *
 * @property int $id
 * @property int $stock_order_id เชื่อมกับ stock_order
 * @property int $item_id เชื่อมกับ stock_item
 * @property float $qty จำนวนที่ทำรายการ
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
            [['unit_price', 'lot_number', 'expiry_date', 'ref', 'data_json', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['stock_order_id', 'item_id', 'qty'], 'required'],
            [['stock_order_id', 'item_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['qty', 'unit_price'], 'number'],
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
            'item_id' => 'Item ID',
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

}
