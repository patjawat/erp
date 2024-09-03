<?php

namespace app\modules\inventory\models;

use app\components\AppHelper;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\Html;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\modules\hr\models\Employees;
use app\modules\sm\models\Product;

/**
 * This is the model class for table "stock_in".
 *
 * @property int $id รหัสการเคลื่อนไหวสินค้า
 * @property string|null $name ชื่อการเก็บของข้อมูล เช่น order, item
 * @property string|null $code รหัส
 * @property string|null $asset_item รหัสสินค้า
 * @property int|null $warehouse_id รหัสคลังสินค้า
 * @property string|null $vendor_id ผู้จำหน่าย ผู้บริจาค
 * @property int|null $from_warehouse_id รหัสคลังสินค้าต้นทาง
 * @property int|null $qty จำนวนสินค้าที่เคลื่อนย้าย
 * @property float|null $total_price รวมราคา
 * @property float|null $unit_price ราคาต่อหน่วย
 * @property string|null $receive_type วิธีนำเข้า (normal = รับเข้าแบบปกติ, purchase = รับเข้าจาก PO)
 * @property string $movement_date วันที่และเวลาที่เกิดการเคลื่อนไหว
 * @property string|null $lot_number หมายเลข Lot
 * @property string|null $category_id หมวดหมูหลักที่เก็บ
 * @property string|null $order_status สถานะของ order (หัวรายการ)
 * @property string|null $ref
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $data_json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class StockEvent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
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


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vendor_id', ''], 'required'],
            [['warehouse_id', 'from_warehouse_id', 'qty', 'thai_year', 'created_by', 'updated_by'], 'integer'],
            [['total_price', 'unit_price'], 'number'],
            [['movement_date', 'data_json', 'created_at', 'updated_at', 'auto_lot', 'po_number', 'checker', 'category_code', 'warehouse_name', 'total'], 'safe'],
            [['name', 'code', 'lot_number'], 'string', 'max' => 50],
            [['asset_item', 'vendor_id', 'receive_type', 'category_id', 'order_status', 'ref'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
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

        $this->mfgDate = (isset($this->data_json['mfg_date']) && $this->data_json['mfg_date'] !=="") ? AppHelper::convertToThai($this->data_json['mfg_date']) : '-';
        $this->expDate = (isset($this->data_json['exp_date']) && $this->data_json['exp_date'] !=="") ? AppHelper::convertToThai($this->data_json['exp_date']) : '-';
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

    //เชื่อมกับ Stock 
    public function getStock()
    {
        return $this->hasOne(Stock::class, ['lot_number' => 'lot_number']);
    }



    public function getTotalOrderPrice()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        // $model =  self::find()
        //     ->where(['name' => 'order', 'warehouse_id' => $warehouse['warehouse_id'], 'order_status' => 'success','category_id' => $this->id])
        //     ->sum('total_price');
        $sql = "SELECT IFNULL(SUM(qty * unit_price),0) as total FROM `stock_events` WHERE name = 'order_item' AND transaction_type = 'IN' AND `category_id` = :category_id;";
        $query = Yii::$app->db
        ->createCommand($sql)
        ->bindValue(':category_id', $this->id)
        ->queryScalar();
        return $query;
        // if ($query) {
        //     return number_format($query, 2);
        // } else {
        //     return 0;
        // }
    }

    
    // ราวมราคาทั้งหมดขแงแต่ละ ชิ้น
    public function getTotalPriceItem()
    {
        // try {

            // $sql = "SELECT SUM(unit_price * qty) AS total FROM `stock_events` WHERE name = 'order_item' AND transaction_type = 'IN' AND order_status = 'success' GROUP BY code;";
            // $sql = "SELECT IFNULL(SUM(qty * unit_price),0) as total FROM `stock_events` WHERE `code` LIKE 'RC-670016';";
            $sql = "SELECT IFNULL(SUM(qty * unit_price),0) as total FROM `stock_events` WHERE name = 'order_item' AND transaction_type = 'IN' AND `code` = :code;";
            $query = Yii::$app->db
            ->createCommand($sql)
            ->bindValue(':code', $this->code)
            ->queryScalar();
            return $query;
        // } catch (\Throwable $th) {
        //     return 0;
        // }
    }

    //รวมราคารับเข้าของคลังนั้นๆ
    public static function getTotalPriceWarehouse()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        // try {

            $sql = "SELECT IFNULL(SUM(qty * unit_price),0) as total FROM `stock_events` WHERE name = 'order_item' AND transaction_type = 'IN' AND `warehouse_id` = :warehouse_id;";
            $query = Yii::$app->db
            ->createCommand($sql)
            ->bindValue(':warehouse_id', $warehouse['warehouse_id'])
            ->queryScalar();
            return $query;
        // } catch (\Throwable $th) {
        //     return 0;
        // }
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
                    'bs-toggle' => "tooltip",
                    'bs-placement' => "top",
                    'bs-title' => $emp->fullname . '(' . $item->data_json['committee_position_name'] . ')'
                ]]);
            }
            $data .= '</div>';
            return $data;
        } catch (\Throwable $th) {
        }
    }

    //นับจำนวนรายการที่มีสถานะเป็น Pending อยู่
    public function isPending()
    {
        return self::find()->where(['name' => 'order_item', 'category_id' => $this->id, 'order_status' => 'pending'])->count();
    }


    // นับจำนวนที่หัวหน้าอนุมัติแล้ว
    public function getTotalCheckerY()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        return self::find()
            ->where(['name' => 'order', 'warehouse_id' => $warehouse['warehouse_id'], 'order_status' => 'pending'])
            ->andWhere(new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.checker_confirm')) = 'Y'"))
            ->count();
    }


    public function getTotalSuccessOrder()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        return self::find()
            ->where(['name' => 'order_item', 'warehouse_id' => $warehouse['warehouse_id'], 'order_status' => 'success'])
            ->count('qty');
    }



    //นับจำนวนที่เบิก
    public function SumQty()
    {
        return self::find()->where(['category_id' => $this->id])->sum('qty');
    }
    //ตรวจสอบรายการว่ากรอบขอ้มูลตรบหรือไม่
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

    // คณะกรรมการ
    public function ListBoard()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'board'])->all(), 'code', 'title');
    }

    //แสดงรายกาผู้ขาย/ผู้บริจาค
    public function listWareHouse()
    {
        return ArrayHelper::map(Warehouse::find()->all(), 'id', 'warehouse_name');
    }

    //แสดงรายการย่อยของ stock
    public function getItems()
    {
        return self::find()->where(['name' => 'order_item', 'category_id' => $this->id])->all();
    }



    //แสดงรายกาผู้ขาย/ผู้บริจาค
    public function listVendor()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'vendor'])->all(), 'code', 'title');
    }

    //แสดงรายชื่อกรรมการตรวจรับ
    public function ListCommittee()
    {
        return self::find()
            ->where(['name' => 'receive_committee', 'category_id' => $this->id])
            ->all();
    }

    //แสดงรายละเอียดของแต่ละ lot
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
                'department' => $employee->departmentName()
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'department' => ''
            ];
        }
    }

    public function viewStatus()
    {

        switch ($this->order_status) {
            case 'await':
                $msg = 'อยู่ระหว่างดำเนินการ';
                break;

            case 'pending':
                $msg = 'รอดำเนินการ';
                break;

            case 'cancel':
                $msg = 'ยกเลิก';
                break;
            case 'success':
                $msg = 'สำเร็จ';
                break;


            default:
            $msg = '';
                break;
        }
        return $msg;
    }

    // แสดงผู้ตรวจสอบ
    public  function viewChecker($msg = null)
    {

        try {
            $status = '';
            switch ($this->data_json['checker_confirm']) {
                case 'Y':
                    $status =  '<i class="fa-regular fa-circle-check text-success fs-6"></i> อนุมัติ';
                    break;

                case 'N':
                    $status = '<i class="fa-solid fa-xmark fs-6 text-danger"></i> ไม่อนุมัติ';
                    break;

                default:
                    $status = '<i class="fa-regular fa-clock fs-6"></i> รออนุมัติ';
                    break;
            }
            return
                [
                    'status' => $status,
                    'avatar' => $this->getAvatar($this->checker, $msg)['avatar']
                ];
        } catch (\Throwable $th) {
            return
                [
                    'status' => '',
                    'avatar' => ''
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
                'product_type_name' => ''
            ];
        }
    }
    // ผู้ขอ
    public  function CreateBy($msg = null)
    {
        return  $this->getAvatar($this->created_by, $msg);
    }

    public function viewCreatedAt()
    {
        return Yii::$app->thaiFormatter->asDateTime($this->created_at, 'php:d/m/Y H:i:s');
    }


    public function viewCreated()
    {
        return AppHelper::timeDifference($this->created_at);
    }

    public function viewUpdated()
    {
        return AppHelper::timeDifference($this->updated_at);
    }
}
