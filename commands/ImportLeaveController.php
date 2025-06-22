<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use DateTime;
use yii\db\Expression;
use app\models\Calendar;
use yii\console\ExitCode;
use app\models\Categorise;
use yii\console\Controller;
use \yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseConsole;
use app\components\AppHelper;
use yii\helpers\BaseFileHelper;
use app\modules\hr\models\Leave;
use app\modules\hr\models\Employees;
use app\modules\approve\models\Approve;
use app\modules\hr\models\LeaveEntitlements;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ImportLeaveController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {
        $querys = Yii::$app->db2->createCommand("SELECT LEAVE_YEAR_ID FROM gleave_register GROUP BY LEAVE_YEAR_ID")->queryAll();
        foreach ($querys as $item) {
            $this->Leave($item['LEAVE_YEAR_ID']);
        }
        // $this->Leave(2568);
    }

    public function Leave($year)
    {
        $querys = Yii::$app->db2->createCommand('SELECT 
                    gleave_register.ID,
                    WORK_DO,
                    LEAVE_YEAR_ID,
                    LEAVE_TYPE_CODE,
                    LEAVE_TYPE_ID,
                    LEAVE_TYPE_NAME,
                    LEAVE_BECAUSE,
                    LEAVE_DATE_BEGIN,
                    LEAVE_DATE_END,
                    LEAVE_DATE_SUM,
                    gleave_register.DAY_TYPE_ID,
                    DAY_TYPE_NAME,
                    LEAVE_CONTACT,
                    LEAVE_DATETIME_REGIS,
                    LEAVE_PERSON_ID,
                    LEAVE_PERSON_CODE,
                    LEAVE_PERSON_FULLNAME,
                    LEAVE_STATUS_CODE,
                    LEAVE_CONTACT_PHONE,
                    LEAVE_WORK_SEND,
                    LEAVE_WORK_SEND_ID,
                    LEADER_PERSON_ID,
                    LEADER_PERSON_NAME,
                    LEADER_PERSON_POSITION,
                    LEADER_DEP_PERSON_ID,
                    USER_CONFIRM_CHECK_ID,
                    STATUS_CODE,
                    STATUS_NAME,
                    LEAVE_SUM_HOLIDAY,
                    LEAVE_SUM_ALL,
                    LEAVE_SUM_SETSUN,
                    gleave_location.LOCATION_ID,
                    gleave_location.LOCATION_NAME,
                    TOP_LEADER_AC_ID,
                    TOP_LEADER_AC_NAME
                    FROM gleave_register
                    LEFT JOIN gleave_type ON gleave_register.LEAVE_TYPE_CODE = gleave_type.LEAVE_TYPE_ID
                    LEFT JOIN gleave_status ON gleave_register.LEAVE_STATUS_CODE = gleave_status.STATUS_CODE
                    LEFT JOIN gleave_location ON gleave_register.LOCATION_ID = gleave_location.LOCATION_ID
                    LEFT JOIN gleave_day_type ON gleave_day_type.DAY_TYPE_ID = gleave_register.DAY_TYPE_ID
                    WHERE LEAVE_YEAR_ID = :year_id 
                    --  AND LEAVE_PERSON_ID = 323
                    -- AND `STATUS_CODE` = "Allow"
                    ORDER BY gleave_register.ID DESC;')
            ->bindValue('year_id', $year)
            ->queryAll();
        $num = 1;
        $total = count($querys);

        foreach ($querys as $key => $item) {
            $emp = Employees::findOne(['cid' => $item['LEAVE_PERSON_CODE']]);

            $sendwork = $this->Person($item['LEAVE_WORK_SEND_ID']);
            $leaderLevel1 = $this->Person($item['LEADER_PERSON_ID']);
            $leaderLevel2 = $this->Person($item['LEADER_DEP_PERSON_ID']);
            $userCheckId = $this->Person($item['USER_CONFIRM_CHECK_ID']);
            $ApproveDirector = $this->Person($item['TOP_LEADER_AC_ID']);

            $leave_work_send_id = Employees::findOne(['cid' => $item['LEAVE_PERSON_CODE']]);

            $checkLeave = Leave::find()->where([
                'emp_id' =>  $emp->id,
                'thai_year' => $item['LEAVE_YEAR_ID'],
                'date_start' => $item['LEAVE_DATE_BEGIN'],
                'date_end' => $item['LEAVE_DATE_END'],
            ])->andWhere(['json_extract(data_json, "$.cid")' => $item['LEAVE_PERSON_CODE']])->one();

            $leaveType = Categorise::findOne(['name' => 'leave_type', 'title' => $item['LEAVE_TYPE_NAME']]);

            $leave = $checkLeave ?? new Leave();


            $leave->leave_type_id = $leaveType ? $leaveType->code : '';
            $leave->emp_id = $emp ? $emp->id : 0;
            $leave->thai_year = $item['LEAVE_YEAR_ID'];
            $leave->date_start = $item['LEAVE_DATE_BEGIN'];
            $leave->date_end = $item['LEAVE_DATE_END'];
            $leave->status = $this->getStatus($item['STATUS_CODE'])['status'];

            if ($item['DAY_TYPE_ID'] == 2) {
                $leave->date_start_type = 0.5;
                $leave->date_end_type = 0;
            } elseif ($item['DAY_TYPE_ID'] == 3) {

                $leave->date_start_type = 0;
                $leave->date_end_type = 0.5;
            } else {
                $leave->date_start_type = 0;
                $leave->date_end_type = 0;
            }

            $leave->total_days = $item['WORK_DO'];

            $leave->data_json = [
                // 'sat_sun_days' => $checkDays['sat_sun_days'],
                'sat_sun_days' => $item['LEAVE_SUM_HOLIDAY'], // วันหยุดเสาร์-อาทิตย์
                'holidays' => $item['LEAVE_SUM_SETSUN'], //วันหยุดนคัตฤก
                'sum_all_days' => $item['LEAVE_SUM_ALL'], //รวมจำนวนวันทั้งหมด
                // 'off_days' => $checkDays['dayOff'],
                // 'total_days' => $checkDays['sat_sun_days'],
                'id' => $item['ID'],
                'cid' => $item['LEAVE_PERSON_CODE'],
                'fullname' => $item['LEAVE_PERSON_FULLNAME'],
                'leave_type_id' => $item['LEAVE_TYPE_ID'],
                'leave_type_name' => $item['LEAVE_TYPE_NAME'],
                'status_code' => $item['STATUS_CODE'],
                'status_name' => $item['STATUS_NAME'],
                'location_id' => $item['LOCATION_ID'],
                'location' => $item['LOCATION_NAME'],
                'reason' => $item['LEAVE_BECAUSE'],
                'leave_date_sum' => $item['LEAVE_DATE_SUM'],
                'day_type_id' => $item['DAY_TYPE_ID'],
                'address' => $item['LEAVE_CONTACT'],
                'leave_datetime_regis' => $item['LEAVE_DATETIME_REGIS'],
                'leave_type_code' => $item['LEAVE_TYPE_CODE'],
                'leave_person_id' => $item['LEAVE_PERSON_ID'],
                'leave_status_code' => $item['LEAVE_STATUS_CODE'],
                'phone' => $item['LEAVE_CONTACT_PHONE'],
                'leave_work_send' => $item['LEAVE_WORK_SEND'],
                'leave_work_send_id' => isset($sendwork) ? $sendwork->id : 0,
                'approve_1' => isset($leaderLevel1) ? (string)$leaderLevel1->id : 0,
                'approve_fullname_1' => isset($leaderLevel1) ? (string)$leaderLevel1->fullname : 0,
                'approve_2' => isset($leaderLevel2) ? (string)$leaderLevel2->id : 0,
                'approve_fullname_2' => isset($leaderLevel2) ? (string)$leaderLevel2->fullname : 0,
                'approve_3' => isset($userCheckId) ? (string)$userCheckId->id : 0,
                'approve_fulname_3' => isset($userCheckId) ? (string)$userCheckId->fullname : 0,
                'approve_4' => isset($ApproveDirector) ? (string)$ApproveDirector->id : 0,
                'approve_fullname_4' => isset($ApproveDirector) ? (string)$ApproveDirector->fullname : 0,
                'director_id' => $item['TOP_LEADER_AC_ID'],
                'director_fullname' => $item['TOP_LEADER_AC_NAME'],
                'leader' => isset($leaderId) ? (string)$leaderId->id : 0,
                'leader_person_name' => $item['LEADER_PERSON_NAME'],
                'leader_person_position' => $item['LEADER_PERSON_POSITION'],
            ];

            if ($leave->save(false)) {
                $percentage = (($num++) / $total) * 100;
                echo 'ข้อมูลการลา ปี ' . $leave->thai_year . ' =>  ดำเนินการแล้ว : ' . number_format($percentage, 2) . "%\n";
                // $this->CreateApprove($item);
            }
        }
        $this->UpdateStatus();
        return ExitCode::OK;
    }

    public function getStatus($variable)
    {

        switch ($variable) {
            //  รอเห็นชอบ
            case 'Pending':
                $level = 1;
                $approve_status = 'Pending';
                $status = 'Pending';
                break;

            //  หัวหน้าเห็นชอบ
            case 'Approve':
                $level = 1;
                $approve_status = 'Pass';
                $status = 'Pending';
                break;
            //หน.กลุ่มเห็นชอบ
            case 'ApproveGroup':
                $level = 2;
                $approve_status = 'Pass';
                $status = 'Pending';
                break;
            case 'Verify':
                $level = 3;
                $approve_status = 'Pass';
                $status = 'Verify';
                break;
            //ผอ.อนุมัติ
            case 'Allow':
                $level = 4;
                $approve_status = 'Pass';
                $status = 'Approve';
                break;
            //แจ้งยกเลิก
            case 'Recancel':
                $level = 0;
                $approve_status = '';
                $status = 'ReqCancel';
                break;
            //ยกเลิก
           case 'Cancel':
                $level = 0;
                $approve_status = '';
                $status = 'Cancel';
                break;
                //ไม่อนุมัติ
           case 'Disapprove':
                $level = 0;
                $approve_status = '';
                $status = 'Reject';
                break;
                
            default:
                $level = 0;
                $approve_status = '';
                $status = '';
                break;
        }
        return [
                'level' => $level,
                'approve_status' => $approve_status,
                'status' => $status
        ];
    }

    public function UpdateStatus()
    {
        // อัปเดตสถานะ leave จาก 'Allow' เป็น 'Approve' เฉพาะที่มี thai_year
        $count = Yii::$app->db->createCommand("UPDATE `leave` SET status = 'Approve' WHERE `thai_year` IS NOT NULL AND status = 'Allow'")->execute();
        echo "อัปเดตข้อมูลจำนวน $count รายการ \n";
        return ExitCode::OK;
    }

    public function actionCreateApprove()
    {

        $sql = "UPDATE `leave` set created_at = data_json->'$.leave_datetime_regis'";
        $leaves = Leave::find()->all();
        foreach ($leaves as $item) {
            $obj1 = ['name' => 'leave','from_id' => $item->id,'level' => 1,'emp_id' => $item->data_json['approve_1'],'status' => 'Pass'];
            $approve1 = Approve::find()->where($obj1)->one();
            if(!$approve1){
                $newApprove1 = new Approve($obj1);
                $newApprove1->save(false);
                echo "Create Approve ID = ".$newApprove1->id."\n";
            }
            
            $obj2 = ['name' => 'leave','from_id' => $item->id,'level' => 2,'emp_id' => $item->data_json['approve_2'],'status' => 'Pass'];
             $approve2 = Approve::find()->where($obj2)->one();
            if(!$approve2){
                $newApprove2 = new Approve($obj2);
                $newApprove2->save(false);
                echo "Create Approve ID = ".$newApprove2->id."\n";
            }

             $obj3 = ['name' => 'leave','from_id' => $item->id,'level' => 3,'emp_id' => $item->data_json['approve_3'],'status' => 'Pass'];
             $approve3 = Approve::find()->where($obj3)->one();
            if(!$approve3){
                $newApprove3 = new Approve($obj3);
                $newApprove3->save(false);
                echo "Create Approve ID = ".$newApprove3->id."\n";
            }

            $obj4 = ['name' => 'leave','from_id' => $item->id,'level' => 4,'emp_id' => $item->data_json['approve_4'],'status' => 'Pass'];
             $approve4 = Approve::find()->where($obj4)->one();
            if(!$approve4){
                $newApprove4 = new Approve($obj4);
                $newApprove4->save(false);
                echo "Create Approve ID = ".$newApprove4->id."\n";
            }
            
        }
    }

    // public function actionClear()
    // {
    //     Approve::deleteAll(['name' => 'leave']);
    //     Yii::$app->db->createCommand('TRUNCATE TABLE `leave`')->execute();
    //     echo "ลบข้อมูล Leave ทั้งหมดแล้ว\n";
    // }

    public function actionEtitlements()
    {
        $querys = Yii::$app->db2->createCommand("SELECT l.*,pt.HR_PERSON_TYPE_NAME FROM `gleave_over` l LEFT JOIN hrd_person_type pt ON pt.HR_PERSON_TYPE_ID = l.HR_PERSON_TYPE_ID;")->queryAll();
        foreach ($querys as $key => $item) {

            $emp = $this->Person($item['PERSON_ID']);
            $positionType = Categorise::find()->where(['name' => 'position_type', 'title' =>  $item['HR_PERSON_TYPE_NAME']])->one();
            $check = LeaveEntitlements::find()->where(['thai_year' => $item['OVER_YEAR_ID'], 'emp_id' => $emp->id])->one();

            if ($check) {
                $model = $check;
            } else {
                $model = new LeaveEntitlements();
            }

            $model->emp_id = $emp->id;
            $model->thai_year = $item['OVER_YEAR_ID'];
            $model->position_type_id = isset($positionType['code']) ?? 0;
            $model->leave_type_id = 'LT4';
            $model->year_of_service = $item['OLDS'];
            $model->month_of_service = 0;
            // $model->leave_days = 10;
            $model->days = $item['DAY_LEAVE_OVER_BEFORE'];
            // $model->leave_limit = 0;
            // $model->leave_total_days = $item['DAY_LEAVE_OVER_BEFORE'];
            if ($model->save(false)) {
                echo 'ดำเนินการ : ' . $model->emp_id . " \n";
            } else {
                echo 'ผิดพลาด : ' . $model->emp_id . " \n";
                return ExitCode::OK;
            }
        }
        return ExitCode::OK;
    }

    public static function Person($id)
    {
        $person = Yii::$app->db2->createCommand('SELECT * FROM `hrd_person` WHERE ID = :id')
            ->bindValue(':id', $id)->queryOne();
        if ($person) {
            $emp = Employees::findOne(['cid' => $person['HR_CID']]);
            return $emp;
        }
    }

    public function SyncDate($year)
    {
        // https://www.myhora.com/calendar/ical/holiday.aspx?2567.json
        $url = "https://www.myhora.com/calendar/ical/holiday.aspx?" . $year . ".json";
        // ดึงข้อมูล JSON จาก URL
        $json = file_get_contents($url);
        // แปลง JSON เป็น array
        $data = json_decode($json, true);
        foreach ($data['VCALENDAR'][0]['VEVENT'] as $Calendar) {
            $dateString =  $Calendar['DTSTART;VALUE=DATE'];
            $date = DateTime::createFromFormat('Ymd', $dateString);
            // แปลงเป็นรูปแบบ Y-m-d
            $CalendarDate = $date->format('Y-m-d');

            $checkDay = Calendar::find()
                ->where(['name' => 'holiday', 'date_start' => $CalendarDate])
                ->one();
            if (!$checkDay) {
                $model =  new Calendar;
                $model->title = $Calendar['SUMMARY'];
                $model->name = 'holiday';
                $model->thai_year = AppHelper::YearBudget($CalendarDate);
                $model->date_start = $CalendarDate;

                $model->save();
            }
        }
    }
}
