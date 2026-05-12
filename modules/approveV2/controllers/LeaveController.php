<?php

namespace app\modules\approveV2\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\approveV2\models\Approve;
use app\modules\approveV2\models\ApproveSearch;
use app\modules\leave\components\LeaveApprovalService;

class LeaveController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $date = Yii::$app->request->get('date', date('Y-m-d'));
        $me = UserHelper::GetEmployee();
        $searchModel = new ApproveSearch([
            'status' => 'Pending'
        ]);

        $dataProvider = $searchModel->search($this->request->queryParams);
        // เพิ่ม join กับ employees
        $dataProvider->query->joinWith(['leave']);       // join leave
        $dataProvider->query->joinWith(['leave.employee']); // join employee
        $dataProvider->query->andWhere(['<>','approve.status','None']);
        $dataProvider->query->andWhere(['<>','leave.status','Cancel']);
        $dataProvider->query->andFilterWhere(['leave.leave_type_id' => $searchModel->leave_type_id]);
        $dataProvider->query->andFilterWhere(['approve.name' => 'leave']);
        $dataProvider->query->andFilterWhere(['approve.emp_id' => $me->id]);
        $dataProvider->query->andFilterWhere(['leave.emp_id' => $searchModel->emp_id]);
        $dataProvider->query->andFilterWhere(['employees.department' => $searchModel->q_department]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', new Expression("JSON_EXTRACT(leave.data_json, '$.reason')"), $searchModel->q],
        ]);
        $dataProvider->query->groupBy(['approve.from_id']);

        // if ($searchModel->date_filter && $searchModel->date_start == '' && $searchModel->date_end == '') {
        //     $range = DateFilterHelper::getRange($searchModel->date_filter);
        //     $searchModel->date_start = AppHelper::convertToThai($range[0]);
        //     $searchModel->date_end = AppHelper::convertToThai($range[1]);
        // }

        $dataProvider->query->andFilterWhere(['>=', 'leave.date_start', AppHelper::convertToGregorian($searchModel->date_start)])->andFilterWhere(['<=', 'leave.date_end', AppHelper::convertToGregorian($searchModel->date_end)]);
        
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
            $me     = UserHelper::GetEmployee();
            $status = $this->request->post('status');

            $result = (new LeaveApprovalService())->process($model, $status, $me ? (int) $me->id : null);

            if ($result['ok'] ?? false) {
                return ['status' => 'success'];
            }
            return ['status' => 'error', 'message' => $result['message'] ?? 'บันทึกไม่สำเร็จ'];
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
        if (!$this->request->isPost) {
            return ['status' => 'error', 'message' => 'Invalid request'];
        }

        $me     = UserHelper::GetEmployee();
        $status = $this->request->post('status');
        $ids    = $this->request->post('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return ['status' => 'error', 'message' => 'No items selected'];
        }

        $service = new LeaveApprovalService();
        foreach ($ids as $id) {
            $model = Approve::findOne((int) $id);
            if (!$model || $model->name !== 'leave') continue;
            $service->process($model, $status, $me ? (int) $me->id : null);
        }

        return ['status' => 'success'];
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

}
