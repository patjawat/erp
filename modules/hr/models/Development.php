<?php

namespace app\modules\hr\models;

use Yii;
use yii\helpers\Html;
use yii\db\Expression;
use app\models\Categorise;
use app\components\LineMsg;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\approve\models\Approve;
use app\modules\usermanager\models\User;
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
    public $q_department;
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
            [['topic', 'status', 'date_start', 'date_end', 'vehicle_date_end', 'leader_id', 'assigned_to', 'emp_id','thai_year','leader_group_id'], 'required'],
            [['date_start', 'date_end', 'vehicle_date_start', 'vehicle_date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at','q','q_department'], 'safe'],
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
            'leader_group_id' => 'หัวหน้ากลุ่มงาน',
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

    // public function behaviors()
    // {
    //     return [
    //         [
    //             'class' => BlameableBehavior::className(),
    //             'createdByAttribute' => 'created_by',
    //             'updatedByAttribute' => 'updated_by',
    //         ],
    //         [
    //             'class' => TimestampBehavior::className(),
    //             'createdAtAttribute' => 'created_at',
    //             'updatedAtAttribute' => ['updated_at'],
    //             'value' => new Expression('NOW()'),
    //         ],
    //     ];
    // }
    

    public function getDevelopmentDetail()
    {
        return $this->hasMany(DevelopmentDetail::class, ['development_id' => 'id']);
    }

    public function getExpenses()
    {
        return $this->hasMany(DevelopmentDetail::class, ['development_id' => 'id'])->andOnCondition(['name' => 'expense_type']);
    }

    
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getCreatedByEmp()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }
    public function getVehicleType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'vehicle_type_id']);
    }
    
       // แสดงวันที่สร้าง
       public function viewCreated()
       {
           // return Yii::$app->thaiFormatter->asDate($this->created_at, 'long');
           return Yii::$app->thaiDate->toThaiDate($this->created_at, true, false);
       }
    

    public function getLeader()
    /**
     * Gets query for [[Leader]].
     *
     * @return yii\db\ActiveQuery
     */
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }
    
    public function getAssignedTo()
    {
        return $this->hasOne(Employees::class, ['id' => 'assigned_to']);
    }
    

    public function VehicleTypeName()
    {
        $model = Categorise::find()
            ->where(['name' => 'vehicle_type'])
            ->andWhere(['code' => $this->data_json['vehicle_type']])
            ->one();
        return $model ? $model->title : '-';
    }
    // สร้างการอนุมัติ
    public function createApprove()
    {
    // หัวหน้างาน
        $developmentStep1Check = Approve::findOne(['from_id' => $this->id, 'level' => 1, 'name' => 'development']);
        try {
            if (!$developmentStep1Check) {
                $developmentStep1 = $developmentStep1Check ? $developmentStep1Check : new Approve();
                $developmentStep1->from_id = $this->id;
                $developmentStep1->name = 'development';
                $developmentStep1->emp_id = $this->leader_id;
                $developmentStep1->title = 'เห็นชอบ';
                $developmentStep1->data_json = ['label' => 'เห็นชอบ'];
                $developmentStep1->level = 1;
                $developmentStep1->status = 'Pending';
                $developmentStep1->save(false);
                // try {
                    // ส่ง msg ให้ Approve
                    $toUserId = $developmentStep1->employee->user->line_id;
                    $msg = 'ขออนุมัติ';
                    $msg .= "\n" . 'หัวข้อ : ' . $this->topic;
                    $msg .= "\n" . 'วันที่ : ' . ThaiDateHelper::formatThaiDate($this->date_start, 'long', 'short');
                    $msg .= "\n" . 'ถึงวันที่ : ' . ThaiDateHelper::formatThaiDate($this->date_end, 'long', 'short');
                    $msg .= "\n" . 'ผู้ขอ : ' . $this->createdByEmp->fullname;
                    LineMsg::sendMsg($toUserId,$msg);
                // } catch (\Throwable $th) {

                // }
            }
        } catch (\Throwable $th) {

        }

        try {
             //หัวหน้ากลุ่มงานเห็นชอบ
            $developmentStep2Check = Approve::findOne(['from_id' => $this->id, 'level' => 2, 'name' => 'development']);
            if (!$developmentStep2Check) {
                $developmentStep2 = $developmentStep2Check ? $developmentStep2Check : new Approve();
                $developmentStep2->from_id = $this->id;
                $developmentStep2->name = 'development';
                $developmentStep2->emp_id = $this->leader_group_id;
                $developmentStep2->title = 'เห็นชอบ';
                $developmentStep2->data_json = ['label' => 'เห็นชอบ'];
                $developmentStep2->level = 2;
                $developmentStep2->status = 'None';
                $developmentStep2->save(false);
            }
        } catch (\Throwable $th) {
        }

        try {
            //ผู้ตรวจสอบ
            $developmentStep3Check = Approve::findOne(['from_id' => $this->id, 'level' => 3, 'name' => 'development']);
            if (!$developmentStep3Check) {
                $developmentStep3 = $developmentStep3Check ? $developmentStep3Check : new Approve();
                $developmentStep3->from_id = $this->id;
                $developmentStep3->name = 'development';
                $developmentStep3->title = 'ตรวจสอบ';
                // $developmentStep3->emp_id = $this->data_json['approve_3'];
                $developmentStep3->data_json = ['label' => 'ผ่าน'];
                $developmentStep3->level = 3;
                $developmentStep3->status = 'None';
               $developmentStep3->save(false);
            }
        } catch (\Throwable $th) {
        }

        //ผู้อำนวยการอนุมัติ
        $director = SiteHelper::viewDirector();
        $developmentStep4Check = Approve::findOne(['from_id' => $this->id, 'level' => 4, 'name' => 'development']);
        // try {
            if (!$developmentStep4Check) {
            
                $developmentStep4 = $developmentStep4Check ? $developmentStep4Check : new Approve();
                $developmentStep4->from_id = $this->id;
                $developmentStep4->name = 'development';
                $developmentStep4->emp_id = $director['id'];
                $developmentStep4->title = 'อนุมัติ';
                $developmentStep4->data_json = ['label' => 'อนุมัติ'];
                $developmentStep4->level = 4;
                $developmentStep4->status = 'None';
                $developmentStep4->save(false);
            }
            // code...
        // } catch (\Throwable $th) {
        // }
    }
    
     //  ภาพทีมคณะกรรมการ
     public function StackMember()
     {
         // try {
         $data = '';
         $data .= '<div class="avatar-stack">';
         foreach (DevelopmentDetail::find()->where(['name' => 'member', 'development_id' => $this->id])->all() as $key => $item) {
             $emp = Employees::findOne(['id' => $item->emp_id]);
             $data .= Html::a(Html::img($emp->ShowAvatar(), ['class' => 'avatar-sm rounded-circle shadow']), ['/me/development-detail/update', 'id' => $item->id, 'title' => '<i class="bi bi-person-circle"></i> กรรมการตรวจรับเข้าคลัง'], ['class' => 'open-modal', 'data' => [
                'size' => 'model-md',
                'bs-toggle' => 'tooltip',
                'bs-placement' => 'top',
                'bs-title' => $emp->fullname
            ]]);
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

public function listStatus()
{
    return [
        'Pending' => 'รออนุมัติ',
        'Pass' => 'ตรวจสอบผ่าน',
        'Approve' => 'อนุมัติ',
        'Reject' => 'ไม่อนุมัติ',
        'Cancel' => 'ยกเลิก',
        'Complete' => 'เสร็จสิ้น',
    ];
}

    public function viewStatus()
    {
       return $this->getStatus($this->status);
    }

    public  function getStatus($status)
    {
        $title = '';
        $color = '';
        $view = '';
        $count = self::find()
            // ->andFilterWhere(['vehicle_type_id' => $this->vehicle_type_id])
            ->andWhere(['status' => $status])->count();
        $total = self::find()->count();
        $data = AppHelper::viewStatus($status);
        $percent = $total > 0 ? ($count / $total * 100) : 0;

        return [
            'total' => $total,
            'count' => $count,
            'percent' => $percent,
            'title' => $data['title'],
            'color' => $data['color'],
            'view' => $data['view']
        ];
    }
    
}
