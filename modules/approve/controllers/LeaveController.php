<?php

namespace app\modules\approve\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\components\DateFilterHelper;
use app\modules\approve\models\Approve;
use app\modules\approve\models\ApproveSearch;
use app\modules\hr\models\Organization;

class LeaveController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $date = Yii::$app->request->get('date', date('Y-m-d'));
        $me = UserHelper::GetEmployee();
        $leaderId = $me->id;
        // 1. ดึง lvl จาก tree โดยใช้ ActiveRecord
        $lvls = Organization::find()
            ->select('lvl')
            ->where(['JSON_UNQUOTE(data_json->"$.leader1")' => (string)$leaderId])
            ->column(); // คืนค่าเป็น array เช่น [1,2]

        // 2. กำหนด $statusLevel ตาม lvl
        $statusLevel = [];
        if (in_array(1, $lvls)) {
            $statusLevel[] = 'Pending';
        }
        if (in_array(2, $lvls)) {
            $statusLevel[] = 'Checking1_pass';
        }

        // 3. ใช้กับ ApproveSearch
        $searchModel = new ApproveSearch([
            'q_status' => $statusLevel
        ]);

        $dataProvider = $searchModel->search($this->request->queryParams);
        // เพิ่ม join กับ employees
        // $dataProvider->query->joinWith(['leave.employee']);
        $dataProvider->query->joinWith(['leave']);       // join leave
        $dataProvider->query->joinWith(['leave.employee']); // join employee
        $dataProvider->query->andFilterWhere(['leave.leave_type_id' => $searchModel->leave_type_id]);
        $dataProvider->query->andFilterWhere(['approve.name' => 'leave']);
        $dataProvider->query->andFilterWhere(['approve.emp_id' => $me->id]);
        $dataProvider->query->andFilterWhere(['leave.emp_id' => $searchModel->emp_id]);
        $dataProvider->query->andFilterWhere(['leave.status' => $searchModel->q_status]);
        $dataProvider->query->andFilterWhere(['employees.department' => $searchModel->q_department]);
        $dataProvider->query->andFilterWhere(['NOT IN', 'leave.status', ['ReqCancel', 'cancel']]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', new Expression("JSON_EXTRACT(leave.data_json, '$.reason')"), $searchModel->q],
        ]);

        if ($searchModel->date_filter && $searchModel->date_start == '' && $searchModel->date_end == '') {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $searchModel->date_start = AppHelper::convertToThai($range[0]);
            $searchModel->date_end = AppHelper::convertToThai($range[1]);
        }

        $dataProvider->query->andFilterWhere(['>=', 'leave.date_start', AppHelper::convertToGregorian($searchModel->date_start)])->andFilterWhere(['<=', 'leave.date_end', AppHelper::convertToGregorian($searchModel->date_end)]);
        $dataProvider->query->andFilterWhere(['leave.thai_year' => $searchModel->thai_year]);
        $dataProvider->query->orderBy(['approve.id' => SORT_DESC]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'date' => $date
        ]);
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
        $me = UserHelper::GetEmployee();
        $approves = Approve::find()->where(['name' => 'leave', 'emp_id' => $me->id, 'status' => 'Pending'])->all();
        foreach ($approves as $item) {
            $model = Approve::findOne($item->id);
            $model->status = 'Pass';
            $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
            $model->data_json = ArrayHelper::merge($model->data_json, $approveDate);
            // if ($model->save()) {
            //     $nextApprove = Approve::findOne(['name' => 'leave', 'from_id' => $model->from_id, 'level' => ($model->level + 1)]);
            //     if ($nextApprove && $model->status !== 'Reject') {
            //         // เงื่อนไขระบบลา
            //         if ($model->name == 'leave') {
            //             if ($model->level == 2) {
            //                 $model->leave->status = 'Checking';
            //                 $model->leave->save();
            //             }
            //             if ($model->level == 3) {
            //                 $model->leave->status = 'Verify';
            //                 $model->leave->save();
            //             } else {
            //             }
            //         }

            //         $nextApprove->status = 'Pending';
            //         $nextApprove->save();
            //     }

            //     if ($model->maxLevel() && $model->status == 'Pass' && $model->name == 'leave') {
            //         $model->leave->status = 'Approve';
            //         $model->leave->save();
            //         $model->leave->MsgApprove();
            //     }
            // }
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

        $me = UserHelper::GetEmployee();
        $searchModel = new ApproveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'leave', 'emp_id' => $me->id, 'status' => 'Pending']);
        $dataProvider->query->orderBy(['id' => SORT_DESC]);

        $result = [];
        foreach ($dataProvider->getModels() as $event) {
            $result[] = [
                'id' => $event->id,
                'title' => $event->leave->data_json['reason'] ?? '-',
                'start' => $event->leave->date_start . ' 08:00',
                'end' => $event->leave->date_end . ' 16:00',
            ];
        }

        return $result;
    }

    public function actionCalendar()
    {
        return $this->render();
    }
}
