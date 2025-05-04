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
use app\modules\dms\models\Documents;
use app\modules\hr\models\Development;
use app\modules\filemanager\models\Uploads;
use app\modules\hr\models\DevelopmentDetail;
use app\modules\filemanager\components\FileManagerHelper;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ImportEmpDevController extends Controller{

public function actionIndex(){
    
    
    $sql = "SELECT i.RECORD_HEAD,i.RECORD_HEAD_USE,go.RECORD_GO_NAME,l.LOCATION_ORG_NAME,
    v.RECORD_VEHICLE_NAME,
    i.DATE_GO,
    i.DATE_BACK,
    i.DATE_TRAVEL_GO,
    i.DATE_TRAVEL_BACK
    FROM `grecord_index` i
    LEFT JOIN grecord_go go ON go.RECORD_GO_ID = i.RECORD_GO_ID
    LEFT JOIN grecord_org_location l ON l.LOCATION_ID = i.RECORD_LOCATION_ID
    LEFT JOIN grecord_vehicle v ON v.RECORD_VEHICLE_ID = i.RECORD_VEHICLE_ID;";
    $querys = Yii::$app->db2->createCommand($sql)->queryAll();
    
    if (BaseConsole::confirm('การพัฒนา '.count($querys).' รายการ ยืนยัน ??')) {
        $num = 1;
        $total = count($querys);
            foreach ($querys as $item) {
                $checkModel = Development::findOne(['topic' => $item['RECORD_HEAD_USE']]);
                if (!$checkModel) {
                    $model = new Development();
                } else {
                    $model = $checkModel;
                }
               
                if($item['RECORD_HEAD_USE']){
                    $model->topic = $item['RECORD_HEAD_USE'] ?? '-';
                    $model->date_start = $item['DATE_GO'];
                    $model->date_end = $item['DATE_BACK'];
                    $model->vehicle_date_start = $item['DATE_TRAVEL_GO'] ?? NULL;
                    $model->vehicle_date_end = $item['DATE_TRAVEL_BACK'] ?? NULL;
                    $model->status = 'Pending';
                    $model->thai_year = AppHelper::YearBudget($item['DATE_GO']);
                    $model->leader_id = 1;
                    $model->assigned_to = 1;
                    $model->emp_id = 1;
                    // $model->vehicle = $item['RECORD_VEHICLE_NAME'];
                    $this->creteDetailMember($model);
                    if($model->save(false)){
                        $percentage = (($num++) / $total) * 100;
                        // $this->createDetailRefer($model,$item);
                        echo 'ดำเนินการแล้ว : ' . number_format($percentage, 2) . "%\n";
                    }
                }
                }
            }

    }

    //นำเข้าส่วนของคณะที่ไปด้วยกัน
    protected function creteDetailMember($data)
    {
            $check = DevelopmentDetail::findOne(['development_id' => $data->id]);
            if(!$check){
                $model = new DevelopmentDetail();
                
            }else{
                $model = $check;
            }
            $model->development_id = $data->id;
            $model->name = 'member';
            $model->emp_id = $data->emp_id;
            $model->save(false);
    } 
}