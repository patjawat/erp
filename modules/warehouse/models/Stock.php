<?php

namespace app\modules\warehouse\models;

use Yii;

/**
 * This is the model class for table "stock".
 *
 * @property int $id รหัสสต็อก
 * @property int $product_id รหัสสินค้า
 * @property int $warehouse_id รหัสคลังสินค้า
 * @property int $qty จำนวนสินค้าในสต็อก
 * @property string|null $lot_number หมายเลข Lot
 * @property string|null $expiry_date วันหมดอายุ
 * @property string|null $ref
 * @property string|null $data_json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Stock extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'warehouse_id'], 'required'],
            [['product_id', 'warehouse_id', 'qty', 'created_by', 'updated_by'], 'integer'],
            [['expiry_date', 'data_json', 'created_at', 'updated_at', 'po_number'], 'safe'],
            [['lot_number'], 'string', 'max' => 50],
            [['ref'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'warehouse_id' => 'Warehouse ID',
            'qty' => 'qty',
            'lot_number' => 'Lot Number',
            'expiry_date' => 'Expiry Date',
            'ref' => 'Ref',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }
}
