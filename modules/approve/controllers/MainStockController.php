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

class MainStockController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        $searchModel = new ApproveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'main_stock', 'emp_id' => $me->id, 'status' => 'Pending']);
        $dataProvider->query->orderBy(['id' => SORT_DESC]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdate($id)
    {
        $me = UserHelper::GetEmployee();
        $model = Approve::findOne(['id' => $id, 'emp_id' => $me->id]);
        \Yii::$app->response->format = Response::FORMAT_JSON;
        if ($this->request->isPost) {
            $status = $this->request->post('status');
             // ระบบอนุมัติเบิกคลัง
             $old = $model->data_json;
             $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
             $model->data_json = ArrayHelper::merge($old, $model->data_json, $approveDate);
             $model->status = $status;
             //ถ้าบันทุกเรียบร้อย
             if($model->save(false))
             {
                // update ส่วน stock
                $oldStockObj = $model->stock->data_json;
                $checkData = $model->stock->empChecker;
                $checkerData = [
                    'checker_confirm_date' => date('Y-m-d H:i:s'),
                    'checker_name' => $checkData->fullname,
                    'checker_position' => $checkData->positionName(),
                    'checker_confirm' => ($model->status == 'Pass' ? 'Y' : 'N')
                ];
                
                if ($model->status == 'Pass') {
                    $model->stock->order_status = 'pending';
                }

                if ($model->status == 'Reject') {
                    $model->stock->order_status = 'cancel';
                }
                $model->stock->data_json = ArrayHelper::merge($oldStockObj, $model->stock->data_json, $checkerData);
                $model->stock->save(false);

                }
                
                return [
                    'status' => 'success'
                ];
                
        }
        
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => isset($model->stock) ? $model->stock->CreateBy('ขอเบิกวัสดุ')['avatar'] : '',
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
