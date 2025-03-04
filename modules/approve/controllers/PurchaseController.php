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
   
    public function actionView($id)
    {
        $me = UserHelper::GetEmployee();
        //ข้อมูลการ Approve
        $approve = Approve::findOne(['id' => $id, 'emp_id' => $me->id]);
        // ข้อมูลการลา
        $leave = Leave::findOne($approve->from_id);
        
        if (!$approve) {
            return [
                'title' => 'แจ้งเตือน',
                'content' => '<h6 class="text-center">ไม่อนุญาต</h6>',
            ];
        }
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                // 'title' => isset($approve->leave) ? $approve->leave->getAvatar('ขอ')['avatar'] : '',
                'title' => '',
                'content' => $this->renderAjax('view', [
                    'model' => $approve,
                ]),
            ];
        } else {
            return $this->render('view', [
               'model' => $approve,
            ]);
        }
    }

    
}
