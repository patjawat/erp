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
        $dataProvider->query->andFilterWhere(['name' => 'leave','emp_id' => $me->id,'status' => 'Pending']);
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
        // ข้อมูลการลา
        $leave = Leave::findOne($approve->from_id);
        
        if (!$approve) {
            return [
                'title' => 'แจ้งเตือน',
                'content' => '<h6 class="text-center">ไม่อนุญาติ</h6>',
            ];
        }
        
        if ($this->request->isPost && $approve->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            
            $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
            $approve->data_json = ArrayHelper::merge($approve->data_json, $approveDate);
            
            return [
                'status' => 'success',
                'container' => '#approve',
            ];
        }
        
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => isset($approve->leave) ? $approve->leave->getAvatar('ขอ')['avatar'] : '',
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
