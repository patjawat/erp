<?php

namespace app\modules\am\models;

use Yii;

/**
 * This is the model class for table "asset".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $asset_group แยกประเภทพัสดุ/ครุภัณฑ์
 * @property string|null $asset_name ชื่อของครุภัณฑ์
 * @property string|null $asset_item
 * @property string|null $code ครุภัณฑ์
 * @property string|null $fsn_number หมายเลขครุภัณฑ์
 * @property int|null $qty จำนวน
 * @property string|null $receive_date วันที่รับเข้า
 * @property float|null $price ราคา
 * @property int|null $purchase
 * @property int|null $department
 * @property string|null $owner
 * @property int|null $life อายุการใช้งาน
 * @property int|null $on_year
 * @property int|null $dep_id ประจำอยู่หน่วยงาน
 * @property int|null $depre_type ประเภทค่าเสื่อมราคา
 * @property int|null $budget_year งบประมาณ
 * @property string|null $asset_status สถานะทรัพย์สิน
 * @property string|null $data_json
 * @property string|null $device_items ครุภัณฑ์ภายใน
 * @property string|null $updated_at วันเวลาแก้ไข
 * @property string|null $created_at วันเวลาสร้าง
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 * @property string|null $license_plate
 * @property string|null $car_type
 */
class Asset2 extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
     public $q_department;
    public static function tableName()
    {
        return 'asset';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ref', 'asset_group', 'asset_name', 'asset_item', 'code', 'fsn_number', 'qty', 'receive_date', 'price', 'purchase', 'department', 'owner', 'life', 'on_year', 'dep_id', 'depre_type', 'budget_year', 'data_json', 'device_items', 'updated_at', 'created_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by', 'license_plate', 'car_type'], 'default', 'value' => null],
            [['asset_status'], 'default', 'value' => 0],
            [['qty', 'purchase', 'department', 'life', 'on_year', 'dep_id', 'depre_type', 'budget_year', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['q_department','receive_date', 'data_json', 'device_items', 'updated_at', 'created_at', 'deleted_at'], 'safe'],
            [['price'], 'number'],
            [['ref', 'asset_group', 'asset_name', 'asset_item', 'code', 'fsn_number', 'owner', 'asset_status'], 'string', 'max' => 255],
            [['license_plate', 'car_type'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ref' => 'Ref',
            'asset_group' => 'แยกประเภทพัสดุ/ครุภัณฑ์',
            'asset_name' => 'ชื่อของครุภัณฑ์',
            'asset_item' => 'Asset Item',
            'code' => 'ครุภัณฑ์',
            'fsn_number' => 'หมายเลขครุภัณฑ์',
            'qty' => 'จำนวน',
            'receive_date' => 'วันที่รับเข้า',
            'price' => 'ราคา',
            'purchase' => 'Purchase',
            'department' => 'Department',
            'owner' => 'Owner',
            'life' => 'อายุการใช้งาน',
            'on_year' => 'On Year',
            'dep_id' => 'ประจำอยู่หน่วยงาน',
            'depre_type' => 'ประเภทค่าเสื่อมราคา',
            'budget_year' => 'งบประมาณ',
            'asset_status' => 'สถานะทรัพย์สิน',
            'data_json' => 'Data Json',
            'device_items' => 'ครุภัณฑ์ภายใน',
            'updated_at' => 'วันเวลาแก้ไข',
            'created_at' => 'วันเวลาสร้าง',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
            'license_plate' => 'License Plate',
            'car_type' => 'Car Type',
        ];
    }

}
