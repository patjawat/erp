<?php

namespace app\modules\booking\models;

use Yii;
use yii\db\Expression;
use app\models\Categorise;
use app\components\LineMsg;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\modules\booking\models\Room;
use app\modules\hr\models\Employees;
use app\modules\booking\models\MeetingDetail;

/**
 * This is the model class for table "meeting".
 *
 * @property int $id
 * @property string|null $ref
 * @property string $code รหัส
 * @property string $room_id รหัส
 * @property string $title หัวข้อการประชุ
 * @property string $date_start เริ่มวันที่
 * @property string $date_end ถึงวันที่
 * @property string $time_start เริ่มเวลา
 * @property string $time_end ถึงเวลา
 * @property int $thai_year ปีงบประมาณ
 * @property int|null $document_id ตามหนังสือ
 * @property int|null $emp_number จำนวนผู้เข้าร่วมประชุม
 * @property string $urgent ความเร่งด่วน
 * @property string $status สถานะ
 * @property string $emp_id ผู้ขอ
 * @property string|null $data_json json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class Meeting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $q;
    public $date_filter;
    public static function tableName()
    {
        return 'meeting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'room_id', 'title', 'date_start', 'date_end', 'time_start', 'time_end', 'thai_year', 'urgent', 'status', 'emp_id'], 'required'],
            [['date_start', 'date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at','q','date_filter'], 'safe'],
            [['thai_year', 'document_id', 'emp_number', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['ref', 'code', 'room_id', 'title', 'time_start', 'time_end', 'urgent', 'status', 'emp_id'], 'string', 'max' => 255],
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
            'room_id' => 'รหัส',
            'title' => 'หัวข้อการประชุ',
            'date_start' => 'เริ่มวันที่',
            'date_end' => 'ถึงวันที่',
            'time_start' => 'เริ่มเวลา',
            'time_end' => 'ถึงเวลา',
            'thai_year' => 'ปีงบประมาณ',
            'document_id' => 'ตามหนังสือ',
            'emp_number' => 'จำนวนผู้เข้าร่วมประชุม',
            'urgent' => 'ความเร่งด่วน',
            'status' => 'สถานะ',
            'emp_id' => 'ผู้ขอ',
            'data_json' => 'json',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

    // ห้องประชุม
    public function getRoom()
    {
        return $this->hasOne(Room::class, ['code' => 'room_id'])->andOnCondition(['name' => 'meeting_room']);
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
            return $this->hasMany(MeetingDetail::class, ['meeting_id' => 'id'])->andOnCondition(['name' => 'meeting_menber']);
        }
    

    public function listRooms()
    {
        $model = Room::find()->where(['name' => 'meeting_room'])->all();
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

    
    public function listUrgent()
    {
        $model = Categorise::find()
            ->where(['name' => 'urgent'])
            ->asArray()
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
    }

        // แสดงรายการาถานะ
        public function ListStatus()
        {
            $model = Categorise::find()
                ->where(['name' => 'meeting_status'])
                ->asArray()
                ->all();
            return ArrayHelper::map($model, 'code', 'title');
        }
        

 public function getUserReq()
    {
        try {
            $emp = $this->employee;
        
            return [
                'avatar' => $emp->getAvatar(false,$emp->departmentName()),
                'fullname' => $emp->fullname,
                'department' => $emp->departmentName(),
            ];
        } catch (\Throwable $th) {
        
            return [
                'avatar' => '',
                'fullname' => '',
                'department' => '',
            ];
        }

    }

    public function viewMeetingDate()
    {
        return ThaiDateHelper::formatThaiDate($this->date_start);
    }

    public function viewMeetingTime()
    {
        return $this->time_start . ' - ' . $this->time_end.' น.';
    }

    public function viewMeetingDateTime()
    {
        return ThaiDateHelper::formatThaiDate($this->date_start).' '.$this->viewMeetingTime();
    }


    public function viewStatus()
    {
       return $this->getStatus($this->status);
  
    }
    

    public function getStatus($status)
    {
      $title = '';
      $color = '';
      $view = '';
      $count = self::find()->where(['status' => $status])->count();
      $total = self::find()->count();
      $data = AppHelper::viewStatus($status);
      return [
          'total' => $total,
          'count' => $count,
          'percent' => $total > 0 ? ($count / $total * 100) : 0,
          'title' => $data['title'],
          'color' => $data['color'],
          'view' => $data['view'],
          'icon' => $data['icon']
      ];
       
    }


    //นับจำนวนการจองห้องประชุมตามสถานะ
    public function countStatus($status = null)
    {
        $count = Meeting::find()
            ->andFilterwhere(['status' => $status])
            ->count();
        return $count;
    }

    // การจองที่กำลังจะถึง
    public function upComing()
    {
        $date = date('Y-m-d');
        $count = Meeting::find()
            ->andFilterwhere(['between', 'date_start', new Expression('CURDATE()'), new Expression('DATE_ADD(CURDATE(), INTERVAL 7 DAY)')])
            ->andFilterwhere(['status' => 'Pass'])
            ->count();
        return $count;
    }

    //สถิติการใช้ห้องประชุม

    public static function getUsageStatistics()
{
    // นับจำนวนทั้งหมดของ meeting
$totalAll = Meeting::find()->count();

// เขียน ActiveRecord query
$data = Meeting::find()
    ->alias('m')
    ->select([
        new Expression("IFNULL(r.title, 'ไม่ระบุห้อง') AS title"),
        new Expression("COUNT(m.id) AS total"),
        new Expression("ROUND(COUNT(m.id) * 100.0 / :totalAll, 0) AS percentage"),
    ])
    ->leftJoin(['r' => Categorise::tableName()], "r.code = m.room_id AND r.name = 'meeting_room'")
    ->groupBy('r.code')
    ->addParams([':totalAll' => $totalAll])
    ->asArray()
    ->all();

return $data;

}


    
    // ส่งข้ความไปยังผู้ดูแลห้องประชุม
    public function SendMeeting()
    {
        //ส่ง lie msg
        try {
           $ownerRoom = Room::find()->where(['name' => 'meeting_room', 'code' => $this->room_id])->one();
        $id = $ownerRoom->data_json['owner'] ?? 0;
        $emp = Employees::findOne($id);
        $lineId = $emp->user->line_id;
        LineMsg::BookMeeting($this->id, $lineId);
        } catch (\Throwable $th) {
            //throw $th;
        }
        // ส่ง telegram
        try {
             $room = 'ขอใช้'.$this->room->title; // ข้อความสำรอง
            $message = $room." วันที่ " . Yii::$app->thaiFormatter->asDate($this->date_start, 'medium') . 
            " เวลา " . $this->time_start . "-" . $this->time_end . "\n" .
            "ผู้ติดต่อ " . $this->employee->fullname . "\n" . 
            "โทรศัพท์ " . $this->data_json['phone'];
        
             $response = Yii::$app->telegram->sendMessage('meeting', $message, [
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
       
            
    }
}
