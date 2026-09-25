<?php

namespace app\modules\approveV2\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use app\components\UserHelper;
use app\modules\approveV2\models\Approve;
use app\modules\approveV2\models\ApproveSearch;
use app\modules\attendance\services\AttendanceAccess;
use app\modules\attendance\services\AttendanceService;

class CheckinController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => ['class' => \yii\filters\AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => \yii\filters\VerbFilter::class, 'actions' => ['update' => ['POST'], 'bulk-update' => ['POST']]],
        ];
    }

    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) throw new \yii\web\ForbiddenHttpException('ไม่พบข้อมูลพนักงาน');
        $q = trim((string) Yii::$app->request->get('q', ''));
        $loc = (string) Yii::$app->request->get('loc', '');
        $query = AttendanceAccess::searchPending($q, $loc);
        $dataProvider = new \yii\data\ActiveDataProvider(['query' => $query, 'pagination' => ['pageSize' => 20]]);
        return $this->render('index', compact('dataProvider', 'q', 'loc'));
    }

    public function actionUpdate($id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $id ?? $this->request->post('id');
        $status = $this->request->post('status');
        $comment = $this->request->post('comment', '');
        if (!is_scalar($id) || !ctype_digit((string)$id) || !is_string($status) || !is_string($comment)) {
            return ['status' => 'error', 'message' => 'ข้อมูลคำขอไม่ถูกต้อง'];
        }
        try {
            AttendanceService::approve((int)$id, $status, trim($comment));
            return ['status' => 'success'];
        } catch (\DomainException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        } catch (\yii\web\ForbiddenHttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->response->statusCode = 500;
            return ['status' => 'error', 'message' => 'บันทึกผลไม่สำเร็จ กรุณาลองใหม่'];
        }
    }

    public function actionBulkUpdate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $ids = $this->request->post('ids', []);
        $status = $this->request->post('status');
        $comment = $this->request->post('comment', '');
        if (!is_array($ids) || !is_string($status) || !is_string($comment)) {
            return ['status' => 'error', 'message' => 'ข้อมูลคำขอไม่ถูกต้อง'];
        }
        $done = 0; $failed = 0;
        foreach ($ids as $id) {
            if (!is_scalar($id) || !ctype_digit((string)$id)) { $failed++; continue; }
            try {
                AttendanceService::approve((int)$id, $status, trim($comment));
                $done++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }
        return ['status' => 'success', 'done' => $done, 'failed' => $failed];
    }

    public function actionView($id)
    {
        $approve = Approve::findOne(['id' => $id, 'name' => 'checkin', 'deleted_at' => null]);
        $model = $approve ? $approve->checkinRecord : null;
        if (!$model) throw new NotFoundHttpException('ไม่พบข้อมูลการลงเวลา');
        if (!AttendanceAccess::canReview($model)) throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิ์ตรวจสอบรายการนี้');
        return $this->render('view', compact('approve', 'model'));
    }
}
