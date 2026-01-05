<?php

namespace app\modules\approveV2\controllers;

use Yii;
use yii\helpers\Json;
use yii\web\Response;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\am\models\AssetDetail;
use app\modules\approve\models\Approve;
use app\modules\am\models\AssetDetailSearch;
use app\modules\approve\models\ApproveSearch;
use Google\Service\Datastream\Merge;

class AssetMoveController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();

        $me = UserHelper::GetEmployee();

        $searchModel = new ApproveSearch([
            'status' => 'Pending'
        ]);

        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith(['assetMove']);
        $dataProvider->query->andFilterWhere(['approve.name' => 'asset_move']);
        $dataProvider->query->andFilterWhere(['approve.emp_id' => $me->id]);
        $dataProvider->query->andFilterWhere(['development_detail.emp_id' => $searchModel->emp_id]);
        $dataProvider->query->andFilterWhere(['development.development_type_id' => $searchModel->q_development_type_id]);



        // $searchModel = new AssetDetailSearch([
        //     'name' => 'move'
        // ]);

        // $dataProvider = $searchModel->search($this->request->queryParams);

        // $dataProvider->query->andFilterWhere([
        //     '=',
        //     new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.leader_id'))"),
        //     (string)$me->id
        // ]);

        // $dataProvider->query->andFilterWhere([
        //     '=',
        //     new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.leader_status'))"),
        //     'Pending'
        // ]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionView($id)
    {
        $model = AssetDetail::findOne($id);
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
        $model = AssetDetail::findOne($id);

        // 1. ดึงข้อมูล JSON เดิมเก็บไว้ก่อน (และจัดการกรณีที่เป็น null)
        $oldJson = is_array($model->data_json) ? $model->data_json : (Json::decode($model->data_json, true) ?? []);
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $newJson = ArrayHelper::merge($oldJson, $model->data_json);
                $newJson['leader_action_at'] = date('Y-m-d H:i:s');
                $newJson['leader_user_id'] = Yii::$app->user->id;

                $model->data_json = $newJson;
                if ($model->data_json['leader_status'] == 'Pass') {
                    $newLocation = [
                        'location' => $model->data_json['location']
                    ];
                    $oldAssetJson = $model->asset->data_json;
                    $model->asset->data_json =  ArrayHelper::merge($oldAssetJson, $newLocation);
                    $model->asset->save();
                }

                $model->save();
                return [
                    'status' => 'success'
                ];
            }
        } else {
            $model->loadDefaultValues();
        }


        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'อนุมัติการเคลื่อนย้าย',
                'content' => $this->renderAjax('_form', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form', [
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

            $statusMap = [
                1 => ['Pass' => 'Checking1_pass', 'Reject' => 'Checking1_reject'],
                2 => ['Pass' => 'Checking2_pass', 'Reject' => 'Checking2_reject'],
                3 => ['Pass' => 'Checkup_pass', 'Reject' => 'Checkup_reject'],
                4 => ['Pass' => 'Approve', 'Reject' => 'Reject']
            ];

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
                        $model->development->status = 'Reject';
                        $model->development->save(false);
                        $model->development->MsgReject();
                        continue; // ไปตัวถัดไป
                    }

                    // ถ้าเป็น level สุดท้าย และอนุมัติผ่าน
                    if ($model->maxLevel() && $status === 'Pass') {
                        $model->development->status = 'Approve';
                        $model->development->save(false);
                        $model->development->MsgApprove();
                        continue;
                    }

                    // หา nextApprove
                    $nextApprove = Approve::findOne([
                        'from_id' => $model->from_id,
                        'name' => 'leave',
                        'level' => $model->level + 1
                    ]);

                    // Mapping สถานะตาม level
                    if (isset($statusMap[$model->level][$status])) {
                        $model->development->status = $statusMap[$model->level][$status];
                        $model->development->save(false);
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
