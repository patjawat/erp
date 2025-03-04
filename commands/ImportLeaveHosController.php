<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\db\Expression;
use app\modules\approve\models\Approve;
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
use app\modules\hr\models\LeaveEntitlements;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ImportLeaveHosController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {
        echo "วันลา \n";
    }

    public function actionLeave()
    {
        // นำวันลา
        $querys = Yii::$app->db2->createCommand('SELECT 
        leave_register.*,
                approve1.LEADER_HR_ID as approve_1_emp_id,
                approve1.LEADER_HR_NAME as approve_1_fullname,
                approve1.DATE_TIME_SAVE as approve_1_date,
                approve1.LEADER_ANS as approve_1_status,
                approve2.LEADER_HR_ID as approve_2_emp_id,
                approve2.LEADER_HR_NAME as approve_2_fullname,
                approve2.DATE_TIME_SAVE as approve_2_date,
                approve2.LEADER_ANS as approve_2_status,
                USER_CONFIRM_CHECK as approve_3_fullname,
                USER_CONFIRM_CHECK,
                USER_CONFIRM_CHECK_DATE,
                    LEAVE_YEAR_ID,
                    LEAVE_BECAUSE,
                    LEAVE_DATE_BEGIN,
                    LEAVE_DATE_END,
                    LEAVE_DATE_SUM,
                    leave_register.DAY_TYPE_ID,
                    DAY_TYPE_NAME,
                    LEAVE_CONTACT,
                    LEAVE_DATETIME_REGIS,
                    LEAVE_TYPE_CODE,
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
                    LEAVE_TYPE_ID,
                    LEAVE_TYPE_NAME,
                    USER_CONFIRM_CHECK_ID,
                    USER_CONFIRM_CHECK,
                    STATUS_CODE,
                    STATUS_NAME,
                    leave_location.LOCATION_ID,
                    leave_location.LOCATION_NAME
                    FROM leave_register
                    LEFT JOIN leave_type ON leave_register.LEAVE_TYPE_CODE = leave_type.LEAVE_TYPE_ID
                    LEFT JOIN leave_status ON leave_register.LEAVE_STATUS_CODE = leave_status.STATUS_CODE
                    LEFT JOIN leave_location ON leave_register.LOCATION_ID = leave_location.LOCATION_ID
                    LEFT JOIN leave_day_type ON leave_day_type.DAY_TYPE_ID = leave_register.DAY_TYPE_ID
                    LEFT JOIN leave_approv_leader_one approve1 ON leave_register.ID = approve1.LEAVE_ID
                    LEFT JOIN leave_approv_leader_one approve2 ON leave_register.ID = approve2.LEAVE_ID
                    WHERE approve1.LEAVE_ID IS NOT NULL
                    ORDER BY leave_register.ID DESC')->queryAll();
        $num = 1;
        $total = count($querys);
        foreach ($querys as $key => $item) {
            $emp = Employees::findOne(['cid' => $item['LEAVE_PERSON_CODE']]);

            $sendwork = $this->Person($item['LEAVE_WORK_SEND_ID']);
            $leaderId = $this->Person($item['approve_1_emp_id']);
            $leaderWorkGroupId = $this->Person($item['approve_2_emp_id']);
            $userCheckId = $this->Person($item['USER_CONFIRM_CHECK_ID']);

            $leave_work_send_id = Employees::findOne(['cid' => $item['LEAVE_PERSON_CODE']]);

            $checkLeave = Leave::find()->where([
                'thai_year' => $item['LEAVE_YEAR_ID'],
                'date_start' => $item['LEAVE_DATE_BEGIN'],
                'date_end' => $item['LEAVE_DATE_END'],
            ])->andWhere(['json_extract(data_json, "$.cid")' => $item['LEAVE_PERSON_CODE']])->one();
            $leaveType = Categorise::findOne(['name' => 'leave_type', 'title' => $item['LEAVE_TYPE_NAME']]);

            $leave = $checkLeave ?? new Leave();

            $leave->leave_type_id = $leaveType ? $leaveType->code : '';
            $leave->emp_id = $emp ? $emp->id : 0;
            $leave->thai_year = $item['LEAVE_YEAR_ID'];
            $leave->date_start = ($item['LEAVE_DATE_BEGIN'] == '0000-00-00' ? null : $item['LEAVE_DATE_BEGIN']);
            $leave->date_end = ($item['LEAVE_DATE_END'] == '0000-00-00' ? null : $item['LEAVE_DATE_END']);

            if ($item['LEAVE_STATUS_CODE'] == 'A') {
                $leave->status = 'Pending';
            } else if ($item['LEAVE_STATUS_CODE'] == 'B') {
                $leave->status = 'Pending';
            } else if ($item['LEAVE_STATUS_CODE'] == 'E') {
                $leave->status = 'Checking';
            } else if ($item['LEAVE_STATUS_CODE'] == 'S') {
                $leave->status = 'Checking';
            } else if ($item['LEAVE_STATUS_CODE'] == 'Z') {
                $leave->status = 'Allow';
            } else if ($item['LEAVE_STATUS_CODE'] == 'N') {
                $leave->status = 'Cancel';
            } else {
                $leave->status = $item['LEAVE_STATUS_CODE'];
            }

            if ($item['DAY_TYPE_ID'] == 2) {
                $leave->date_start_type = 0.5;
                $leave->date_end_type = 0;
                $leave->total_days = $item['LEAVE_DATE_SUM'] - 0.5;
            } elseif ($item['DAY_TYPE_ID'] == 3) {
                $leave->total_days = $item['LEAVE_DATE_SUM'] - 0.5;
                $leave->date_start_type = 0;
                $leave->date_end_type = 0.5;
            } else {
                $leave->date_start_type = 0;
                $leave->date_end_type = 0;
                $leave->total_days = $item['LEAVE_DATE_SUM'];
            }

            $leave->data_json = [
                'old_data' => $item,
                'cid' => $item['LEAVE_PERSON_CODE'],
                'fullname' => $item['LEAVE_PERSON_FULLNAME'],
                'leave_type_id' => $item['LEAVE_TYPE_ID'],
                'leave_type_name' => $item['LEAVE_TYPE_NAME'],
                'status_code' => $item['LEAVE_STATUS_CODE'],
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
                'approve_1' => isset($leaderId) ? (string) $leaderId->id : 0,
                'approve_1_date' => $item['approve_1_date'],
                'approve_1_fullname' => $item['approve_1_fullname'],
                'approve_1_status' => $item['approve_1_status'],
                'approve_2' => isset($leaderWorkGroupId) ? (string) $leaderWorkGroupId->id : 0,
                'approve_2_fullname' => $item['approve_2_fullname'],
                'approve_2_date' => $item['approve_2_date'],
                'approve_2_status' => $item['approve_2_status'],
                'approve_3' => isset($userCheckId) ? (string) $userCheckId->id : 0,
                'approve_3_date' => $item['USER_CONFIRM_CHECK_DATE'],
                'approve_3_fullname' => isset($userCheckId) ? (string) $userCheckId->fullname : $item['USER_CONFIRM_CHECK'],
                'approve_fulname_3' => isset($userCheckId) ? (string) $userCheckId->fullname : 0,
                'leader' => isset($leaderId) ? (string) $leaderId->id : 0,
                'leader_person_name' => $item['LEADER_PERSON_NAME'],
                'leader_person_position' => $item['LEADER_PERSON_POSITION'],
            ];

            if ($leave->save(false)) {
                $this->createApprove($leave);
                $percentage = (($num++) / $total) * 100;
                echo 'ดำเนินการแล้ว : ' . number_format($percentage, 2) . ' Status = ' . $leave->status . $item['LEAVE_STATUS_CODE'] . "%\n";
            }
        }
        return ExitCode::OK;
    }

    public static function CreateApprove($leave)
    {
        // หนัวหน้าเห็ยชอบ
        $approve1 = Approve::find()->where(['name' => 'leave', 'from_id' => $leave->id, 'emp_id' => $leave->data_json['approve_1'], 'level' => 1])->one();
        if (!$approve1) {
            $newApprove1 = new Approve();
            $newApprove1->data_json = [
                'topic' => 'เห็นชอบ',
                'approve_date' => $leave->data_json['approve_1_date'],
                'approve_fullname' => $leave->data_json['approve_1_fullname']
            ];

            $newApprove1->level = 1;
            $newApprove1->name = 'leave';
            $newApprove1->title = 'เห็นชอบ';
            $newApprove1->from_id = $leave->id;
            $newApprove1->emp_id = $leave->data_json['approve_1'];
            $newApprove1->status = ($leave->data_json['approve_1_status'] == 'YES' ? 'Approve' : 'Reject');
            $newApprove1->save(false);
        }

        // หนัวหน้ากลุ่มงานเห็ยชอบ
        $approve2 = Approve::find()->where(['name' => 'leave', 'from_id' => $leave->id, 'emp_id' => $leave->data_json['approve_2'], 'level' => 2])->one();
        if (!$approve2) {
            $newApprove2 = new Approve();
            $newApprove2->level = 2;
            $newApprove2->title = 'เห็นชอบ';
            $newApprove2->name = 'leave';
            $newApprove2->from_id = $leave->id;
            $newApprove2->data_json = [
                'topic' => 'เห็นชอบ',
                'approve_date' => $leave->data_json['approve_2_date'],
                'approve_fullname' => $leave->data_json['approve_2_fullname']
            ];
            $newApprove2->emp_id = $leave->data_json['approve_2'];
            $newApprove2->status = ($leave->data_json['approve_2_status'] == 'YES' ? 'Approve' : 'Reject');
            $newApprove2->save(false);
        }

        // เจ้าหน้าที่ตรวจสอบ
        $approve3 = Approve::find()->where(['name' => 'leave', 'from_id' => $leave->id, 'emp_id' => $leave->data_json['approve_3'], 'level' => 3])->one();
        if (!$approve3) {
            $newApprove3 = new Approve();
           
        }else{
            $newApprove3 =  $approve3;
        }
        $newApprove3->level = 3;
        $newApprove3->name = 'leave';
        $newApprove3->title = 'ตรวจสอบ';
        $newApprove3->from_id = $leave->id;
        $newApprove3->emp_id = $leave->data_json['approve_3'];
        $newApprove3->data_json = [
            'topic' => 'ผ่าน',
            'approve_date' => $leave->data_json['approve_3_date'],
            'approve_fullname' => $leave->data_json['approve_3_fullname'],
        ];
        $newApprove3->status = ($leave->data_json['approve_3_date'] !== '' ? 'Approve' : 'Reject');
        $newApprove3->save(false);

         // ผู้อำนวยการอนุมัติ (ให้หัวหน้าอนุมัติแทน) 
         $approve4 = Approve::find()->where(['name' => 'leave', 'from_id' => $leave->id, 'emp_id' => $leave->data_json['approve_3'], 'level' => 4])->one();
         if (!$approve4) {
             $newApprove4 = new Approve();
             $newApprove4->level = 4;
             $newApprove4->name = 'leave';
             $newApprove4->title = 'อนุมัติ';
             $newApprove4->from_id = $leave->id;
             $newApprove4->emp_id = $leave->data_json['approve_2'];
             $newApprove4->data_json = [
                 'topic' => 'อนุมัติ',
                 'approve_date' => $leave->data_json['approve_2_date'],
                 'approve_fullname' => $leave->data_json['approve_2_fullname'],
                ];
                $newApprove4->status = ($leave->data_json['approve_2_date'] !== '' ? 'Approve' : 'Reject');
                $newApprove4->save(false);
            }
         
    }

    public function actionEtitlements()
    {
        $querys = Yii::$app->db2->createCommand('SELECT l.*,pt.HR_PERSON_TYPE_NAME FROM `leave_over` l LEFT JOIN hr_person_type pt ON pt.HR_PERSON_TYPE_ID = l.HR_PERSON_TYPE_ID;')->queryAll();
        foreach ($querys as $key => $item) {
            try {
             
            $emp = $this->Person($item['PERSON_ID']);
            $positionType = Categorise::find()->where(['name' => 'position_type', 'title' => $item['HR_PERSON_TYPE_NAME']])->one();
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
            $model->days = $item['DAY_LEAVE_OVER_BEFORE'] ?? 0;
            // $model->leave_limit = 0;
            // $model->leave_total_days = $item['DAY_LEAVE_OVER_BEFORE'];
            if ($model->save(false)) {
                echo 'ดำเนินการ : ' . $model->emp_id . " \n";
            } else {
                echo 'ผิดพลาด : ' . $model->emp_id . " \n";
                return ExitCode::OK;
            }
               //code...
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        return ExitCode::OK;
    }

    public static function Person($id)
    {
        $person = Yii::$app
            ->db2
            ->createCommand('SELECT * FROM `hr_person` WHERE ID = :id')
            ->bindValue(':id', $id)
            ->queryOne();
        if ($person) {
            $emp = Employees::findOne(['cid' => $person['HR_CID']]);
            return $emp;
        }
    }
}

// SELECT x4.*,(x4.days-x4.sum_leave) as total FROM(
//     SELECT x3.*,
//     COALESCE((SELECT days FROM leave_entitlements WHERE emp_id = x3.emp_id AND leave_type_id = x3.leave_type_id AND thai_year=2567),0) as days,
//     COALESCE((SELECT sum(total_days) FROM `leave` WHERE emp_id = x3.emp_id AND leave_type_id = x3.leave_type_id AND thai_year=2567),0) as sum_leave
//     FROM(
//     SELECT x2.*,
//     (SELECT max_days FROM `leave_policies` WHERE position_type_id = x2.position_type AND year_of_service <= x2.years_of_service ORDER BY year_of_service DESC limit 1) as max_days
//     FROM(
//     select x1.* from (SELECT
//                 e.id as emp_id,
//                 concat(e.fname,' ',e.lname) as fullname,
//                 lt.title as leave_type_name,
//                 l.leave_type_id,
//                 e.position_type,
//                 pt.title as position_type_name,
//                 TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) AS years_of_service
//                 FROM employees e
//                 LEFT JOIN leave_policies lp ON lp.position_type_id = e.position_type
//                 LEFT JOIN `leave` l ON e.id = l.emp_id AND l.leave_type_id = 'LT4'
//                 JOIN categorise lt ON l.leave_type_id = lt.code AND lt.name = 'leave_type'
//                 JOIN categorise pt ON e.position_type = pt.code AND pt.name = 'position_type'
//                 AND e.position_type NOT IN('PT5')
//                 GROUP BY e.id
//                 ORDER BY `e`.`id` ASC) as x1) as x2) as x3) as x4;
