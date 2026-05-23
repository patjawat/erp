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
    public $order_id;

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
            [['data_json', 'created_at', 'updated_at', 'unit_price', 'q', 'total','asset_type', 'qty','order_id'], 'safe'],
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

    //ผลรวมจำนวนคงเหลือแยกตาม รายการสิค้า
    public function sumStockItem()
    {
        return self::find()->where(['warehouse_id' => $this->warehouse_id,'asset_item' => $this->asset_item])->sum('qty');

    }


    // แสดง วันรับเข้าจาก lotnumber
public function getLotDate()
{
    $sql = "SELECT s.lot_number, o.movement_date, s.qty 
            FROM stock s 
            LEFT JOIN stock_events i ON i.lot_number = s.lot_number
            LEFT JOIN stock_events o ON o.id = i.category_id
            WHERE s.warehouse_id = :warehouse_id
              AND o.name = 'order'
              AND i.name = 'order_item'
              AND s.qty > 0 
              AND s.lot_number = :lot_number
            GROUP BY s.lot_number;";

    $query = Yii::$app->db->createCommand($sql)
        ->bindValue(':lot_number', $this->lot_number)
        ->bindValue(':warehouse_id', $this->warehouse_id)
        ->queryOne();

    return $query ?: [];
}

    /**
     * ดึงข้อมูลสำหรับสต๊อกการ์ด (asset_item × warehouse) ในช่วงเวลา [dateFrom, dateTo]
     *
     * @param string      $itemCode     รหัสสินค้า (asset_item / categorise.code)
     * @param int         $warehouseId  รหัสคลังหลัก
     * @param string      $dateFrom     'YYYY-MM-DD' (วันที่เริ่มต้นช่วง)
     * @param string      $dateTo       'YYYY-MM-DD' (วันที่สุดท้ายของช่วง)
     * @return array {
     *     opening    : ['qty'=>float,'value'=>float,'source'=>'monthly_close'|'bootstrap'|'none'],
     *     movements  : [ ['movement_date','code','note','lot_number','exp_date','transaction_type','wh_kind','qty','unit_price','value'], ... ],
     *     adjustments: [ ['report_year','report_month','delta_qty','delta_value','note','adjusted_at'], ... ],
     *     closing    : ['qty'=>float,'value'=>float],
     *     item_info  : ['code','title','unit'],
     * }
     */
    public static function getStockCardData($itemCode, $warehouseId, $dateFrom, $dateTo)
    {
        $itemCode    = (string) $itemCode;
        $warehouseId = (int) $warehouseId;
        $dateFromTs  = $dateFrom . ' 00:00:00';
        $dateToTs    = $dateTo . ' 23:59:59';

        // ----- 1. opening: หาว่า dateFrom ตรงกับวันที่ 1 ของเดือนไหม -----
        $isMonthStart = (date('d', strtotime($dateFrom)) === '01');
        $opening = ['qty' => 0.0, 'value' => 0.0, 'source' => 'none'];

        if ($isMonthStart) {
            $year  = (int) date('Y', strtotime($dateFrom));
            $month = (int) date('n', strtotime($dateFrom));
            $prevMonth = $month - 1;
            $prevYear  = $year;
            if ($prevMonth < 1) { $prevMonth += 12; $prevYear--; }

            $prev = (new \yii\db\Query())
                ->select(['closing_qty', 'closing_value'])
                ->from('stock_monthly_report')
                ->where([
                    'report_year'  => $prevYear,
                    'report_month' => $prevMonth,
                    'warehouse_id' => $warehouseId,
                    'item_code'    => $itemCode,
                ])
                ->one();
            if ($prev) {
                $opening = [
                    'qty'    => (float) $prev['closing_qty'],
                    'value'  => (float) $prev['closing_value'],
                    'source' => 'monthly_close',
                ];
            }
        }

        // ถ้าไม่มี opening จาก monthly close → bootstrap คำนวณจาก stock_events ก่อน dateFrom
        if ($opening['source'] === 'none') {
            $bootstrapSql = "
                SELECT
                    SUM(CASE
                        WHEN i.transaction_type = 'IN'
                             AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
                        THEN CAST(i.qty AS DECIMAL(20,5))
                        WHEN i.transaction_type = 'OUT'
                             AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB','BRANCH')
                        THEN -CAST(i.qty AS DECIMAL(20,5))
                        ELSE 0
                    END) AS qty,
                    SUM(CASE
                        WHEN i.transaction_type = 'IN'
                             AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
                        THEN CAST(i.qty AS DECIMAL(20,5)) * CAST(i.unit_price AS DECIMAL(20,5))
                        WHEN i.transaction_type = 'OUT'
                             AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB','BRANCH')
                        THEN -CAST(i.qty AS DECIMAL(20,5)) * CAST(i.unit_price AS DECIMAL(20,5))
                        ELSE 0
                    END) AS value
                FROM stock_events i
                LEFT JOIN stock_events e ON e.id = i.category_id AND e.name = 'order'
                LEFT JOIN warehouses wo ON wo.id = e.from_warehouse_id
                LEFT JOIN warehouses wi ON wi.id = e.warehouse_id
                WHERE i.name = 'order_item'
                  AND i.order_status = 'success'
                  AND i.asset_item = :item_code
                  AND e.warehouse_id = :warehouse_id
                  AND i.movement_date < :date_from
            ";
            $b = Yii::$app->db->createCommand($bootstrapSql, [
                ':item_code'    => $itemCode,
                ':warehouse_id' => $warehouseId,
                ':date_from'    => $dateFromTs,
            ])->queryOne();
            $opening = [
                'qty'    => (float) ($b['qty'] ?? 0),
                'value'  => (float) ($b['value'] ?? 0),
                'source' => 'bootstrap',
            ];
        }

        // ----- 2. movements ในช่วง dateFrom..dateTo -----
        $movementSql = "
            SELECT
                i.id,
                e.movement_date,
                e.code,
                e.po_number,
                e.data_json->>'$.note' AS note,
                i.lot_number,
                i.data_json->>'$.exp_date' AS exp_date,
                i.qty,
                i.unit_price,
                i.transaction_type,
                COALESCE(wo.warehouse_type, wi.warehouse_type) AS wh_type,
                wo.warehouse_name AS from_warehouse,
                wi.warehouse_name AS to_warehouse
            FROM stock_events i
            LEFT JOIN stock_events e ON e.id = i.category_id AND e.name = 'order'
            LEFT JOIN warehouses wo ON wo.id = e.from_warehouse_id
            LEFT JOIN warehouses wi ON wi.id = e.warehouse_id
            WHERE i.name = 'order_item'
              AND i.order_status = 'success'
              AND i.asset_item = :item_code
              AND e.warehouse_id = :warehouse_id
              AND i.movement_date BETWEEN :date_from AND :date_to
            ORDER BY i.movement_date ASC, i.id ASC
        ";
        $movRows = Yii::$app->db->createCommand($movementSql, [
            ':item_code'    => $itemCode,
            ':warehouse_id' => $warehouseId,
            ':date_from'    => $dateFromTs,
            ':date_to'      => $dateToTs,
        ])->queryAll();

        $movements = [];
        foreach ($movRows as $r) {
            $qty       = (float) $r['qty'];
            $unitPrice = (float) $r['unit_price'];
            $value     = $qty * $unitPrice;

            // จัดประเภทแถวเพื่อแสดงในสต๊อกการ์ด
            $kind = 'OTHER';
            if ($r['transaction_type'] === 'IN' && $r['wh_type'] === 'MAIN') {
                $kind = 'IN';
            } elseif ($r['transaction_type'] === 'OUT' && $r['wh_type'] === 'SUB') {
                $kind = 'OUT_HOSP';   // จ่ายส่วนของโรงพยาบาล
            } elseif ($r['transaction_type'] === 'OUT' && $r['wh_type'] === 'BRANCH') {
                $kind = 'OUT_BRANCH'; // จ่าย รพ.สต.
            } elseif ($r['transaction_type'] === 'OUT') {
                $kind = 'OUT';
            }

            $movements[] = [
                'id'              => $r['id'],
                'movement_date'   => $r['movement_date'],
                'code'            => $r['code'],
                'po_number'       => $r['po_number'],
                'note'            => $r['note'],
                'lot_number'      => $r['lot_number'],
                'exp_date'        => $r['exp_date'],
                'qty'             => $qty,
                'unit_price'      => $unitPrice,
                'value'           => $value,
                'transaction_type'=> $r['transaction_type'],
                'wh_type'         => $r['wh_type'],
                'from_warehouse'  => $r['from_warehouse'],
                'to_warehouse'    => $r['to_warehouse'],
                'kind'            => $kind,
            ];
        }

        // ----- 3. adjustments: ดึงแถวที่ถูกปรับยอดใน stock_monthly_report ของเดือนที่อยู่ในช่วง -----
        $startYM = date('Y-m', strtotime($dateFrom));
        $endYM   = date('Y-m', strtotime($dateTo));
        $adjRows = (new \yii\db\Query())
            ->select(['report_year', 'report_month',
                'closing_qty', 'closing_value',
                'original_closing_qty', 'original_closing_value',
                'adjustment_note', 'adjusted_at'])
            ->from('stock_monthly_report')
            ->where([
                'warehouse_id' => $warehouseId,
                'item_code'    => $itemCode,
            ])
            ->andWhere(['IS NOT', 'adjusted_at', null])
            ->andWhere(['>=', "CONCAT(report_year,'-',LPAD(report_month,2,'0'))", $startYM])
            ->andWhere(['<=', "CONCAT(report_year,'-',LPAD(report_month,2,'0'))", $endYM])
            ->orderBy(['report_year' => SORT_ASC, 'report_month' => SORT_ASC])
            ->all();
        $adjustments = [];
        foreach ($adjRows as $r) {
            $deltaQty   = (float) $r['closing_qty']   - (float) ($r['original_closing_qty']   ?? 0);
            $deltaValue = (float) $r['closing_value'] - (float) ($r['original_closing_value'] ?? 0);
            if ($deltaQty == 0 && $deltaValue == 0) continue;
            $adjustments[] = [
                'report_year'   => (int) $r['report_year'],
                'report_month'  => (int) $r['report_month'],
                'delta_qty'     => $deltaQty,
                'delta_value'   => $deltaValue,
                'note'          => $r['adjustment_note'],
                'adjusted_at'   => $r['adjusted_at'],
                // วันที่แสดงในการ์ด: สิ้นเดือนของ report_month
                'shown_date'    => date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $r['report_year'], $r['report_month']))),
            ];
        }

        // ----- 4. closing: opening + sum(movements) + sum(adjustments) -----
        $sumQty = $opening['qty']; $sumVal = $opening['value'];
        foreach ($movements as $m) {
            if ($m['kind'] === 'IN') {
                $sumQty += $m['qty']; $sumVal += $m['value'];
            } elseif (in_array($m['kind'], ['OUT', 'OUT_HOSP', 'OUT_BRANCH'])) {
                $sumQty -= $m['qty']; $sumVal -= $m['value'];
            }
        }
        foreach ($adjustments as $a) {
            $sumQty += $a['delta_qty']; $sumVal += $a['delta_value'];
        }

        // ----- 5. item info -----
        $itemInfo = (new \yii\db\Query())
            ->select([
                'code'  => 'a.code',
                'title' => 'a.title',
                'unit'  => new Expression("a.data_json->>'$.unit'"),
            ])
            ->from(['a' => 'categorise'])
            ->where(['a.code' => $itemCode, 'a.name' => 'asset_item'])
            ->one();

        return [
            'opening'     => $opening,
            'movements'   => $movements,
            'adjustments' => $adjustments,
            'closing'     => ['qty' => $sumQty, 'value' => $sumVal],
            'item_info'   => $itemInfo ?: ['code' => $itemCode, 'title' => $itemCode, 'unit' => ''],
        ];
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
