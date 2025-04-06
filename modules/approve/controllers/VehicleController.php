<?php

namespace app\modules\approve\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use app\modules\hr\models\Leave;
use yii\web\NotFoundHttpException;
use app\modules\approve\models\Approve;
use app\modules\booking\models\Vehicle;
use app\modules\approve\models\ApproveSearch;

class VehicleController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        $searchModel = new ApproveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'vehicle','emp_id' => $me->id,'status' => 'Pending']);
        $dataProvider->query->orderBy(['id' => SORT_DESC]);
        
        return $this->render('index',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdate($id)
    {
        $me = UserHelper::GetEmployee();
        //ข้อมูลการ Approve
        $approve = Approve::findOne(['id' => $id, 'emp_id' => $me->id]);
        $old = $approve->data_json;
        // ข้อมูลการลา
        $leave = Vehicle::findOne($approve->from_id);
        
        if (!$approve) {
            return [
                'title' => 'แจ้งเตือน',
                'content' => '<h6 class="text-center">ไม่อนุญาต</h6>',
            ];
        }
        
        if ($this->request->isPost && $approve->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            
            if($approve->status == 'Reject'){
                $approve->bookCar->status = 'Demo';
                return $approve->bookCar->save(false);   
            }
            $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
            $approve->data_json = ArrayHelper::merge($old,$approve->data_json, $approveDate);      
           
        //     //ถ้าหัวหน้ากลุ่ม Approve
        //     if($approve->level == 2){
        //         $leave->status = 'Checking';
        //         $leave->save();
        //   }

        //     // ผุ้ตรวจสอบวันลาก่อนส่งให้ ผอ.
        //     if ($approve->level == 3) {
        //         $approve->emp_id = $me->id;
        //     }
           

        //     if ($approve->save()) {
        //         $nextApprove = Approve::findOne(['from_id' => $approve->from_id, 'level' => ($approve->level + 1)]);
        //         if ($nextApprove) {
        //             $nextApprove->status = 'Pending';
        //             $nextApprove->save();
        //         }
             
                
        //         // ถ้า ผอ. อนุมัติ ให้สถานะการลาเป็น Allow
        //         if ($approve->level == 4) {
        //             $leave->status = 'Allow';
        //             $leave->save();
        //         } else if ($approve->status == 'Reject') {
        //             $leave->status = 'Reject';
        //             $leave->save();
        //         } else {
                  
        //         }
             
        //         return [
        //             'status' => 'success',
        //             'container' => '#approve',
        //         ];
        //     }
        }
        
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => isset($approve->vehicle) ? $approve->vehicle->userRequest()['avatar'] : '',
                'content' => $this->renderAjax('update', [
                    'model' => $approve,
                ]),
            ];
        } else {
            return $this->render('update', [
               'model' => $approve,
            ]);
        }
    }

    
}
