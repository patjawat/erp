<?php

namespace app\modules\approve\controllers;

use Yii;
use yii\web\Response;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\components\DateFilterHelper;
use app\modules\approve\models\Approve;
use app\modules\approve\models\ApproveSearch;

class DevelopmentController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
    
         $leaveFilterStatusModel = Categorise::findOne(['name' => 'development_filter_status', 'emp_id' => $me->id]);

        $searchModel = new ApproveSearch([
            'q_status' => $leaveFilterStatusModel->data_json ?? [],
        ]);
        
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith(['development', 'development.developmentDetail']);
        $dataProvider->query->andFilterWhere(['approve.name' => 'development']);
        $dataProvider->query->andFilterWhere(['approve.emp_id' => $me->id]);
         $dataProvider->query->andFilterWhere(['approve.status' => $searchModel->q_status]);
        // $dataProvider->query->andFilterWhere(['development_detail.emp_id' => $searchModel->emp_id]);
        // if ($me->isDirector()) {
        //     $dataProvider->query->andFilterWhere(['development.status' => $searchModel->q_status ?? 'Pass']);
        // } else {
        //     $dataProvider->query->andFilterWhere(['development.status' => $searchModel->q_status ?? 'Pending']);
        // }
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'title', $searchModel->q],
        ]);




        $dataProvider->query->andFilterWhere(['>=', 'date_start', AppHelper::convertToGregorian($searchModel->date_start)])->andFilterWhere(['<=', 'date_end', AppHelper::convertToGregorian($searchModel->date_end)]);

        $dataProvider->query->orderBy(['approve.id' => SORT_DESC]);
        $dataProvider->pagination = ['pageSize' => false];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
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
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);

        // ป้องกันกรณี model ไม่พบ หรือไม่ใช่ใบอนุมัติของผู้ใช้ที่มีสิทธิ์ (security)
        if (!$model) {
            throw new NotFoundHttpException('Approve not found');
        }

        if ($this->request->isPost) {
            $me = UserHelper::GetEmployee();
            $status = $this->request->post('status');

            // การ merge ข้อมูล JSON เพื่อให้ข้อมูลเก่ายังอยู่
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
                if ($model->status === 'Reject') {
                    $model->leave->status = 'Reject';
                    $model->leave->save(false);
                    $model->leave->MsgReject();
                    return ['status' => 'success'];
                }

                // ถ้าเป็น level สุดท้าย
                if ($model->maxLevel() && $model->status === 'Pass') {
                    $model->leave->status = 'Approve';
                    $model->leave->save(false);
                    $model->leave->MsgApprove();
                    return ['status' => 'success'];
                }

                // หา nextApprove
                $nextApprove = Approve::findOne([
                    'from_id' => $model->from_id,
                    'name' => 'leave',
                    'level' => $model->level + 1
                ]);

                // Mapping สถานะ
                $statusMap = [
                    1 => ['Pass' => 'Checking1_pass', 'Reject' => 'Checking1_reject'],
                    2 => ['Pass' => 'Checking2_pass', 'Reject' => 'Checking2_reject'],
                    3 => ['Pass' => 'Checkup_pass', 'Reject' => 'Checkup_reject'],
                    4 => ['Pass' => 'Approve', 'Reject' => 'Reject']
                ];

                // ตั้งค่าตาม map
                if (isset($statusMap[$model->level][$model->status])) {
                    $model->leave->status = $statusMap[$model->level][$model->status];
                    $model->leave->save(false);
                }

                // ถ้ามีคนอนุมัติถัดไป และสถานะผ่าน
                if ($nextApprove && $model->status === 'Pass') {
                    $nextApprove->status = 'Pending';
                    $nextApprove->save(false);
                }

                return ['status' => 'success'];
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

        $ids = Yii::$app->request->post('ids', []);

        foreach ($ids as $id) {
            $model = $this->findModel($id);
            $model->status == 'Pass';
            $this->Approve($model);
        }

        return ['status' => 'success', 'ids' => $ids];
    }


    protected function Approve($model)
    {
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


            $nextApprove = Approve::findOne(['from_id' => $model->from_id, 'name' => 'development', 'level' => ($model->level + 1)]);
            // เงื่อนไขระบบลา
            if ($nextApprove) {

                if ($model->level == 1 && $model->status == 'Pass') {
                    $model->development->status = 'Pending';
                    $model->development->save();
                    $nextApprove->status = 'Pending';
                    $nextApprove->save();
                } elseif ($model->level == 1 && $model->status == 'Reject') {
                    $model->development->status = 'Reject';
                    $model->development->save();
                }


                if ($model->level == 2 && $model->status == 'Pass') {
                    $model->development->status = 'Checking';
                    $model->development->save();

                    $nextApprove->status = 'Pending';
                    $nextApprove->save();
                } elseif ($model->level == 2 && $model->status == 'Reject') {
                    $model->development->status = 'Reject';
                    $model->development->save();
                }

                if ($model->level == 3 && $model->status == 'Pass') {
                    $model->development->status = 'Pass';
                    $model->development->save();
                    $nextApprove->status = 'Pending';
                    $nextApprove->save();
                } elseif ($model->level == 3 && $model->status == 'Reject') {
                    $model->development->status = 'Reject';
                    $model->development->save();
                }

                if ($model->level == 4 && $model->status == 'Pass') {
                    $model->development->status = 'Approve';
                    $model->development->save();
                } elseif ($model->level == 4 && $model->status == 'Reject') {
                    $model->development->status = 'Reject';
                    $model->development->save();
                }
            }
        }
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
                'start' => $event->development->date_start . ' 08:00',
                'end' => $event->development->date_end . ' 16:00',
                // 'start' => $event->date_start . ' ' . $event->start_time,
                // 'end' => $event->date_end . ' ' . $event->end_time,
                // 'description' => $event->description,
                // 'color' => $event->color,
            ];
        }

        return $result;
    }
}
