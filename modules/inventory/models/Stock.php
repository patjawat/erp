<?php

namespace app\modules\inventory\models;

use Yii;
use yii\helpers\Html;
use app\models\Categorise;
use app\modules\filemanager\components\FileManagerHelper;
use yii\helpers\ArrayHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\sm\models\Product;
use asyou99\cart\ItemTrait;
use asyou99\cart\ItemInterface;
use yii\db\Expression;
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
class Stock extends \yii\db\ActiveRecord implements ItemInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock';
    }


    use ItemTrait;

    public function getPrice()
    {
        return $this->price;
    }

    public function getQty()
    {
        return $this->qty;
    }

    public function getId()
    {
        return $this->id;
    }


    public $q;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['warehouse_id', 'qty', 'created_by'], 'integer'],
            [['data_json', 'created_at', 'updated_at','unit_price','q'], 'safe'],
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

public function getWarehouse()
{
    return $this->hasOne(Warehouse::class, ['id' => 'warehouse_id']);
}


public function getStockOut()
{
    return $this->hasOne(StockOut::class, ['warehouse_id' => 'warehouse_id']);
}



public function ShowImg()
{
    $model = Uploads::find()->where(['ref' => $this->product->ref])->one();
    if ($model) {
        return FileManagerHelper::getImg($model->id);
    } else {
        return Yii::getAlias('@web') . '/img/placeholder-img.jpg';
    }
}

public function Avatar(){
    return '<div class="d-flex">
    '.Html::img($this->ShowImg(),['class' => 'avatar object-fit-cover']).'
                            <div class="avatar-detail">
                                <h6 class="mb-1 fs-15" data-bs-toggle="tooltip" data-bs-placement="top">
                                    '.$this->product->title.'
                                </h6>
                                <p class="text-primary mb-0 fs-13">'. $this->ViewTypeName()['title'].'</p>
                            </div>
                        </div>';
}

//แสดงรูปแบบประเภท
public function ViewTypeName(){
    try {

        $model =  self::find()->where(['name' => $this->name])->one();
        
            return [
                'title' =>  isset($this->productType->title) ? $this->productType->title : 'ไม่ได้ระบุ',
                'code' => (isset($model->data_json['unit']) ? $model->data_json['unit'] : '-')
            ];

    } catch (\Throwable $th) {
          return [
                'title' =>  '',
                'code' => ''
            ];
    }
         
}

public function listAssets()
{
    return StockEvent::find()->where(['name' => 'order_item', 'asset_item' => $this->asset_item,'warehouse_id' => $this->warehouse_id])->all();
}

public function SumQty()
{
    return self::find()->where(['warehouse_id' => $this->warehouse_id,'asset_item' => $this->asset_item])->sum('qty');
}

public function SumPrice()
{
    $model =  self::find()
    ->where(['>', 'qty', 0])
    ->select(['total' => new Expression('SUM(unit_price * qty)')])
    ->scalar();
    return number_format($model,2);
}

public function getStockCard()
{
    $warehouse = Yii::$app->session->get('warehouse');
    $sql = "SELECT 
           t.*,
           w.warehouse_name,
            @running_total := IF(t.transaction_type = 'IN', @running_total + t.qty, @running_total - t.qty) AS total
        FROM 
            stock_events t
        JOIN 
            (SELECT @running_total := 0) r
        LEFT JOIN warehouses w ON w.id =  t.from_warehouse_id
            WHERE t.asset_item = :asset_item AND t.name = 'order_item' AND warehouse_id = :warehouse_id
        ORDER BY 
            t.created_at, t.id";
    return  Yii::$app->db->createCommand($sql)
    ->bindValue(':asset_item',$this->asset_item)
    ->bindValue(':warehouse_id', $warehouse['warehouse_id'])
    ->queryAll();

}

public function listWarehouseMe()
{
    try {

    $data = Yii::$app->user->identity;
    $department = $data->employee->positions[0]->data_json['department']; 
    $results = Warehouse::find()
    ->where(new \yii\db\Expression('FIND_IN_SET(:department, department) > 0', [':department' =>  $department]))
    ->all();
    return ArrayHelper::map($results,'id','warehouse_name');

} catch (\Throwable $th) {
    return [];
}
}

}
