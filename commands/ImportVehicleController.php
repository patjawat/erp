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
use app\modules\booking\models\Vehicle;
use app\modules\hr\models\LeaveEntitlements;
use app\modules\booking\models\VehicleDetail;

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
        p.HR_FNAME, 
        p.HR_LNAME, 
        pr.PRIORITY_NAME,
        i.CAR_REG,
        car_req.CAR_REG as car_req,
        d.PERSON_ID as driver_id,
        l.LOCATION_ORG_NAME,
        v.*
    FROM 
        vehicle_car_reserve v
    LEFT JOIN 
        hrd_person p ON p.ID = v.RESERVE_PERSON_ID
    LEFT JOIN 
         vehicle_car_index i  ON i.CAR_ID = v.CAR_SET_ID
    LEFT JOIN 
         vehicle_car_index car_req  ON car_req.CAR_ID = v.CAR_REQUEST_ID
    LEFT JOIN 
         vehicle_car_driver d  ON d.DRIVER_ID = v.CAR_DRIVER_SET_ID
    LEFT JOIN 
    	 grecord_org_location l ON l.LOCATION_ID = v.RESERVE_LOCATION_ID
    LEFT JOIN 
    	vehicle_car_priority pr ON CAST(pr.PRIORITY_ID AS UNSIGNED) = v.PRIORITY_ID ORDER BY v.RESERVE_END_DATE ASC;')
        ->queryAll();
        
        $num = 1;
        $total = count($querys);
        foreach ($querys as $key => $item) {
            if($item['RESERVE_BEGIN_DATE'] && $item['RESERVE_END_DATE']){



           $emp = $this->Person($item['RESERVE_PERSON_ID']);

            $check = Vehicle::find()->where([
                'reason' => $item['RESERVE_NAME'],
                'date_start' => $item['RESERVE_BEGIN_DATE'],
                'date_end' => $item['RESERVE_END_DATE'],
                'time_start' => $item['RESERVE_BEGIN_TIME'],
                'time_end' => $item['RESERVE_END_TIME'],
            ])->one();

            $model = $check ?? new Vehicle();
            $check ?? ($model->code  =   \mdm\autonumber\AutoNumber::generate('CAR' .date('ymd') . '-???'));
            $model->thai_year = AppHelper::YearBudget($item['RESERVE_BEGIN_DATE']);
            $model->reason = $item['RESERVE_NAME'];
            $model->vehicle_type_id = 'official';
            $model->refer_type = 'normal';
            $model->go_type = 1;
            $model->urgent = $item['PRIORITY_NAME'];
            $model->location = $this->checkLocation($item['LOCATION_ORG_NAME']);
            $model->status = $this->Status($item['STATUS']);
            $model->leader_id =$this->Person($item['LEADER_PERSON_ID'])?->id;
            $model->date_start = $item['RESERVE_BEGIN_DATE'] ?? date('Y-m-d');
            $model->date_end = $item['RESERVE_END_DATE'];
            $model->time_start = $item['RESERVE_BEGIN_TIME'] ?? '00:00';
            $model->time_end = $item['RESERVE_END_TIME'] ?? '00:00';
            $model->emp_id = $emp->id ?? 0;
            $model->driver_id = $this->Person($item['CAR_DRIVER_ID'])?->id;
            $model->created_at = $item['RESERVE_DATE_TIME'];
            $model->license_plate = $item['car_req'];
          
            if($emp){
                $model->data_json = [
                    'old_data' => $item
                    
                ];
            }

            if($model->save(false)){
                $percentage = (($num++) / $total) * 100;
                $this->createDetail($model,$item);
                echo 'ดำเนินการแล้ว : ' . number_format($percentage, 2) . "%\n";
            }

        }
    }
        return ExitCode::OK;
    }


    
    protected function createDetail($model,$item)
    {
        if($model->date_start && $model->date_end){
            
            $startDate = new DateTime($model->date_start);
            $endDate = new DateTime($model->date_end);
      
        $endDate->modify('+1 day');  // เพิ่ม 1 วัน เพื่อให้รวมวันที่สิ้นสุด

        $interval = new DateInterval('P1D');  // ระยะห่าง 1 วัน
        $period = new DatePeriod($startDate, $interval, $endDate);
        //ถ้าเป็นรถยนต์ส่วนตัว
        if($model->vehicle_type_id == "personal"){
            $me = UserHelper::GetEmployee();
            if($model->go_type == "1"){
                $dates = [];
                foreach ($period as $date) {
                $check1 = VehicleDetail::find()->where(['vehicle_id' => $model->id])->one();
                if(!$check1){
                    $newDetail = new VehicleDetail;
                    $newDetail->date_start = $date->format('Y-m-d');
                    $newDetail->date_end = $date->format('Y-m-d');
                    $newDetail->vehicle_id = $model->id;
                    $newDetail->driver_id = $me->id;
                    $newDetail->license_plate = $model->license_plate;
                    $newDetail->mileage_start = $item['CAR_NUMBER_BEGIN'];
                    $newDetail->mileage_end = $item['CAR_NUMBER_BACK'];
                    $newDetail->oil_price = $item['OIL_IN_BATH'];
                    $newDetail->oil_liter = $item['OIL_IN_LIT'];
                    $newDetail->driver_id = $this->Person($item['CAR_DRIVER_SET_ID'])?->id;
                    $newDetail->status = $this->Status($model->status);
                    $newDetail->save(false);
                }
                }
            }else{
                $check2 = VehicleDetail::find()->where(['vehicle_id' => $model->id])->one();
                if(!$check2){
                $newDetail = new VehicleDetail;
                $newDetail->date_start = $model->date_start;
                $newDetail->date_end = $model->date_end;
                $newDetail->vehicle_id = $model->id;
                $newDetail->driver_id = $me->id;
                $newDetail->license_plate = $model->license_plate;
                $newDetail->mileage_start = $item['CAR_NUMBER_BEGIN'];
                $newDetail->mileage_end = $item['CAR_NUMBER_BACK'];
                $newDetail->oil_price = $item['CAR_NUMBER_BACK'];
                $newDetail->oil_price = $item['OIL_IN_BATH'];
                $newDetail->oil_liter = $item['OIL_IN_LIT'];
                $newDetail->driver_id = $this->Person($item['CAR_DRIVER_SET_ID'])?->id;
                $newDetail->status = $this->Status($model->status);
                $newDetail->save(false);
                }
            }
            
          
            $checkApprove = Approve::find()->where(['from_id' => $model->id,'name' => 'vehicle','level' => 1])->one();
            if(!$checkApprove){
                $info = SiteHelper::getInfo();
                $newAprove = new Approve();
                $newAprove->from_id = $model->id;
                $newAprove->name = 'vehicle';
                $newAprove->emp_id = $info['director']->id;
                $newAprove->title = 'ขออนุมัติใช้รถ';
                $newAprove->data_json = ['label' => 'อนุมัติ'];
                $newAprove->level = 1;
                $newAprove->status = 'Pending';
                $newAprove->save(false);            
            }

        }else{

            if ($model->go_type == "1") {
                $dates = [];
                foreach ($period as $date) {
                    $check3 = VehicleDetail::find()->where(['vehicle_id' => $model->id])->one();
                    if(!$check3){
                        $dates[] = $date->format('Y-m-d');
                        $newDetail = new VehicleDetail;
                        $newDetail->vehicle_id = $model->id;
                        $newDetail->date_start = $date->format('Y-m-d');
                        $newDetail->date_end = $date->format('Y-m-d');
                        $newDetail->mileage_start = $item['CAR_NUMBER_BEGIN'];
                        $newDetail->mileage_end = $item['CAR_NUMBER_BACK'];
                        $newDetail->oil_price = $item['OIL_IN_BATH'];
                        $newDetail->oil_liter = $item['OIL_IN_LIT'];
                        $newDetail->driver_id = $this->Person($item['CAR_DRIVER_SET_ID'])?->id;
                        $newDetail->status = $this->Status($model->status);
                        $newDetail->save(false);
                    }
                }
            }else{
                $check4 = VehicleDetail::find()->where(['vehicle_id' => $model->id])->one();
                if(!$check4){   
                    $newDetail = new VehicleDetail;
                    $newDetail->vehicle_id = $model->id;
                    $newDetail->date_start = $model->date_start;
                    $newDetail->date_end = $model->date_end;
                    $newDetail->mileage_start = $item['CAR_NUMBER_BEGIN'];
                    $newDetail->mileage_end = $item['CAR_NUMBER_BACK'];
                    $newDetail->oil_price = $item['OIL_IN_BATH'];
                    $newDetail->oil_liter = $item['OIL_IN_LIT'];
                    $newDetail->driver_id = $this->Person($item['CAR_DRIVER_SET_ID'])?->id;
                    $newDetail->status = $this->Status($model->status);
                    $newDetail->save(false);
                }
            }
        }
    }
    }
    

    public function actionRefer()
    {
        $sql = "SELECT rt.REFER_TYPE_NAME,c.CAR_REG,l.LOCATION_ORG_NAME,r.* FROM `vehicle_car_refer` r
        LEFT JOIN vehicle_car_index c ON c.CAR_ID = r.CAR_ID
        LEFT JOIN grecord_org_location l ON l.LOCATION_ID = r.REFER_LOCATION_GO_ID
        LEFT JOIN vehicle_car_refer_type rt ON rt.REFER_TYPE_ID = r.REFER_TYPE_ID WHERE r.OUT_DATE IS NOT NULL;;";
        // นำวันลา
        $querys = Yii::$app->db2->createCommand($sql)
        ->queryAll();
        
        $num = 1;
        $total = count($querys);
        foreach ($querys as $key => $item) {
            try {

           $emp = $this->Person($item['USER_REQUEST_ID']);

            $check = Vehicle::find()->where([
                'reason' => $item['REFER_TYPE_NAME'],
                'date_start' => $item['OUT_DATE'],
                'date_end' => $item['BACK_DATE'],
                'time_start' => $item['OUT_TIME'],
                'time_end' => $item['BACK_TIME'],
            ])->one();

            $model = $check ?? new Vehicle();
            $check ?? ($model->code  =   \mdm\autonumber\AutoNumber::generate('AMB' .date('ymd') . '-???'));
            $model->thai_year = AppHelper::YearBudget($item['OUT_DATE']);
            $model->reason = $item['REFER_TYPE_NAME'] ?? '-';
            $model->vehicle_type_id = 'ambulance'; //รถพยาบาล
            $model->refer_type = $this->referType($item['REFER_TYPE_NAME']) ?? '-';
            $model->go_type = 1;
            $model->urgent = 'ปกติ';
            $model->location = $this->checkLocation($item['LOCATION_ORG_NAME']);
            $model->status = $this->Status($item['STATUS']) ?? 'None';
            $model->leader_id = 0;
            $model->date_start = $item['OUT_DATE'];
            $model->date_end = ($item['BACK_DATE'] === '0000-00-00' || empty($item['BACK_DATE'])) ? null : $item['BACK_DATE'];
            $model->time_start = $item['OUT_TIME'] ?? '00:00';
            $model->time_end = $item['BACK_TIME'] ?? '00:00';
            $model->emp_id = $emp->id ?? 0;
            $model->license_plate = $item['CAR_REG'];
          
            // if($emp){
                $model->data_json = [
                    'old_date' => $item,
                    
                ];
            // }

            if($model->save(false)){
                $percentage = (($num++) / $total) * 100;
                // $this->createDetailRefer($model,$item);
                echo 'ดำเนินการแล้ว : ' . number_format($percentage, 2) . "%\n";
            }
                            //code...
                        } catch (\Throwable $th) {
                            echo "Error processing item: " . json_encode($item) . "\n";
                            echo "Exception: " . $th->getMessage() . "\n";
                        }
            

        }
        return ExitCode::OK;
    }



    protected function createDetailRefer($model,$item)
    {
        if($model->date_start && $model->date_end){
                // foreach ($period as $date) {
                $check1 = VehicleDetail::find()->where(['vehicle_id' => $model->id])->one();
                if(!$check1){
                    $newDetail = new VehicleDetail;
                    $newDetail->date_start = $model->date_start;
                    $newDetail->date_end = $model->date_end ?? $model->date_start;
                    $newDetail->vehicle_id = $model->id;
                    $newDetail->license_plate = $model->license_plate;
                    $newDetail->mileage_start = $item['CAR_GO_MILE'];
                    $newDetail->mileage_end = $item['CAR_BACK_MILE'];
                    $newDetail->oil_price = $item['ADD_OIL_BATH'];
                    $newDetail->oil_liter = $item['ADD_OIL_LIT'];
                    $newDetail->driver_id = $this->Person($item['DRIVER_ID'])?->id ?? 0;
                    $newDetail->status = $this->Status($model->status);
                    $newDetail->save(false);
                }
                // }
            }
    }
    
    public static function referType($referType){
        if($referType == 'REFER'){
            return 'REFER';
        }elseif($referType == 'EMS'){
            return 'EMS';
        }elseif($referType == 'รับ-ส่ง [ไม่ฉุกเฉิน]'){
            return 'NORMAL';
        }else{
            return $referType;
        }
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


    protected function checkLocation($locationName)
    {
     $location = Categorise::findOne(['name' => 'document_org','title' => $locationName]);  
     if(!$location){
        $maxCode = Categorise::find()
    ->select(['code' => new \yii\db\Expression('MAX(CAST(code AS UNSIGNED))')])
    ->where(['like', 'name', 'document_org'])
    ->scalar();
        $newLocation = new Categorise;
        $newLocation->code = ($maxCode+1);
        $newLocation->title = $locationName;
        $newLocation->name = 'document_org';
        $newLocation->save(false);
        return $newLocation->code;
     }else{
        return $location->code;
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
