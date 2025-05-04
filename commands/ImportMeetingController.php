<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use DateTime;
use DatePeriod;
use DateInterval;
use yii\db\Expression;
use yii\console\ExitCode;
use app\models\Categorise;
use yii\console\Controller;
use \yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseConsole;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use mdm\autonumber\AutoNumber;
use yii\helpers\BaseFileHelper;
use app\modules\hr\models\Leave;
use app\modules\hr\models\Employees;
use app\modules\approve\models\Approve;
use app\modules\booking\models\Meeting;
use app\modules\hr\models\LeaveEntitlements;
use app\modules\booking\models\MeetingDetail;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ImportMeetingController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {
        $sql = "SELECT r.ROOM_ID,r.ROOM_NAME,s.* FROM `meetingroom_service` s LEFT JOIN meetingroom_index r ON r.ROOM_ID = s.ROOM_ID ORDER BY s.DATE_TIME_REQUEST ASC";
        $querys = Yii::$app->db2->createCommand($sql)
        ->queryAll();
        
        $num = 1;
        $total = count($querys);
        foreach ($querys as $key => $item) {
            try {

           $emp = $this->Person($item['PERSON_REQUEST_ID']);

            $check = Meeting::find()->where([
                'emp_id' => $emp?->id  ?? 0,
                'room_id' => $item['ROOM_ID'],
                'thai_year' => $item['YEAR_ID'],
                'title' => $item['SERVICE_STORY'],
                'date_start' => $item['DATE_BEGIN'],
                'date_end' => $item['DATE_END'],
                'time_start' => $item['TIME_BEGIN'],
                'time_end' => $item['TIME_END'],
            ])->one();

            $model = $check ?? new Meeting();
            $check ?? ($model->code  =   \mdm\autonumber\AutoNumber::generate('MEETING' . date('ymd', strtotime($item['DATE_BEGIN'])) . '-???'));
            $model->emp_id = $emp?->id  ?? 0;
            $model->room_id = $item['ROOM_ID'];
            $model->thai_year = $item['YEAR_ID'];
            $model->title = $item['SERVICE_STORY'];
            $model->date_start = $item['DATE_BEGIN'];
            $model->date_end = $item['DATE_END'];
            $model->time_start = $item['TIME_BEGIN'];
            $model->time_end = $item['TIME_END'];
            $model->urgent = 'ปกติ';
            $model->status = 'Pass';
            $model->data_json = [
                'old_data' => $item  
            ];
            if($model->save(false)){
                $percentage = (($num++) / $total) * 100;
                echo 'ดำเนินการแล้ว : ' . number_format($percentage, 2) . "%\n";
            }
        } catch (\Throwable $th) {
            echo 'เกิดข้อผิดพลาด : ' . $item['ID'] . " - " . $th->getMessage() . "\n";
        }
        }
        return ExitCode::OK;
    }

    public static function Status($status) {
        if($status == 'LASTAPP'){
            return 'Approve';
        }else if($status == 'Cancel'){
            return 'Cancel';
        }else if($status == 'RECERVE'){
            return 'Pending';
        }else if($status == 'SUCCESS'){
            return 'Pass';
        }else{
            return $status;
        }
    }

    public static function Person($id) {
        $person = Yii::$app->db2->createCommand('SELECT * FROM `hrd_person` WHERE ID = :id')
        ->bindValue(':id',$id)->queryOne();
        if($person){
            $emp = Employees::findOne(['cid' => $person['HR_CID']]);
            return $emp;
        }
    }
}
