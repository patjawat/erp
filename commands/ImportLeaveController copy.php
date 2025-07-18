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
use app\modules\lm\models\Leave;
use app\modules\hr\models\Employees;
use app\modules\hr\models\LeavePermission;

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
        //นำวันลา
        $querys = Yii::$app->db2->createCommand("SELECT 
                    LEAVE_YEAR_ID,
                    LEAVE_BECAUSE,
                    LEAVE_DATE_BEGIN,
                    LEAVE_DATE_END,
                    LEAVE_DATE_SUM,
                    DAY_TYPE_ID,
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
                    STATUS_CODE,
                    STATUS_NAME,
                    gleave_location.LOCATION_ID,
                    gleave_location.LOCATION_NAME
                    FROM gleave_register
                    LEFT JOIN gleave_type ON gleave_register.LEAVE_TYPE_CODE = gleave_type.LEAVE_TYPE_ID
                    LEFT JOIN gleave_status ON gleave_register.LEAVE_STATUS_CODE = gleave_status.STATUS_CODE
                    LEFT JOIN gleave_location ON gleave_register.LOCATION_ID = gleave_location.LOCATION_ID
                    ORDER BY gleave_register.ID DESC;")->queryAll();
        $num = 1;
        $total = count($querys);
        foreach ($querys as $key => $item) {
            $emp = Employees::findOne(['cid' => $item['LEAVE_PERSON_CODE']]);
            
            $checkLeave = Leave::find()->where([
                'thai_year' => $item['LEAVE_YEAR_ID'],
                'date_start' => $item['LEAVE_DATE_BEGIN'],
                'date_end' => $item['LEAVE_DATE_END'],
                
                ])->andWhere(['json_extract(data_json, "$.cid")' => $item['LEAVE_PERSON_CODE']])->one();
                $leaveType = Categorise::findOne(['name' => 'leave_type','title' => $item['LEAVE_TYPE_NAME']]);

                
            if (!$checkLeave) {
                $newLeave = new Leave([
                    'leave_type_id' => $leaveType ? $leaveType->code : '',
                    'emp_id' => $emp->id,
                    'thai_year' => $item['LEAVE_YEAR_ID'],
                    'date_start' => $item['LEAVE_DATE_BEGIN'],
                    'date_end' => $item['LEAVE_DATE_END'],
                    'data_json' => [
                        'cid' => $item['LEAVE_PERSON_CODE'],
                        'fullname' => $item['LEAVE_PERSON_FULLNAME'],
                        'leave_type_id' => $item['LEAVE_TYPE_ID'],
                        'leave_type_name' => $item['LEAVE_TYPE_NAME'],
                        'status_code' => $item['STATUS_CODE'],
                        'status_name' => $item['STATUS_NAME'],
                        'location_id' => $item['LOCATION_ID'],
                        'location_name' => $item['LOCATION_NAME'],
                        'leave_because' => $item['LEAVE_BECAUSE'],
                        'leave_date_sum' => $item['LEAVE_DATE_SUM'],
                        'day_type_id' => $item['DAY_TYPE_ID'],
                        'leave_contact' => $item['LEAVE_CONTACT'],
                        'leave_datetime_regis' => $item['LEAVE_DATETIME_REGIS'],
                        'leave_type_code' => $item['LEAVE_TYPE_CODE'],
                        'leave_person_id' => $item['LEAVE_PERSON_ID'],
                        'leave_status_code' => $item['LEAVE_STATUS_CODE'],
                        'leave_contact_phone' => $item['LEAVE_CONTACT_PHONE'],
                        'leave_work_send' => $item['LEAVE_WORK_SEND'],
                        'leave_work_send_id' => $item['LEAVE_WORK_SEND_ID'],
                        'leader_person_id' => $item['LEADER_PERSON_ID'],
                        'leader_person_name' => $item['LEADER_PERSON_NAME'],
                        'leader_person_position' => $item['LEADER_PERSON_POSITION'],
                    ]
                ]);
                $newLeave->save();
                // echo "new Save".$item['LEAVE_PERSON_CODE'].' => '.$item['LEAVE_DATE_BEGIN']."\n";
            } else {
                $checkLeave->leave_type_id = $leaveType ? $leaveType->code : '';
                $checkLeave->thai_year = $item['LEAVE_YEAR_ID'];
                $checkLeave->date_start = $item['LEAVE_DATE_BEGIN'];
                $checkLeave->date_end = $item['LEAVE_DATE_END'];
                $checkLeave->total_days =$item['LEAVE_DATE_SUM'];
                $checkLeave->data_json = [
                        'cid' => $item['LEAVE_PERSON_CODE'],
                        'fullname' => $item['LEAVE_PERSON_FULLNAME'],
                        'leave_type_id' => $item['LEAVE_TYPE_ID'],
                        'leave_type_name' => $item['LEAVE_TYPE_NAME'],
                        'status_code' => $item['STATUS_CODE'],
                        'status_name' => $item['STATUS_NAME'],
                        'location_id' => $item['LOCATION_ID'],
                        'location_name' => $item['LOCATION_NAME'],
                        'leave_because' => $item['LEAVE_BECAUSE'],
                        'leave_date_sum' => $item['LEAVE_DATE_SUM'],
                        'day_type_id' => $item['DAY_TYPE_ID'],
                        'leave_contact' => $item['LEAVE_CONTACT'],
                        'leave_datetime_regis' => $item['LEAVE_DATETIME_REGIS'],
                        'leave_type_code' => $item['LEAVE_TYPE_CODE'],
                        'leave_person_id' => $item['LEAVE_PERSON_ID'],
                        'leave_status_code' => $item['LEAVE_STATUS_CODE'],
                        'leave_contact_phone' => $item['LEAVE_CONTACT_PHONE'],
                        'leave_work_send' => $item['LEAVE_WORK_SEND'],
                        'leave_work_send_id' => $item['LEAVE_WORK_SEND_ID'],
                        'leader_person_id' => $item['LEADER_PERSON_ID'],
                        'leader_person_name' => $item['LEADER_PERSON_NAME'],
                        'leader_person_position' => $item['LEADER_PERSON_POSITION'],
                ];
                $checkLeave->save();
                // echo "Update ".$item['LEAVE_PERSON_CODE'].' => '.$item['LEAVE_DATE_BEGIN']."\n";
            }
            $percentage = (($num++) / $total) * 100;
            echo "ดำเนินการแล้ว : " . number_format($percentage, 2) . "%\n";
        }
        return ExitCode::OK;
    }

    public function actionPermission()
    {
        $querys = Yii::$app->db2->createCommand("SELECT l.*,pt.HR_PERSON_TYPE_NAME FROM `gleave_over` l LEFT JOIN hrd_person_type pt ON pt.HR_PERSON_TYPE_ID = l.HR_PERSON_TYPE_ID;")->queryAll();
        foreach ($querys as $key => $item) {
            $emp = Employees::findOne(['cid' => $item['PERSON_ID']]);
            $positionType = Categorise::find()->where(['name' => 'position_type','title' =>  $item['HR_PERSON_TYPE_NAME']])->one();
            $check = LeavePermission::find()->where(['thai_year' => $item['OVER_YEAR_ID'],'emp_id' => $emp->id])->one();
            if($check)
            {
                $model = $check;
            }else{
                $model = new LeavePermission();
            }
            
            $model->emp_id = $emp->id;
            $model->thai_year = $item['OVER_YEAR_ID'];
            $model->thai_year = $item['OVER_YEAR_ID'];
        }
        
    }
}
