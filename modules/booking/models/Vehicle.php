<?php

namespace app\modules\booking\models;

use Yii;
use DateTime;
use yii\helpers\Url;
use yii\db\Expression;
use yii\bootstrap5\Html;
use app\models\Categorise;
use app\components\LineMsg;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\am\models\Asset;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\hr\models\Organization;
use app\modules\dms\models\DocumentTags;
use app\modules\booking\models\BookingDetail;
use app\modules\booking\models\VehicleDetail;
use app\modules\booking\models\BookingCarItems;

/**
 * This is the model class for table "vehicle".
 *
 * @property int $id
 * @property string|null $ref
 * @property string $code รหัส
 * @property int $thai_year ปีงบประมาณ
 * @property string $vehicle_type_id ประเภทของรถ general หรือ ambulance
 * @property int $go_type ประเภทการเดินทาง 1 = ไปกลับ, 2 = ค้างคืน
 * @property float|null $oil_price น้ำมันที่เติม
 * @property float|null $oil_liter ปริมาณน้ำมัน
 * @property int|null $document_id ตามหนังสือ
 * @property int|null $owner_id ผู้ดูแลห้องประชุม
 * @property string $urgent ความเร่งด่วน
 * @property string|null $license_plate ทะเบียนยานพาหนะ
 * @property string $location สถานที่ไป
 * @property string $reason เหตุผล
 * @property string $status สถานะ
 * @property string $date_start เริ่มวันที่
 * @property string $time_start เริ่มเวลา
 * @property string $date_end ถึงวันที่
 * @property string $time_end ถึงเวลา
 * @property string|null $driver_id พนักงานขับ
 * @property string $leader_id หัวหน้างานรับรอง
 * @property string $emp_id ผู้ขอ
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class Vehicle extends \yii\db\ActiveRecord
{
    public $q;
    public $q_department;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vehicle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'thai_year', 'vehicle_type_id', 'go_type', 'urgent', 'location', 'reason', 'status', 'date_start', 'time_start', 'date_end', 'time_end', 'leader_id', 'emp_id'], 'required', 'message' => 'ต้องระบุ'],
            [['thai_year', 'go_type', 'document_id', 'owner_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['oil_price', 'oil_liter'], 'number'],
            [['date_start', 'date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at', 'q', 'q_department', 'refer_type'], 'safe'],
            [['ref', 'code', 'vehicle_type_id', 'urgent', 'license_plate', 'location', 'reason', 'status', 'time_start', 'time_end', 'driver_id', 'leader_id', 'emp_id'], 'string', 'max' => 255],
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
            'code' => 'รหัส',
            'thai_year' => 'ปีงบประมาณ',
            'vehicle_type_id' => 'ประเภทของรถ general หรือ ambulance',
            'refer_type' => 'ประเภทของการ refer รถพยาบาล refer,ems,normal',
            'go_type' => 'ประเภทการเดินทาง 1 = ไปกลับ, 2 = ค้างคืน',
            'oil_price' => 'น้ำมันที่เติม',
            'oil_liter' => 'ปริมาณน้ำมัน',
            'document_id' => 'ตามหนังสือ',
            'owner_id' => 'ผู้ดูแลห้องประชุม',
            'urgent' => 'ความเร่งด่วน',
            'license_plate' => 'ทะเบียนยานพาหนะ',
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

    // สถานที่ไห
    public function getLocationOrg()
    {
        return $this->hasOne(Categorise::class, ['code' => 'location'])->andOnCondition(['name' => 'document_org']);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    // ประเภทของการจองรถ
    public function getCarType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'vehicle_type_id']);
    }

    public function getReferType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'refer_type'])->andOnCondition(['name' => 'refer_type']);
    }

    // สถานะ
    public function getVehicleStatus()
    {
        return $this->hasOne(Categorise::class, ['code' => 'status'])->andOnCondition(['name' => 'vehicle_status']);
    }

    public function getvehicleDetails()
    {
        return $this->hasMany(VehicleDetail::class, ['vehicle_id' => 'id']);
    }


    public function getTemplate()
    {
        // ชื่อแบบฟอร์มที่ใช้สำหรับการจัดเก็บ layout
        $formName = 'vehicle_layout_form'; 
        $template = Categorise::find()->where(['name' => $formName])->one();
        if($template){
            return  Url::to(['/dms/documents/show', 'ref' => $template->ref]);
        }else{
            return false;
        }

    }
    // ผู้ขอบริการ
    public function userRequest()
    {
        try {
            $emp = Employees::findOne(['user_id' => $this->created_by]);
        $msg = $emp->departmentName();
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

    // กรณีแสดงช่วงวันที่
    public function showDateRange()
    {
        return ThaiDateHelper::formatThaiDateRange($this->date_start, $this->date_end);  // แสดงผล: 1 - 3 ม.ค. 2568
    }

    // แสดงราชชื่อพนักงานขับรถ
    public function listDriver()
    {
        $arrDrivers = Employees::find()
            ->from('employees e')
            ->leftJoin('auth_assignment a', 'e.user_id = a.user_id')
            ->where(['a.item_name' => 'driver'])
            ->all();
        return ArrayHelper::map($arrDrivers, 'id', function ($model) {
            return $model->fullname;
        });
    }

    public function listReferType()
    {
        $list = Categorise::find()
            ->where(['name' => 'refer_type'])
            ->asArray()
            ->all();
        return ArrayHelper::map($list, 'code', 'title');
    }

    public function listDriverDetails()
    {
        return BookingDetail::find()->where(['name' => 'driver_detail', 'booking_id' => $this->id])->all();
    }

    public function sendMessage($msg = null)
    {
        $message = $msg . $this->reason . 'วันเวลา ' . Yii::$app->thaiFormatter->asDate($this->date_start, 'medium') . ' เวลา' . $this->time_end . ' - ' . $this->time_end;
        
        //ส่ง telegram
        try {

        $response = Yii::$app->telegram->sendMessage('vehicle', $message, [
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
        
        try {

        $data = [];
        foreach ($this->listMembers as $item) {
            if (isset($item->employee->user->line_id)) {
                $lineId = $item->employee->user->line_id;
                LineMsg::sendMsg($lineId, $message);
            }
        }
        // return $data;
                    //code...
        } catch (\Throwable $th) {

        }
        
    }

    public function viewTime()
    {
        return $this->time_start . ' - ' . $this->time_end . ' น.';
    }

    public function showStartTime()
    {
        try {
            $time = $this->time_start;
            $formattedTime = (new DateTime($time))->format('H:i');
            return $formattedTime;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function showEndTime()
    {
        try {
            $time = $this->time_end;
            $formattedTime = (new DateTime($time))->format('H:i');
            return $formattedTime;
        } catch (\Throwable $th) {
            return false;
        }
    }

    // พนักงานขับที่ร้องขอ
    public function reqDriver()
    {
        try {
            return Employees::findOne($this->data_json['req_driver_id']);
        } catch (\Throwable $th) {
            return false;
        }
    }

    // section Relationships
    public function getCar()
    {
        return $this->hasOne(Asset::class, ['license_plate' => 'license_plate']);
    }

    // thai_year
    public function groupYear()
    {
        $year = self::find()
            ->andWhere(['IS NOT', 'thai_year', null])
            ->groupBy(['thai_year'])
            ->orderBy(['thai_year' => SORT_DESC])
            ->all();
        return ArrayHelper::map($year, 'thai_year', 'thai_year');
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
            ->andFilterWhere(['vehicle_type_id' => $this->vehicle_type_id])
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
    

    // public static function getStatus($status)
    // {
    //     $title = '';
    //     $color = '';
    //     $view = '';
    //     $count = Vehicle::find()->where(['status' => $status])->count();
    //     $total = self::find()->count();
    //     switch ($status) {
    //         case 'Pending':
    //             $title = 'รอการอนุมัติ';
    //             $color = 'warning';
    //             $view = '<span class="badge rounded-pill badge-soft-' . $color . ' text-' . $color . ' fs-13 "><i class="fa-solid fa-hourglass-start"></i> ' . $title . '</span>';
    //             break;
    //         case 'Pass':
    //             $title = 'จัดสรร';
    //             $color = 'primary';
    //             $view = '<span class="badge rounded-pill badge-soft-' . $color . ' text-' . $color . ' fs-13 "><i class="fa-solid fa-circle-check"></i> ' . $title . '</span>';
    //             break;
    //         case 'Success':
    //             $title = 'เสร็จสิ้นภารกิจ';
    //             $color = 'primary';
    //             $view = '<span class="badge rounded-pill badge-soft-' . $color . ' text-' . $color . ' fs-13 "><i class="fa-solid fa-circle-check"></i> ' . $title . '</span>';
    //             break;
    //         case 'Approve':
    //             $title = 'ผอ.อนุมัติ';
    //             $color = 'success';
    //             $view = '<span class="badge rounded-pill badge-soft-' . $color . ' text-' . $color . ' fs-13 "><i class="fa-regular fa-star"></i> ' . $title . '</span>';
    //             break;
    //         case 'Cancel':
    //             $title = 'ยกเลิก';
    //             $color = 'secondary';
    //             $view = '<span class="badge rounded-pill badge-soft-' . $color . ' text-' . $color . ' fs-13 "><i class="fa-solid fa-circle-stop"></i> ' . $title . '</span>';
    //             break;
    //         default:
    //             $title = 'ไม่ระบุ';
    //             $color = 'light';
    //             $view = '<span class="badge-soft-' . $color . ' rounded-pill">' . $title . '</span>';
    //             break;
    //     }
    //     return [
    //         'total' => $total,
    //         'count' => $count,
    //         'percent' => $count / $total * 100,
    //         'title' => $title,
    //         'color' => $color,
    //         'view' => $view,
    //         'status' => $status,
    //     ];
    // }

    public function viewCarType()
    {
        try {
            $title = $this->carType?->title ?? '-';
            if ($this->vehicle_type_id == 'ambulance') {
                return $title . ' (<code>' . $this->referType->title . '</code>)';
            } else {
                return $title;
            }
        } catch (\Throwable $th) {
            // throw $th;
        }
    }

    // แสดงรายการาถานะ
    public function ListStatus()
    {
        $model = Categorise::find()
            ->where(['name' => 'vehicle_status'])
            ->asArray()
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
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

    // แสดงรายการประเภทการเดินทาง
    public function viewGoType()
    {
        if ($this->go_type == 1) {
            return 'ไปกลับ';
        } else {
            return 'ค้างคืน';
        }
    }

    public function listUrgent()
    {
        $model = Categorise::find()
            ->where(['name' => 'urgent'])
            ->asArray()
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
    }

    public function viewUrgent()
    {
        return $this->urgent;
    }


         //  ภาพทีมคณะกรรมการ
         public function StackDriver()
         {
             try {
             $data = '';
             $data .= '<div class="avatar-stack">';
             foreach (VehicleDetail::find()->where(['vehicle_id' => $this->id])->all() as $key => $item) {
                 $emp = Employees::findOne(['id' => $item->driver_id]);
                    $data .= Html::img('@web/img/placeholder-img.jpg', ['class' => 'avatar-sm rounded-circle shadow lazyload blur-up',
                         'data' => [
                             'expand' => '-20',
                             'sizes' => 'auto',
                             'src' => $emp->showAvatar()
                         ]]);
             }
             $data .= '</div>';
             return $data;
             } catch (\Throwable $th) {
             }
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
        $items = Asset::find()->andWhere(['AND',
            ['IS NOT', 'license_plate', null],
            ['<>', 'license_plate', ''],
            ['<>', 'license_plate', ' ']])->all();
        return ArrayHelper::map($items, 'license_plate', 'license_plate');
    }

    public function Approve()
    {
        $emp = UserHelper::GetEmployee();
        $department_id = $emp->department;
        $sql = "SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, 
                    t1.id,
                    t1.name as t1name,

                    t1.data_json->>'\$.leader1' as t1_leader,
                    t1.data_json->>'\$.leader1_fullname' as t1_leader_fullname,
                    t2.id,t2.name as t2name,

                    t2.data_json->>'\$.leader1' as t2_leader,
                    t2.data_json->>'\$.leader1_fullname' as t2_leader_fullname,
                    t3.id,t3.name as t3name,
                    t3.data_json->>'\$.leader1' as t3_leader,
                    t3.data_json->>'\$.leader1_fullname' as t3_leader_fullname
                    FROM tree t1
                    JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
                    JOIN tree t3 ON t2.lft BETWEEN t3.lft AND t3.rgt AND t2.lvl = t3.lvl + 1
                    WHERE t1.id  = :id";
        $query = Yii::$app
            ->db
            ->createCommand($sql)
            ->bindValue('id', $department_id)
            ->queryOne();
        if ($query) {
            $leader = Employees::find()->where(['id' => $query['t1_leader']])->one();
            $leaderGroup = Employees::find()->where(['id' => $query['t2_leader']])->one();
            $director = Employees::find()->where(['id' => $query['t3_leader']])->one();

            return [
                'approve_1' => (isset($query['t1_leader']) && $leader) ? [
                    'id' => $query['t1_leader'],
                    'avatar' => $leader ? $leader->getAvatar(false) : '',
                    'fullname' => $leader ? $leader->fullname : '',
                    'position' => $leader ? $leader->positionName() : '',
                    'title' => 'หัวหน้างาน'
                ] : [],
                'approve_2' => ($leaderGroup) ? [
                    'id' => $query['t2_leader'],
                    'avatar' => $leaderGroup ? $leaderGroup->getAvatar(false) : '',
                    'fullname' => $leaderGroup ? $leaderGroup->fullname : '',
                    'position' => $leaderGroup ? $leaderGroup->positionName() : '',
                    'title' => 'หัวหน้ากลุ่มงาน'
                ] : [],
                'approve_3' => ($director) ? [
                    'id' => $query['t3_leader'],
                    'avatar' => $director ? $director->getAvatar(false) : '',
                    'fullname' => $director ? $director->fullname : '',
                    'position' => $director ? $director->positionName() : '',
                    'title' => 'ผู้อำนวยการ'
                ] : []
            ];
        } else {
            // ถ้าเป็นหัวหน้าลาเอง
            $leader = Employees::find()->where(['id' => $emp->id])->one();
            if ($leader) {
                return [
                    'approve_1' => [
                        'id' => $leader->id,
                        'avatar' => $leader->getAvatar(false),
                        'fullname' => $leader->fullname,
                        'position' => $leader->positionName(),
                        'title' => 'หัวหน้างาน'
                    ],
                    'approve_2' => [
                        'id' => $leader->id,
                        'fullname' => $leader->fullname,
                        'position' => $leader->positionName(),
                        'title' => 'หัวหน้ากลุ่มงาน'
                    ],
                    'approve_3' => [
                        'id' => $leader->id,
                        'fullname' => $leader->fullname,
                        'position' => $leader->positionName(),
                        'title' => 'ผู้อำนวยการ'
                    ]
                ];
            } else {
                return [
                    'approve_1' => [],
                    'approve_2' => [],
                    'approve_3' => []
                ];
            }
        }
    }

   
        // รายงานแยกตามเดือน
        public function getChartSummary($name)
        {
            // return $name;
            $arr =  Vehicle::find()
            ->select([
                'thai_year',
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 1 THEN 1 END) AS m1'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 2 THEN 1 END) AS m2'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 3 THEN 1 END) AS m3'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 4 THEN 1 END) AS m4'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 5 THEN 1 END) AS m5'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 6 THEN 1 END) AS m6'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 7 THEN 1 END) AS m7'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 8 THEN 1 END) AS m8'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 9 THEN 1 END) AS m9'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 10 THEN 1 END) AS m10'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 11 THEN 1 END) AS m11'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 12 THEN 1 END) AS m12'),
            ])
            ->leftJoin('vehicle_detail d', 'd.vehicle_id = vehicle.id')
                // ->where(['thai_year' => $this->thai_year, 'vehicle_type_id' => $name])
                ->where(['vehicle_type_id' => $name])
                ->andWhere(['IN','d.status',['Approve']])
                ->andFilterWhere(['thai_year' => $this->thai_year])
                ->groupBy('thai_year')
                ->asArray()
                ->one();
                // กำหนดค่าเริ่มต้น 0 ให้เดือนที่ไม่มีข้อมูล
            for ($i = 1; $i <= 12; $i++) {
                $key = 'm' . $i;
                if (!isset($arr[$key])) {
                    $arr[$key] = 0;
                }
            }
            return $arr;
        }
         // รายงานแยกตามเดือน
         public function getChartSummaryAmbulance($ambulanceType)
         {
             // return $name;
             $arr =  Vehicle::find()
             ->select([
                 'thai_year',
                 new Expression('COUNT(CASE WHEN MONTH(date_start) = 1 THEN 1 END) AS m1'),
                 new Expression('COUNT(CASE WHEN MONTH(date_start) = 2 THEN 1 END) AS m2'),
                 new Expression('COUNT(CASE WHEN MONTH(date_start) = 3 THEN 1 END) AS m3'),
                 new Expression('COUNT(CASE WHEN MONTH(date_start) = 4 THEN 1 END) AS m4'),
                 new Expression('COUNT(CASE WHEN MONTH(date_start) = 5 THEN 1 END) AS m5'),
                 new Expression('COUNT(CASE WHEN MONTH(date_start) = 6 THEN 1 END) AS m6'),
                 new Expression('COUNT(CASE WHEN MONTH(date_start) = 7 THEN 1 END) AS m7'),
                 new Expression('COUNT(CASE WHEN MONTH(date_start) = 8 THEN 1 END) AS m8'),
                 new Expression('COUNT(CASE WHEN MONTH(date_start) = 9 THEN 1 END) AS m9'),
                 new Expression('COUNT(CASE WHEN MONTH(date_start) = 10 THEN 1 END) AS m10'),
                 new Expression('COUNT(CASE WHEN MONTH(date_start) = 11 THEN 1 END) AS m11'),
                 new Expression('COUNT(CASE WHEN MONTH(date_start) = 12 THEN 1 END) AS m12'),
             ])
            //  ->leftJoin('vehicle_detail d', 'd.vehicle_id = vehicle.id')
                 // ->where(['thai_year' => $this->thai_year, 'vehicle_type_id' => $name])

                 ->andWhere(['vehicle_type_id' => 'ambulance'])
                 ->andWhere(['refer_type' => $ambulanceType])
                //  ->where(['vehicle_type_id' => 'ambulance'])
                //  ->andWhere(['IN','d.status',['Approve']])
                 ->andFilterWhere(['thai_year' => $this->thai_year])
                 
                 ->groupBy('thai_year')
                 ->asArray()
                 ->one();
                 // กำหนดค่าเริ่มต้น 0 ให้เดือนที่ไม่มีข้อมูล
             for ($i = 1; $i <= 12; $i++) {
                 $key = 'm' . $i;
                 if (!isset($arr[$key])) {
                     $arr[$key] = 0;
                 }
             }
             return $arr;
         }

         public function departmentSummary()
         {
            $query = Vehicle::find()
                ->select(['d.name', 'COUNT(v.id) as total'])
                ->from(['v' => Vehicle::tableName()])
                ->leftJoin(['e' => Employees::tableName()], 'e.id = v.emp_id')
                ->leftJoin(['d' => Organization::tableName()], 'd.id = e.department')
                ->where(['IN','v.status', ['Approve','Pass','Success']])
                ->andFilterWhere(['thai_year' => $this->thai_year])
                ->groupBy('d.id')
                ->orderBy(['total' => SORT_ASC]);

            $result = $query->asArray()->all();
            return $result;
         }

         public function carSummary()
         {
            $vehicles = Vehicle::find()
                    ->select([
                        'license_plate',
                        new Expression('COUNT(id) AS total'),
                    ])
                    ->groupBy('license_plate')
                    ->where(['IN','status', ['Approve','Pass','Success']])
                    ->asArray()
                    ->all();

                    return $vehicles;
         }

           // รายงานค่าใช้จ่ายรถ
           public function getPriceSummary()
           {
               // return $name;
               $arr = Vehicle::find()
                   ->select([
                       'thai_year',
                       new Expression('SUM(CASE WHEN MONTH(d.date_start) = 1 THEN d.oil_price ELSE 0 END) AS m1'),
                       new Expression('SUM(CASE WHEN MONTH(d.date_start) = 2 THEN d.oil_price ELSE 0 END) AS m2'),
                       new Expression('SUM(CASE WHEN MONTH(d.date_start) = 3 THEN d.oil_price ELSE 0 END) AS m3'),
                       new Expression('SUM(CASE WHEN MONTH(d.date_start) = 4 THEN d.oil_price ELSE 0 END) AS m4'),
                       new Expression('SUM(CASE WHEN MONTH(d.date_start) = 5 THEN d.oil_price ELSE 0 END) AS m5'),
                       new Expression('SUM(CASE WHEN MONTH(d.date_start) = 6 THEN d.oil_price ELSE 0 END) AS m6'),
                       new Expression('SUM(CASE WHEN MONTH(d.date_start) = 7 THEN d.oil_price ELSE 0 END) AS m7'),
                       new Expression('SUM(CASE WHEN MONTH(d.date_start) = 8 THEN d.oil_price ELSE 0 END) AS m8'),
                       new Expression('SUM(CASE WHEN MONTH(d.date_start) = 9 THEN d.oil_price ELSE 0 END) AS m9'),
                       new Expression('SUM(CASE WHEN MONTH(d.date_start) = 10 THEN d.oil_price ELSE 0 END) AS m10'),
                       new Expression('SUM(CASE WHEN MONTH(d.date_start) = 11 THEN d.oil_price ELSE 0 END) AS m11'),
                       new Expression('SUM(CASE WHEN MONTH(d.date_start) = 12 THEN d.oil_price ELSE 0 END) AS m12'),
                   ])
                   ->leftJoin('vehicle_detail d', 'd.vehicle_id = vehicle.id')
                   ->andWhere(['NOT IN', 'vehicle.status', ['Pending', 'Cancel']])
                   ->andFilterWhere(['vehicle.thai_year' => $this->thai_year])
                   ->groupBy('thai_year')
                   ->asArray()
                   ->one();
                   
               // กำหนดค่าเริ่มต้น 0 ให้เดือนที่ไม่มีข้อมูล
               for ($i = 1; $i <= 12; $i++) {
                   $key = 'm' . $i;
                   if (!isset($arr[$key])) {
                       $arr[$key] = 0;
                   }
               }
               return $arr;
           }
           

           public function sendMessageTelegram()
           {
              //ส่งการแจ้งเตือนทาง Telegram

                 $message = <<<MSG
                        📌 <b>{$this->reason}</b>\n
                        🧑‍💼 <b>ผู้ขอ:</b> นายสมชาย ใจดี\n
                        📍 <b>สถานที่:</b> ศาลากลางจังหวัด\n
                        📅 <b>วันที่:</b> 1 มิ.ย. 2567\n
                        🕒 <b>เวลา:</b> 08:30 - 16:00 น.\n
                        🚗 <b>ประเภทรถ:</b> รถตู้\n
                        // 🔗 <a href="https://your-app.com/booking/{$id}">ดูรายละเอียด</a>
                        MSG;
    
                         $response = Yii::$app->telegram->sendMessage('book_vehicle', $message, [
                        'parse_mode' => 'HTML',
                        'disable_web_page_preview' => true,
                        ]);
           }
         
        
}
