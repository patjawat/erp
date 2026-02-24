<?php

namespace app\modules\inventoryV2\models;

use app\modules\inventoryV2\models\Warehouse;
use Yii;

/**
 * This is the model class for table "stock_order".
 *
 * @property int $id
 * @property string $order_no เลขที่เอกสาร (RCV, ISS, TRN)
 * @property string $order_type ประเภทธุรกรรม
 * @property string|null $source_type เช่น PO, DONATE, INITIAL, REQUEST, ADJUST
 * @property string $order_date วันที่ทำรายการ
 * @property int|null $main_warehouse_id คลังสินค้าต้นทาง/คลังหลัก/คลังจ่าย
 * @property int|null $sub_warehouse_id คลังสินค้าปลายทาง/คลังรับ (กรณีโอน)
 * @property int|null $contact_id ID ผู้ขาย หรือ ผู้เบิก/แผนก
 * @property string|null $status สถานะเอกสาร
 * @property string|null $ref อ้างอิงเลขที่ใบ PO หรือ PR
 * @property string|null $data_json
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 *
 * @property StockDetail[] $stockDetails
 */
class StockOrder extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const ORDER_TYPE_IN = 'IN';
    const ORDER_TYPE_OUT = 'OUT';
    const ORDER_TYPE_TRANSFER = 'TRANSFER';
    const ORDER_TYPE_ADJUST = 'ADJUST';
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_CONFIRMED = 'CONFIRMED';
    const STATUS_CANCELLED = 'CANCELLED';

    /** ประเภทการรับเข้า (source_type สำหรับ order_type = IN) */
    const SOURCE_NORMAL = 'NORMAL';
    const SOURCE_PO = 'PO';
    const SOURCE_INITIAL = 'INITIAL';
    const SOURCE_FREE_GIFT = 'FREE_GIFT';
    const SOURCE_DONATE = 'DONATE';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['source_type', 'main_warehouse_id', 'sub_warehouse_id', 'contact_id', 'ref', 'data_json', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'DRAFT'],
            [['order_type', 'order_date'], 'required'],
            [['order_no'], 'string', 'max' => 100],
            [['order_no'], 'unique'],
            [['order_type', 'status'], 'string'],
            [['order_date', 'data_json'], 'safe'],
            [['main_warehouse_id', 'sub_warehouse_id', 'contact_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['source_type'], 'string', 'max' => 50],
            [['ref'], 'string', 'max' => 255],
            ['order_type', 'in', 'range' => array_keys(self::optsOrderType())],
            ['status', 'in', 'range' => array_keys(self::optsStatus())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_no' => 'Order No',
            'order_type' => 'Order Type',
            'source_type' => 'Source Type',
            'order_date' => 'Order Date',
            'main_warehouse_id' => 'Warehouse ID',
            'sub_warehouse_id' => 'To Warehouse ID',
            'contact_id' => 'Contact ID',
            'status' => 'Status',
            'ref' => 'Ref',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[StockDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockDetails()
    {
        return $this->hasMany(StockDetail::class, ['stock_order_id' => 'id']);
    }
    

    public function getMainWarehouse()
{
    // เปลี่ยน Warehouse::class เป็นชื่อ Model คลังสินค้าของคุณ
    // 'id' คือ PK ของตารางคลังสินค้า, 'main_warehouse_id' คือ FK ในตาราง stock_order
    return $this->hasOne(Warehouse::class, ['id' => 'main_warehouse_id']);
}

public function getSubWarehouse()
{
    // เชื่อม sub_warehouse_id ในตาราง stock_order กับ id ในตาราง warehouse
    return $this->hasOne(Warehouse::class, ['id' => 'sub_warehouse_id']);
}

public function getToWarehouse()
{
    // เชื่อม sub_warehouse_id ในตาราง stock_order กับ id ในตาราง warehouse (คลังปลายทาง)
    return $this->hasOne(Warehouse::class, ['id' => 'sub_warehouse_id']);
}






    /**
     * column order_type ENUM value labels
     * @return string[]
     */
    public static function optsOrderType()
    {
        return [
            self::ORDER_TYPE_IN => 'IN',
            self::ORDER_TYPE_OUT => 'OUT',
            self::ORDER_TYPE_TRANSFER => 'TRANSFER',
            self::ORDER_TYPE_ADJUST => 'ADJUST',
        ];
    }

    /**
     * column status ENUM value labels
     * @return string[]
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_DRAFT => 'DRAFT',
            self::STATUS_PENDING => 'PENDING',
            self::STATUS_APPROVED => 'APPROVED',
            self::STATUS_CONFIRMED => 'CONFIRMED',
            self::STATUS_CANCELLED => 'CANCELLED',
        ];
    }

    /**
     * ประเภทการรับเข้า (ใช้ในหน้าสร้างใบรับเข้า)
     * @return string[]
     */
    public static function optsReceiveSourceType()
    {
        return [
            self::SOURCE_NORMAL => 'รายการปกติ',
            self::SOURCE_PO => 'จัดซื้อ',
            self::SOURCE_INITIAL => 'ยอดยกมา',
            self::SOURCE_FREE_GIFT => 'ของแถม',
            self::SOURCE_DONATE => 'บริจาค',
        ];
    }

    /** คืน label ประเภทการรับเข้า (สำหรับแสดงใน view/รายงาน) */
    public function displayReceiveSourceType()
    {
        $opts = self::optsReceiveSourceType();
        return $opts[$this->source_type] ?? $this->source_type;
    }

    /**
     * @return string
     */
    public function displayOrderType()
    {
        return self::optsOrderType()[$this->order_type];
    }

    /**
     * @return bool
     */
    public function isOrderTypeIn()
    {
        return $this->order_type === self::ORDER_TYPE_IN;
    }

    public function setOrderTypeToIn()
    {
        $this->order_type = self::ORDER_TYPE_IN;
    }

    /**
     * @return bool
     */
    public function isOrderTypeOut()
    {
        return $this->order_type === self::ORDER_TYPE_OUT;
    }

    public function setOrderTypeToOut()
    {
        $this->order_type = self::ORDER_TYPE_OUT;
    }

    /**
     * @return bool
     */
    public function isOrderTypeTransfer()
    {
        return $this->order_type === self::ORDER_TYPE_TRANSFER;
    }

    public function setOrderTypeToTransfer()
    {
        $this->order_type = self::ORDER_TYPE_TRANSFER;
    }

    /**
     * @return string
     */
    public function displayStatus()
    {
        return self::optsStatus()[$this->status];
    }

    /**
     * @return bool
     */
    public function isStatusDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function setStatusToDraft()
    {
        $this->status = self::STATUS_DRAFT;
    }

    /**
     * @return bool
     */
    public function isStatusPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function isStatusApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function setStatusToApproved()
    {
        $this->status = self::STATUS_APPROVED;
    }

    /**
     * @return bool
     */
    public function isStatusConfirmed()
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function setStatusToConfirmed()
    {
        $this->status = self::STATUS_CONFIRMED;
    }

    /**
     * @return bool
     */
    public function isStatusCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function setStatusToCancelled()
    {
        $this->status = self::STATUS_CANCELLED;
    }

    /**
     * ตรวจสอบว่าเอกสารนี้แก้ไขได้หรือไม่
     * แก้ไขได้เฉพาะเมื่อ status = DRAFT หรือ PENDING (ยังไม่ตัดสต็อก)
     * @return bool
     */
    public function canEdit()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING]);
    }

    /**
     * ตรวจสอบว่าเอกสารนี้ยกเลิกได้หรือไม่
     * ยกเลิกได้ทุกสถานะยกเว้น CANCELLED
     * @return bool
     */
    public function canCancel()
    {
        return $this->status !== self::STATUS_CANCELLED;
    }

    /**
     * ตรวจสอบว่าเอกสารนี้ตัดสต็อกแล้วหรือยัง
     * ตัดสต็อกแล้วเมื่อ status = CONFIRMED
     * @return bool
     */
    public function isStockDeducted()
    {
        return $this->status === self::STATUS_CONFIRMED;
    }
}
