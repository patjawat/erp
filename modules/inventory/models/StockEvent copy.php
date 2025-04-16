<?php

namespace app\modules\inventory\models;

use app\components\AppHelper;
use app\components\UserHelper;
use app\models\Categorise;
use app\modules\hr\models\Employees;
use app\modules\purchase\models\Order;
use app\modules\sm\models\Product;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "stock_in".
 *
 * @property int         $id                รหัสการเคลื่อนไหวสินค้า
 * @property string|null $name              ชื่อการเก็บของข้อมูล เช่น order, item
 * @property string|null $code              รหัส
 * @property string|null $asset_item        รหัสสินค้า
 * @property int|null    $warehouse_id      รหัสคลังสินค้า
 * @property string|null $vendor_id         ผู้จำหน่าย ผู้บริจาค
 * @property int|null    $from_warehouse_id รหัสคลังสินค้าต้นทาง
 * @property int|null    $qty               จำนวนสินค้าที่เคลื่อนย้าย
 * @property float|null  $total_price       รวมราคา
 * @property float|null  $unit_price        ราคาต่อหน่วย
 * @property string|null $receive_type      วิธีนำเข้า (normal = รับเข้าแบบปกติ, purchase = รับเข้าจาก PO)
 * @property string      $movement_date     วันที่และเวลาที่เกิดการเคลื่อนไหว
 * @property string|null $lot_number        หมายเลข Lot
 * @property string|null $category_id       หมวดหมูหลักที่เก็บ
 * @property string|null $order_status      สถานะของ order (หัวรายการ)
 * @property string|null $ref
 * @property int|null    $thai_year         ปีงบประมาณ
 * @property string|null $data_json
 * @property string|null $created_at        วันที่สร้าง
 * @property string|null $updated_at        วันที่แก้ไข
 * @property int|null    $created_by        ผู้สร้าง
 * @property int|null    $updated_by        ผู้แก้ไข
 */
class StockEvent extends Yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'stock_events';
    }

    public $auto_lot;
    public $category_code;
    public $warehouse_name;
    public $total;
    public $q;
    public $mfgDate;
    public $expDate;
    public $note;
    public $asset_type_name;
    public $date_start;
    public $date_end;

    public function rules()
    {
        return [
            [['vendor_id', ''], 'required'],
            [['warehouse_id', 'from_warehouse_id', 'qty', 'thai_year', 'created_by', 'updated_by'], 'integer'],
            [['total_price', 'unit_price'], 'number'],
            [['movement_date', 'data_json', 'created_at', 'updated_at', 'auto_lot', 'po_number', 'checker', 'category_code', 'warehouse_name', 'total', 'asset_type_name', 'q', 'date_start',
            'date_end'], 'safe'],
            [['name', 'code', 'lot_number'], 'string', 'max' => 50],
            [['asset_item', 'vendor_id', 'receive_type', 'category_id', 'order_status', 'ref'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'รหัสการเคลื่อนไหวสินค้า',
            'name' => 'ชื่อการเก็บของข้อมูล เช่น order, item',
            'vendor_id' => 'ผู้จำหน่ายหรือผู้บริจาค',
            'code' => 'รหัส',
            'asset_item' => 'รหัสสินค้า',
            'warehouse_id' => 'รหัสคลังสินค้า',
            'vendor_id' => 'ผู้จำหน่าย ผู้บริจาค',
            'from_warehouse_id' => 'รหัสคลังสินค้าต้นทาง',
            'qty' => 'จำนวนสินค้าที่เคลื่อนย้าย',
            'total_price' => 'รวมราคา',
            'unit_price' => 'ราคาต่อหน่วย',
            'receive_type' => 'วิธีนำเข้า (normal = รับเข้าแบบปกติ, purchase = รับเข้าจาก PO)',
            'movement_date' => 'วันที่และเวลาที่เกิดการเคลื่อนไหว',
            'lot_number' => 'หมายเลข Lot',
            'category_id' => 'หมวดหมูหลักที่เก็บ',
            'order_status' => 'สถานะของ order (หัวรายการ)',
            'ref' => 'Ref',
            'thai_year' => 'ปีงบประมาณ',
            'data_json' => 'Data Json',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => ['updated_at'],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function afterFind()
    {
        $this->mfgDate = (isset($this->data_json['mfg_date']) && $this->data_json['mfg_date'] !== '') ? AppHelper::convertToThai($this->data_json['mfg_date']) : '-';
        $this->expDate = (isset($this->data_json['exp_date']) && $this->data_json['exp_date'] !== '') ? AppHelper::convertToThai($this->data_json['exp_date']) : '-';
        $this->note = isset($this->data_json['note']) ? $this->data_json['note'] : '-';
        parent::afterFind();
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // แปลงวันที่จาก พ.ศ. เป็น ค.ศ. ก่อนบันทึกในฐานข้อมูล
            $this->thai_year = AppHelper::YearBudget();

            return true;
        } else {
            return false;
        }
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

    public function getFromWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'from_warehouse_id']);
    }

    // เชื่อมกับ Stock
    public function getStock()
    {
        return $this->hasOne(Stock::class, ['lot_number' => 'lot_number']);
    }

    // การสั่งซื้อ
    public function getPurchase()
    {
        return $this->hasOne(Order::class, ['id' => 'category_id']);
    }

    // นีับจำนวน stock คงเหลือในคลังที่เลือก
    public static function SumStockWarehouse()
    {
        $warehouse = \Yii::$app->session->get('warehouse');
        $model = Stock::find()->where(['warehouse_id' => $warehouse['warehouse_id']])->sum('qty');
        if ($model) {
            return $model;
        } else {
            return 0;
        }
    }

    public function getTotalOrderPrice()
    {
        $sql = "SELECT IFNULL(SUM(qty * unit_price),0) as total FROM `stock_events` WHERE name = 'order_item' AND `category_id` = :category_id;";
        $query = \Yii::$app->db
        ->createCommand($sql)
        ->bindValue(':category_id', $this->id)
        ->queryScalar();

        return $query;
    }

    public function getTotalOrderPriceSuccess()
    {
        $sql = "SELECT IFNULL(SUM(qty * unit_price),0) as total FROM `stock_events` WHERE name = 'order_item' AND `category_id` = :category_id;";
        $query = \Yii::$app->db
        ->createCommand($sql)
        ->bindValue(':category_id', $this->id)
        ->queryScalar();

        return $query;
    }

    // ราวมราคาทั้งหมดขแงแต่ละ ชิ้น
    public function getTotalPriceItem()
    {
        // try {

        // $sql = "SELECT SUM(unit_price * qty) AS total FROM `stock_events` WHERE name = 'order_item' AND transaction_type = 'IN' AND order_status = 'success' GROUP BY code;";
        // $sql = "SELECT IFNULL(SUM(qty * unit_price),0) as total FROM `stock_events` WHERE `code` LIKE 'RC-670016';";
        $sql = "SELECT IFNULL(SUM(qty * unit_price),0) as total FROM `stock_events` WHERE name = 'order_item' AND transaction_type = 'IN' AND `code` = :code;";
        $query = \Yii::$app->db
        ->createCommand($sql)
        ->bindValue(':code', $this->code)
        ->queryScalar();

        return $query;
        // } catch (\Throwable $th) {
        //     return 0;
        // }
    }

    // รวมราคารับเข้าของคลังนั้นๆ
    public static function getTotalPriceWarehouse()
    {
        // $warehouse = Yii::$app->session->get('warehouse');
        // try {

        // $sql = "SELECT IFNULL(SUM(qty * unit_price),0) as total FROM `stock_events` WHERE name = 'order_item' AND transaction_type = 'IN' AND `warehouse_id` = :warehouse_id;";
        $sql = "SELECT IFNULL(SUM(qty * unit_price),0) as total FROM `stock_events` WHERE name = 'order_item' AND transaction_type = 'IN'";
        $query = \Yii::$app->db
        ->createCommand($sql)
        // ->bindValue(':warehouse_id', $warehouse['warehouse_id'])
        ->queryScalar();

        return $query;
        // } catch (\Throwable $th) {
        //     return 0;
        // }
    }

    // ตรวจสอบค่าวาง qty
    public function countNullQty()
    {
        return self::find()
        ->where(['category_id' => $this->id, 'name' => 'order_item'])
        ->andWhere(['qty' => null])
        ->count();
    }

    //  ภาพทีมคณะกรรมการ
    public function StackComittee()
    {
        try {
            $data = '';
            $data .= '<div class="avatar-stack">';
            foreach (self::find()->where(['name' => 'receive_committee', 'category_id' => $this->id])->all() as $key => $item) {
                $emp = Employees::findOne(['id' => $item->data_json['employee_id']]);
                $data .= Html::a(Html::img($emp->ShowAvatar(), ['class' => 'avatar-sm rounded-circle shadow']), ['/inventory/receive/update-committee', 'id' => $item->id, 'title' => '<i class="bi bi-person-circle"></i> กรรมการตรวจรับเข้าคลัง'], ['class' => 'open-modal', 'data' => [
                    'size' => 'model-md',
                    'bs-toggle' => 'tooltip',
                    'bs-placement' => 'top',
                    'bs-title' => $emp->fullname.'('.$item->data_json['committee_position_name'].')',
                ]]);
            }
            $data .= '</div>';

            return $data;
        } catch (\Throwable $th) {
        }
    }

    // นับจำนวนรายการที่มีสถานะเป็น Pending อยู่
    public function isPending()
    {
        return self::find()->where(['name' => 'order_item', 'category_id' => $this->id, 'order_status' => 'pending'])->count();
    }

    // นับจำนวนที่หัวหน้าอนุมัติแล้ว
    public function getTotalCheckerY()
    {
        $warehouse = \Yii::$app->session->get('warehouse');

        return self::find()
            ->where(['name' => 'order', 'order_status' => 'pending'])
            ->andWhere(new Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.checker_confirm')) = 'Y'"))
            ->count();
    }

    // public function getTotalSuccessOrder()
    // {
    //     $warehouse = Yii::$app->session->get('warehouse');
    //     return self::find()
    //         ->where(['name' => 'order_item', 'order_status' => 'success'])
    //         ->count('qty');
    // }

    // นับจำนวนทีอยู่ใน stock
    public function SumStockQty()
    {
        return Stock::find()->where(['asset_item' => $this->asset_item, 'warehouse_id' => $this->warehouse_id])->sum('qty');
    }

    // นับจำนวนทีอยู่ใน lot_number stock
    public function SumLotQty()
    {
        try {
            return Stock::find()->where(['asset_item' => $this->asset_item, 'lot_number' => $this->lot_number, 'warehouse_id' => $this->warehouse_id])->sum('qty');
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function CountItem($category_id)
    {
        return self::find()->where(['category_id' => $category_id,'name' => 'order_item','asset_item' => $this->asset_item, 'warehouse_id' => $this->warehouse_id])->count('asset_item');
    }

    // นับจำนวนที่เบิก
    public function SumQty()
    {
        return self::find()->where(['category_id' => $this->id])->sum('qty');
    }

    // นับจำนวนที่ขอเบิก
    public function SumReqQty()
    {
        $sql = "SELECT SUM(data_json->>'$.req_qty') as total FROM `stock_events` WHERE name = 'order_item' AND category_id = :category_id";
        $query = \Yii::$app->db->createCommand($sql)
        ->bindValue(':category_id', $this->id)
        ->queryScalar();

        return $query;
    }

    // ตรวจสอบรายการว่ากรอบขอ้มูลตรบหรือไม่
    public function checkItem()
    {
        // return $this->id;
        // return self::find()->where(['name' => 'order_item','category_id' => $this->id])
        // ->andWhere(['=','qty',''])
        // ->andWhere(["IS NULL",new Expression("JSON_EXTRACT(data_json, '$.exp_date')"),""])
        // ->count('id');
        //     $sql = "SELECT count(id) as total FROM `stock_in`
        //     WHERE lot_number IS NULL
        //     AND category_id = :category_id OR qty IS NULL OR JSON_EXTRACT(data_json, '$.mfg_date') IS NULL OR JSON_EXTRACT(data_json, '$.exp_date') IS NULL";
        //    return  Yii::$app->db->createCommand($sql)
        //    ->bindValue(':category_id', $this->id)
        //    ->queryScalar();
    }

// ประเภทของวัสดุ
    public function ListAssetType()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type','category_id' => 4])->all(), 'code', 'title');
    }

    // คณะกรรมการ
    public function ListBoard()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'committee'])->all(), 'code', 'title');
    }

    // แสดงรายกาผู้ขาย/ผู้บริจาค
    public function listWareHouseMain()
    {
        return ArrayHelper::map(Warehouse::find()->where(['warehouse_type' => 'MAIN'])->all(), 'id', 'warehouse_name');
    }

    // แสดงรายกาผู้ขาย/ผู้บริจาค
    public function listWareHouse()
    {
        return ArrayHelper::map(Warehouse::find()->all(), 'id', 'warehouse_name');
    }

    // แสดงรายการย่อยของ stock
    public function getItems()
    {
        return self::find()->where(['name' => 'order_item', 'category_id' => $this->id])->all();
    }

    // แสดงรายกาผู้ขาย/ผู้บริจาค
    public function listVendor()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'vendor'])->all(), 'code', 'title');
    }

    // แสดงรายชื่อกรรมการตรวจรับ
    public function ListCommittee()
    {
        return self::find()
            ->where(['name' => 'receive_committee', 'category_id' => $this->id])
            ->all();
    }

    // แสดงรายละเอียดของแต่ละ lot
    public function LotNumberDetail()
    {
        return $this->lot_number.' ผลิต : '.$this->mfgDate.' หมดอายุ : '.$this->expDate;
    }

    // คณะกรรมการ
    public function ShowCommittee()
    {
        try {
            $employee = Employees::find()->where(['id' => $this->data_json['employee_id']])->one();

            return [
                'avatar' => $employee->getAvatar(false),
                'department' => $employee->departmentName(),
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'department' => '',
            ];
        }
    }

    // Avatar ของฉัน
    public function getMe($msg = null)
    {
        return UserHelper::getMe($msg);
    }

    public function viewStatus()
    {
        switch ($this->order_status) {
            case 'await':
                $msg = '<div class="badge badge-soft-success fs-13">Paid</div>';
                // $msg = '<i class="fa-regular fa-clock"></i> <span>อยู่ระหว่างดำเนินการ</span>';
                break;

            case 'pending':
                $msg = '<div class="badge rounded-pill badge-soft-warning text-warning fs-13"><i class="fa-solid fa-hourglass"></i> รอดำเนินการ</div>';
                // $msg = '<i class="fa-solid fa-hourglass"></i> <span>รอดำเนินการ</span>';
                break;

            case 'cancel':
                $msg = '<div class="badge rounded-pill badge-soft-danger text-danger fs-13"> <i class="fa-solid fa-xmark fs-6 text-danger"></i> ยกเลิก </div>';
                break;
            case 'success':
                $msg = '<i class="bi bi-check2-circle text-success"></i> <span>สำเร็จ</span>';
                break;

            default:
                $msg = '';
                break;
        }

        return $msg;
    }

    // แสดงผู้ตรวจสอบ
    public function viewChecker($msg = null)
    {
        try {
            $status = '';
            switch ($this->data_json['checker_confirm']) {
                case 'Y':
                    $status = '<span class="badge rounded-pill badge-soft-success text-success fs-13"><i class="bi bi-check2-circle"></i> อนุมัติ </span>';
                    break;

                case 'N':
                    $status = '<span class="badge rounded-pill badge-soft-danger text-danger fs-13"><i class="fa-solid fa-xmark fs-6 text-danger"></i> ไม่อนุมัติ </span>';
                    break;

                default:
                    $status = '<span class="badge rounded-pill badge-soft-warning text-warning fs-13"><i class="fa-regular fa-clock"></i> รออนุมัติ </span>';
                    break;
            }

            $checkerTime = isset($this->data_json['checker_confirm_date']) ? AppHelper::timeDifference($this->data_json['checker_confirm_date']) : null;

            return
                [
                    'status' => $status,
                    'avatar' => $this->getAvatar($this->checker, $status.' <i class="bi bi-clock"></i> <span class="text-muted fs-13">'.$checkerTime.'</span>')['avatar'],
                ];
        } catch (\Throwable $th) {
            return
                [
                    'status' => '',
                    'avatar' => '',
                ];
        }
    }

    public function getAvatar($empid, $msg = '')
    {
        try {
            $employee = Employees::find()->where(['id' => $empid])->one();

            return [
                'avatar' => $employee->getAvatar(false, $msg),
                'department' => $employee->departmentName(),
                'fullname' => $employee->fullname,
                'position_name' => $employee->positionName(),
                // 'product_type_name' => $this->data_json['product_type_name']
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'department' => '',
                'fullname' => '',
                'position_name' => '',
                'product_type_name' => '',
            ];
        }
    }

    // ผู้ขอ
    public function CreateBy($msg = null)
    {
        $emp = UserHelper::GetEmployee($this->created_by);

        return $this->getAvatar($emp->id, $msg);
    }

    public function ViewReceiveDate()
    {
        return $date = isset($this->data_json['receive_date']) ? \Yii::$app->thaiFormatter->asDate($this->data_json['receive_date'], 'long') : '-';
    }

    public function viewCreatedAt()
    {
        return \Yii::$app->thaiFormatter->asDateTime($this->created_at, 'php:d/m/Y H:i:s');
    }

    public function viewCreated()
    {
        return AppHelper::timeDifference($this->created_at);
    }

    public function viewUpdated()
    {
        return AppHelper::timeDifference($this->updated_at);
    }

    // รวมเงินทั้งหมด
    public function SummaryTotal($status = true)
    {

        $query =  self::find()
        ->select([new Expression("IFNULL(FORMAT(SUM(i.unit_price * i.qty),2), 0) AS total")])
        ->alias('e')
        ->innerJoin(['i' => 'stock_events'], 'i.category_id = e.id AND i.name = "order_item"')
        ->andFilterWhere(['e.thai_year' => $this->thai_year])
        ->andFilterWhere(['e.warehouse_id' => $this->warehouse_id]);
        if($status == true){
            $query->andFilterWhere(['e.order_status' => 'success']);
            $query->andFilterWhere(['i.order_status' => 'success']);
        }
        $query->andFilterWhere(['=', new Expression("JSON_EXTRACT(e.data_json, '$.asset_type_name')"), $this->asset_type_name])
       ->andFilterWhere([
            'or',
            ['like', 'e.code', $this->q],
            ['like', new Expression("JSON_EXTRACT(e.data_json, '$.vendor_name')"), $this->q],
            ['like', new Expression("JSON_EXTRACT(e.data_json, '$.pq_number')"), $this->q],
            ['like', new Expression("JSON_EXTRACT(e.data_json, '$.po_number')"), $this->q],
       ]);
       
        return $query->scalar();
      
    }

    
    public function ListOrderType()
    {
        $arr = [];
        try {
            $variable = self::find()->where(['name' => 'order'])->all();
            foreach ($variable as $model) {
                $arr[] = ['id' => $model->data_json['asset_type_name'], 'name' => $model->data_json['asset_type_name']];
            }

            return $arr;
            // code...
        } catch (\Throwable $th) {
            return $arr;
        }
    }

    // แสดงปีงบประมานทั้งหมดใน stock event
    public function ListGroupYear()
    {
        $model = self::find()
        ->select('thai_year')
        ->where(['name' => 'order'])
        ->groupBy('thai_year')
        ->all();

        return ArrayHelper::map($model, 'thai_year', 'thai_year');
    }

    //ผลสรุปราคาแบ่งประเภท
    public function Summary()
    {

        $StockIn = self::find()
        ->where(['name' => 'order_item'])
        ->andFilterWhere(['thai_year' => $this->thai_year,'order_status' => 'success'])
        ->andFilterWhere(['transaction_type' => 'IN'])->sum(new Expression('qty * unit_price'));

        $StockOut = self::find()
            ->alias('i')
            ->leftJoin(['o' => 'stock_events'], 'i.category_id = o.id AND i.name = :order', [':order' => 'order'])
            ->leftJoin(['w' => 'warehouses'], 'w.id = i.warehouse_id')
            ->where([
                'i.name' => 'order_item',
                'i.transaction_type' => 'OUT',
                'w.warehouse_type' => 'SUB',
            ])
            ->andFilterWhere(['i.thai_year' => $this->thai_year])
        ->sum(new Expression('i.qty * i.unit_price'));

        return [
            'in' => number_format($StockIn ?? 0,2),
            'out' => number_format($StockOut ?? 0,2),
        ];
    }

    // ข้อมูล  chart summary แบบรายเดือนและปี
    public function SummaryChart()
    {
        $where = ['and'];
        $where[] = ['thai_year' => $this->thai_year]; // ใช้กรองถ้าค่ามี

    $StockIn =  self::find()
                ->select([
                    'thai_year',
                    new Expression('SUM(CASE WHEN transaction_type = "IN" AND MONTH(created_at) = 10 THEN qty * unit_price ELSE 0 END) as in10'),
                    new Expression('SUM(CASE WHEN transaction_type = "OUT" AND MONTH(created_at) = 10 THEN qty * unit_price ELSE 0 END) as out10'),
                    new Expression('SUM(CASE WHEN transaction_type = "IN" AND MONTH(created_at) = 11 THEN qty * unit_price ELSE 0 END) as in11'),
                    new Expression('SUM(CASE WHEN transaction_type = "OUT" AND MONTH(created_at) = 11 THEN qty * unit_price ELSE 0 END) as out11'),
                    new Expression('SUM(CASE WHEN transaction_type = "IN" AND MONTH(created_at) = 12 THEN qty * unit_price ELSE 0 END) as in12'),
                    new Expression('SUM(CASE WHEN transaction_type = "OUT" AND MONTH(created_at) = 12 THEN qty * unit_price ELSE 0 END) as out12'),
                    new Expression('SUM(CASE WHEN transaction_type = "IN" AND MONTH(created_at) = 1 THEN qty * unit_price ELSE 0 END) as in1'),
                    new Expression('SUM(CASE WHEN transaction_type = "OUT" AND MONTH(created_at) = 1 THEN qty * unit_price ELSE 0 END) as out1'),
                    new Expression('SUM(CASE WHEN transaction_type = "IN" AND MONTH(created_at) = 2 THEN qty * unit_price ELSE 0 END) as in2'),
                    new Expression('SUM(CASE WHEN transaction_type = "OUT" AND MONTH(created_at) = 2 THEN qty * unit_price ELSE 0 END) as out2'),
                    new Expression('SUM(CASE WHEN transaction_type = "IN" AND MONTH(created_at) = 3 THEN qty * unit_price ELSE 0 END) as in3'),
                    new Expression('SUM(CASE WHEN transaction_type = "OUT" AND MONTH(created_at) = 3 THEN qty * unit_price ELSE 0 END) as out3'),
                    new Expression('SUM(CASE WHEN transaction_type = "IN" AND MONTH(created_at) = 4 THEN qty * unit_price ELSE 0 END) as in4'),
                    new Expression('SUM(CASE WHEN transaction_type = "OUT" AND MONTH(created_at) = 4 THEN qty * unit_price ELSE 0 END) as out4'),
                    new Expression('SUM(CASE WHEN transaction_type = "IN" AND MONTH(created_at) = 5 THEN qty * unit_price ELSE 0 END) as in5'),
                    new Expression('SUM(CASE WHEN transaction_type = "OUT" AND MONTH(created_at) = 5 THEN qty * unit_price ELSE 0 END) as out5'),
                    new Expression('SUM(CASE WHEN transaction_type = "IN" AND MONTH(created_at) = 6 THEN qty * unit_price ELSE 0 END) as in6'),
                    new Expression('SUM(CASE WHEN transaction_type = "OUT" AND MONTH(created_at) = 6 THEN qty * unit_price ELSE 0 END) as out6'),
                    new Expression('SUM(CASE WHEN transaction_type = "IN" AND MONTH(created_at) = 7 THEN qty * unit_price ELSE 0 END) as in7'),
                    new Expression('SUM(CASE WHEN transaction_type = "OUT" AND MONTH(created_at) = 7 THEN qty * unit_price ELSE 0 END) as out7'),
                    new Expression('SUM(CASE WHEN transaction_type = "IN" AND MONTH(created_at) = 8 THEN qty * unit_price ELSE 0 END) as in8'),
                    new Expression('SUM(CASE WHEN transaction_type = "OUT" AND MONTH(created_at) = 8 THEN qty * unit_price ELSE 0 END) as out8'),
                    new Expression('SUM(CASE WHEN transaction_type = "IN" AND MONTH(created_at) = 9 THEN qty * unit_price ELSE 0 END) as in9'),
                    new Expression('SUM(CASE WHEN transaction_type = "OUT" AND MONTH(created_at) = 9 THEN qty * unit_price ELSE 0 END) as out9'),
                ])
                ->where($where)
                ->andWhere(['order_status' => 'success'])
                ->groupBy('thai_year')
                ->asArray()
                ->all();

$StockOut = self::find()
    ->alias('i')
    ->select([
        'i.thai_year',
        new Expression('SUM(CASE WHEN i.transaction_type = "OUT" AND MONTH(i.created_at) = 10 THEN i.qty * i.unit_price ELSE 0 END) AS out10'),
        new Expression('SUM(CASE WHEN i.transaction_type = "OUT" AND MONTH(i.created_at) = 11 THEN i.qty * i.unit_price ELSE 0 END) AS out11'),
        new Expression('SUM(CASE WHEN i.transaction_type = "OUT" AND MONTH(i.created_at) = 12 THEN i.qty * i.unit_price ELSE 0 END) AS out12'),
        new Expression('SUM(CASE WHEN i.transaction_type = "OUT" AND MONTH(i.created_at) = 1 THEN i.qty * i.unit_price ELSE 0 END) AS out1'),
        new Expression('SUM(CASE WHEN i.transaction_type = "OUT" AND MONTH(i.created_at) = 2 THEN i.qty * i.unit_price ELSE 0 END) AS out2'),
        new Expression('SUM(CASE WHEN i.transaction_type = "OUT" AND MONTH(i.created_at) = 3 THEN i.qty * i.unit_price ELSE 0 END) AS out3'),
        new Expression('SUM(CASE WHEN i.transaction_type = "OUT" AND MONTH(i.created_at) = 4 THEN i.qty * i.unit_price ELSE 0 END) AS out4'),
        new Expression('SUM(CASE WHEN i.transaction_type = "OUT" AND MONTH(i.created_at) = 5 THEN i.qty * i.unit_price ELSE 0 END) AS out5'),
        new Expression('SUM(CASE WHEN i.transaction_type = "OUT" AND MONTH(i.created_at) = 6 THEN i.qty * i.unit_price ELSE 0 END) AS out6'),
        new Expression('SUM(CASE WHEN i.transaction_type = "OUT" AND MONTH(i.created_at) = 7 THEN i.qty * i.unit_price ELSE 0 END) AS out7'),
        new Expression('SUM(CASE WHEN i.transaction_type = "OUT" AND MONTH(i.created_at) = 8 THEN i.qty * i.unit_price ELSE 0 END) AS out8'),
        new Expression('SUM(CASE WHEN i.transaction_type = "OUT" AND MONTH(i.created_at) = 9 THEN i.qty * i.unit_price ELSE 0 END) AS out9')
    ])
    ->leftJoin(['o' => 'stock_events'], 'i.category_id = o.id AND i.name = :order', [':order' => 'order'])
    ->leftJoin(['w' => 'warehouses'], 'w.id = i.warehouse_id')
    ->where([
        'i.name' => 'order_item',
        'i.transaction_type' => 'OUT',
        'w.warehouse_type' => 'SUB'
    ])
    ->groupBy('i.thai_year')
    ->asArray()
    ->all();

    return [
        'in' => $StockIn,
        'out' => $StockOut
    ];
        
    }

    //ยอดรวมที่จ่ายออก
    public function SummaryOut()
    {
        $sql ="SELECT (i.qty * i.unit_price) as total_price FROM stock_events i
                LEFT JOIN stock_events o ON i.category_id = o.id AND i.name = 'order'
                LEFT JOIN warehouses w ON w.id = i.warehouse_id
                WHERE i.name = 'order_item' AND i.transaction_type = 'OUT' AND w.warehouse_type = 'SUB'";
    return Yii::$app->db
->createCommand($sql)->queryScalar();
    }
}
