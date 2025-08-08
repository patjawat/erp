<?php

namespace app\modules\inventory\models;

use Yii;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;

/**
 * This is the model class for table "view_stock_transaction".
 *
 * @property string|null $asset_type ชื่อ
 * @property string|null $category_id
 * @property string|null $asset_item รหัส
 * @property string|null $asset_name ชื่อ
 * @property string|null $unit
 * @property string|null $code รหัส
 * @property string|null $po_number จากเลขที่สั่งซื้อ
 * @property string|null $from_warehouse_type ประเภทการเคลื่อนไหว (MAIN = คลังหลัก, SUB = ตลังย่อย, BRANCH = สาขา รพสต.)
 * @property string|null $from_warehouse_name ชื่อคลังสินค้า
 * @property string|null $warehouse_type ประเภทการเคลื่อนไหว (MAIN = คลังหลัก, SUB = ตลังย่อย, BRANCH = สาขา รพสต.)
 * @property string|null $warehouse_name ชื่อคลังสินค้า
 * @property string|null $transaction_type ธุรกรรม
 * @property string|null $order_status สถานะของ order (หัวรายการ)
 * @property int|null $warehouse_id รหัสคลังสินค้า
 * @property float|null $qty จำนวนสินค้าที่เคลื่อนย้าย
 * @property float|null $unit_price ราคาต่อหน่วย
 * @property string|null $receive_date
 * @property string|null $created_at วันที่สร้าง
 * @property int|null $thai_year ปีงบประมาณ
 * @property float|null $total_price รวมราคา
 * @property int|null $order_month
 */
class StockTransaction extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const FROM_WAREHOUSE_TYPE_MAIN = 'MAIN';
    const FROM_WAREHOUSE_TYPE_SUB = 'SUB';
    const FROM_WAREHOUSE_TYPE_BRANCH = 'BRANCH';
    const WAREHOUSE_TYPE_MAIN = 'MAIN';
    const WAREHOUSE_TYPE_SUB = 'SUB';
    const WAREHOUSE_TYPE_BRANCH = 'BRANCH';


    public $date_filter;
    public $date_start;
    public $date_end;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_stock_transaction';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['asset_type', 'category_id', 'asset_item', 'asset_name', 'unit', 'code', 'po_number', 'from_warehouse_type', 'from_warehouse_name', 'warehouse_type', 'warehouse_name', 'transaction_type', 'order_status', 'warehouse_id', 'qty', 'unit_price', 'receive_date', 'created_at', 'thai_year', 'total_price', 'order_month'], 'default', 'value' => null],
            [['asset_type', 'asset_name', 'unit', 'from_warehouse_type', 'warehouse_type', 'receive_date'], 'string'],
            [['warehouse_id', 'thai_year', 'order_month'], 'integer'],
            [['qty', 'unit_price', 'total_price'], 'number'],
            [['created_at', 'date_filter', 'date_start', 'date_end'], 'safe'],
            [['category_id', 'asset_item', 'po_number', 'transaction_type', 'order_status'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50],
            [['from_warehouse_name', 'warehouse_name'], 'string', 'max' => 100],
            ['from_warehouse_type', 'in', 'range' => array_keys(self::optsFromWarehouseType())],
            ['warehouse_type', 'in', 'range' => array_keys(self::optsWarehouseType())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'asset_type' => 'Asset Type',
            'category_id' => 'Category ID',
            'asset_item' => 'Asset Item',
            'asset_name' => 'Asset Name',
            'unit' => 'Unit',
            'code' => 'Code',
            'po_number' => 'Po Number',
            'from_warehouse_type' => 'From Warehouse Type',
            'from_warehouse_name' => 'From Warehouse Name',
            'warehouse_type' => 'Warehouse Type',
            'warehouse_name' => 'Warehouse Name',
            'transaction_type' => 'Transaction Type',
            'order_status' => 'Order Status',
            'warehouse_id' => 'Warehouse ID',
            'qty' => 'Qty',
            'unit_price' => 'Unit Price',
            'receive_date' => 'Receive Date',
            'created_at' => 'Created At',
            'thai_year' => 'Thai Year',
            'total_price' => 'Total Price',
            'order_month' => 'Order Month',
        ];
    }


    // แสดงปีงบประมานทั้งหมด
    public function ListThaiYear()
    {
        $model = self::find()
            ->select('thai_year')
            ->groupBy('thai_year')
            ->orderBy(['thai_year' => SORT_DESC])
            ->asArray()
            ->all();

        $year = AppHelper::YearBudget();
        $isYear = [['thai_year' => $year]];  // ห่อด้วย array เพื่อให้รูปแบบตรงกัน
        // รวมข้อมูล
        $model = ArrayHelper::merge($isYear, $model);
        return ArrayHelper::map($model, 'thai_year', 'thai_year');
    }


        // แสดงปีงบประมานทั้งหมด
    public function ListAssetName()
    {
        $model = self::find()
            ->select('asset_name')
            ->groupBy('asset_name')
            ->orderBy(['asset_name' => SORT_DESC])
            ->asArray()
            ->all();

        return ArrayHelper::map($model, 'asset_name', 'asset_name');
    }

        public function ListCode()
    {
        $model = self::find()
            ->select('code')
            ->groupBy('code')
            ->orderBy(['code' => SORT_DESC])
            ->asArray()
            ->all();

        return ArrayHelper::map($model, 'code', 'code');
    }





    public function ListAssetType()
    {
        $data = self::find()->groupBy('asset_type')->all();
        return ArrayHelper::map($data, 'asset_type', 'asset_type');
    }

    /**
     * column from_warehouse_type ENUM value labels
     * @return string[]
     */
    public static function optsFromWarehouseType()
    {
        return [
            self::FROM_WAREHOUSE_TYPE_MAIN => 'MAIN',
            self::FROM_WAREHOUSE_TYPE_SUB => 'SUB',
            self::FROM_WAREHOUSE_TYPE_BRANCH => 'BRANCH',
        ];
    }

    /**
     * column warehouse_type ENUM value labels
     * @return string[]
     */
    public static function optsWarehouseType()
    {
        return [
            self::WAREHOUSE_TYPE_MAIN => 'MAIN',
            self::WAREHOUSE_TYPE_SUB => 'SUB',
            self::WAREHOUSE_TYPE_BRANCH => 'BRANCH',
        ];
    }

    /**
     * @return string
     */
    public function displayFromWarehouseType()
    {
        return self::optsFromWarehouseType()[$this->from_warehouse_type];
    }

    /**
     * @return bool
     */
    public function isFromWarehouseTypeMain()
    {
        return $this->from_warehouse_type === self::FROM_WAREHOUSE_TYPE_MAIN;
    }

    public function setFromWarehouseTypeToMain()
    {
        $this->from_warehouse_type = self::FROM_WAREHOUSE_TYPE_MAIN;
    }

    /**
     * @return bool
     */
    public function isFromWarehouseTypeSub()
    {
        return $this->from_warehouse_type === self::FROM_WAREHOUSE_TYPE_SUB;
    }

    public function setFromWarehouseTypeToSub()
    {
        $this->from_warehouse_type = self::FROM_WAREHOUSE_TYPE_SUB;
    }

    /**
     * @return bool
     */
    public function isFromWarehouseTypeBranch()
    {
        return $this->from_warehouse_type === self::FROM_WAREHOUSE_TYPE_BRANCH;
    }

    public function setFromWarehouseTypeToBranch()
    {
        $this->from_warehouse_type = self::FROM_WAREHOUSE_TYPE_BRANCH;
    }

    /**
     * @return string
     */
    public function displayWarehouseType()
    {
        return self::optsWarehouseType()[$this->warehouse_type];
    }

    /**
     * @return bool
     */
    public function isWarehouseTypeMain()
    {
        return $this->warehouse_type === self::WAREHOUSE_TYPE_MAIN;
    }

    public function setWarehouseTypeToMain()
    {
        $this->warehouse_type = self::WAREHOUSE_TYPE_MAIN;
    }

    /**
     * @return bool
     */
    public function isWarehouseTypeSub()
    {
        return $this->warehouse_type === self::WAREHOUSE_TYPE_SUB;
    }

    public function setWarehouseTypeToSub()
    {
        $this->warehouse_type = self::WAREHOUSE_TYPE_SUB;
    }

    /**
     * @return bool
     */
    public function isWarehouseTypeBranch()
    {
        return $this->warehouse_type === self::WAREHOUSE_TYPE_BRANCH;
    }

    public function setWarehouseTypeToBranch()
    {
        $this->warehouse_type = self::WAREHOUSE_TYPE_BRANCH;
    }

        // รวมราคาทั้งหมด
    public function sumPrice()
    {
        $data = sum(($this->qty ?? 0) * ($this->unit_price ?? 0));
        return $data ? number_format($data, 2) : 0;
    }


}
