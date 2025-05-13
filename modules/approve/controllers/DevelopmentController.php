<?php

namespace app\modules\approve\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\development;
use app\modules\approve\models\Approve;
use app\modules\approve\models\ApproveSearch;

class DevelopmentController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $date = Yii::$app->request->get('date', date('Y-m-d'));
        $me = UserHelper::GetEmployee();
        $searchModel = new ApproveSearch([
            'status' => ['Pending']
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith(['development', 'development.developmentDetail']);
        $dataProvider->query->andFilterWhere(['approve.name' => 'development']);
        $dataProvider->query->andFilterWhere(['approve.emp_id' => $me->id]);
        $dataProvider->query->andFilterWhere(['development_detail.emp_id' => $searchModel->emp_id]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like','title', $searchModel->q],
        ]);
        if ($searchModel->thai_year !== '' && $searchModel->thai_year !== null) {
            $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
            $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        }
        
        try {
         
        $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
        $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        $dataProvider->query->andFilterWhere(['>=', 'date_start', $dateStart])->andFilterWhere(['<=', 'date_end', $dateEnd]);
           
    } catch (\Throwable $th) {
        //throw $th;
    }
    
        $dataProvider->query->orderBy(['approve.id' => SORT_DESC]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'date' => $date
        ]);
    }


    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'ขออนุมัติอบรม/ประชุม/ดูงาน',
                'content' => $this->renderAjax('@app/modules/hr/views/development/view', [
                    'model' => $model->development,
                ]),
            ];
        } else {
            return $this->render('@app/modules/hr/views/development/view', [
                'model' => $model->development,
            ]);
        }
    }
    
    public function actionUpdate($id)
    {
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
                \Yii::$app->response->format = Response::FORMAT_JSON;
                // ถ้าไม่อนุมัติให้ return ออกเลย
                if ($model->status == 'Reject') {
                    $model->development->status = 'Reject';
                    $model->development->save();
                    $model->development->MsgReject();

                    // return [
                    //     'status' => 'success'
                    // ];
                }
                //ถ้าเป็น level สุดท้ายให้ Approve
                if ($model->maxLevel() && $model->status == 'Pass') {
                    $model->development->status = 'Approve';
                    $model->development->save();
                    $model->development->MsgApprove();
                    // return [
                    //     'status' => 'success'
                    // ];
                }


                $nextApprove = Approve::findOne(['from_id' => $model->from_id,'name' => 'development', 'level' => ($model->level + 1)]);
                    // เงื่อนไขระบบลา
                    if($nextApprove){

                        if ($model->level == 1 && $model->status == 'Pass') {
                            $model->development->status = 'Checking';
                            $model->development->save();
                            
                            $nextApprove->status = 'Pending';
                            $nextApprove->save();
                           
                        }


                        if ($model->level == 2 && $model->status == 'Pass') {
                            $model->development->status = 'Checking';
                            $model->development->save();
                            
                            $nextApprove->status = 'Pending';
                            $nextApprove->save();
                           
                        }
                        
                        if ($model->level == 3 && $model->status == 'Pass') {
                            $model->development->status = 'Verify';
                            $model->development->save();
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
                'title' => 'ขออนุมัติอบรม/ประชุม/ดูงาน',
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
        $approves = Approve::find()->where(['name' => 'development', 'emp_id' => $me->id, 'status' => 'Pending'])->all();
        foreach ($approves as $item) {
            $model = Approve::findOne($item->id);
            $model->status = 'Pass';
            $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
            $model->data_json = ArrayHelper::merge($model->data_json, $approveDate);
            if ($model->save()) {
                $nextApprove = Approve::findOne(['from_id' => $model->from_id, 'level' => ($model->level + 1)]);
                if ($nextApprove && $model->status !== 'Reject') {
                    // เงื่อนไขระบบลา
                    if ($model->name == 'development') {
                        if ($model->level == 2) {
                            $model->development->status = 'Checking';
                            $model->development->save();
                        }
                        if ($model->level == 3) {
                            $model->development->status = 'Verify';
                            $model->development->save();
                        } else {
                        }
                    }

                    $nextApprove->status = 'Pending';
                    $nextApprove->save();
                }

                if ($model->maxLevel() && $model->status == 'Pass' && $model->name == 'development') {
                    $model->development->status = 'Approve';
                    $model->development->save();
                    $model->development->MsgApprove();
                }

                if ($model->maxLevel() && $model->status == 'Pass' && $model->name == 'purchase') {
                    $model->purchase->status = 2;
                    $model->purchase->save();
                    // $model->development->MsgApprove();
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
    

    public function actionGetEvents()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $start = Yii::$app->request->get('start');
        $end = Yii::$app->request->get('end');
        
        $me = UserHelper::GetEmployee();
        $searchModel = new ApproveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'development', 'emp_id' => $me->id, 'status' => 'Pending']);
        $dataProvider->query->orderBy(['id' => SORT_DESC]);
        
        $result = [];
        foreach ($dataProvider->getModels() as $event) {
            $result[] = [
                'id' => $event->id,
                'title' => $event->development->data_json['reason'] ?? '-',
                'start' => $event->development->date_start. ' 08:00',
                'end' => $event->development->date_end. ' 16:00',
                // 'start' => $event->date_start . ' ' . $event->start_time,
                // 'end' => $event->date_end . ' ' . $event->end_time,
                // 'description' => $event->description,
                // 'color' => $event->color,
            ];
        }
        
        return $result;
    }
    

    
}
