<?php

namespace app\modules\booking\models;

use Yii;
use app\modules\am\models\Asset;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Employees;

/**
 * This is the model class for table "booking_detail".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $name ชื่อการเก็บข้อมุล,ผู้ร่วมเดินทาง,บุคคลอื่นร่วมเดินทาง,งานมอบหมาย
 * @property string|null $booking_id เชื่อมกับตารางหลัก
 * @property string|null $ambulance_type ประเภทของการรับส่งสำหรับรถพยาบาล
 * @property string|null $date_start เริ่มวันที่
 * @property string|null $time_start เริ่มเวลา
 * @property string|null $date_end ถึงวันที่
 * @property string|null $time_end ถึงเวลา
 * @property float|null $mileage_start เลขไมล์รถก่อนออกเดินทาง
 * @property float|null $mileage_end เลขไมล์หลังเดินทาง
 * @property float|null $distance_km นะยะทาง กม.
 * @property float|null $oil_price น้ำมันที่เติม
 * @property float|null $oil_liter ปริมาณน้ำมัน
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class BookingDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'booking_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_start', 'date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at','driver_id','emp_id'], 'safe'],
            [['mileage_start', 'mileage_end', 'distance_km', 'oil_price', 'oil_liter'], 'number'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['ref', 'name', 'booking_id', 'ambulance_type', 'time_start', 'time_end'], 'string', 'max' => 255],
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
            'name' => 'ชื่อการเก็บข้อมุล,ผู้ร่วมเดินทาง,บุคคลอื่นร่วมเดินทาง,งานมอบหมาย',
            'booking_id' => 'เชื่อมกับตารางหลัก',
            'ambulance_type' => 'ประเภทของการรับส่งสำหรับรถพยาบาล',
            'date_start' => 'เริ่มวันที่',
            'time_start' => 'เริ่มเวลา',
            'date_end' => 'ถึงวันที่',
            'time_end' => 'ถึงเวลา',
            'mileage_start' => 'เลขไมล์รถก่อนออกเดินทาง',
            'mileage_end' => 'เลขไมล์หลังเดินทาง',
            'distance_km' => 'นะยะทาง กม.',
            'oil_price' => 'น้ำมันที่เติม',
            'oil_liter' => 'ปริมาณน้ำมัน',
            'data_json' => 'ยานพาหนะ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

    public function getCar()
    {
        return $this->hasOne(Asset::class, ['license_plate' => 'license_plate']);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getDriver()
    {
        return $this->hasOne(Employees::class, ['id' => 'driver_id']);
    }

    public function showDate()
    {
        return ThaiDateHelper::formatThaiDate($this->date_start);
    }

    
    
}
