<?php

namespace app\modules\booking\models;

use Yii;
use DateTime;
use yii\db\Expression;
use app\models\Categorise;
use app\components\LineMsg;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\dms\models\DocumentTags;
use app\modules\booking\models\BookingDetail;
use app\modules\booking\models\BookingCarItems;

/**
 * This is the model class for table "vehicle".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $code
 * @property int $thai_year ปีงบประมาณ
 * @property int $go_type ประเภทการเดินทาง 1 = ไปกลับ, 2 = ค้างคืน
 * @property float|null $oil_price น้ำมันที่เติม
 * @property float|null $oil_liter ปริมาณน้ำมัน
 * @property string|null $car_type_id ประเภทของรถ general หรือ ambulance
 * @property int|null $document_id ตามหนังสือ
 * @property int|null $owner_id ผู้ดูแลห้องประชุม
 * @property string|null $urgent ความเร่งด่วน
 * @property string|null $license_plate ทะเบียนยานพาหนะ
 * @property string|null $location สถานที่ไป
 * @property string|null $reason เหตุผล
 * @property string $status สถานะ
 * @property string $date_start เริ่มวันที่
 * @property string $time_start เริ่มเวลา
 * @property string $date_end ถึงวันที่
 * @property string $time_end ถึงเวลา
 * @property string|null $driver_id พนักงานขับ
 * @property string|null $leader_id หัวหน้างานรับรอง
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
            [['thai_year', 'go_type', 'status', 'date_start', 'time_start', 'date_end', 'time_end', 'emp_id'], 'required'],
            [['thai_year', 'go_type', 'document_id', 'owner_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['oil_price', 'oil_liter'], 'number'],
            [['date_start', 'date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['ref', 'code', 'car_type_id', 'urgent', 'license_plate', 'location', 'reason', 'status', 'time_start', 'time_end', 'driver_id', 'leader_id', 'emp_id'], 'string', 'max' => 255],
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
            'code' => 'Code',
            'thai_year' => 'ปีงบประมาณ',
            'go_type' => 'ประเภทการเดินทาง 1 = ไปกลับ, 2 = ค้างคืน',
            'oil_price' => 'น้ำมันที่เติม',
            'oil_liter' => 'ปริมาณน้ำมัน',
            'car_type_id' => 'ประเภทของรถ general หรือ ambulance',
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

    


    //แสดงราชชื่อพนักงานขับรถ
    public function listDriver()
    {
        $arrDrivers = Employees::find()
                ->from('employees e')
                ->leftJoin('auth_assignment a', 'e.user_id = a.user_id')
                ->where(['a.item_name' => 'driver'])
                ->all();
return  ArrayHelper::map($arrDrivers,'id',function($model){
    return $model->fullname;
});

    }

    
    public function listDriverDetails()
    {
    return BookingDetail::find()->where(['name' => 'driver_detail','booking_id' => $this->id])->all();
    }


    public function sendMessage($msg)
    {
        $message = $msg.$this->reason. 'วันเวลา '.Yii::$app->thaiFormatter->asDate($this->date_start, 'medium').' เวลา' .$this->time_end.' - '. $this->time_end;
       $data =[];
        foreach($this->listMembers as $item){
            if(isset($item->employee->user->line_id)){
                $lineId = $item->employee->user->line_id;
                    LineMsg::sendMsg($lineId, $message);
            }
        }
        return $data;
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

    //พนักงานขับที่ร้องขอ
    public function reqDriver()
    {
        try {
            return Employees::findOne( $this->data_json['req_driver_id']);
        } catch (\Throwable $th) {
            return false;
        }
    }
    
        // section Relationships
        public function getCar()
        {
            return $this->hasOne(BookingCarItems::class, ['license_plate' => 'license_plate']);
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



//แสดงรายการาถานะ
    public function ListStatus()
    {
        $model = Categorise::find()
            ->where(['name' => 'driver_service_status'])
            ->asArray()
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
    }

    public function ListUrgent()
    {
        $model = Categorise::find()
            ->where(['name' => 'urgent'])
            ->asArray()
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
    }

    public function listDocument()
    {
        $me = UserHelper::GetEmployee();
        $document = DocumentTags::find()->where(['tag_id' => 1,'name' => 'comment'])->all();
        return ArrayHelper::map($document, 'id',function($model){
            return $model->document->topic;
        });
    }

    //แสดงรายการทะยานพาหนะ
    public function ListCarItems()
    {
            $items = BookingCarItems::find()->all();
            return ArrayHelper::map($items, 'license_plate','license_plate');
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
    
}
