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
        $model = Approve::findOne(['id' => $id, 'name' => 'purchase']);
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
            if ($model->save(false)) {
                if ($model->level == 3 && $model->status == 'Pass') {
                    $model->purchase->status = 2;
                    $model->purchase->save();
                } else {
                    // ถ้ามีสถานะถัดไป
                    $nextApprove = Approve::findOne(['from_id' => $model->from_id, 'name' => 'purchase',  'level' => ($model->level + 1)]);
                    if ($nextApprove) {
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


    public function actionApproveAll()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        if ($this->request->isPost) {
            $me = UserHelper::GetEmployee();
            $status = $this->request->post('status'); // เช่น 'Pass' หรือ 'Reject'
            $ids = $this->request->post('ids', []);   // array ของ id ที่ต้องการ update

            if (empty($ids) || !is_array($ids)) {
                return ['status' => 'error', 'message' => 'No items selected'];
            }


            foreach ($ids as $id) {
                $model = Approve::findOne($id);
                if (!$model) continue;

                // Merge ข้อมูล JSON
                $model->data_json = ArrayHelper::merge(
                    (array)$model->data_json,
                    ['approve_date' => date('Y-m-d H:i:s')]
                );

                $model->status = $status;

                if (empty($model->emp_id)) {
                    $model->emp_id = $me->id;
                }

                if ($model->save()) {

                    // ถ้าไม่อนุมัติ
                    if ($status === 'Reject') {
                        $model->leave->status = 'Reject';
                        $model->leave->save(false);
                        $model->leave->MsgReject();
                        continue; // ไปตัวถัดไป
                    }


                    // หา nextApprove
                    $nextApprove = Approve::findOne([
                        'from_id' => $model->from_id,
                        'name' => 'purchase',
                        'level' => $model->level + 1
                    ]);

                    // Mapping สถานะตาม level
                    if ($model->level == 3 && $model->status == 'Pass') {
                        $model->purchase->status = 2;
                        $model->purchase->save();
                    }

                    // ถ้ามีคนอนุมัติถัดไป และ status ผ่าน
                    if ($nextApprove && $status === 'Pass') {
                        $nextApprove->status = 'Pending';
                        $nextApprove->save(false);
                    }
                }
            }

            return ['status' => 'success'];
        }

        return ['status' => 'error', 'message' => 'Invalid request'];
    }
}
