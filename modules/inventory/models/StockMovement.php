<?php

namespace app\modules\inventory\models;

use app\components\AppHelper;
use app\modules\inventory\models\Warehouse;
use app\modules\purchase\models\Order;
use DateTime;
use Yii;
use app\modules\sm\models\Product;

/**
 * This is the model class for table "stock_order".
 *
 * @property int $id รหัสการเคลื่อนไหวสินค้า
 * @property string|null $name ชื่อการเก็บของข้อมูล เช่น stock_order, stock_item
 * @property string|null $po_number รหัสใบสั่งซื้อ
 * @property string|null $rc_number รหัสใบรับสินค้า
 * @property int|null $product_id รหัสสินค้า
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
class StockMovement extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $qty_check;
    public $sum_qty;

    public static function tableName()
    {
        return 'stock_movements';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'from_warehouse_id', 'to_warehouse_id', 'qty', 'created_by', 'updated_by', 'lot_number'], 'integer'],
            [['movement_type'], 'required'],
            [['movement_type'], 'string'],
            [['movement_date', 'expiry_date', 'data_json', 'created_at', 'updated_at', 'qty_check', 'receive_type','sum_qty'], 'safe'],
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
            'product_id' => 'Product ID',
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

    // นับจำนวนที่เคยที่รับเข้าคลังแล้ว
    public function QtyCheck()
    {
        // return $this->product_id;
        $stockOrder = self::find()->where(['po_number' => $this->po_number, 'product_id' => $this->product_id])->sum('qty');
        $Order = Order::findOne(['name' => 'order_item', 'product_id' => $this->product_id, 'po_number' => $this->po_number]);
        $summeryQty = ($Order->qty - $stockOrder);

        return $stockOrder ? $summeryQty : $Order->qty;
    }

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

//แสดงรายการรับสินค้าเข้าคลัง
    public function ListItemFormRcNumber(){
        return self::find()->where(['name' => 'receive_item', 'rc_number' => $this->rc_number])->all();
    }

    public function getPoQty(){
        return Order::findOne(['po_number' => $this->po_number,'product_id' => $this->product_id]);
    }

    public function getProduct(){
        $model = Product::findOne($this->product_id);
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
        (SELECT SUM(s.qty) FROM stock_movements s WHERE s.po_number = :po_number AND s.name = 'receive_item') AS stock";

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
