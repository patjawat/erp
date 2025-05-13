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
use app\modules\approve\models\Approve;
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
class ImportDevelopmentController extends Controller{

public function actionIndex(){
    
    
    $sql = "SELECT go.RECORD_GO_NAME,l.LOCATION_ORG_NAME,
    v.RECORD_VEHICLE_NAME,
    i.*
    FROM `grecord_index` i
    LEFT JOIN grecord_go go ON go.RECORD_GO_ID = i.RECORD_GO_ID
    LEFT JOIN grecord_org_location l ON l.LOCATION_ID = i.RECORD_LOCATION_ID
    LEFT JOIN grecord_vehicle v ON v.RECORD_VEHICLE_ID = i.RECORD_VEHICLE_ID;;";
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
                    $model->status = $this->getStatus($item['STATUS']);
                    $model->thai_year = AppHelper::YearBudget($item['DATE_GO']);
                    $model->assigned_to = $this->Person($item['OFFER_WORK_HR_ID'])?->id  ?? 0;
                    $model->emp_id = $this->Person($item['HR_ID'])?->id ?? 0;
            
                    $model->leader_id = $this->Person($item['LEADER_HR_ID'])?->id;
                    $model->data_json = $item;
                    // $model->vehicle = $item['RECORD_VEHICLE_NAME'];
                    if($model->save(false)){
                        $this->creteDetailMember($model);
                        $this->creteApprove($model);
                        $percentage = (($num++) / $total) * 100;
                        // $this->createDetailRefer($model,$item);
                        echo 'ดำเนินการแล้ว : ' . number_format($percentage, 2) . "%\n";
                    }

                }
                }
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
    
    private function getStatus($data)
    {
        switch ($data) {
            case 'APPLY':
                return 'Pending';
            case 'RECEIVE':
                return 'Pending';
            case 'SUCCESS':
                return 'Approve';
            case 'CANCEL':
                return 'Cancel';
            case 'NOTALLOW':
                return 'Reject';
            default:
                return 'Unknown';
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
    protected function creteApprove($model)
    {
        $checkApprove1 = Approve::findOne(['from_id' => $model->id, 'name' => 'development','emp_id' => $model->leader_id,'level' => 1]);
        if($checkApprove1){
            $approve1 = $checkApprove1;
        }else{
            $approve1 = new Approve();
        }
            $approve1->name = 'development';
            $approve1->from_id = $model->id;
            $approve1->level = 1;
            $approve1->emp_id = $model->leader_id;
            $approve1->title = "เห็นชอบ";
            $approve1->data_json = [
                "topic" => "เห็นชอบ",
                "approve_date" => null
            ];   
        $approve1->status = $model->status;
        if($approve1->save(false)){
            echo 'Create Approve : '.$approve1->id."\n";
            
        }
    }
}