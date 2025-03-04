<?php

namespace app\modules\line\controllers;

use Yii;
use DateTime;
use yii\filters\Cors;
use yii\helpers\Html;
use yii\web\Response;
use yii\db\Expression;
use app\modules\approve\models\Approve;
use yii\web\Controller;
use app\components\LineMsg;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\hr\models\Leave;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\LeaveSearch;
use app\modules\hr\models\LeavePermission;

class ApproveController extends \yii\web\Controller
{
    public function beforeAction($action)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/line/profile']);
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('index');
    }


    public function actionLeave()
    {
        $id = $this->request->get('id');
        $me = UserHelper::GetEmployee();
        // $model = Approve::findOne(['id' => $id, 'emp_id' => $me->id]);
        $model = Approve::findOne(['id' => $id]);

        $leave = Leave::findOne($model->from_id);
        if (!$model) {
            return [
                'title' => 'แจ้งเตือน',
                'content' => '<h6 class="text-center">ไม่อนุญาต</h6>',
            ];
        }
        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
            $model->data_json = ArrayHelper::merge($model->data_json, $approveDate);
            if ($model->level == 3) {
                $model->emp_id = $me->id;
            }
            if ($model->save()) {
                $nextApprove = Approve::findOne(['from_id' => $model->from_id, 'level' => ($model->level + 1)]);
                if ($nextApprove) {
                    $nextApprove->status = 'Pending';
                    $nextApprove->save();
                    
                    try {
                        // ส่ง msg ให้คนถัดไป 
                        $toUserId = $nextApprove->employee->user->line_id;
                        LineMsg::sendLeave($nextApprove->id, $toUserId);
                    } catch (\Throwable $th) {
                        $toUserId = '';

                    }
                }

                // ถ้า ผอ. อนุมัติ ให้สถานะการลาเป็น Allow
                if ($model->level == 4) {
                    $leave->status = 'Allow';
                    $leave->save();
                } else if ($model->status == 'Reject') {
                    $leave->status = 'Reject';
                    $leave->save();
                } else {
                    $leave->status = 'Checking';
                    $leave->save();
                }

                return [
                    'status' => 'success',
                    'container' => '#leave',
                    'toUserId' => $toUserId,
                    'nextApprove' => $nextApprove->id
                ];
            }
        }
        return $this->render('leave/form_approve', [
            'model' => $model,
        ]);
    }
}
