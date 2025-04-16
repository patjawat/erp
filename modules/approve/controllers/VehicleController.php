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
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            $me = UserHelper::GetEmployee();
            $status = $this->request->post('status');
            $old = $model->data_json;

            $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
            $model->data_json = ArrayHelper::merge($old, $model->data_json, $approveDate);
            $model->status = $status;

            if ($model->emp_id == '') {
                $model->emp_id = $me->id;
            }

            if ($model->save()) {
                // ถ้าไม่อนุมัติให้ return ออกเลย
                if ($model->status == 'Reject') {
                    $model->vehicle->status = 'Reject';
                    $model->vehicle->save();
                    $model->vehicle->MsgReject();

                }else{
                    $model->vehicle->status = 'Approve';
                    $model->vehicle->save();
                }

                    return [
                        'status' => 'success'
                    ];
                

            }
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'ขออนุมัติวันลา',
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

    protected function findModel($id)
    {
        if (($model = Approve::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
    
    
}
