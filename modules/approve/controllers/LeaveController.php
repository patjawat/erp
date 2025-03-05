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

class LeaveController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        $searchModel = new ApproveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'leave', 'emp_id' => $me->id, 'status' => 'Pending']);
        $dataProvider->query->orderBy(['id' => SORT_DESC]);

        return $this->render('index', [
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
                    $model->leave->status = 'Reject';
                    $model->leave->save();
                    $model->leave->MsgReject();

                    // return [
                    //     'status' => 'success'
                    // ];
                }
                //ถ้าเป็น level สุดท้ายให้ Approve
                if ($model->maxLevel() && $model->status == 'Pass') {
                    $model->leave->status = 'Approve';
                    $model->leave->save();
                    $model->leave->MsgApprove();
                    // return [
                    //     'status' => 'success'
                    // ];
                }


                $nextApprove = Approve::findOne(['from_id' => $model->from_id, 'level' => ($model->level + 1)]);
                    // เงื่อนไขระบบลา
                    if($nextApprove){

                        if ($model->level == 1 && $model->status == 'Pass') {
                            $model->leave->status = 'Checking';
                            $model->leave->save();
                            
                            $nextApprove->status = 'Pending';
                            $nextApprove->save();
                           
                        }


                        if ($model->level == 2 && $model->status == 'Pass') {
                            $model->leave->status = 'Checking';
                            $model->leave->save();
                            
                            $nextApprove->status = 'Pending';
                            $nextApprove->save();
                           
                        }
                        
                        if ($model->level == 3 && $model->status == 'Pass') {
                            $model->leave->status = 'Verify';
                            $model->leave->save();
                            $nextApprove->status = 'Pending';
                            $nextApprove->save();
                           
                        }
                      
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

    public function actionApproveAll()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $me = UserHelper::GetEmployee();
        $approves = Approve::find()->where(['name' => 'leave', 'emp_id' => $me->id, 'status' => 'Pending'])->all();
        foreach ($approves as $item) {
            $model = Approve::findOne($item->id);
            $model->status = 'Pass';
            $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
            $model->data_json = ArrayHelper::merge($model->data_json, $approveDate);
            if ($model->save()) {
                $nextApprove = Approve::findOne(['from_id' => $model->from_id, 'level' => ($model->level + 1)]);
                if ($nextApprove && $model->status !== 'Reject') {
                    // เงื่อนไขระบบลา
                    if ($model->name == 'leave') {
                        if ($model->level == 2) {
                            $model->leave->status = 'Checking';
                            $model->leave->save();
                        }
                        if ($model->level == 3) {
                            $model->leave->status = 'Verify';
                            $model->leave->save();
                        } else {
                        }
                    }

                    $nextApprove->status = 'Pending';
                    $nextApprove->save();
                }

                if ($model->maxLevel() && $model->status == 'Pass' && $model->name == 'leave') {
                    $model->leave->status = 'Approve';
                    $model->leave->save();
                    $model->leave->MsgApprove();
                }

                if ($model->maxLevel() && $model->status == 'Pass' && $model->name == 'purchase') {
                    $model->purchase->status = 2;
                    $model->purchase->save();
                    // $model->leave->MsgApprove();
                }
            }
        }
        return [
            'status' => 'success'
        ];
    }

    protected function findModel($id)
    {
        if (($model = Approve::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
}
