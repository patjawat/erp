<?php

namespace app\modules\me\controllers;

use Yii;
use yii\web\Response;
use app\models\Approve;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\models\ApproveSearch;
use app\components\LineNotify;
use app\components\UserHelper;
use app\modules\hr\models\Leave;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\Employees;

class ApprovePurchaseController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        $searchModel = new ApproveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'purchase','emp_id' => $me->id,'status' => 'Pending']);
        $dataProvider->query->orderBy(['id' => SORT_DESC]);
        
        return $this->render('index',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Approve::findOne($id);
        
        
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('view',[
                'model' => $model
        ]),
        ];
    }
    
    public function actionApprove()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        if ($this->request->isPost) 
        {
            $data = $this->request->post();
            $approve = Approve::findOne($data['id']);
            
            $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
            $approve->data_json = ArrayHelper::merge($approve->data_json, $approveDate);
            $approve->status = 'Approve';
            

            if ($approve->save()) {
                
                $nextApprove = Approve::findOne(['from_id' => $approve->from_id, 'level' => ($approve->level + 1)]);
                if ($nextApprove) {
                    $nextApprove->status = 'Pending';
                    $nextApprove->save();
                }
             
                
                // ถ้า ผอ. อนุมัติ ให้สถานะการลาเป็น Allow
                if ($approve->level == 3) {
                  
                    $employee = Employees::find()->where(['user_id' => $approve->purchase->created_by])->one();
                    $message = 'ใบขอซื้อ : '. $approve->purchase->pr_number.' ได้รับการอนุมัติแล้ว';
                    LineNotify::sendPushMessage($employee->user->line_id, $message);
                }

                return [
                    'status' => 'success',
                    'container' => '#approve',
                ];
            }
        }
    }

}
