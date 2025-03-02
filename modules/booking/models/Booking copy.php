<?php

namespace app\modules\booking\models;

use Yii;

/**
 * This is the model class for table "booking".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $name ชื่อกาารเก็บข้อมูล (meeting_service,driver_service)
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $ambulance_type ประเภทของการรับส่งสำหรับรถพยาบาล
 * @property float|null $mileage_start เลขไมล์รถก่อนออกเดินทาง
 * @property float|null $mileage_end เลขไมล์หลังเดินทาง
 * @property float|null $distance_km นะยะทาง กม.
 * @property float|null $oil_price น้ำมันที่เติม
 * @property float|null $oil_liter ปริมาณน้ำมัน
 * @property string|null $car_type ประเภทของรถ general หรือ ambulance
 * @property int|null $document_id ตามหนังสือ
 * @property int|null $owner_id ผู้ดูแลห้องประชุม
 * @property string|null $urgent ความเร่งด่วน
 * @property string|null $license_plate ทะเบียนยานพาหนะ
 * @property string|null $room_id ห้องประชุม
 * @property string|null $location สถานที่ไป
 * @property string|null $reason เหตุผล
 * @property string|null $status สถานะ
 * @property string|null $date_start เริ่มวันที่
 * @property string|null $time_start เริ่มเวลา
 * @property string|null $date_end ถึงวันที่
 * @property string|null $time_end ถึงเวลา
 * @property string|null $driver_id พนักงานขับ
 * @property string|null $leader_id หัวหน้างานรับรอง
 * @property string|null $emp_id ผู้ขอ
 * @property int $private_car รถยนต์ส่วนตัว
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class Booking extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'booking';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['thai_year', 'document_id', 'owner_id', 'private_car', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['mileage_start', 'mileage_end', 'distance_km', 'oil_price', 'oil_liter'], 'number'],
            [['date_start', 'date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['ref', 'name', 'ambulance_type', 'car_type', 'urgent', 'license_plate', 'room_id', 'location', 'reason', 'status', 'time_start', 'time_end', 'driver_id', 'leader_id', 'emp_id'], 'string', 'max' => 255],
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
            'name' => 'ชื่อกาารเก็บข้อมูล (meeting_service,driver_service)',
            'thai_year' => 'ปีงบประมาณ',
            'ambulance_type' => 'ประเภทของการรับส่งสำหรับรถพยาบาล',
            'mileage_start' => 'เลขไมล์รถก่อนออกเดินทาง',
            'mileage_end' => 'เลขไมล์หลังเดินทาง',
            'distance_km' => 'นะยะทาง กม.',
            'oil_price' => 'น้ำมันที่เติม',
            'oil_liter' => 'ปริมาณน้ำมัน',
            'car_type' => 'ประเภทของรถ general หรือ ambulance',
            'document_id' => 'ตามหนังสือ',
            'owner_id' => 'ผู้ดูแลห้องประชุม',
            'urgent' => 'ความเร่งด่วน',
            'license_plate' => 'ทะเบียนยานพาหนะ',
            'room_id' => 'ห้องประชุม',
            'location' => 'สถานที่ไป',
            'reason' => 'เหตุผล',
            'status' => 'สถานะ',
            'date_start' => 'เริ่มวันที่',
            'time_start' => 'เริ่มเวลา',
            'date_end' => 'ถึงวันที่',
            'time_end' => 'ถึงเวลา',
            'driver_id' => 'พนักงานขับ',
            'leader_id' => 'หัวหน้างานรับรอง',
            'emp_id' => 'ผู้ขอ',
            'private_car' => 'รถยนต์ส่วนตัว',
            'data_json' => 'ยานพาหนะ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }
}
