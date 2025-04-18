<?php

namespace app\modules\hr\models;

use Yii;

/**
 * This is the model class for table "development".
 *
 * @property int $id
 * @property int|null $document_id ตามหนังสือ
 * @property string $development_type_id ประเภทการพัฒนา
 * @property string $topic หัวข้อ
 * @property string|null $description
 * @property string $location สถานที่ไป
 * @property string $location_org หน่วยงานที่จัด
 * @property string $province_name จังหวัด
 * @property string $status สถานะ
 * @property string $vehicle_type พาหนะเดินทาง
 * @property string $claim_type การเบิกเงิน
 * @property string|null $time_slot ช่วงเวลา
 * @property string $date_start ออกเดินทาง
 * @property string|null $time_start เริ่มเวลา
 * @property string $date_end ถึงวันที่
 * @property string|null $time_end ถึงเวลา
 * @property string|null $driver_id พนักงานขับ
 * @property string $leader_id หัวหน้าฝ่าย
 * @property int $assigned_to มอบหมายงานให้
 * @property string $emp_id ผู้ขอ
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class Development extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'development';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['document_id', 'description', 'time_slot', 'time_start', 'time_end', 'driver_id', 'data_json', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['document_id', 'assigned_to', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['development_type_id', 'topic', 'location', 'location_org', 'province_name', 'status', 'vehicle_type', 'claim_type', 'date_start', 'date_end', 'leader_id', 'assigned_to', 'emp_id'], 'required'],
            [['description'], 'string'],
            [['time_slot', 'date_start', 'date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['development_type_id', 'topic', 'location', 'location_org', 'province_name', 'status', 'vehicle_type', 'claim_type', 'time_start', 'time_end', 'driver_id', 'leader_id', 'emp_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'document_id' => 'ตามหนังสือ',
            'development_type_id' => 'ประเภทการพัฒนา',
            'topic' => 'หัวข้อ',
            'description' => 'Description',
            'location' => 'สถานที่ไป',
            'location_org' => 'หน่วยงานที่จัด',
            'province_name' => 'จังหวัด',
            'status' => 'สถานะ',
            'vehicle_type' => 'พาหนะเดินทาง',
            'claim_type' => 'การเบิกเงิน',
            'time_slot' => 'ช่วงเวลา',
            'date_start' => 'ออกเดินทาง',
            'time_start' => 'เริ่มเวลา',
            'date_end' => 'ถึงวันที่',
            'time_end' => 'ถึงเวลา',
            'driver_id' => 'พนักงานขับ',
            'leader_id' => 'หัวหน้าฝ่าย',
            'assigned_to' => 'มอบหมายงานให้',
            'emp_id' => 'ผู้ขอ',
            'data_json' => 'ยานพาหนะ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

    public function listDevelopmentType()
    {
        return ArrayHelper::map(DevelopmentType::find()->asArray()->all(), 'id', 'name');
    }
}
