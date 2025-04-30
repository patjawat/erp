<?php

namespace app\modules\inventory\models;
use Yii;
use yii\helpers\Html;
use yii\db\Expression;
use app\models\Categorise;
use asyou99\cart\ItemTrait;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use asyou99\cart\ItemInterface;
use app\modules\sm\models\Product;
use app\modules\filemanager\models\Uploads;
use app\modules\inventory\models\StockEvent;
use app\modules\filemanager\components\FileManagerHelper;

/**
 * This is the model class for table "stock".
 *
 * @property int         $id
 * @property string|null $name         ชื่อการเก็บของข้อมูล เช่น order, item
 * @property string|null $code         รหัส
 * @property string|null $asset_item   รหัสสินค้า
 * @property int|null    $warehouse_id รหัสคลังสินค้า
 * @property int|null    $qty          จำนวนสินค้าที่เคลื่อนย้าย
 * @property string|null $data_json
 * @property string|null $created_at   วันที่สร้าง
 * @property string|null $updated_at   วันที่แก้ไข
 * @property int|null    $created_by   ผู้สร้าง
 */
class Stock extends Yii\db\ActiveRecord implements ItemInterface
{
    use ItemTrait;

    public $total;
    public $asset_type;

    public static function tableName()
    {
        return 'stock';
    }

    public function getPrice()
    {
        return $this->unit_price;
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

    public function rules()
    {
        return [
            [['warehouse_id','created_by'], 'integer'],
            [['data_json', 'created_at', 'updated_at', 'unit_price', 'q', 'total','asset_type', 'qty'], 'safe'],
            [['name', 'code'], 'string', 'max' => 50],
            [['asset_item'], 'string', 'max' => 255],
        ];
    }

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

    public function getOrder()
    {
        return $this->hasOne(StockEvent::class, ['lot_number' => 'lot_number']);
    }

    public function ShowImg()
    {
        $model = Uploads::find()->where(['ref' => $this->product->ref])->one();
        if ($model) {
            return FileManagerHelper::getImg($model->id);
        } else {
            return \Yii::getAlias('@web').'/img/placeholder-img.jpg';
        }
    }

    public function Avatar()
    {
        return '<div class="d-flex">
    '.Html::img($this->ShowImg(), ['class' => 'avatar object-fit-cover']).'
                            <div class="avatar-detail">
                                <h6 class="mb-1 fs-15" data-bs-toggle="tooltip" data-bs-placement="top">
                                    '.$this->product->title.'
                                </h6>
                                <p class="text-primary mb-0 fs-13">'.$this->ViewTypeName()['title'].'</p>
                            </div>
                        </div>';
    }

    // แสดงรูปแบบประเภท
    public function ViewTypeName()
    {
        try {
            $model = self::find()->where(['name' => $this->name])->one();

            return [
                'title' => isset($this->productType->title) ? $this->productType->title : 'ไม่ได้ระบุ',
                'code' => (isset($model->data_json['unit']) ? $model->data_json['unit'] : '-'),
            ];
        } catch (\Throwable $th) {
            return [
                'title' => '',
                'code' => '',
            ];
        }
    }

    public function listAssets()
    {
        return StockEvent::find()->where(['name' => 'order_item', 'asset_item' => $this->asset_item, 'warehouse_id' => $this->warehouse_id])->all();
    }

    public function ListProductType()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type','category_id' => 4])->all(), 'code', 'title');
    }

//แสดง lot สินค้าทั้งหมด
public function listLotNumber()
{
  return  self::find()->where(['asset_item' => $this->asset_item,'warehouse_id' => $this->warehouse_id])
  ->andWhere(['>','qty',0])->all();
}
    public function SumQty()
    {
        // $warehouse = \Yii::$app->session->get('warehouse');
        // return self::find()->where(['warehouse_id' => $warehouse['warehouse_id'], 'asset_item' => $this->asset_item])->sum('qty');
        
        $totalQty = self::find()->where(['warehouse_id' => $this->warehouse_id, 'asset_item' => $this->asset_item])->sum('qty');
        return round($totalQty ?? 0, 2);
    }
       // นับจำนวนทีอยู่ใน lot_number stock
       public function SumLotQty()
       {
           try {
               return self::find()->where(['asset_item' => $this->asset_item, 'lot_number' => $this->lot_number, 'warehouse_id' => $this->warehouse_id])->sum('qty');
           } catch (\Throwable $th) {
               return 0;
           }
       }

    public function SumPriceByItem()
    {
        $warehouse = \Yii::$app->session->get('warehouse');
        $model = self::find()
        ->where(['>', 'qty', 0])
        ->andWhere(['warehouse_id' => $this->warehouse_id, 'asset_item' => $this->asset_item])
        ->select(['total' => new Expression('SUM(unit_price * qty)')])
        ->scalar();
        if ($model) {
            return number_format($model, 2);
        } else {
            return 0;
        }
    }

    //รวมราคา
    public function SumPrice()
    {
        $model = self::find()
        ->leftJoin('categorise p', 'p.code=stock.asset_item')
        ->andWhere(['warehouse_id' => $this->warehouse_id])
        ->andFilterWhere(['p.category_id' => $this->asset_type])
        ->andFilterWhere([
            'or',
            ['like', 'asset_item', $this->q],
            ['like', 'title', $this->q],
        ])
        ->select(['total' => new Expression('SUM(stock.unit_price * stock.qty)')])
        ->scalar();

        if ($model) {
            return number_format($model, 2);
        } else {
            return 0;
        }
    }

    public function getStockCard()
    {
        $sql = "SELECT x.*,(x.unit_price * qty) as total_price FROM(SELECT 
          t.*,o.category_id as category_code,
           w.warehouse_name,
            @running_total := IF(t.transaction_type = 'IN', @running_total + t.qty, @running_total - t.qty) AS total
        FROM 
            stock_events t
        JOIN 
            (SELECT @running_total := 0) r
        LEFT JOIN warehouses w ON w.id =  t.from_warehouse_id
        LEFT JOIN stock_events o ON o.id = t.category_id AND o.name = 'order'
            WHERE t.asset_item = :asset_item AND t.name = 'order_item' AND t.warehouse_id = :warehouse_id
        ORDER BY 
            t.created_at, t.id) as x";

        return \Yii::$app->db->createCommand($sql)
        ->bindValue(':asset_item', $this->asset_item)
        ->bindValue(':warehouse_id', $this->warehouse_id)
        ->queryAll();
    }



    public function listWarehouseMe()
    {
        try {
            $data = \Yii::$app->user->identity;
            $department = $data->employee->positions[0]->data_json['department'];
            $results = Warehouse::find()
            ->where(new Expression('FIND_IN_SET(:department, department) > 0', [':department' => $department]))
            ->all();

            return ArrayHelper::map($results, 'id', 'warehouse_name');
        } catch (\Throwable $th) {
            return [];
        }
    }

    public function getLotQty()
    {
        $sql = "SELECT s.id,o.data_json->>'$.receive_date' as receive_date,i.lot_number,IFNULL(s.qty,0) as qty FROM `stock_events` i
                LEFT JOIN stock_events o ON i.code = o.code AND o.name ='order'
                LEFT JOIN stock s ON s.lot_number = i.lot_number
                -- WHERE i.asset_item = :asset_item AND IFNULL(s.qty,0) > 0
                WHERE i.asset_item = :asset_item
                AND s.warehouse_id = :warehouse_id
                ORDER BY JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.receive_date')) ASC limit 1;";
                
                $query = Yii::$app->db->createCommand($sql,[
                    ':asset_item' => $this->asset_item,
                    ':warehouse_id' => $this->warehouse_id
                ])->queryOne();
                return $query;
    }

    //เบิกวัสดุคลังย่อยgเลือก lot ที่ล่าสุด
    public function getLotQtyOut()
    {
        $sql = "SELECT id,lot_number,qty  FROM stock WHERE asset_item = :asset_item AND IFNULL(qty,0) > 0 AND warehouse_id = :warehouse_id LIMIT 1;";
                
                $query = Yii::$app->db->createCommand($sql,[
                    ':asset_item' => $this->asset_item,
                    ':warehouse_id' => $this->warehouse_id
                ])->queryOne();
                return $query;
    }

        // แสดงปีงบประมานทั้งหมดใน stock event
        public function ListGroupYear()
        {
            $model = self::find()
                ->select('thai_year')
                ->where(['name' => 'order'])
                ->groupBy('thai_year')
                ->asArray()
                ->all();
    
            $year = AppHelper::YearBudget();
            $isYear = [['thai_year' => $year]];  // ห่อด้วย array เพื่อให้รูปแบบตรงกัน
            // รวมข้อมูล
            $model = ArrayHelper::merge($model, $isYear);
            return ArrayHelper::map($model, 'thai_year', 'thai_year');
        }
    // ยอดยกมา
    public function LastTotalStock()
    {
 
        $year = $this->thai_year- 1;
        $total = self::find()
            ->select([new Expression('ROUND(COALESCE(SUM(qty * unit_price), 0), 2)')])
            ->where(['thai_year' => $year])
            ->andFilterWhere(['warehouse_id' => $this->warehouse_id])
            ->scalar();
        return $total;
    }

    // // จำนวนรับเข้าของคลังหลักปีงบประมานนี้
    // public function ReceiveMainSummary()
    // {
    //     $year = $this->thai_year;
    //     $total = StockEvent::find()
    //     ->alias('se')
    //     ->select([
    //         new Expression('ROUND(COALESCE(SUM(se.qty * se.unit_price), 0), 2) as total')
    //     ])
    //     ->joinWith('warehouse w')
    //     ->where([
    //         'se.thai_year' => $year,
    //         'se.transaction_type' => 'IN',
    //         'w.warehouse_type' => 'MAIN'
    //     ])
    //     ->andFilterWhere(['se.warehouse_id' => $this->warehouse_id])
    //     ->scalar();
    //     return $total;
    // }


    //     // จำนวนรับเข้าของคลังย่อยปีงบประมานนี้
    //     public function ReceiveSubSummary()
    //     {
    //         $year = $this->thai_year;
    //         $total = StockEvent::find()
    //         ->alias('se')
    //         ->select([
    //             new Expression('ROUND(COALESCE(SUM(se.qty * se.unit_price), 0), 2) as total')
    //         ])
    //         ->joinWith('warehouse w')
    //         ->where([
    //             'se.thai_year' => $year,
    //             'se.transaction_type' => 'IN',
    //             'w.warehouse_type' => 'SUB'
    //         ])
    //         ->andFilterWhere(['se.warehouse_id' => $this->warehouse_id])
    //         ->scalar();
    //         return $total;
    //     }

    //     // จำนวนที่ใช้ไป
    //     public function OutSummary()
    //     {

    //         $query = StockEvent::find()
    //             ->alias('se')
    //             ->joinWith('warehouse w')
    //             ->where([
    //                 'se.thai_year' => $this->thai_year,
    //                 'se.transaction_type' => 'OUT',
    //                 'w.warehouse_type' => 'SUB'
    //             ]);

    //         if ($this->warehouse_id) {
    //             $query->andWhere(['se.warehouse_id' => $this->warehouse_id]);
    //         }

    //         $total = $query->select(['total' => new Expression('ROUND(COALESCE(SUM(se.qty * se.unit_price), 0), 2)')])->scalar();

    //         return $total;

    //     //     $where = ['and'];
    //     //     $where[] = ['se.warehouse_id' => $this->warehouse_id];  // ใช้กรองถ้าค่ามี
    
    //     //     $sql = "SELECT ROUND(COALESCE(SUM(se.qty*se.unit_price),0),2) as total
    //     //             FROM stock_events AS se
    //     //             JOIN warehouses AS w ON se.warehouse_id = w.id
    //     //             WHERE se.thai_year = :thai_year
    //     //             AND se.transaction_type = 'OUT' 
    //     //             AND w.warehouse_type = 'SUB'";
    //     //    return Yii::$app->db->createCommand($sql)
    //     //    ->bindValue(':thai_year', $this->thai_year)->queryScalar();
    //     }

    // public function TotalPrice()
    // {
    //     return ($this->LastTotalStock()+$this->ReceiveMainSummary()) - $this->OutSummary() ;
    // }

    


}
