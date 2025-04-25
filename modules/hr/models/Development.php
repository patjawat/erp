<?php

namespace app\modules\hr\models;

use Yii;
use yii\helpers\Html;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\DevelopmentDetail;

/**
 * This is the model class for table "development".
 *
 * @property int $id
 * @property int|null $document_id ตามหนังสือ
 * @property string $topic หัวข้อ
 * @property string $status สถานะ
 * @property string $date_start วันที่เริ่ม
 * @property string|null $time_start เริ่มเวลา
 * @property string $date_end ถึงวันที่
 * @property string|null $time_end ถึงเวลา
 * @property string|null $vehicle_type_id ยานพาหนะ
 * @property string $vehicle_date_start วันออกเดินทาง
 * @property string $vehicle_date_end วันกลับ
 * @property string|null $driver_id พนักงานขับ
 * @property string $leader_id หัวหน้าฝ่าย
 * @property int $assigned_to มอบหมายงานให้
 * @property string $emp_id ผู้ขอ
 * @property string|null $data_json JSON
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
    public $q;
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
            [['document_id', 'time_start', 'time_end', 'vehicle_type_id', 'driver_id', 'data_json', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['document_id', 'assigned_to', 'created_by', 'updated_by', 'deleted_by','thai_year'], 'integer'],
            // [['topic', 'status', 'date_start', 'date_end', 'vehicle_date_start', 'vehicle_date_end', 'leader_id', 'assigned_to', 'emp_id'], 'required'],
            [['date_start', 'date_end', 'vehicle_date_start', 'vehicle_date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['topic', 'status', 'time_start', 'time_end', 'vehicle_type_id', 'driver_id', 'leader_id', 'emp_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'thai_year' => 'ปีงบประมาณ',
            'document_id' => 'ตามหนังสือ',
            'topic' => 'หัวข้อ',
            'status' => 'สถานะ',
            'date_start' => 'วันที่เริ่ม',
            'time_start' => 'เริ่มเวลา',
            'date_end' => 'ถึงวันที่',
            'time_end' => 'ถึงเวลา',
            'vehicle_type_id' => 'ยานพาหนะ',
            'vehicle_date_start' => 'วันออกเดินทาง',
            'vehicle_date_end' => 'วันกลับ',
            'driver_id' => 'พนักงานขับ',
            'leader_id' => 'หัวหน้าฝ่าย',
            'assigned_to' => 'มอบหมายงานให้',
            'emp_id' => 'ผู้ขอ',
            'data_json' => 'JSON',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

    public function getLeader()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }
    
    public function getAssignedTo()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }
    


     //  ภาพทีมคณะกรรมการ
     public function StackMember()
     {
         // try {
         $data = '';
         $data .= '<div class="avatar-stack">';
         foreach (DevelopmentDetail::find()->where(['name' => 'member', 'development_id' => $this->id])->all() as $key => $item) {
             $emp = Employees::findOne(['id' => $item->emp_id]);
             $data .= Html::a(
                 Html::img('@web/img/placeholder-img.jpg', ['class' => 'avatar-sm rounded-circle shadow lazyload blur-up',
                     'data' => [
                         'expand' => '-20',
                         'sizes' => 'auto',
                         'src' => $emp->showAvatar()
                     ]]),
                 ['/me/development-detail/update-member', 'id' => $item->id, 'name' => 'committee', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'],
                 [
                     'class' => 'open-modal',
                     'data' => [
                         'size' => 'modal-md',
                         'bs-trigger' => 'hover focus',
                         'bs-toggle' => 'popover',
                         'bs-placement' => 'top',
                         'bs-title' => 'คณะเดินทาง',
                         'bs-html' => 'true',
                         'bs-content' => $emp->fullname . '<br>' . $emp->positionName()
                     ]
                 ]
             );
         }
         $data .= '</div>';
         return $data;
         // } catch (\Throwable $th) {
         // }
     }
     
    //วันที่เอกสาร
    public function showDateRange()
    {
        return ThaiDateHelper::formatThaiDateRange($this->date_start, $this->date_end, 'long', 'short');
    }

    //วันที่ออกเดินทาง
    public function showVehicleDateRange()
    {
        return ThaiDateHelper::formatThaiDateRange($this->vehicle_date_start, $this->vehicle_date_end, 'long', 'short');
    }

    
    public function ListVehicleType()
    {
        $model = Categorise::find()
            ->where(['name' => 'vehicle_type'])
            ->andWhere(['!=', 'code', 'ambulance'])
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
    }
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


}
