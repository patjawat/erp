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
use app\modules\approve\models\ApproveSearch;

class PurchaseController extends \yii\web\Controller
{

    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        $searchModel = new ApproveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'purchase', 'emp_id' => $me->id, 'status' => 'Pending']);
        $dataProvider->query->orderBy(['id' => SORT_DESC]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    
   
    public function actionUpdate($id)
    {
        $me = UserHelper::GetEmployee();
        $model = Approve::findOne(['id' => $id,'name' => 'purchase']);
        if ($this->request->isPost) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $status = $this->request->post('status');
            $model->emp_id = $me->id;

             // ระบบอนุมัติเบิกคลัง
             $oldData = $model->data_json;
             $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
             $model->data_json = ArrayHelper::merge($oldData, $model->data_json, $approveDate);
             $model->status = $status;
             //ถ้าบันทุกเรียบร้อย
             if($model->save(false))
             {
                if($model->level == 3 && $model->status == 'Pass'){
                    $model->purchase->status = 2;
                    $model->purchase->save();
                }else{
                    // ถ้ามีสถานะถัดไป
                    $nextApprove = Approve::findOne(['from_id' => $model->from_id,'name' => 'purchase',  'level' => ($model->level + 1)]);
                    if($nextApprove){
                        $nextApprove->status = 'Pending';
                        $nextApprove->save();
                    }
                }
                    return ['status' => 'success'];       
        }
        
    }
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'ขออนุมัติขอซื้อ/ขอจ้าง',
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    
}
