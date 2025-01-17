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
class ImportLeaveController extends Controller
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
                    LEAVE_YEAR_ID,
                    LEAVE_BECAUSE,
                    LEAVE_DATE_BEGIN,
                    LEAVE_DATE_END,
                    LEAVE_DATE_SUM,
                    gleave_register.DAY_TYPE_ID,
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
                    STATUS_CODE,
                    STATUS_NAME,
                    gleave_location.LOCATION_ID,
                    gleave_location.LOCATION_NAME
                    FROM gleave_register
                    LEFT JOIN gleave_type ON gleave_register.LEAVE_TYPE_CODE = gleave_type.LEAVE_TYPE_ID
                    LEFT JOIN gleave_status ON gleave_register.LEAVE_STATUS_CODE = gleave_status.STATUS_CODE
                    LEFT JOIN gleave_location ON gleave_register.LOCATION_ID = gleave_location.LOCATION_ID
                    LEFT JOIN gleave_day_type ON gleave_day_type.DAY_TYPE_ID = gleave_register.DAY_TYPE_ID
                    ORDER BY gleave_register.ID DESC;')->queryAll();
        $num = 1;
        $total = count($querys);
        foreach ($querys as $key => $item) {
            $emp = Employees::findOne(['cid' => $item['LEAVE_PERSON_CODE']]);

           $sendwork = $this->Person($item['LEAVE_WORK_SEND_ID']);
           $leaderId = $this->Person($item['LEADER_PERSON_ID']);
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
            $leave->date_start = $item['LEAVE_DATE_BEGIN'];
            $leave->date_end = $item['LEAVE_DATE_END'];
            $leave->status = $item['STATUS_CODE'];
            if($item['DAY_TYPE_ID'] == 2){
                $leave->date_start_type = 0.5;
                $leave->date_end_type = 0;
                $leave->total_days = $item['LEAVE_DATE_SUM'] - 0.5;
            }elseif($item['DAY_TYPE_ID'] == 3){
                $leave->total_days = $item['LEAVE_DATE_SUM'] - 0.5;
                $leave->date_start_type = 0;
                $leave->date_end_type = 0.5;
            }else{
                $leave->date_start_type = 0;
                $leave->date_end_type = 0;
                $leave->total_days = $item['LEAVE_DATE_SUM'];
            }
            
            
            $leave->data_json = [
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
                'approve_1' => isset($leaderId) ? (string)$leaderId->id : 0,
                'approve_3' => isset($userCheckId) ? (string)$userCheckId->id : 0,
                'approve_fulname_3' => isset($userCheckId) ? (string)$userCheckId->fullname : 0,
                'leader' => isset($leaderId) ? (string)$leaderId->id : 0,
                'leader_person_name' => $item['LEADER_PERSON_NAME'],
                'leader_person_position' => $item['LEADER_PERSON_POSITION'],
            ];

            if($leave->save()){
                $leave->createApprove();
                // $leave->createLeaveStep();
                $percentage = (($num++) / $total) * 100;
                echo 'ดำเนินการแล้ว : ' . number_format($percentage, 2) . "%\n";
            }

        }
        return ExitCode::OK;
    }

    public function actionCreateApprove()
    {
        $leaves = Leave::find()->all();
        foreach ($leaves as $item) {
            $leave = Leave::find()->where(['id' => $item->id])->one();
            if($leave){
                $leave->createApprove();
                echo 'ดำเนินการแล้ว : '.$leave->createApprove(). "\n";
            }
        }
    }

    public function actionEtitlements()
    {
        $querys = Yii::$app->db2->createCommand("SELECT l.*,pt.HR_PERSON_TYPE_NAME FROM `gleave_over` l LEFT JOIN hrd_person_type pt ON pt.HR_PERSON_TYPE_ID = l.HR_PERSON_TYPE_ID;")->queryAll();
        foreach ($querys as $key => $item) {
            
            $emp = $this->Person($item['PERSON_ID']);
            $positionType = Categorise::find()->where(['name' => 'position_type','title' =>  $item['HR_PERSON_TYPE_NAME']])->one();
            $check = LeaveEntitlements::find()->where(['thai_year' => $item['OVER_YEAR_ID'],'emp_id' => $emp->id])->one();
            
            if($check)
            {
                $model = $check;
            }else{
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
            if($model->save(false)){
                echo 'ดำเนินการ : '.$model->emp_id. " \n";
            }else{
                echo 'ผิดพลาด : '.$model->emp_id. " \n";
                return ExitCode::OK;
            }
    }
    return ExitCode::OK;
        
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