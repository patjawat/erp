<?php

namespace app\modules\inventory\models;

use Yii;
use app\modules\sm\models\Product;

/**
 * This is the model class for table "stock".
 *
 * @property int $id
 * @property string|null $name ชื่อการเก็บของข้อมูล เช่น order, item
 * @property string|null $code รหัส
 * @property string|null $asset_item รหัสสินค้า
 * @property int|null $warehouse_id รหัสคลังสินค้า
 * @property int|null $qty จำนวนสินค้าที่เคลื่อนย้าย
 * @property string|null $data_json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
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

    public $q;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['warehouse_id', 'qty', 'created_by'], 'integer'],
            [['data_json', 'created_at', 'updated_at'], 'safe'],
            [['name', 'code'], 'string', 'max' => 50],
            [['asset_item'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'ชื่อการเก็บของข้อมูล เช่น order, item',
            'code' => 'รหัส',
            'asset_item' => 'รหัสสินค้า',
            'warehouse_id' => 'รหัสคลังสินค้า',
            'qty' => 'จำนวนสินค้าที่เคลื่อนย้าย',
            'data_json' => 'Data Json',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
        ];
    }

        // เชื่อมกับรายการ ทรัพสินและวัสดุ
public function getProduct()
{
    return $this->hasOne(Product::class, ['code' => 'asset_item'])->andOnCondition(['name' => 'asset_item']);
}

public function getStockOut()
{
    return $this->hasOne(StockOut::class, ['warehouse_id' => 'warehouse_id']);
}

public function listAssets()
{
    return StockEvent::find()->where(['name' => 'order_item', 'asset_item' => $this->asset_item,'warehouse_id' => $this->warehouse_id])->all();
}

public function SumQty()
{
    return self::find()->where(['warehouse_id' => $this->warehouse_id,'asset_item' => $this->asset_item])->sum('qty');
}

}
