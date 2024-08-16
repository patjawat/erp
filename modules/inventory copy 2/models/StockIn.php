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
 * @property string|null $expiry_date วันหมดอายุ
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
class StockIn extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_in';
    }

    public $auto_lot;
    public $q;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vendor_id', ''], 'required'],
            [['warehouse_id', 'from_warehouse_id', 'qty', 'thai_year', 'created_by', 'updated_by'], 'integer'],
            [['total_price', 'unit_price'], 'number'],
            [['movement_date', 'expiry_date', 'data_json', 'created_at', 'updated_at','auto_lot','po_number'], 'safe'],
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
            'expiry_date' => 'วันหมดอายุ',
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



        
}
