<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\db\Expression;
use yii\console\ExitCode;
use app\models\Categorise;
use yii\console\Controller;
use \yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseConsole;
use app\components\AppHelper;
use yii\helpers\BaseFileHelper;
use app\modules\hr\models\Leave;
use app\modules\booking\models\Room;
use app\modules\hr\models\Employees;
use app\modules\booking\models\Booking;
use app\modules\filemanager\models\Uploads;
use app\modules\hr\models\LeaveEntitlements;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ImportBookingController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {
        echo "นำเข้า \n";
        // $this->Room();
        // $this->MeetingService();
        $this->DriverService();
        $this->DriverReferService();
    }

    public function actionMeeting()
    {
        // นำวันลา
    }

    // นำเข้าห้องประชุม
    public function Room()
    {
        $queryRooms = Yii::$app->db2->createCommand('select * from meetingroom_index')->queryAll();
        foreach ($queryRooms as $room) {
            $model = Room::findOne(['code' => $room['ROOM_ID'],'name' => 'meeting_room','title' => $room['ROOM_NAME']]);
            // ถ้าหากไม่มีให้สร้างหม่
            if ($model === null) {
                // Record does not exist, create a new one
                $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new Room();
                $model->ref = $ref;
                //นำเข้ารูปภาพ
                $this->CreateDir($ref);
                if ($room['IMG1']) {
                    $name = time() . '.jpg';
                    file_put_contents(Yii::getAlias('@app') . '/modules/filemanager/fileupload/' . $ref . '/' . $name, $room['IMG1']);
                    $upload = new Uploads;
                    $upload->ref = $ref;
                    $upload->name = 'meeting_room';
                    $upload->file_name = $name;
                    $upload->real_filename = $name;
                    $upload->type = 'jpg';
                    $upload->save(false);
                }
            }

            // ถ้ามีของเดิมอยู่แล้วให้ update
            $model->name = 'meeting_room';
            $model->code = $room['ROOM_ID'];
            $model->title = $room['ROOM_NAME'];

            // Save the model
            if ($model->save()) {
                echo "นำเข้าห้องประชุม ".$model->title." สำเร็จ \n";
            } else {
                
                $errors = $model->getErrors();
                echo "นำเข้าห้องประชุม ".$model->title." ไม่สำเร็จ \n";
            }
        }
    }

 // ทะเบียนข้อมูลบริการห้องประชุม
     public function MeetingService()
    {
        $queryMeetingServices = Yii::$app->db2->createCommand("SELECT * FROM `meetingroom_service`")->queryAll();
        foreach($queryMeetingServices as $item){

        $model = Booking::findOne([
            'name' => 'meeting_service',
            'thai_year' => $item['YEAR_ID'],
            'room_id' => $item['ROOM_ID'],
            'reason' => $item['SERVICE_STORY'],
            'date_start' => $item['DATE_BEGIN'],
            'time_start' => $item['TIME_BEGIN'],
            'date_end'=> $item['DATE_END'],
            'time_end'=> $item['TIME_END']
        ]);
        // ถ้าหากไม่มีให้สร้างหม่
        if ($model === null) {
            $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $model = new Booking();
            $model->ref = $ref;
        }

         // ถ้ามีของเดิมอยู่แล้วให้ update
         $model->name = 'meeting_service';
         $model->thai_year = $item['YEAR_ID'];
         $model->room_id = $item['ROOM_ID'];
         $model->reason = $item['SERVICE_STORY'];
         $model->date_start = $item['DATE_BEGIN'];
         $model->time_start = $item['TIME_BEGIN'];
         $model->date_end= $item['DATE_END'];
         $model->time_end= $item['TIME_END'];
         $model->emp_id = $this->Person($item['PERSON_REQUEST_ID'])->id ?? 0;
          // Save the model
          if ($model->save()) {
            echo "ทะเบียนข้อมูลบริการห้องประชุม ".$model->reason." สำเร็จ \n";
        } else {
            
            $errors = $model->getErrors();
            echo "ทะเบียนข้อมูลบริการห้องประชุม ".$model->reason." ไม่สำเร็จ \n";
        }
        }
    }

    // ทะเบียนข้อมูลบริการรถยนต์ทั่วไป
    public function DriverService()
    {
        $queryDriverServices = Yii::$app->db2->createCommand("SELECT * FROM `vehicle_car_reserve`")->queryAll();
        foreach($queryDriverServices as $item){

        $model = Booking::findOne([
            'name' => 'driver_service',
            'car_type' => 'general',
            'reason' => $item['RESERVE_NAME'],
            'date_start' => $item['RESERVE_BEGIN_DATE'],
            'time_start' => $item['RESERVE_BEGIN_TIME'],
            'date_end'=> $item['RESERVE_END_DATE'],
            'time_end'=> $item['RESERVE_END_TIME']
        ]);
        // ถ้าหากไม่มีให้สร้างหม่
        if ($model === null) {
            $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $model = new Booking();
            $model->ref = $ref;
        }
         // ถ้ามีของเดิมอยู่แล้วให้ update
         $model->name = 'driver_service';
         $model->car_type = 'general';
         $model->reason = $item['RESERVE_NAME'];
         $model->date_start = $item['RESERVE_BEGIN_DATE']; 
         $model->time_start = $item['RESERVE_BEGIN_TIME'];
         $model->date_end= $item['RESERVE_END_DATE'];
         $model->time_end= $item['RESERVE_END_TIME'];
         $model->emp_id = $this->Person($item['RESERVE_PERSON_ID'])->id ?? 0;
          // Save the model
          if ($model->save()) {
            echo "ทะเบียนข้อมูลบริการรถยนต์ทั่วไป ".$model->reason." สำเร็จ \n";
        } else {
            
            $errors = $model->getErrors();
            echo "ทะเบียนข้อมูลบริการรถยนต์ทั่วไป ".$model->reason." ไม่สำเร็จ \n";
        }
        
        }
    }

    // ทะเบียนข้อมูลบริการรถพยาบาล
    public function DriverReferService()
    {
        $queryDriverServices = Yii::$app->db2->createCommand("SELECT * FROM `vehicle_car_refer`")->queryAll();
        foreach($queryDriverServices as $item){
            switch ($item['REFER_TYPE_ID']) {
                case 1:
                    $ambulance_type = 'REFER';
                    break;
                    case 2:
                        $ambulance_type = 'EMS';
                        break;
                        case 3:
                            $ambulance_type = 'NORMAL';
                            break;
                            default:
                            $ambulance_type = '';
                            
                break;
        }

        $model = Booking::findOne([
            'name' => 'driver_service',
            'car_type' => 'ambulance',
            'ambulance_type' =>  $ambulance_type ,
            'date_start' => ($item['OUT_DATE'] == '0000-00-00' ? NULL : $item['OUT_DATE']),
            'time_start' => ($item['OUT_TIME'] == '00:00:00' ? NULL : $item['OUT_TIME']),
            'date_end'=> ($item['BACK_DATE'] == '0000-00-00' ? NULL : $item['BACK_DATE']),
            'time_end'=> ($item['BACK_TIME'] == '00:00:00' ? NULL : $item['BACK_TIME']),
            'mileage_start'=> $item['CAR_GO_MILE'],
            'mileage_end'=> $item['CAR_BACK_MILE']
        ]);
        // ถ้าหากไม่มีให้สร้างหม่
        if ($model === null) {
            $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $model = new Booking();
            $model->ref = $ref;
        }
        // ถ้ามีของเดิมอยู่แล้วให้ update
        $model->name = 'driver_service';
        $model->car_type  = 'ambulance';
        $model->ambulance_type =  $ambulance_type;
        $model->date_start = ($item['OUT_DATE'] == '0000-00-00' ? NULL : $item['OUT_DATE']);
        $model->time_start = ($item['OUT_TIME'] == '00:00:00' ? NULL : $item['OUT_TIME']);
        $model->date_end = ($item['BACK_DATE'] == '0000-00-00' ? NULL : $item['BACK_DATE']);
        $model->time_end = ($item['BACK_TIME'] == '00:00:00' ? NULL : $item['BACK_TIME']);
         $model->mileage_start = $item['CAR_GO_MILE'];
         $model->mileage_end = $item['CAR_BACK_MILE'];
        //  $model->emp_id = $this->Person($item['RESERVE_PERSON_ID'])->id ?? 0;
          // Save the model
          if ($model->save(false)) {
            echo "ทะเบียนข้อมูลบริการรถพยาบาล ".$model->id." สำเร็จ \n";
        } else {
            
            $errors = $model->getErrors();
            echo "ทะเบียนข้อมูลบริการรถพยาบาล ".$model->id." ไม่สำเร็จ \n";
        }
        
        }
    }
    
    
    
    
    public static function Person($id)
    {
        $person = Yii::$app
            ->db2
            ->createCommand('SELECT * FROM `hrd_person` WHERE ID = :id')
            ->bindValue(':id', $id)
            ->queryOne();
        if ($person) {
            $emp = Employees::findOne(['cid' => $person['HR_CID']]);
            return $emp;
        }
    }

    public static function CreateDir($folderName)
    {
        if ($folderName != null) {
            $basePath = Yii::getAlias('@app') . '/modules/filemanager/fileupload/';
            if (BaseFileHelper::createDirectory($basePath . $folderName, 0777)) {
                BaseFileHelper::createDirectory($basePath . $folderName . '/thumbnail', 0777);
            }
        }
        return;
    }
    
}
