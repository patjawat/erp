<?php

namespace app\modules\inventory\models;

use Yii;
use yii\helpers\Html;
use yii\db\Expression;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
// use app\modules\sm\models\Product;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\purchase\models\Order;
use app\modules\approve\models\Approve;
use app\modules\inventory\models\Product;

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
    public $q_month;
    public $receive_month;

    public function rules()
    {
        return [
            // [['vendor_id'], 'required'],
            [['warehouse_id', 'from_warehouse_id', 'thai_year', 'created_by', 'updated_by'], 'integer'],
            [['total_price', 'unit_price'], 'number'],
            [['movement_date', 'data_json', 'created_at', 'updated_at', 'auto_lot', 'po_number', 'checker', 'category_code', 'warehouse_name', 'total', 'asset_type_name', 'q', 'date_start','q_month','receive_month',
                'date_end','transaction_type', 'category_id', 'qty'], 'safe'],
            [['name', 'code', 'lot_number'], 'string', 'max' => 50],
            [['asset_item', 'vendor_id', 'receive_type', 'order_status', 'ref'], 'string', 'max' => 255],
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
            // $this->thai_year = AppHelper::YearBudget();

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

    // เชื่อกับ Order
    public function getOrder()
    {
        return $this->hasOne(StockEvent::class, ['id' => 'category_id']);
    }

    // การสั่งซื้อ
    public function getPurchase()
    {
        return $this->hasOne(Order::class, ['id' => 'category_id']);
    }

        // ผู้ตรวจสอบ
    public function getEmpChecker()
        {
            return $this->hasOne(Employees::class, ['id' => 'checker']);
        }




        /**
     * คำนวณราคาต่อหน่วย (price_per_unit)
     */
    public function getPricePerUnit()
    {
        if ($this->total_volume > 0) {
            return round($this->total_price / $this->total_volume, 6);
        }
        return 0;
    }

    /**
     * คำนวณราคารวมจากราคาต่อหน่วย (total_price)
     */
    public function getTotalPrice()
    {
        return round($this->getPricePerUnit() * $this->total_volume, 2);
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
        $query = \Yii::$app
            ->db
            ->createCommand($sql)
            ->bindValue(':category_id', $this->id)
            ->queryScalar();

        return $query;
    }

    public function getTotalOrderPriceSuccess()
    {
        $sql = "SELECT IFNULL(SUM(qty * unit_price),0) as total FROM `stock_events` WHERE name = 'order_item' AND order_status = 'success' AND `category_id` = :category_id;";
        $query = \Yii::$app
            ->db
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
        $query = \Yii::$app
            ->db
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
        $query = \Yii::$app
            ->db
            ->createCommand($sql)
            // ->bindValue(':warehouse_id', $warehouse['warehouse_id'])
            ->queryScalar();

        return $query;
        // } catch (\Throwable $th) {
        //     return 0;
        // }
    }

    public function fromWarehouseName()
    {
        return Warehouse::findOne($this->from_warehouse_id)->warehouse_name ?? '-';
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
                    'bs-title' => $emp->fullname . '(' . $item->data_json['committee_position_name'] . ')',
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
            ->andWhere(new Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '\$.checker_confirm')) = 'Y'"))
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
        return self::find()->where(['category_id' => $category_id, 'name' => 'order_item', 'asset_item' => $this->asset_item, 'warehouse_id' => $this->warehouse_id])->count('asset_item');
    }

    // นับจำนวนที่เบิก
    public function SumQty()
    {
        return self::find()->where(['category_id' => $this->id])->sum('qty');
    }

    // นับจำนวนที่ขอเบิก
    public function SumReqQty()
    {
        $sql = "SELECT SUM(data_json->>'\$.req_qty') as total FROM `stock_events` WHERE name = 'order_item' AND category_id = :category_id";
        $query = \Yii::$app
            ->db
            ->createCommand($sql)
            ->bindValue(':category_id', $this->id)
            ->queryScalar();

        return $query;
    }

    // แสดงรายการสถานะคำขอ
    public function listStatus()
    {
        $data = [
            'pending' => 'รอดำเนินการ',
            'success' => 'เสร็จสิ้น',
            'cancel' => 'ยกเลิก',
        ];

        return $data;
    }

    // ตรวจสอบการอนุมติให้เบิก
    public function OrderApprove()
    {
        try {
            if ($this->data_json['checker_confirm'] == 'Y') {
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            return false;
        }
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
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type', 'category_id' => 4])->all(), 'code', 'title');
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
    public function ListItems()
    {
        return self::find()
            ->where(['name' => 'order_item', 'category_id' => $this->id])
            ->all();
    }
    

        // รายชื่อคลังสินค้าย่อยตามผู้รับผิดชอบมีสิทธิ์
        public function listWareHouseSub(): array
        {
            $id = \Yii::$app->user->id;
            return ArrayHelper::map(
                Warehouse::find()->where(new Expression("JSON_CONTAINS(data_json->'$.officer','\"$id\"')"))
                    ->all(),
                'id',
                'warehouse_name'
            );
        }
    public function listFormWarehouse(): array
    {
        // $fromWarehouseList = self::find()->select('from_warehouse_id')->where(['name' => 'order'])->column();
        return ArrayHelper::map(Warehouse::find()->where(['warehouse_type' => 'SUB' ])->all(), 'id', 'warehouse_name');
    }

    // แสดงรายละเอียดของแต่ละ lot
    public function LotNumberDetail()
    {
        return $this->lot_number . ' ผลิต : ' . $this->mfgDate . ' หมดอายุ : ' . $this->expDate;
    }

    // คณะกรรมการ
    public function ShowCommittee()
    {
        try {
            $employee = Employees::find()->where(['id' => $this->data_json['employee_id']])->one();

            return [
                'avatar' => $employee->getAvatar(false),
                'img' => $employee->showAvatar(false),
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


//สรุปราคาและจำนวนคลังหลัก
public function mainOrderSummary($status = null)
{
    try {
        $dateStart = AppHelper::convertToGregorian($this->date_start);
        $dateEnd = AppHelper::convertToGregorian($this->date_end);
    } catch (\Throwable $th) {
        $dateStart = null;
        $dateEnd = null;
    }
    
    // Query นับจำนวนออเดอร์
    $queryTotalOrder = self::find()
        ->where([
            'name' => 'order',
            'warehouse_id' => $this->warehouse_id
        ])
        ->andFilterWhere(['from_warehouse_id' => $this->from_warehouse_id])
        ->andFilterWhere(['transaction_type' => $this->transaction_type])
        ->andFilterWhere(['order_status' => $status])
        ->andFilterWhere(['between', 'created_at', $dateStart, $dateEnd]);

    $totalOrder = $queryTotalOrder->count();
    $totalOrderSql = $queryTotalOrder->createCommand()->getRawSql(); // ดึง SQL ออกมา

    // Query คำนวณยอดรวม
    $queryTotalPrice = self::find()
        ->select(['total' => new \yii\db\Expression('SUM(stock_events.qty * stock_events.unit_price)')])
        ->leftJoin('categorise i', 'i.code = stock_events.asset_item')
        ->where(['warehouse_id' => $this->warehouse_id])
        ->andFilterWhere(['stock_events.from_warehouse_id' => $this->from_warehouse_id])
        ->andFilterWhere(['stock_events.transaction_type' => $this->transaction_type])
        ->andFilterWhere(['stock_events.order_status' => $status])
        ->andFilterWhere(['between', 'stock_events.created_at', $dateStart, $dateEnd])
        ->groupBy('stock_events.transaction_type');

    $totalPrice = $queryTotalPrice->scalar();
    $totalPriceSql = $queryTotalPrice->createCommand()->getRawSql(); // ดึง SQL ออกมา

    return [
        'totalPrice' => $totalPrice,
        'totalOrder' => $totalOrder,
        'totalPriceSql' => $totalPriceSql,
        'totalOrderSql' => $totalOrderSql
    ];
}

    
    public function viewStatus()
    {
        switch ($this->order_status) {
            case 'await':
                $msg = '<div class="badge badge-soft-success fs-13">รอดำเนินการ</div>';
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
                    $msg = '<div class="badge rounded-pill badge-soft-success text-success fs-13"> <i class="bi bi-check2-circle text-success"></i> สำเร็จ </div>';
                    // $msg = '<i class="bi bi-check2-circle text-success"></i> <span>สำเร็จ</span>';
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
                    $status = '<span class="badge rounded-pill badge-soft-success text-success fs-13"><i class="bi bi-check2-circle"></i> เห็นชอบ </span>';
                    break;

                case 'N':
                    $status = '<span class="badge rounded-pill badge-soft-danger text-danger fs-13"><i class="fa-solid fa-xmark fs-6 text-danger"></i> ไม่เห็นชอบ </span>';
                    break;

                default:
                    $status = '<span class="badge rounded-pill badge-soft-warning text-warning fs-13"><i class="fa-regular fa-clock"></i> รออนุมัติ </span>';
                    break;
            }

            $checkerTime = isset($this->data_json['checker_confirm_date']) ? AppHelper::timeDifference($this->data_json['checker_confirm_date']) : null;
            $approve = Approve::findOne(['name' => 'main_stock','from_id' => $this->id,'status' => 'Pass']);

            return
                [
                    'status' => $status,
                    // 'fullname' => isset($this->data_json['checker_name']) ? $this->data_json['checker_name'] : '',
                    // 'checker_date' => isset($this->data_json['checker_confirm_date']) ?   explode(' ',Yii::$app->thaiFormatter->asDateTime($this->data_json['checker_confirm_date'], 'php:d/m/Y H:i:s'))[0] : '',
                    'fullname' => $this->getAvatar($approve->emp_id)['fullname'],
                    'position' => $this->getAvatar($approve->emp_id)['position_name'],
                    'approve_date' => $approve->data_json['approve_date'] ?  Yii::$app->thaiDate->toThaiDate($approve->data_json['approve_date'], true, false) : '',
                    'avatar' => $this->getAvatar($approve->emp_id, '<span class="fw-bolder">'.$msg.'</span> ' . $status . ' | <i class="bi bi-clock"></i> <span class="text-muted fs-13">' . $checkerTime . '</span>')['avatar'],
                    'checker_date' => isset($this->data_json['checker_confirm_date']) ?  Yii::$app->thaiDate->toThaiDate($this->data_json['checker_confirm_date'], true, false) : '',
                    // 'avatar' => $this->getAvatar($this->checker, '<span class="fw-bolder">'.$msg.'</span> ' . $status . ' | <i class="bi bi-clock"></i> <span class="text-muted fs-13">' . $checkerTime . '</span>')['avatar'],
                ];
        } catch (\Throwable $th) {
            return
                [
                    'status' => '',
                    'fullname' => '',
                    'position' => '',
                    'approve_date' => '',
                    'avatar' => '',
                ];
        }
    }

    //แสดงวันเวลารับวัสดุ
    public function viewRecipient()
    {
        try {
            $dateTime = $this->data_json['recipient_date'].' '.$this->data_json['recipient_time'];
            return  \Yii::$app->thaiDate->toThaiDate($dateTime, true, false);
        } catch (\Throwable $th) {
            return null;
        }
    }
// ผู้รับวสดุ
    public function Recipient()
    {
        try{
           
            $msg = 'ผู้รับวัสดุ'.' | '.$this->viewRecipient();
            return $this->getAvatar($this->data_json['recipient'],$msg);

        }catch (\Throwable $th) {
            return [
                'avatar' => '',
                'img' => '',
                'department' => '',
                'fullname' => '',
                'position_name' => '',
                'product_type_name' => '',
            ];
        }
    }

    public function getAvatar($empid, $msg = '')
    {
        try {
            $employee = Employees::find()->where(['id' => $empid])->one();

            return [
                'avatar' => $employee->getAvatar(false, $msg),
                'img' => $employee->getImg(),
                'department' => $employee->departmentName(),
                'fullname' => $employee->fullname,
                'position_name' => $employee->positionName(),
                // 'product_type_name' => $this->data_json['product_type_name']
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'img' => '',
                'department' => '',
                'fullname' => '',
                'position_name' => '',
                'product_type_name' => '',
            ];
        }
    }

    // ผู้สร้าง
    public function CreateBy($msg = null)
    {
        $emp = UserHelper::GetEmployee($this->created_by);

        return $this->getAvatar($emp->id, $msg);
    }

        // ผู้ขอเบิก
        public function UserReq($msg = null)
        {
            try {

            $emp = UserHelper::GetEmployee($this->data_json['user_req']);
            return $this->getAvatar($emp->id, $msg);
                            //code...
                        } catch (\Throwable $th) {
                           return [
                            'avatar' => ''
                           ];
                        }
        }
        

    // ผู้สั่งจ่ายวัสดุ
    public function ShowPlayer($data = '')
    {
        try {
            $datetime = \Yii::$app->thaiDate->toThaiDate($this->data_json['player_date'], true, false);
            if($data){
                $msg = $data;
            }else{
                $msg = 'ผู้จ่าย' . ' | ' . $datetime;
            }
            return $this->getAvatar($this->data_json['player'], $msg);
        } catch (\Throwable $th) {
           return [
            'fullname' => 'ไม่ระบุผู้จ่าย',
            'position_name' => '',
            'avatar' => ''
           ];
        }
    }

    public function ViewReceiveDate()
    {
        return $date = isset($this->data_json['receive_date']) ? \Yii::$app->thaiFormatter->asDate($this->data_json['receive_date'], 'long') : '-';
    }

    public function viewCreatedAt()
    {
        return Yii::$app->thaiDate->toThaiDate($this->created_at, true, false);
    }

    public function viewCreated()
    {
        return AppHelper::timeDifference($this->created_at);
    }

    public function viewUpdated()
    {
        return AppHelper::timeDifference($this->updated_at);
    }

    // สรุปรายการ Order
    public function OrderSummary()
    {
        $totalPrice = self::find()
            ->where(['name' => 'order_item', 'category_id' => $this->id])
            ->sum('qty * unit_price');
         $totalItem =    self::find()->where(['name' => 'order_item', 'category_id' => $this->id])->sum('qty');
        return [
                'total' => $totalPrice,
                'total_item' => $totalItem 
        ];
    }


     // ตรวจสอบว่ามีพอให้เบิกหรือไม่
    public function checkBalance()
    {
                $balanced=0;
                foreach ($this->getItems() as $item){
                    if($item->qty > $item->SumlotQty()){
                        $balanced +=1;
                    }
                    // if($item->qty == 0 && $item->SumlotQty() == 0){
                    //     $balanced -=1;
                    // }
                }
            return $balanced;
    }
    // รวมเงินทั้งหมด
    public function SummaryTotal($status = true)
    {


        $query = self::find()
        ->select([
            new Expression('ROUND(SUM(CASE WHEN e.transaction_type = "in" THEN COALESCE(i.qty, 0) * COALESCE(i.unit_price, 0) ELSE -COALESCE(i.qty, 0) * COALESCE(i.unit_price, 0) END), 2) as total')
        ])
        ->alias('e')
        ->innerJoin(['i' => 'stock_events'], 'i.category_id = e.id AND i.name = "order_item"')
        ->andFilterWhere(['e.thai_year' => $this->thai_year])
        ->andFilterWhere(['e.warehouse_id' => $this->warehouse_id])
        ->andFilterWhere(['e.transaction_type' => $this->transaction_type]);
    
    // เพิ่มเงื่อนไขการตรวจสอบสถานะเมื่อ $status เป็น true
    if ($status === true) {
        $query->andFilterWhere(['e.order_status' => 'success']);
        $query->andFilterWhere(['i.order_status' => 'success']);
    }
    
    // กรองตามข้อมูล JSON ที่ต้องการ
    $query->andFilterWhere([
        '=',
        new Expression("JSON_EXTRACT(e.data_json, '$.asset_type_name')"),
        $this->asset_type_name
    ]);
    
    // กรองข้อมูลตามคำค้นหา $this->q
    $query->andFilterWhere([
        'or',
        ['like', 'e.code', $this->q],
        ['like', new Expression("JSON_EXTRACT(e.data_json, '$.vendor_name')"), $this->q],
        ['like', new Expression("JSON_EXTRACT(e.data_json, '$.pq_number')"), $this->q],
        ['like', new Expression("JSON_EXTRACT(e.data_json, '$.po_number')"), $this->q],
    ]);
    
    // ดึงข้อมูลเพียงแถวเดียว
    $result = $query->one();
    
    // ตรวจสอบผลลัพธ์
    return $result['total'] ?: 0;
    
       
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

        // $sql = "SELECT 
        //         asset_item,
        //         ROUND(SUM(CASE WHEN transaction_type = 'in' THEN qty * unit_price ELSE 0 END),2) AS total_in_value,
        //         ROUND(SUM(CASE WHEN transaction_type = 'out' THEN qty * unit_price ELSE 0 END),2) AS total_out_value,
        //         ROUND(SUM(CASE WHEN transaction_type = 'in' THEN qty * unit_price ELSE -qty * unit_price END),2) AS total
        //         FROM 
        //             stock_events
        //         WHERE  thai_year =(:thai_year-1)";
        //     $sql = "SELECT ROUND(COALESCE(SUM(qty*unit_price),0),2) FROM stock WHERE thai_year =(:thai_year-1);";
        //    return \Yii::$app
        //     ->db
        //     ->createCommand($sql)
        //     ->bindValue(':thai_year', $this->thai_year)
        //     ->queryScalar();
        //     $total = self::find()
        //     ->select([new Expression('ROUND(COALESCE(SUM(qty * unit_price), 0), 2)')])
        //     ->where(['thai_year' => $year])
        //     ->andFilterWhere(['warehouse_id' => $this->warehouse_id])
        //     ->scalar();
        //     return $total;
            
            // $year = ($this->thai_year) ? ($this->thai_year - 1) : '';
            $where = ['and'];
            if($this->thai_year){
                $where[] = ['thai_year' => ($this->thai_year - 1)];  // ใช้กรองถ้าค่ามี

            }
            
            $query = self::find()->select([
                    // 'asset_item',
                    // 'total_in_value' => 'ROUND(SUM(CASE WHEN transaction_type = "in" THEN qty * unit_price ELSE 0 END), 2)',
                    // 'total_out_value' => 'ROUND(SUM(CASE WHEN transaction_type = "out" THEN qty * unit_price ELSE 0 END), 2)',
                  'total' => 'ROUND(SUM(CASE WHEN transaction_type = "in" THEN COALESCE(qty, 0) * COALESCE(unit_price, 0) ELSE -COALESCE(qty, 0) * COALESCE(unit_price, 0) END), 2)'
                ])
                ->where($where)
                ->andFilterWhere(['warehouse_id' => $this->warehouse_id])->scalar();
                if($query){
                   return $query; 
                }else{
                    return 0;
                }
    
    }

    // 0จำนวนรับเข้าปีงบประมานนี้
    public function ReceiveSummary()
    {
        $year = $this->thai_year;
        $total = self::find()
            ->select([new Expression('ROUND(COALESCE(SUM(qty * unit_price), 0), 2)')])
            ->where(['thai_year' => $year])
            ->andFilterWhere(['warehouse_id' => $this->warehouse_id])
            ->scalar();
        return $total;
    }

    // จำนวนที่ใช้ไป
    // public function OutSummary()
    // {
    //     $sql = "SELECT ROUND(COALESCE(SUM(se.qty*se.unit_price),0),2) as total
    //             FROM stock_events AS se
    //             JOIN warehouses AS w ON se.warehouse_id = w.id
    //             WHERE se.thai_year = :thai_year
    //             AND se.transaction_type = 'OUT'
    //             AND w.warehouse_type = 'SUB'";
    //    return Yii::$app->db->createCommand($sql)->bindValue(':thai_year', $this->thai_year)->queryScalar();
    // }

    public function SumSubStock()
    {
        $query =  \Yii::$app->db->createCommand("SELECT ROUND(sum(qty*unit_price),2) FROM stock where warehouse_id = :warehouse_id",[':warehouse_id' => $this->warehouse_id])->queryScalar();
        return $query ?? 0;
        
    }

    public function TotalPrice()
    {
        $query =  \Yii::$app->db->createCommand("SELECT ROUND(sum(qty*unit_price),2) FROM stock")->queryScalar();
        return $query ?? 0;
    }

    // จำนวนรับเข้าของคลังหลักปีงบประมานนี้
    public function ReceiveMainSummary()
    {
        $year = $this->thai_year;
        $total = StockEvent::find()
            ->alias('se')
            ->select([
                new Expression('ROUND(COALESCE(SUM(se.qty * se.unit_price), 0), 2) as total')
            ])
            ->joinWith('warehouse w')
            ->where([
                'se.thai_year' => $year,
                'se.transaction_type' => 'IN',
                'w.warehouse_type' => 'MAIN',
                'order_status' => 'success'
            ])
            ->andFilterWhere(['se.warehouse_id' => $this->warehouse_id])
            ->scalar();
        return $total;
    }

    // จำนวนรับเข้าของคลังย่อยปีงบประมานนี้
    public function ReceiveSubSummary()
    {
        $year = $this->thai_year;
        $total = StockEvent::find()
            ->alias('se')
            ->select([
                new Expression('ROUND(COALESCE(SUM(se.qty * se.unit_price), 0), 2) as total')
            ])
            ->joinWith('warehouse w')
            ->where([
                'se.thai_year' => $year,
                'se.transaction_type' => 'IN',
                'se.order_status' => 'success'
                // 'w.warehouse_type' => 'SUB'
            ])
            ->andFilterWhere(['!=', 'w.warehouse_type', 'MAIN'])
            ->andFilterWhere(['se.warehouse_id' => $this->warehouse_id])
            ->scalar();
        return $total;
    }

    // จำนวนที่ใช้ของการตัดออกจากคัลงย่อย
    // public function OutSummary($type =null)
    // {
    //     $query = StockEvent::find()
    //         ->alias('se')
    //         ->joinWith('warehouse w')
    //         ->where([
    //             'se.thai_year' => $this->thai_year,
    //             'se.transaction_type' => 'OUT',
    //             'order_status' => 'success',
    //             'w.warehouse_type' => 'SUB'
    //         ]);

    //     if ($this->warehouse_id) {
    //         $query->andWhere(['se.warehouse_id' => $this->warehouse_id]);
    //     }
        
    //     $total = $query->select(['total' => new Expression('ROUND(COALESCE(SUM(se.qty * se.unit_price), 0), 2)')])->scalar();
    //     return $total;
    // }


     // จำนวนที่ใช้
     public function OutSummary($type =null)
     {
         $query = StockEvent::find()
             ->alias('se')
             ->joinWith('warehouse w')
             ->where([
                 'se.thai_year' => $this->thai_year,
                 'se.transaction_type' => 'OUT',
                 'order_status' => 'success'
             ]);
 
         if ($this->warehouse_id) {
             $query->andWhere(['se.warehouse_id' => $this->warehouse_id]);
         }
         
         if ($type) {
             $query->andWhere(['w.warehouse_type' => $type]);
         }
         
         $total = $query->select(['total' => new Expression('ROUND(COALESCE(SUM(se.qty * se.unit_price), 0), 2)')])->scalar();
        //  $total = $query->select(['total' => new Expression('ROUND(COALESCE(SUM(se.qty * se.unit_price), 0), 2)')])->createCommand()->getRawSql();
         return $total;
     }
     

    // ยอดรวมที่จ่ายออก
    public function SummaryOut()
    {
        $sql = "SELECT (i.qty * i.unit_price) as total_price FROM stock_events i
                LEFT JOIN stock_events o ON i.category_id = o.id AND i.name = 'order'
                LEFT JOIN warehouses w ON w.id = i.warehouse_id
                WHERE i.name = 'order_item' AND i.transaction_type = 'OUT' AND w.warehouse_type = 'SUB'";

        $query = Yii::$app->db->createCommand($sql)->queryScalar();
            
        return $query ?? 0;
            
    }

    // สถิติมูลค่าการับเข้าจ่ายออกตามปีงบประมาณ
    public function SummaryPriceYear()
    {
        $sql = "SELECT thai_year,
             ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 10 THEN qty * unit_price ELSE 0 END), 2) AS in10,
            ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 10 THEN qty * unit_price ELSE 0 END), 2) AS out10,
            ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 11 THEN qty * unit_price ELSE 0 END), 2) AS in11,
            ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 11 THEN qty * unit_price ELSE 0 END), 2) AS out11,
            ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 12 THEN qty * unit_price ELSE 0 END), 2) AS in12,
            ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 12 THEN qty * unit_price ELSE 0 END), 2) AS out12,
            ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 1 THEN qty * unit_price ELSE 0 END), 2) AS in1,
            ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 1 THEN qty * unit_price ELSE 0 END), 2) AS out1,
            ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 2 THEN qty * unit_price ELSE 0 END), 2) AS in2,
            ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 2 THEN qty * unit_price ELSE 0 END), 2) AS out2,
            ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 3 THEN qty * unit_price ELSE 0 END), 2) AS in3,
            ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 3 THEN qty * unit_price ELSE 0 END), 2) AS out3,
            ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 4 THEN qty * unit_price ELSE 0 END), 2) AS in4,
            ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 4 THEN qty * unit_price ELSE 0 END), 2) AS out4,
            ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 5 THEN qty * unit_price ELSE 0 END), 2) AS in5,
            ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 5 THEN qty * unit_price ELSE 0 END), 2) AS out5,
            ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 6 THEN qty * unit_price ELSE 0 END), 2) AS in6,
            ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 6 THEN qty * unit_price ELSE 0 END), 2) AS out6,
            ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 7 THEN qty * unit_price ELSE 0 END), 2) AS in7,
            ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 7 THEN qty * unit_price ELSE 0 END), 2) AS out7,
            ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 8 THEN qty * unit_price ELSE 0 END), 2) AS in8,
            ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 8 THEN qty * unit_price ELSE 0 END), 2) AS out8,
            ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 9 THEN qty * unit_price ELSE 0 END), 2) AS in9,
            ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 9 THEN qty * unit_price ELSE 0 END), 2) AS out9
         FROM stock_events
         where order_status = 'success' AND thai_year = :thai_year
         GROUP BY thai_year";
        $query = \Yii::$app
            ->db
            ->createCommand($sql)
            ->bindValue(':warehouse_id', $this->warehouse_id)
            ->bindValue(':thai_year', $this->thai_year)
            ->queryOne();


        try {
            $chartSummary = [
                'in' => [$query['in10'], $query['in11'], $query['in12'], $query['in1'], $query['in2'], $query['in3'], $query['in4'], $query['in5'], $query['in6'], $query['in7'], $query['in8'], $query['in9']],
                'out' => [$query['out10'], $query['out11'], $query['out12'], $query['out1'], $query['out2'], $query['out3'], $query['out4'], $query['out5'], $query['out6'], $query['out7'], $query['out8'], $query['out9']]
            ];
            // code...
        } catch (\Throwable $th) {
            $chartSummary = [
                'in' => [],
                'out' => [],
            ];
        }
        return $chartSummary;
    }


    
    // ข้อมูล  chart summary แบบรายเดือนและปี

    public function SummaryChart($warehouseType = null)
    {
        $where = ['and'];
        $where[] = ['thai_year' => $this->thai_year];  // ใช้กรองถ้าค่ามี
        // $where[] = ['w.warehouse_type' => $warehouseType];  // ใช้กรองถ้าค่ามี

        return StockEvent::find()
            ->alias('i')
            ->select([
                'thai_year',
                new Expression("SUM(CASE WHEN i.transaction_type = 'IN' AND MONTH(i.created_at) = 10  AND w.warehouse_type = 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as in10"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'OUT' AND MONTH(i.created_at) = 10 AND w.warehouse_type != 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as out10"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'IN' AND MONTH(i.created_at) = 11 AND w.warehouse_type = 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as in11"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'OUT' AND MONTH(i.created_at) = 11 AND w.warehouse_type != 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as out11"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'IN' AND MONTH(i.created_at) = 12 AND w.warehouse_type = 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as in12"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'OUT' AND MONTH(i.created_at) = 12 AND w.warehouse_type != 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as out12"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'IN' AND MONTH(i.created_at) = 1 AND w.warehouse_type = 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as in1"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'OUT' AND MONTH(i.created_at) = 1 AND w.warehouse_type != 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as out1"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'IN' AND MONTH(i.created_at) = 2 AND w.warehouse_type = 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as in2"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'OUT' AND MONTH(i.created_at) = 2 AND w.warehouse_type != 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as out2"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'IN' AND MONTH(i.created_at) = 3 AND w.warehouse_type = 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as in3"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'OUT' AND MONTH(i.created_at) = 3 AND w.warehouse_type != 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as out3"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'IN' AND MONTH(i.created_at) = 4 AND w.warehouse_type = 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as in4"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'OUT' AND MONTH(i.created_at) = 4 AND w.warehouse_type != 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as out4"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'IN' AND MONTH(i.created_at) = 5 AND w.warehouse_type = 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as in5"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'OUT' AND MONTH(i.created_at) = 5 AND w.warehouse_type != 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as out5"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'IN' AND MONTH(i.created_at) = 6 AND w.warehouse_type = 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as in6"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'OUT' AND MONTH(i.created_at) = 6 AND w.warehouse_type != 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as out6"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'IN' AND MONTH(i.created_at) = 7 AND w.warehouse_type = 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as in7"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'OUT' AND MONTH(i.created_at) = 7 AND w.warehouse_type != 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as out7"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'IN' AND MONTH(i.created_at) = 8 AND w.warehouse_type = 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as in8"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'OUT' AND MONTH(i.created_at) = 8 AND w.warehouse_type != 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as out8"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'IN' AND MONTH(i.created_at) = 9 AND w.warehouse_type = 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as in9"),
                new Expression("SUM(CASE WHEN i.transaction_type = 'OUT' AND MONTH(i.created_at) = 9 AND w.warehouse_type != 'MAIN' THEN i.qty * i.unit_price ELSE 0 END) as out9"),
            ])
            ->where($where)
            ->andWhere(['order_status' => 'success'])
            ->leftJoin(['w' => 'warehouses'], 'w.id = i.warehouse_id')
            ->groupBy('thai_year')
            ->asArray()
            ->one();
    }

}
