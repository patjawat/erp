<?php

namespace app\modules\inventory\models;

use Yii;
use app\modules\sm\models\Product;
use asyou99\cart\ItemTrait;
use asyou99\cart\ItemInterface;
/**
 * This is the model class for table "store".
 *
 * @property int $id รหัสการเคลื่อนไหวสินค้า
 * @property string|null $name ชื่อการเก็บของข้อมูล เช่น stock_order, order_item
 * @property string|null $asset_item รหัสสินค้า
 * @property int|null $warehouse_id รหัสคลังสินค้าปลายทาง
 * @property int|null $stock_qty จำนวนสินค้าที่เคลื่อนย้าย
 * @property string|null $ref
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $data_json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Store extends \yii\db\ActiveRecord implements ItemInterface
{
    /**
     * {@inheritdoc}
     */
    public $q;
    public static function tableName()
    {
        return 'store';
    }


    use ItemTrait;

    public function getPrice()
    {
        return $this->price;
    }

    public function getQty()
    {
        return $this->stock_qty;
    }

    public function getId()
    {
        return $this->id;
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['warehouse_id', 'stock_qty', 'thai_year', 'created_by', 'updated_by'], 'integer'],
            [['data_json', 'created_at', 'updated_at','q'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['asset_item', 'ref'], 'string', 'max' => 255],
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
            'asset_item' => 'Asset Item',
            'warehouse_id' => 'Warehouse ID',
            'stock_qty' => 'stock_qty',
            'ref' => 'Ref',
            'thai_year' => 'Thai Year',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    // เชื่อมกับรายการ ทรัพสินและวัสดุ
public function getProduct()
{
    return $this->hasOne(Product::class, ['code' => 'asset_item'])->andOnCondition(['name' => 'asset_item']);
}
}
