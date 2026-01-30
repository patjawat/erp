<?php

namespace app\modules\approveV2\controllers;

use Yii;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\approveV2\models\Approve;
use app\modules\approveV2\models\ApproveSearch;

class MainStockController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        $searchModel = new ApproveSearch([
              'status' => 'Pending'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith(['stock'], true, 'INNER JOIN');
         $dataProvider->query->andFilterWhere(['approve.name' => 'main_stock', 'approve.emp_id' => $me->id]);
        $dataProvider->query->andWhere(['<>','approve.status','None']);
         $dataProvider->query->andFilterWhere(['stock_events.emp_id' => $searchModel->emp_id]);
        $dataProvider->query->andFilterWhere(['>=', 'stock_events.created_at', AppHelper::convertToGregorian($searchModel->date_start). ' 00:00:00']);
         $dataProvider->query->andFilterWhere(['<=', 'stock_events.created_at', AppHelper::convertToGregorian($searchModel->date_end). ' 23:59:59']);
        
        $dataProvider->query->orderBy(['id' => SORT_DESC]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdate($id)
    {
        $me = UserHelper::GetEmployee();
        $model = Approve::findOne(['id' => $id,  'name' => 'main_stock','emp_id' => $me->id]);
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

     public function actionUpdateFormStore($id)
    {
        $me = UserHelper::GetEmployee();
        $model = Approve::findOne(['id' => $id, 'name' => 'main_stock']);
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
