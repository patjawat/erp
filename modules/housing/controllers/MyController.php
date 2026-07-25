<?php

declare(strict_types=1);

namespace app\modules\housing\controllers;

use app\modules\housing\models\Building;
use app\modules\housing\models\HousingRequest;
use app\modules\housing\services\HousingContextService;
use app\modules\housing\services\RequestNumberService;
use app\modules\housing\services\RequestWorkflowService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

final class MyController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['housing.user']]],
            ],
        ];
    }

    public function actionIndex()
    {
        $context = (new HousingContextService())->forUser((int)Yii::$app->user->id);
        return $this->render('index', ['context' => $context]);
    }

    public function actionCreateRequest()
    {
        $context = (new HousingContextService())->forUser((int)Yii::$app->user->id);
        if (!$context['employee']) {
            throw new NotFoundHttpException('ไม่พบข้อมูลบุคลากรที่เชื่อมกับบัญชีผู้ใช้');
        }
        if ($context['mode'] !== 'applicant') {
            Yii::$app->session->setFlash('error', 'มีคำขอหรือสิทธิ์เข้าพักที่กำลังใช้งานอยู่แล้ว');
            return $this->redirect(['index']);
        }
        $model = new HousingRequest([
            'request_no' => (new RequestNumberService())->next(),
            'request_type' => HousingRequest::TYPE_MOVE_IN,
            'emp_id' => $context['employee']->id,
        ]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (Yii::$app->request->post('submit') === '1') {
                (new RequestWorkflowService())->transition($model, HousingRequest::STATUS_SUBMITTED, 'ผู้ใช้ส่งคำขอ');
            }
            Yii::$app->session->setFlash('success', 'บันทึกคำขอเรียบร้อย');
            return $this->redirect(['index']);
        }
        return $this->render('request-form', ['model' => $model]);
    }

    public function actionSubmit(int $id)
    {
        $context = (new HousingContextService())->forUser((int)Yii::$app->user->id);
        $model = HousingRequest::findOne(['id' => $id, 'emp_id' => $context['employee']->id ?? 0, 'status' => HousingRequest::STATUS_DRAFT]);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบคำขอที่ส่งได้');
        }
        (new RequestWorkflowService())->transition($model, HousingRequest::STATUS_SUBMITTED, 'ผู้ใช้ส่งคำขอ');
        return $this->redirect(['index']);
    }
}
