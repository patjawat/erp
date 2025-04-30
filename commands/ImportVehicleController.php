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
use mdm\autonumber\AutoNumber;
use yii\helpers\BaseFileHelper;
use app\modules\hr\models\Leave;
use app\modules\hr\models\Employees;
use app\modules\booking\models\Vehicle;
use app\modules\hr\models\LeaveEntitlements;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ImportVehicleController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {
        // นำวันลา
        $querys = Yii::$app->db2->createCommand('SELECT 
        p.HR_PREFIX_ID, 
        p.HR_FNAME, 
        p.HR_LNAME, 
        v.RESERVE_NAME,
        v.RESERVE_BEGIN_DATE,
        v.RESERVE_BEGIN_TIME,
        v.RESERVE_END_DATE,
        v.RESERVE_END_TIME,
        v.STATUS
    FROM 
        vehicle_car_reserve v
    LEFT JOIN 
        hrd_person p ON p.ID = v.RESERVE_PERSON_ID;')
        ->queryAll();
        
        $num = 1;
        $total = count($querys);
        foreach ($querys as $key => $item) {

           $emp = $this->Person($item['HR_PREFIX_ID']);

            $check = Vehicle::find()->where([
                'reason' => $item['RESERVE_NAME'],
                'date_start' => $item['RESERVE_BEGIN_DATE'],
                'date_end' => $item['RESERVE_END_DATE'],
                'time_start' => $item['RESERVE_BEGIN_TIME'],
                'time_end' => $item['RESERVE_END_TIME'],
            ])->one();

            $model = $check ?? new Vehicle();
            $check ?? ($model->code  =   \mdm\autonumber\AutoNumber::generate('REQ-CAR' .date('ymd') . '-???'));
            $model->thai_year = AppHelper::YearBudget($item['RESERVE_BEGIN_DATE']);
            $model->reason = $item['RESERVE_NAME'];
            $model->vehicle_type_id = 'car';
            $model->go_type = 2;
            $model->urgent = 1;
            $model->location = 1;
            $model->status = $this->Status($item['STATUS']);
            $model->leader_id = 2;
            $model->date_start = $item['RESERVE_BEGIN_DATE'] ?? date('Y-m-d');
            $model->date_end = $item['RESERVE_END_DATE'];
            $model->time_start = $item['RESERVE_BEGIN_TIME'] ?? '00:00';
            $model->time_end = $item['RESERVE_END_TIME'] ?? '00:00';
            $model->emp_id = $emp->id ?? 0;
          
            // $RESERVE_END_TIME->data_json = [
            //     'cid' => $item['LEAVE_PERSON_CODE'],
            //     'fullname' => $item['LEAVE_PERSON_FULLNAME'],
            // ];

            if($model->save(false)){
                $percentage = (($num++) / $total) * 100;
                echo 'ดำเนินการแล้ว : ' . number_format($percentage, 2) . "%\n";
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
            return 'NULL';
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
