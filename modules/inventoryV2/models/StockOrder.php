<?php

namespace app\modules\inventoryV2\models;

use Yii;

/**
 * This is the model class for table "stock_order".
 *
 * @property int $id
 * @property string $order_no เลขที่เอกสาร (RCV, ISS, TRN)
 * @property string $order_type ประเภทธุรกรรม
 * @property string $order_date วันที่ทำรายการ
 * @property int|null $warehouse_id คลังสินค้าต้นทาง/คลังหลัก
 * @property int|null $to_warehouse_id คลังสินค้าปลายทาง (กรณีโอน)
 * @property int|null $contact_id ID ผู้ขาย หรือ ผู้เบิก/แผนก
 * @property string|null $status สถานะเอกสาร
 * @property string|null $ref อ้างอิงเลขที่ใบ PO หรือ PR
 * @property string|null $data_json
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 *
 * @property StockDetail[] $stockDetails
 */
class StockOrder extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const ORDER_TYPE_IN = 'IN';
    const ORDER_TYPE_OUT = 'OUT';
    const ORDER_TYPE_TRANSFER = 'TRANSFER';

    /**
     * {@inheritdoc}
     */
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
            [['warehouse_id', 'to_warehouse_id', 'contact_id', 'ref', 'data_json', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'DRAFT'],
            [['order_no', 'order_type', 'order_date'], 'required'],
            [['order_type'], 'string'],
            [['order_date', 'data_json'], 'safe'],
            [['warehouse_id', 'to_warehouse_id', 'contact_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['order_no'], 'string', 'max' => 100],
            [['status'], 'string', 'max' => 50],
            [['ref'], 'string', 'max' => 255],
            ['order_type', 'in', 'range' => array_keys(self::optsOrderType())],
            [['order_no'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_no' => 'Order No',
            'order_type' => 'Order Type',
            'order_date' => 'Order Date',
            'warehouse_id' => 'Warehouse ID',
            'to_warehouse_id' => 'To Warehouse ID',
            'contact_id' => 'Contact ID',
            'status' => 'Status',
            'ref' => 'Ref',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[StockDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockDetails()
    {
        return $this->hasMany(StockDetail::class, ['stock_order_id' => 'id']);
    }


    /**
     * column order_type ENUM value labels
     * @return string[]
     */
    public static function optsOrderType()
    {
        return [
            self::ORDER_TYPE_IN => 'IN',
            self::ORDER_TYPE_OUT => 'OUT',
            self::ORDER_TYPE_TRANSFER => 'TRANSFER',
        ];
    }

    /**
     * @return string
     */
    public function displayOrderType()
    {
        return self::optsOrderType()[$this->order_type];
    }

    /**
     * @return bool
     */
    public function isOrderTypeIn()
    {
        return $this->order_type === self::ORDER_TYPE_IN;
    }

    public function setOrderTypeToIn()
    {
        $this->order_type = self::ORDER_TYPE_IN;
    }

    /**
     * @return bool
     */
    public function isOrderTypeOut()
    {
        return $this->order_type === self::ORDER_TYPE_OUT;
    }

    public function setOrderTypeToOut()
    {
        $this->order_type = self::ORDER_TYPE_OUT;
    }

    /**
     * @return bool
     */
    public function isOrderTypeTransfer()
    {
        return $this->order_type === self::ORDER_TYPE_TRANSFER;
    }

    public function setOrderTypeToTransfer()
    {
        $this->order_type = self::ORDER_TYPE_TRANSFER;
    }
}
