<?php

namespace app\modules\inventory\models;

use app\components\AppHelper;
use app\components\UserHelper;
use app\components\AssetHelper;
use app\modules\inventory\models\Warehouse;
use app\modules\purchase\models\Order;
use DateTime;
use Yii;
use yii\helpers\Html;
use app\modules\sm\models\Product;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\modules\hr\models\Employees;
use app\models\Categorise;
/**
 * This is the model class for table "stock_order".
 *
 * @property int $id รหัสการเคลื่อนไหวสินค้า
 * @property string|null $name ชื่อการเก็บของข้อมูล เช่น stock_order, order_item
 * @property string|null $po_number รหัสใบสั่งซื้อ
 * @property string|null $rc_number รหัสใบรับสินค้า
 * @property int|null $asset_item รหัสสินค้า
 * @property int|null $from_warehouse_id รหัสคลังสินค้าต้นทาง
 * @property int|null $to_warehouse_id รหัสคลังสินค้าปลายทาง
 * @property int|null $qty จำนวนสินค้าที่เคลื่อนย้าย
 * @property string $movement_type ประเภทการเคลื่อนไหว (IN = รับเข้า, OUT = จ่ายออก, TRANSFER = โอนย้าย)
 * @property string $movement_date วันที่และเวลาที่เกิดการเคลื่อนไหว
 * @property string|null $lot_number หมายเลข Lot
 * @property string|null $expiry_date วันหมดอายุ
 * @property string|null $category_id หมวดหมูหลักที่เก็บ
 * @property string|null $ref
 * @property string|null $data_json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Stock extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $qty_check;
    public $sum_qty;
    public $auto_lot;

    public static function tableName()
    {
        return 'stock';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['from_warehouse_id', 'to_warehouse_id', 'qty', 'created_by', 'updated_by', 'lot_number'], 'integer'],
            // [['movement_type'], 'required'],
            [['movement_type'], 'string'],
            [['asset_item','movement_date', 'expiry_date', 'data_json', 'created_at', 'updated_at', 'qty_check', 'receive_type','sum_qty','auto_lot'], 'safe'],
            [['name', 'po_number', 'rc_number', 'lot_number'], 'string', 'max' => 50],
            [['category_id', 'ref'], 'string', 'max' => 255],
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
            'po_number' => 'Po Number',
            'rc_number' => 'Rc Number',
            'asset_item' => 'Product ID',
            'from_warehouse_id' => 'From Warehouse ID',
            'to_warehouse_id' => 'To Warehouse ID',
            'qty' => 'Qty',
            'movement_type' => 'Movement Type',
            'receive_type' => 'วิธีนำเข้า',
            'movement_date' => 'Movement Date',
            'lot_number' => 'Lot Number',
            'expiry_date' => 'Expiry Date',
            'category_id' => 'Category ID',
            'order_status' => 'สถานะของ order (หัวรายการ)',
            'item_status' => 'สถานะของรายการ item (รายการแต่ละอัน)',
            'ref' => 'Ref',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
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
            // $this->movement_date = AppHelper::convertToGregorian($this->movement_date);
            return true;
        } else {
            return false;
        }
    }

    public function afterFind()
    {
        parent::afterFind();
        // แปลงวันที่จาก ค.ศ. เป็น พ.ศ. หลังจากดึงข้อมูลจากฐานข้อมูล
        // $this->movement_date = AppHelper::convertToThaiBuddhist($this->movement_date);
    }

        // Avatar ของฉัน
        public  function getMe($msg=null)
        {
            return UserHelper::getMe($msg);
        }

    
    // แสดงวันที่ส่งซ่อม
    public function viewCreateDate()
    {
        return Yii::$app->thaiFormatter->asDate($this->created_at, 'php:d/m/Y เวลา H:i:s');
    }

//  Relation 
// เชื่อกับใบสั่งซื้อ
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['po_number' => 'po_number'])->andOnCondition(['name' => 'order']);
    }
// เชื่อมกับรายการ order
    public function getOrderItem()
    {
        return $this->hasOne(Order::class, ['po_number' => 'po_number'])->andOnCondition(['name' => 'order_item']);
    }

// เชื่อมกับรายการ ทรัพสินและวัสดุ
public function getProduct()
{
    return $this->hasOne(Product::class, ['code' => 'asset_item'])->andOnCondition(['name' => 'asset_item']);
}



    // ผู้ขอ
    public function CreateBy()
    {
        // try {
            $employee = Employees::find()->where(['user_id' => $this->created_by])->one();

            return [
                'avatar' => $employee->getAvatar(false),
                'department' => $employee->departmentName(),
                'fullname' => $employee->fullname,
                'position_name' => $employee->positionName(),
                // 'product_type_name' => $this->data_json['product_type_name']
            ];
        // } catch (\Throwable $th) {
        //     return [
        //         'avatar' => '',
        //         'department' => '',
        //         'fullname' => '',
        //         'position_name' => '',
        //         'product_type_name' => ''
        //     ];
        // }
    }

    public function getCurrDate()
    {
        $year = (date('Y') - 1) + 543;
        $m = date('m');
        $startDate = "01/{$m}/{$year}";
        $endDate = date('d/m/') . ((date('Y') + 1) + 543);
        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    //แสดงระยะเวลาที่ผ่านมา
    public function viewCreated(){
        return AppHelper::timeDifference($this->created_at);
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
        //  ภาพทีมคณะกรรมการ
        public function StackComittee()
        {
            try {
                $data = '';
                $data .= '<div class="avatar-stack">';
                foreach (self::find()->where(['name' => 'receive_committee'])->all() as $key => $item) {
                    $emp = Employees::findOne(['id' => $item->data_json['employee_id']]);
                    $data .= Html::a(Html::img($emp->ShowAvatar(), ['class' => 'avatar-sm rounded-circle shadow']),['/inventory/receive/update-committee','id' => $item->id,'title' => '<i class="bi bi-person-circle"></i> กรรมการตรวจรับเข้าคลัง'],['class' => 'open-modal','data' => [
                        'size' => 'model-md',
                        'bs-toggle'=>"tooltip",
                        'bs-placement'=>"top",
                        'bs-title' => $emp->fullname.'('.$item->data_json['committee_position_name'].')'
                    ]]);
                    // $data .= '<a href="javascript: void(0);" class="me-1" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-title="' . $emp->fullname . '">';
                    // $data .= Html::img($emp->ShowAvatar(), ['class' => 'avatar-sm rounded-circle shadow']);
                    // $data .= '</a>';
                }
                $data .= '</div>';
                return $data;
            } catch (\Throwable $th) {
            }
        }

        
    // นับจำนวนที่เคยที่รับเข้าคลังแล้ว
    public function QtyCheck()
    {
        // return $this->asset_item;
        $stockOrder = self::find()->where(['po_number' => $this->po_number, 'asset_item' => $this->asset_item])->sum('qty');
        $order = Order::findOne(['name' => 'order_item', 'asset_item' => $this->asset_item, 'po_number' => $this->po_number]);
        return $this->asset_item;
        $summeryQty = ($order->qty - $stockOrder);

        return $stockOrder ? $summeryQty : $order->qty;
    }

        // คณะกรรมการ
        public function ListBoard()
        {
            return ArrayHelper::map(Categorise::find()->where(['name' => 'board'])->all(), 'code', 'title');
        }


           // คณะกรรมการ
           public function ListAssetType()
           {
               return AssetHelper::ListAssetType();
           }
   


    public function ListStockItems()
    {
        return self::find()
            ->where(['name' => 'order_item', 'category_id' => $this->id])
            ->all();
    }

            //แสดงรายชื่อกรรมการตรวจรับ
    public function ListCommittee()
    {
        return self::find()
            ->where(['name' => 'receive_committee', 'rc_number' => $this->rc_number])
            ->all();
    }

    //แสดงรายการสั่งซื้อจาก PO
    public function ListOrderItems()
    {
        return Order::find()
            ->where(['name' => 'order_item', 'po_number' => $this->po_number])
            ->all();
    }

    public function ListPoOrder()
    {
        return Order::find()
            ->where(['name' => 'order_item', 'po_number' => $this->po_number])
            ->all();

    }


    //แสดงรายการวัสดุจากคัลงที่เลือก
    public function ListProductFormwarehouse(){
        return self::find()
        ->select('id,asset_item,sum(qty) as sum_qty')
        ->where(['name' => 'receive_item','to_warehouse_id' => $this->from_warehouse_id])
        ->groupBy('asset_item')
        ->all();
    }
//แสดงรายการรับสินค้าเข้าคลัง
    public function ListItemFormRcNumber(){
        return self::find()->where(['name' => 'receive_item', 'rc_number' => $this->rc_number])->all();
    }


    //แสดงรายการจาเลขที่ขอเบิก
    public function ListItemRequest()
    {
        return self::find()->where(['name' => 'request_item', 'rq_number' => $this->rq_number])->all();
    }
    public function getPoQty(){
        return Order::findOne(['po_number' => $this->po_number,'asset_item' => $this->asset_item]);
    }

    public function getProductItem(){
        $model = Product::findOne($this->asset_item);
        if($model){
            return $model;
        }else{
            return $model;
        }
    }

//ตรวจสอบว่าวัสดจกใบ PO รับเข้าหมดหือยัง
    public function OrderSuccess(){
        $sql = "SELECT 
        (SELECT SUM(o.qty) FROM `order` o WHERE o.po_number = :po_number AND o.name = 'order_item') AS po,
        (SELECT SUM(s.qty) FROM stock s WHERE s.po_number = :po_number AND s.name = 'receive_item') AS stock";

    $query =   Yii::$app->db->createCommand($sql)
    ->bindValue(':po_number', $this->po_number)
    ->queryOne();
    $status = ($query['po'] == $query['stock'] ? true : false);

        return [
            'status' =>  $status,
            'po' => $query['po'],
            'stock' => $query['stock']
        ];
   

}
    // แสดงชื่อสถานะ
    public function viewStatus()
    {
        if ($this->order_status == 'success') {
            return 'รับเข้าสำเร็จ';
        } else {
            return 'อยูระหว่างดำเนินการ';
        }
    }

    public function viewReceiveType()
    {
        if ($this->receive_type == 'purchase') {
            return 'รับจากการสั่งซื้อ';
        } else {
            return 'รับเข้าปกติ';
        }
    }

    // จากคลัง
    public function tromWarehouse()
    {
        $model = Warehouse::findOne($this->from_warehouse_id);
        if ($model) {
            return $model->warehouse_name;
        } else {
            return '-';
        }
    }

    // เข้าคลัง
    public function tomWarehouse()
    {
        $model = Warehouse::findOne($this->to_warehouse_id);
        if ($model) {
            return $model->warehouse_name;
        } else {
            return '-';
        }
    }

    // ฟังก์ชันในการแปลงปี ค.ศ. เป็น พ.ศ.
    public function convertToThaiDate($date)
    {
        if ($date !== null) {
            $dateTime = new DateTime($date);
            $year = $dateTime->format('Y') + 543;
            return $dateTime->format("d/m/{$year}");
        }
        return null;
    }
}
