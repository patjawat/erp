<?php

namespace app\modules\booking\models;

use Yii;
use DateTime;
use yii\db\Expression;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\usermanager\models\User;

/**
 * This is the model class for table "booking".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $name ชื่อกาารเก็บข้อมูล (car,conference)
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $car_type ประเภทของรถ general หรือ ambulance
 * @property int|null $document_id ตามหนังสือ
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
            [['name', 'date_start', 'time_start', 'date_end', 'time_end'], 'required'],
            [['thai_year', 'document_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['date_start', 'date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at', 'emp_id', 'ambulance_type', 'mileage_start', 'mileage_end'], 'safe'],
            [['ref', 'name', 'car_type', 'urgent', 'license_plate', 'room_id', 'location', 'reason', 'status', 'time_start', 'time_end', 'driver_id', 'leader_id'], 'string', 'max' => 255],
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
            'name' => 'ชื่อกาารเก็บข้อมูล (car,conference)',
            'thai_year' => 'ปีงบประมาณ',
            'car_type' => 'ประเภทของรถ general หรือ ambulance',
            'document_id' => 'ตามหนังสือ',
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
            'data_json' => 'ยานพาหนะ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
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

    // section Relationships
    public function getCar()
    {
        return $this->hasOne(BookingCarItems::class, ['license_plate' => 'license_plate']);
    }

    // ห้องประชุม
    public function getRoom()
    {
        return $this->hasOne(Room::class, ['code' => 'room_id'])->andOnCondition(['name' => 'meeting_room']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function showStartTime()
    {
        $time = $this->time_start;
        $formattedTime = (new DateTime($time))->format('H:i');
        return $formattedTime;
    }

    public function showEndTime()
    {
        $time = $this->time_end;
        $formattedTime = (new DateTime($time))->format('H:i');
        return $formattedTime;
    }

    // แสดงหน่วยงานภานนอก
    public function ListOrg()
    {
        $model = Categorise::find()
            ->where(['name' => 'document_org'])
            ->asArray()
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
    }

    public function listDocument()
    {
        $me = UserHelper::GetEmployee();
        $document = DocumentTags::find()->where(['tag_id' => 1, 'name' => 'comment'])->all();
        return ArrayHelper::map($document, 'id', function ($model) {
            return $model->document->topic;
        });
    }

    // แสดงรายการทะยานพาหนะ
    public function ListCarItems()
    {
        $items = BookingCarItems::find()->all();
        return ArrayHelper::map($items, 'license_plate', 'license_plate');
    }

    // นับจำแนกตามประเภท
    public function SummaryCarType()
    {
        $general = self::find()->where(['car_type' => 'general'])->count();
        $refer = self::find()->where(['car_type' => 'ambulance', 'ambulance_type' => 'REFER'])->count();
        $ems = self::find()->where(['car_type' => 'ambulance', 'ambulance_type' => 'EMS'])->count();
        $normal = self::find()->where(['car_type' => 'ambulance', 'ambulance_type' => 'normal'])->count();
        return [
            'general' => $general,
            'refer' => $refer,
            'ems' => $ems,
            'normal' => $normal
        ];
    }

    // ข้อมูล  chart summary สถิติใช้งานรถยนต์ทั่วไป
    public function ChartSummaryGeneral()
    {
        $where = ['and'];
        $where[] = ['thai_year' => $this->thai_year];  // ใช้กรองถ้าค่ามี
        return self::find()
            ->select([
                'thai_year',
                new Expression('SUM(CASE WHEN car_type = "general" AND MONTH(date_start) = 10 THEN 1 ELSE 0 END) AS general_10'),
                new Expression('SUM(CASE WHEN car_type = "general" AND MONTH(date_start) = 10 THEN 1 ELSE 0 END) AS general_10'),
                new Expression('SUM(CASE WHEN car_type = "general" AND MONTH(date_start) = 11 THEN 1 ELSE 0 END) AS general_11'),
                new Expression('SUM(CASE WHEN car_type = "general" AND MONTH(date_start) = 12 THEN 1 ELSE 0 END) AS general_12'),
                new Expression('SUM(CASE WHEN car_type = "general" AND MONTH(date_start) = 1 THEN 1 ELSE 0 END) AS general_1'),
                new Expression('SUM(CASE WHEN car_type = "general" AND MONTH(date_start) = 2 THEN 1 ELSE 0 END) AS general_2'),
                new Expression('SUM(CASE WHEN car_type = "general" AND MONTH(date_start) = 3 THEN 1 ELSE 0 END) AS general_3'),
                new Expression('SUM(CASE WHEN car_type = "general" AND MONTH(date_start) = 4 THEN 1 ELSE 0 END) AS general_4'),
                new Expression('SUM(CASE WHEN car_type = "general" AND MONTH(date_start) = 5 THEN 1 ELSE 0 END) AS general_5'),
                new Expression('SUM(CASE WHEN car_type = "general" AND MONTH(date_start) = 6 THEN 1 ELSE 0 END) AS general_6'),
                new Expression('SUM(CASE WHEN car_type = "general" AND MONTH(date_start) = 7 THEN 1 ELSE 0 END) AS general_7'),
                new Expression('SUM(CASE WHEN car_type = "general" AND MONTH(date_start) = 8 THEN 1 ELSE 0 END) AS general_8'),
                new Expression('SUM(CASE WHEN car_type = "general" AND MONTH(date_start) = 9 THEN 1 ELSE 0 END) AS general_9')
            ])
            ->where($where)
            ->andWhere(['car_type' => 'general'])
            ->groupBy('thai_year')
            ->asArray()
            ->one();
    }

    // ข้อมูล  chart summary สถิติใช้งานรถพยาบาล
    public function ChartSummaryAmbulance()
    {
        $where = ['and'];
        $where[] = ['thai_year' => $this->thai_year];  // ใช้กรองถ้าค่ามี
        return self::find()
            ->select([
                'thai_year',
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "refer" AND MONTH(date_start) = 10 THEN 1 ELSE 0 END) AS refer_10'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "refer" AND MONTH(date_start) = 10 THEN 1 ELSE 0 END) AS refer_10'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "refer" AND MONTH(date_start) = 11 THEN 1 ELSE 0 END) AS refer_11'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "refer" AND MONTH(date_start) = 12 THEN 1 ELSE 0 END) AS refer_12'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "refer" AND MONTH(date_start) = 1 THEN 1 ELSE 0 END) AS refer_1'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "refer" AND MONTH(date_start) = 2 THEN 1 ELSE 0 END) AS refer_2'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "refer" AND MONTH(date_start) = 3 THEN 1 ELSE 0 END) AS refer_3'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "refer" AND MONTH(date_start) = 4 THEN 1 ELSE 0 END) AS refer_4'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "refer" AND MONTH(date_start) = 5 THEN 1 ELSE 0 END) AS refer_5'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "refer" AND MONTH(date_start) = 6 THEN 1 ELSE 0 END) AS refer_6'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "refer" AND MONTH(date_start) = 7 THEN 1 ELSE 0 END) AS refer_7'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "refer" AND MONTH(date_start) = 8 THEN 1 ELSE 0 END) AS refer_8'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "refer" AND MONTH(date_start) = 9 THEN 1 ELSE 0 END) AS refer_9'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "ems" AND MONTH(date_start) = 10 THEN 1 ELSE 0 END) AS ems_10'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "ems" AND MONTH(date_start) = 10 THEN 1 ELSE 0 END) AS ems_10'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "ems" AND MONTH(date_start) = 11 THEN 1 ELSE 0 END) AS ems_11'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "ems" AND MONTH(date_start) = 12 THEN 1 ELSE 0 END) AS ems_12'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "ems" AND MONTH(date_start) = 1 THEN 1 ELSE 0 END) AS ems_1'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "ems" AND MONTH(date_start) = 2 THEN 1 ELSE 0 END) AS ems_2'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "ems" AND MONTH(date_start) = 3 THEN 1 ELSE 0 END) AS ems_3'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "ems" AND MONTH(date_start) = 4 THEN 1 ELSE 0 END) AS ems_4'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "ems" AND MONTH(date_start) = 5 THEN 1 ELSE 0 END) AS ems_5'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "ems" AND MONTH(date_start) = 6 THEN 1 ELSE 0 END) AS ems_6'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "ems" AND MONTH(date_start) = 7 THEN 1 ELSE 0 END) AS ems_7'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "ems" AND MONTH(date_start) = 8 THEN 1 ELSE 0 END) AS ems_8'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "ems" AND MONTH(date_start) = 9 THEN 1 ELSE 0 END) AS ems_9'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 10 THEN 1 ELSE 0 END) AS normal_10'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 10 THEN 1 ELSE 0 END) AS normal_10'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 11 THEN 1 ELSE 0 END) AS normal_11'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 12 THEN 1 ELSE 0 END) AS normal_12'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 1 THEN 1 ELSE 0 END) AS normal_1'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 2 THEN 1 ELSE 0 END) AS normal_2'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 3 THEN 1 ELSE 0 END) AS normal_3'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 4 THEN 1 ELSE 0 END) AS normal_4'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 5 THEN 1 ELSE 0 END) AS normal_5'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 6 THEN 1 ELSE 0 END) AS normal_6'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 7 THEN 1 ELSE 0 END) AS normal_7'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 8 THEN 1 ELSE 0 END) AS normal_8'),
                new Expression('SUM(CASE WHEN car_type = "ambulance" AND ambulance_type = "normal" AND MONTH(date_start) = 9 THEN 1 ELSE 0 END) AS normal_9'),
            ])
            ->where($where)
            ->andWhere(['car_type' => 'ambulance'])
            ->groupBy('thai_year')
            ->asArray()
            ->one();
    }
    
}
