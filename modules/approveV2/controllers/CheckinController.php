<?php

namespace app\modules\approveV2\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use app\components\UserHelper;
use app\modules\approveV2\models\Approve;
use app\modules\approveV2\models\ApproveSearch;
use app\modules\attendance\models\CheckinRecord;

class CheckinController extends Controller
{
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        $searchModel = new ApproveSearch(['status' => 'Pending']);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['approve.name' => 'checkin']);
        $dataProvider->query->andWhere(['approve.emp_id' => $me->id]);
        $dataProvider->query->andWhere(['approve.status' => 'Pending']);
        $dataProvider->query->orderBy(['approve.id' => SORT_DESC]);
        $dataProvider->query->joinWith(['checkinRecord', 'checkinRecord.employee']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        if (!$model || $model->name !== 'checkin') {
            throw new NotFoundHttpException('ไม่พบรายการ');
        }
        $me = UserHelper::GetEmployee();
        if ($model->emp_id != $me->id) {
            return ['status' => 'error', 'message' => 'ไม่มีสิทธิ์อนุมัติรายการนี้'];
        }
        if (!$this->request->isPost) {
            return ['status' => 'error', 'message' => 'Invalid request'];
        }
        $status = $this->request->post('status'); // Pass | Reject
        $comment = $this->request->post('comment', '');
        $model->status = $status;
        $model->data_json = array_merge((array)$model->data_json, [
            'approve_date' => date('Y-m-d H:i:s'),
            'comment' => $comment,
        ]);
        if ($model->save(false)) {
            $record = CheckinRecord::findOne((int)$model->from_id);
            if ($record) {
                $record->applyApproveResult($status, $comment);
            }
            return ['status' => 'success'];
        }
        return ['status' => 'error', 'message' => 'บันทึกไม่สำเร็จ'];
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        if (!$model || $model->name !== 'checkin') {
            throw new NotFoundHttpException('ไม่พบรายการ');
        }
        $record = $model->checkinRecord;
        if (!$record) {
            throw new NotFoundHttpException('ไม่พบข้อมูลการลงเวลา');
        }
        return $this->render('view', [
            'approve' => $model,
            'model' => $record,
        ]);
    }

    protected function findModel($id)
    {
        $model = Approve::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบรายการอนุมัติ');
        }
        return $model;
    }
}
