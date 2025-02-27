<?php

namespace app\modules\booking\models;

use Yii;
use DateTime;
use yii\db\Expression;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\LineNotify;
use app\components\UserHelper;
use app\modules\am\models\Asset;
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
    public $tags_department;
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
            [['date_start', 'date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at', 'emp_id', 'ambulance_type', 'mileage_start', 'mileage_end','oil_liter','oil_price','owner_id','tags_department'], 'safe'],
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
            'owner_id' => 'ผู้ดูแลห้องประชุม',
            'location' => 'สถานที่ไป',
            'reason' => 'เหตุผล',
            'status' => 'สถานะ',
            'date_start' => 'เริ่มวันที่',
            'time_start' => 'เริ่มเวลา',
            'date_end' => 'ถึงวันที่',
            'time_end' => 'ถึงเวลา',
            'driver_id' => 'พนักงานขับ',
            'leader_id' => 'หัวหน้างานรับรอง',
            'mileage_start' => 'เลขไมล์รถก่อนออกเดินทาง',
            'mileage_end' => 'เลขไมล์หลังเดินทาง',
            'oil_liter' => 'จำนวนลิตร',
            'oil_price' => 'ราคาที่เติม',
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
        return $this->hasOne(Asset::class, ['license_plate' => 'license_plate']);
    }

    public function getDriver()
    {
        return $this->hasOne(Employees::class, ['id' => 'driver_id']);
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

    public function getOwner()
    {
        return $this->hasOne(Employees::class, ['id' => 'owner_id']);
    }


    
    public function getBookingStatus()
    {
        return $this->hasOne(Categorise::class, ['code' => 'status'])->andOnCondition(['name' => 'driver_service_status']);
    }

    //สมาชิกที่จะเข้าร่วมประชุม
    public function getlistMembers()
    {
        return $this->hasMany(BookingDetail::class, ['booking_id' => 'id'])->andOnCondition(['name' => 'meeting_menber']);
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
                    LineNotify::sendMsg($lineId, $message);
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

    // การขอใช้งานรถทั่วไปของหน่วยงานต่าง ๆ 10 อันดับ
    public function TopTenDriverService()
    {
        $querys = self::find()
            ->select([
                'b.id',
                'b.thai_year',
                'b.car_type',
                'b.reason',
                'b.emp_id',
                'fullname' => new Expression("CONCAT(e.fname, ' ', e.lname)"),
                'd.name',
                'total' => new Expression('COUNT(b.id)')
            ])
            ->from('booking b')
            ->leftJoin('employees e', 'b.emp_id = e.id')
            ->leftJoin('tree d', 'e.department = d.id')
            ->where([
                'b.name' => 'driver_service',
                'b.car_type' => 'general'
            ])
            ->groupBy('e.department')
            ->orderBy(['total' => SORT_DESC])
            ->asArray()
            ->limit(10)
            ->all();

        $total = [];
        $categories = [];
        foreach ($querys as $query) {
            $total[] = $query['total'];
            $categories[] = $query['name'];
        }

        return [
            'categorise' => $categories,
            'total' => $total
        ];
    }


    public function showDriver()
    {
        try {
            $id = $this->driver_id;
        $emp = Employees::findOne($id);
        return $emp->getAvatar(false);
        } catch (\Throwable $th) {
          return false;
        }
       
    }
    
    public function showStatus()
    {
        try {
        return $this->bookignStatus->title;
        } catch (\Throwable $th) {
          return false;
        }
       
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

    public function listAccessoryUse()
    {
        try {
            $datas = $this->data_json['accessory'];
            $item = '<ul>';
            foreach($datas as $data){
                $item .= '<li>'.$data.'</li>';
                
            }
            $item .= '</ul>';
            return $item;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function statusColor()
    {
        switch ($this->status) {
            case 'pending':
                return 'warning';
                break;
            case 'approve':
                return 'success';
                break;
            case 'cancel':
                return 'danger';
                break;
            default:
                return 'secondary';
                break;
        }
    }

    public function viewStatus()
    {
      switch ($this->status) {
        case 'pending':
          return '<span class="badge rounded-pill text-bg-'.$this->statusColor().'">รออนุมัติ</span>';
          break;
        case 'approve':
          return '<span class="badge rounded-pill text-bg-'.$this->statusColor().'">อนุมัติ</span>';
          break;
        case 'cancel':
          return '<span class="badge rounded-pill text-bg-'.$this->statusColor().'">ยกเลิก</span>';
          break;
        default:
          return '<span class="badge rounded-pill text-bg-'.$this->statusColor().'">ไม่ระบุ</span>';
          break;
      }
    }
}
