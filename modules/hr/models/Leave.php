<?php

namespace app\modules\hr\models;

use Yii;
use yii\helpers\Html;
use yii\db\Expression;
use app\models\Approve;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\hr\models\Organization;

/**
 * This is the model class for table "leave".
 *
 * @property int $id
 * @property string|null $leave_type_id ประเภทการขอลา
 * @property float|null $leave_time_type ประเภทการลา
 * @property float|null $sum_days จำนวนวัน
 * @property string|null $data_json
 * @property string|null $date_start วันที่ลา
 * @property string|null $date_end ถึงวันที่
 * @property string|null $status สถานะ
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class Leave extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $q;

    public $reason;
    public $data_start_th;
    public $data_end_th;
    public $q_department;

    public static function tableName()
    {
        return 'leave';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['leave_time_type', 'sum_days'], 'number'],
            [['on_holidays', 'data_json', 'date_start', 'date_end', 'created_at', 'updated_at', 'deleted_at', 'emp_id', 'q','q_department'], 'safe'],
            [['thai_year', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['leave_type_id', 'status'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'leave_type_id' => 'ประเภทการขอลา',
            'leave_time_type' => 'ประเภทการลา',
            'sum_days' => 'จำนวนวัน',
            'data_json' => 'Data Json',
            'date_start' => 'วันที่ลา',
            'date_end' => 'ถึงวันที่',
            'status' => 'สถานะ',
            'thai_year' => 'ปีงบประมาณ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
            'q' => 'คำค้นหา'
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

    public static function find()
    {
        return new LeaveQuery(get_called_class());
    }

    public function afterFind()
    {
        try {
            $this->reason = isset($this->data_json['reason']) ? $this->data_json['reason'] : '';
        } catch (\Throwable $th) {
        }

        // $this->asset_name = isset($this->data_json['name']) ? $this->data_json['name'] : '-';

        parent::afterFind();
    }

    // บันทึกผู้ตรวจสอบ
    // public function createLeaveStep()
    // {
    //     $leaveStep1 = LeaveStep::findOne(['leave_id' => $this->id, 'level' => 1]);
    //     if (!$leaveStep1)
    //         $leaveStep1 = new LeaveStep();
    //     $leaveStep1->leave_id = $this->id;
    //     $leaveStep1->emp_id = $this->data_json['leader'];
    //     $leaveStep1->title = 'หัวหน้าหน่วยงาน';
    //     $leaveStep1->level = 1;
    //     $leaveStep1->status = 'Pending';
    //     $leaveStep1->save();

    //     $leaveStep2 = LeaveStep::findOne(['leave_id' => $this->id, 'level' => 2]);
    //     if (!$leaveStep2)
    //         $leaveStep2 = new LeaveStep();
    //     $leaveStep2->leave_id = $this->id;
    //     $leaveStep2->emp_id = $this->data_json['leader_group'];
    //     $leaveStep2->title = 'ตรวจสอบ';
    //     $leaveStep2->level = 2;
    //     $leaveStep2->status = 'Pending';
    //     $leaveStep2->save();

    //     $director = SiteHelper::viewDirector();
    //     $leaveStep3 = LeaveStep::findOne(['leave_id' => $this->id, 'level' => 3]);
    //     if (!$leaveStep3)
    //         $leaveStep3 = new LeaveStep();
    //     $leaveStep3->leave_id = $this->id;
    //     $leaveStep3->emp_id = $director['id'];
    //     $leaveStep3->title = 'ผู้อำนวยการ';
    //     $leaveStep3->level = 3;
    //     $leaveStep3->status = 'Pending';
    //     $leaveStep3->save();
    // }

    public function createApprove()
    {
        $leaveStep1 = Approve::findOne(['from_id' => $this->id, 'level' => 1, 'name' => 'leave']);
        if (!$leaveStep1)
        $leaveStep1 = new Approve();
        $leaveStep1->from_id = $this->id;
        $leaveStep1->name = 'leave';
        $leaveStep1->emp_id = $this->data_json['approve_1'];
        $leaveStep1->title = 'เห็นชอบ';
        $leaveStep1->data_json = ['topic' => 'เห็นชอบ'];
        $leaveStep1->level = 1;
        $leaveStep1->status = 'Pending';
        $leaveStep1->save(false);

        $leaveStep2 = Approve::findOne(['from_id' => $this->id, 'level' => 2, 'name' => 'leave']);
        if (!$leaveStep2)
        $leaveStep2 = new Approve();
        $leaveStep2->from_id = $this->id;
        $leaveStep2->name = 'leave';
        $leaveStep2->emp_id = $this->data_json['approve_2'];
        $leaveStep2->title = 'เห็นชอบ';
        $leaveStep2->data_json = ['topic' => 'เห็นชอบ'];
        $leaveStep2->level = 2;
        $leaveStep2->status = 'None';
        $leaveStep2->save(false);

        $leaveStep3 = Approve::findOne(['from_id' => $this->id, 'level' => 3, 'name' => 'leave']);
        if (!$leaveStep3)
        $leaveStep3 = new Approve();
        $leaveStep3->from_id = $this->id;
        $leaveStep3->name = 'leave';
        $leaveStep3->title = 'ตรวจสอบ';
        $leaveStep3->data_json = ['topic' => 'ผ่าน'];
        $leaveStep3->level = 3;
        $leaveStep3->status = 'None';
        $leaveStep3->save(false);

        $director = SiteHelper::viewDirector();
        $leaveStep4 = Approve::findOne(['from_id' => $this->id, 'level' => 4, 'name' => 'leave']);
        if (!$leaveStep4)
        $leaveStep4 = new Approve();
        $leaveStep4->from_id = $this->id;
        $leaveStep4->name = 'leave';
        $leaveStep4->emp_id = $director['id'];
        $leaveStep4->title = 'อนุมัติ';
        $leaveStep4->data_json = ['topic' => 'อนุมัติ'];
        $leaveStep4->level = 4;
        $leaveStep4->status = 'None';
        $leaveStep4->save(false);
    }

    public function listApprove()
    {
        return Approve::find()->where(['from_id' => $this->id])->orderBy(['level' => SORT_ASC])->all();
    }

    // section Relationships
    public function getLeaveType()
    {
        return $this->hasOne(LeaveType::class, ['code' => 'leave_type_id'])->andOnCondition(['name' => 'leave_type']);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getLeaveStatus()
    {
        return $this->hasOne(Categorise::class, ['code' => 'status'])->andOnCondition(['name' => 'leave_status']);
    }

    public function listLeaveType()
    {
        return ArrayHelper::map(LeaveType::find()->where(['name' => 'leave_type', 'active' => 1])->all(), 'code', 'title');
    }

    public function listStatus()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'leave_status'])->all(), 'code', 'title');
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

    // แสดงข้อมูลวันลาที่เหลือ
    public function leaveCount()
    {
        // ลามาแล้ว
        $lastDays = self::find()
            ->where([
                'emp_id' => $this->emp_id,
                'thai_year' => $this->thai_year,
                'leave_type_id' => $this->leave_type_id
            ])
            ->sum('sum_days');
    }
    
    // สรุปการลารายบุคคล
    public function leaveEmpSummary()
    {
        // $sql = "SELECT 
        //         lt.code,
        //         lt.title,
        //         COALESCE(SUM(l.sum_days), 0) AS total
        //         FROM 
        //             categorise lt
        //         LEFT JOIN 
        //             `leave` l ON lt.code = l.leave_type_id 
        //                     AND l.emp_id = 8 
        //                     AND l.thai_year = :thai_year
        //         WHERE 
        //             lt.name = 'leave_type' 
        //             AND lt.code IN ('LT1', 'LT2', 'LT3','LT4')
        //         GROUP BY 
        //             lt.code, lt.title;";
        $sql = "WITH summary AS (
                SELECT 
                    IFNULL(SUM(CASE WHEN l.leave_type_id = 'LT1' THEN l.sum_days ELSE 0 END), 0) AS last_lt1,
                    IFNULL(SUM(CASE WHEN l.leave_type_id = 'LT3' THEN l.sum_days ELSE 0 END), 0) AS last_lt3,
                    IFNULL(SUM(CASE WHEN l.leave_type_id = 'LT4' THEN l.sum_days ELSE 0 END), 0) AS last_lt4
                FROM 
                    `leave` l
                WHERE 
                    l.emp_id = :emp_id 
                    AND thai_year = :thai_year 
                    AND l.status = 'Allow'
                )
                SELECT * FROM summary";

        return Yii::$app
            ->db
            ->createCommand($sql)
            ->bindValue(':thai_year', $this->thai_year)
            ->bindValue(':emp_id',$this->emp_id)
            ->queryOne();
    }

    public function getAvatar($empid, $msg = '')
    {
        try {
            $employee = Employees::find()->where(['id' => $this->emp_id])->one();
            $msg = '<span class="badge rounded-pill badge-soft-primary text-primary fs-13 "><i class="bi bi-exclamation-circle-fill"></i> ' . $this->leaveType->title . '</span> เขียนเมื่อ' . $this->createdDays();
            // $msg = $employee->departmentName();
            return [
                'avatar' => $employee->getAvatar(false, $msg),
                // 'avatar' => $employee->getAvatar(false,$this->viewLeaveType()),
                'department' => $employee->departmentName(),
                'fullname' => $employee->fullname,
                'position_name' => $employee->positionName(),
                // 'product_type_name' => $this->data_json['product_type_name']
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'department' => '',
                'fullname' => '',
                'position_name' => '',
                'product_type_name' => ''
            ];
        }
    }


    // แสดงสถานะในรูปแบบสี
    public function viewStatus()
    {
               switch ($this->status) {
            case "Pending":
             $color = 'warning';
             $icon = '<i class="bi bi-hourglass-split"></i>';
              break;
            case "Allow":
                $color = 'success';
                $icon = '<i class="bi bi-check-circle-fill text-success"></i>';
              break;
              case "ReqCancel":
                $color = 'warning';
                $icon = '<i class="bi bi-exclamation-triangle text-danger"></i>';
              break;
              case "Cancel":
                $color = 'secondary';
                $icon = '<i class="bi bi-exclamation-circle-fill text-secondary"></i>';
              break;
              case "Checking":
                $color = 'warning';
                $icon = '<i class="fa-solid fa-magnifying-glass"></i>';
              break;
              case "Reject":
                $color = 'danger';
                $icon = '<i class="bi bi-exclamation-circle-fill text-danger"></i>';
              break;
              
            default:
            $color = '';
            $icon = '';
          }
          
          return '<span class="badge rounded-pill badge-soft-'.$color.' text-primary fs-13 ">'.$icon.' '.$this->leaveStatus->title.'</span>';
          
    }
    //นับเวลาที่ผ่านมาแล้ว
    public function createdDays()
    {
            return AppHelper::timeDifference($this->created_at);
    }
    
        //แสดงวันที่สร้าง
        public function viewCreated()
        {
            return Yii::$app->thaiFormatter->asDate($this->created_at, 'long');
        }
        
    
    public function viewLeaveType()
    {
       return '<span class="badge rounded-pill badge-soft-primary text-primary fs-13 "><i class="bi bi-exclamation-circle-fill"></i> ' . $this->leaveType->title . '</span> เนื่องจาก ' . $this->reason;
    }

    //ผู้ตรวจสอบการลา
    public function checkerName($level)
    {
        $check = Approve::find()->where(['from_id' => $this->id, 'level' => $level])->andWhere(['IS NOT', 'emp_id', null])->one();
        if ($check) {
            return [
                'fullname' => $check->employee->fullname,
                'position' => $check->employee->positionName(),
                'approve_date' => isset($this->data_json['approve_date']) ? Yii::$app->thaiFormatter->asDate($this->data_json['approve_date'], 'long') : '',
            ];
        }else{
            return [
                'fullname' => '',
                'position' => '',
                'approve_date'=>''
            ];
        }
    }
    //  ภาพทีมผูตรวจสอบ
    public function stackChecker()
    {
        // try {
        $data = '';
        $data .= '<div class="avatar-stack">';
        foreach (Approve::find()->where(['from_id' => $this->id])->andWhere(['!=', 'status', 'None'])->all() as $key => $item) {
           try {
            $data .= Html::a(
                Html::img('@web/img/placeholder-img.jpg', ['class' => 'avatar-sm rounded-circle shadow lazyload blur-up'.($item->status == 'Reject' ? ' border-danger' : null),
                    'data' => [
                        'expand' => '-20',
                        'sizes' => 'auto',
                        'src' => $item->employee->showAvatar()
                    ]]),
                ['/purchase/order-item/update', 'id' => $item->id, 'name' => 'committee', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'],
                [
                    'class' => 'open-modal',
                    'data' => [
                        'size' => 'modal-md',
                        'bs-trigger' => 'hover focus',
                        'bs-toggle' => 'popover',
                        'bs-placement' => 'top',
                        'bs-title' => $item->title,
                        'bs-html' => 'true',
                        'bs-content' => $item->employee->fullname . '<br>' . ($item->status == 'Reject' ? '<i class="bi bi-exclamation-circle text-danger"></i> ไม่' : '<i class="bi bi-check-circle-fill text-primary"></i> ').$item->data_json['topic']
                    ]
                ]
            );
           
            } catch (\Throwable $th) {
                //throw $th;
            }
    
        }
        $data .= '</div>';
        return $data;
    }

    // มอบหมายงานให้

    public function leaveWorkSend()
    {
        try {
            $model = Employees::find()->where(['id' => $this->data_json['leave_work_send_id']])->one();
            $msg = 'ผู้ปฏิบัติหน้าที่แทน';
            return [
                'fullname' => $model->fullname,
                'position' => $model->positionName(),
                'avatar' => $model->getAvatar(false, $msg),
            ];
        } catch (\Throwable $th) {
            return [
                'fullname' => '',
                'position' => '',
                'avatar' => '',
            ];
        }
    }

    public function statusProcess()
    {
        $total = Approve::find()->where(['from_id' => $this->id])->count();
        $accept = Approve::find()->where(['from_id' => $this->id, 'status' => 'Approve'])->count();
        if ($total > 0) {
            $percentageCompleted = ($accept / $total) * 100;
        } else {
            $percentageCompleted = 0;  // หรือค่าเริ่มต้นที่เหมาะสมเมื่อไม่มีข้อมูล
        }

        return number_format($percentageCompleted, 0);
    }

    public function CreateBy($msg = null)
    {
        $id = $this->created_by ? $this->created_by : null;
        return UserHelper::GetEmployee($this->created_by);
    }

    public function Approve()
    {
        $emp = $this->CreateBy();
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
                'approve_1' => isset($query['t1_leader']) ? [
                    'id' => $query['t1_leader'],
                    'avatar' => $leader->getAvatar(false),
                    'fullname' => $leader->fullname,
                    'position' => $leader->positionName(),
                    'title' => 'หัวหน้างาน'
                ] : [],
                'approve_2' => [
                    'id' => $query['t2_leader'],
                    'avatar' => $leader->getAvatar(false),
                    'fullname' => $leaderGroup->fullname,
                    'position' => $leaderGroup->positionName(),
                    'title' => 'หัวหน้ากลุ่มงาน'
                ],
                'approve_3' => [
                    'id' => $query['t3_leader'],
                    'avatar' => $director->getAvatar(false),
                    'fullname' => $director->fullname,
                    'position' => $director->positionName(),
                    'title' => 'ผู้อำนวยการ'
                ]
            ];
        } else {
            // ถ้าเป็นหัวหน้าลาเอง
            $leader = Employees::find()->where(['id' => $emp->id])->one();
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
        }
    }

    public function leader()
    {
        $userCreate = $this->CreateBy();
        $model = Organization::find()->where(['id' => $userCreate->department])->one();
        $employee = self::find()->where(['id' => $model->data_json['leader1']])->one();
    }

    public function Avatar($id)
    {
        try {
            $emp = Employees::find()->where(['id' => $id])->one();

            return [
                'avatar' => $emp->getAvatar(false),
                'department' => $emp->departmentName()
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'department' => ''
            ];
        }
    }

    public function showStatus()
    {
        $leaveStep = Approve::find()->where(['from_id' => $this->id])->andWhere(['!=', 'status', 'None'])->orderBy(['level' => SORT_DESC])->one();

        $color = 'primary';
        $statusName = '';
        if ($leaveStep) {
            $status = $leaveStep->status == 'Approve' ? '<i class="bi bi-check-circle fw-semibold text-success"></i> ผ่าน' : '<i class="bi bi-exclamation-circle-fill text-danger"></i> ไม่ผ่าน';
            // $title = $leaveStep->title . ' ' . $status;
            $title = $leaveStep->title;
            $color = '';
        } else {
            $status = '';
            $title = '';
            $color = '';
        }

        return '<div class="d-flex justify-content-between">
                            <span class="text-muted mb-2 fs-13">
                                <span class="badge rounded-pill badge-soft-warning text-primary fs-13 ">' . $title . '</span>
                                <span class="text-' . $color . '"></span>
                            </span>
                            <span class="text-muted mb-0 fs-13">' . $this->statusProcess() . '%</span>
                        </div>

                                                <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-' . $color . '" role="progressbar" aria-label="Progress" aria-valuenow="' . $this->statusProcess() . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $this->statusProcess() . '%;">
                            </div>
                        </div>';
                        
    }

    public function listStatusSummary()
    {
        $query = Leave::find();
        $query
            ->select(['categorise.code', 'categorise.title', 'COUNT(leave.id) AS total'])
            ->leftJoin('categorise', 'categorise.code = leave.status')
            ->where(['leave.thai_year' => $this->thai_year])
            ->andFilterWhere(['leave.emp_id' => $this->emp_id])
            ->groupBy('leave.status')
            ->asArray();

        $data = $query->all();
        return $data;
    }

    // นับจำนวนประเภทวันลา
    public function sumLeaveType($type = null)
    {
        $sum = self::find()
            ->where(['thai_year' => $this->thai_year, 'emp_id' => $this->emp_id, 'leave_type_id' => $type, 'status' => 'Allow'])
            ->sum('sum_days');
        if (!$sum) {
            $sum = 0;
        }
        return $sum;
    }

    public function sumLeaveStatus($status = null)
    {
        $sum = self::find()
            ->where(['thai_year' => $this->thai_year, 'emp_id' => $this->emp_id, 'status' => $status])
            ->sum('sum_days');
        if (!$sum) {
            $sum = 0;
        }
        return $sum;
    }

    // นับจำนวนประเภท
    public function countLeaveType($type = null)
    {
        $sum = self::find()
            ->where(['status' => 'Allow'])
            ->andFilterWhere(['leave.thai_year' => $this->thai_year])
            ->andFilterWhere(['emp_id' => $this->emp_id])
            ->andFilterWhere(['leave_type_id' => $type])
            ->count('id');
        if (!$sum) {
            $sum = 0;
        }
        return $sum;
    }

    // นับจำนวนวันลาพักผ่อนคงเหลือ
    public function sumLeavePermission()
    {
        $lP = LeavePermission::find()->where(['thai_year' => $this->thai_year, 'emp_id' => $this->emp_id])->one();
        $leaveDays = 0;
        if ($lP) {
            $leaveDays = ($lP->leave_sum_days - $this->sumLeaveType('LT4'));
        }
        return $leaveDays;
    }

    // นับจำนวนสถานะ
    public function countLeaveStatus($status = null)
    {
        // return $this->emp_id;
        $sum = self::find()
            ->andFilterWhere(['leave.thai_year' => $this->thai_year])
            ->andFilterWhere(['emp_id' => $this->emp_id])
            ->andFilterWhere(['status' => $status])
            ->count('id');
        if (!$sum) {
            $sum = 0;
        }
        return $sum;
    }

    // นับจำนวนวันลาที่ขออนุมัติ
    public function awaitLeave()
    {
        $sum = self::find()
            ->where(['IN', 'ststua', ['Pendind', '']])
            ->andFilterWhere(['leave.thai_year' => $this->thai_year])
            ->andFilterWhere(['emp_id' => $this->emp_id])
            ->andFilterWhere(['status' => $status])
            ->count('id');
        if (!$sum) {
            $sum = 0;
        }
        return $sum;
    }

    // คำนวนวันหยุดคงเหลือ
    public function leaveSumDays()
    {
        $emp = UserHelper::GetEmployee();
        $sql = "SELECT 
                        lp.leave_sum_days,
                        COALESCE(SUM(l.sum_days), 0) AS used_leave
                    FROM 
                        leave_permission lp
                    LEFT JOIN 
                        `leave` l 
                        ON l.emp_id = lp.emp_id 
                        AND l.thai_year = lp.thai_year 
                        AND l.leave_type_id = 'LT4' 
                        AND l.status = 'Allow'
                    WHERE 
                        lp.emp_id = :emp_id
                        AND lp.thai_year = :thai_year
                    GROUP BY 
                        lp.leave_sum_days";
        $query = Yii::$app
            ->db
            ->createCommand($sql)
            ->bindValue(':thai_year', $this->thai_year)
            ->bindValue(':emp_id', $emp->id)
            ->queryOne();
        try {
            return [
                'sum_days' => $query['leave_sum_days'],
                'used_leave' => $query['used_leave']
            ];
        } catch (\Throwable $th) {
            return [
                'sum_days' => 0,
                'used_leave' => 0
            ];
        }
    }

    public function listEmployees()
    {
        $employes = Employees::find()->where(['status' => 1])->all();
        return ArrayHelper::map($employes, 'id', function ($model) {
            return $model->fullname;
        });
    }
}
