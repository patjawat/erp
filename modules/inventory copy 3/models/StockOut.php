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
use app\modules\inventory\models\Warehouse;


/**
 * This is the model class for table "stock_out".
 *
 * @property int $id
 * @property string|null $name ชื่อการเก็บของข้อมูล เช่น order, item
 * @property string|null $code รหัส
 * @property string|null $asset_item รหัสสินค้า
 * @property int|null $warehouse_id รหัสคลังสินค้า
 * @property int|null $from_warehouse_id รหัสคลังสินค้าปลายทาง
 * @property int|null $qty จำนวนสินค้าที่เคลื่อนย้าย
 * @property float|null $price ราคาต่อหน่วย
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
 */
class StockOut extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_out';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['warehouse_id', 'from_warehouse_id', 'qty', 'thai_year', 'created_by', 'updated_by'], 'integer'],
            [['price'], 'number'],
            [['movement_date', 'data_json', 'created_at', 'updated_at', 'checker'], 'safe'],
            [['name', 'code', 'lot_number'], 'string', 'max' => 50],
            [['asset_item', 'category_id', 'order_status', 'ref'], 'string', 'max' => 255],
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
            'from_warehouse_id' => 'รหัสคลังสินค้าปลายทาง',
            'qty' => 'จำนวนสินค้าที่เคลื่อนย้าย',
            'price' => 'ราคาต่อหน่วย',
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



    public function getAvatar($user_id, $msg = '')
    {
        try {
            $employee = Employees::find()->where(['user_id' => $user_id])->one();

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

    // แสดงผู้ตรวจสอบ
    public  function viewChecker($msg = null)
    {

        $status = '';
        switch ($this->data_json['checker_confirm']) {
            case 'Y':
                $status =  '<i class="fa-regular fa-circle-check text-success fs-6"></i> อนุมัติ';
                break;

            case 'N':
                $status = '<i class="fa-solid fa-xmark fs-6 text-danger"></i> ไม่อนุมัติ';
                break;

            default:
            $status = '<i class="fa-regular fa-clock fs-6"></i> รอดำเนินการ';
                break;
        }
        return 
        [
            'status' => $status,
            'avatar' => $this->getAvatar($this->checker, $msg)['avatar']
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

    public function getFromWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'from_warehouse_id']);
    }


    // เชื่อมกับรายการ ทรัพสินและวัสดุหลายชิ้น
    public function getStocks()
    {
        return $this->hasMany(Stock::class, ['warehouse_id' => 'warehouse_id']);
    }

        // เชื่อมกับรายการ ทรัพสินและวัสดุ
        public function getStock()
        {
            return $this->hasOne(Stock::class, ['warehouse_id' => 'warehouse_id']);
        }

    // เชื่อมกับรายการ lot สินค้า
    public function listLots()
    {
        return  StockEvent::find()->where(['asset_item' => $this->asset_item])->all();
    }


    //แสดงรายกาผู้ขาย/ผู้บริจาค
    public function listWareHouse()
    {
        return ArrayHelper::map(Warehouse::find()->all(), 'id', 'warehouse_name');
    }


    //นับจำนวนรายการที่มีสถานะเป็น Pending อยู่
    public function isPending()
    {
        return self::find()->where(['name' => 'order_item', 'category_id' => $this->id, 'order_status' => 'pending'])->count();
    }


    // คณะกรรมการ
    public function ListBoard()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'board'])->all(), 'code', 'title');
    }




    //แสดงรายการย่อยของ stock
    public function listItems()
    {
        return self::find()->where(['name' => 'order_item', 'category_id' => $this->id])->all();
    }
}
