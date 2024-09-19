<?php

namespace app\modules\lm\models;

use app\components\UserHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "leave".
 *
 * @property int $id
 * @property string|null $leave_type_id ประเภทการขอลา
 * @property float|null $leave_time_type ประเภทการลา
 * @property float|null $days_off จำนวนวัน
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
            [['leave_time_type', 'days_off'], 'number'],
            [['data_json', 'date_start', 'date_end', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
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
            'days_off' => 'จำนวนวัน',
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
        public function getLeaveType()
        {
            return $this->hasOne(LeaveType::class, ['code' => 'leave_type_id']);
        }



    public function getAvatar($empid, $msg = '')
    {
        try {
            $employee = Employees::find()->where(['id' => $empid])->one();

            return [
                'avatar' => $employee->getAvatar(false, $msg),
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

          // ผู้ขอลา
    public  function CreateBy($msg = null)
    {
        $id = $this->created_by ? $this->created_by : null;
        return  UserHelper::GetEmployee($this->created_by);
    }

        public function Approve()
        {
            $emp = $this->CreateBy();
            $department_id = $emp->department;
            $sql = "SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, 
                    t1.id,
                    t1.name as t1name,

                    t1.data_json->>'$.leader1' as t1_leader,
                    t1.data_json->>'$.leader1_fullname' as t1_leader_fullname,
                    t2.id,t2.name as t2name,

                    t2.data_json->>'$.leader1' as t2_leader,
                    t2.data_json->>'$.leader1_fullname' as t2_leader_fullname,
                    t3.id,t3.name as t3name,
                    t3.data_json->>'$.leader1' as t3_leader,
                    t3.data_json->>'$.leader1_fullname' as t3_leader_fullname
                    FROM tree t1
                    JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
                    JOIN tree t3 ON t2.lft BETWEEN t3.lft AND t3.rgt AND t2.lvl = t3.lvl + 1
                    WHERE t1.id  = :id";
            $query = Yii::$app->db->createCommand($sql)
            ->bindValue('id', $department_id)
            ->queryOne();
if($query){

    $leader1 =  Employees::find()->where(['id' => $query['t1_leader']])->one();
    $leader2 =  Employees::find()->where(['id' => $query['t2_leader']])->one();
    $leader3 =  Employees::find()->where(['id' => $query['t3_leader']])->one();

    return 
    [
        'leader1' => isset($query['t1_leader']) ? [
            'id' => $query['t1_leader'],
            'fullname' => $leader1->fullname,
            'position' => $leader1->positionName(),
            'title' => 'หัวหน้างาน'
            ] : [],
            'leader2' => [
                'id' => $query['t2_leader'],
                'fullname' => $leader2->fullname,
                'position' => $leader2->positionName(),
                'title' => 'หัวหน้ากลุ่มงาน'
            ],
            'leader3' => [
                'id' => $query['t3_leader'],
                'fullname' => $leader3->fullname,
                'position' => $leader3->positionName(),
                'title' => 'ผู้อำนวยการ'
                ]
            ];
        }else{
//ถ้าเป็นหัวหน้าลาเอง
$leader =  Employees::find()->where(['id' => $emp->id])->one();
            return 
            [
                'leader1' => [
                    'id' => $leader->id,
                    'fullname' => $leader->fullname,
                    'position' => $leader->positionName(),
                    'title' => 'หัวหน้างาน'
                    ],
                    'leader2' => [
                        'id' => $leader->id,
                    'fullname' => $leader->fullname,
                    'position' => $leader->positionName(),
                        'title' => 'หัวหน้ากลุ่มงาน'
                    ],
                    'leader3' => [
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
            $model = Organization::find()->where(['id' => $this->department])->one();
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
}
