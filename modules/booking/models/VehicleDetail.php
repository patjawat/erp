<?php

namespace app\modules\booking\models;

use Yii;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\modules\am\models\Asset;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Employees;
use app\modules\booking\models\Vehicle;
use app\modules\filemanager\components\FileManagerHelper;

/**
 * This is the model class for table "vehicle_detail".
 *
 * @property int $id
 * @property int $vehicle_id ID ของรถยนต์
 * @property string|null $ref
 * @property float|null $mileage_start เลขไมล์รถก่อนออกเดินทาง
 * @property float|null $mileage_end เลขไมล์หลังเดินทาง
 * @property float|null $distance_km ระยะทาง กม.
 * @property float|null $oil_price น้ำมันที่เติม
 * @property float|null $oil_liter ปริมาณน้ำมัน
 * @property string|null $license_plate ทะเบียนยานพาหนะ
 * @property string|null $status สถานะ
 * @property string|null $date_start เริ่มวันที่
 * @property string|null $time_start เริ่มเวลา
 * @property string|null $date_end ถึงวันที่
 * @property string|null $time_end ถึงเวลา
 * @property string|null $driver_id พนักงานขับ
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class VehicleDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $q;
    public $emp_id;
    public $thai_year;
    public $date_filter;
    public static function tableName()
    {
        return 'vehicle_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vehicle_id'], 'required'],
            [['vehicle_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['mileage_start', 'mileage_end', 'distance_km', 'oil_price', 'oil_liter'], 'number'],
            [['date_start', 'date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at','q','emp_id','thai_year','date_filter'], 'safe'],
            [['ref', 'license_plate', 'status', 'time_start', 'time_end', 'driver_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'vehicle_id' => 'ID ของรถยนต์',
            'ref' => 'Ref',
            'mileage_start' => 'เลขไมล์รถก่อนออกเดินทาง',
            'mileage_end' => 'เลขไมล์หลังเดินทาง',
            'distance_km' => 'ระยะทาง กม.',
            'oil_price' => 'น้ำมันที่เติม',
            'oil_liter' => 'ปริมาณน้ำมัน',
            'license_plate' => 'ทะเบียนยานพาหนะ',
            'status' => 'สถานะ',
            'date_start' => 'เริ่มวันที่',
            'time_start' => 'เริ่มเวลา',
            'date_end' => 'ถึงวันที่',
            'time_end' => 'ถึงเวลา',
            'driver_id' => 'พนักงานขับ',
            'data_json' => 'ยานพาหนะ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

    public function getVehicle()
    {
        return $this->hasOne(Vehicle::class, ['id' => 'vehicle_id']);
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

    public function Upload()
    {
        $ref = $this->ref;
        $name = 'vehicle_bill';
        return FileManagerHelper::FileUpload($ref, $name);
    }

    public function showDriver($msg = null)
    {
        try {
            $emp = Employees::findOne(['id' => $this->driver_id]);
        // $msg = $emp->departmentName();
        return [
            'avatar' => $emp->getAvatar(false, $msg),
            'fullname' => $emp->fullname
        ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'fullname' => ''
            ];
        }
        
    }

    public function ListThaiYear()
    {
        $model = Vehicle::find()
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

        // แสดงรายการาถานะ
        public function ListStatus()
        {
            $model = Categorise::find()
                ->where(['name' => 'vehicle_detail_status'])
                ->asArray()
                ->all();
            return ArrayHelper::map($model, 'code', 'title');
        }
    
}
