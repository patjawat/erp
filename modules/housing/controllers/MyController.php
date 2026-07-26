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
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class MyController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['housing.user']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['submit' => ['POST']],
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
        if ((string)$context['employee']->status !== '1') {
            Yii::$app->session->setFlash('error', 'ไม่สามารถยื่นคำขอได้ เนื่องจากสถานะบุคลากรไม่ได้อยู่ระหว่างปฏิบัติงาน');
            return $this->redirect(['/profile', 'name' => 'housing']);
        }
        if ($context['mode'] !== 'applicant') {
            Yii::$app->session->setFlash('error', 'มีคำขอหรือสิทธิ์เข้าพักที่กำลังใช้งานอยู่แล้ว');
            return $this->redirect(['/profile', 'name' => 'housing']);
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
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'success', 'redirect' => \yii\helpers\Url::to(['/profile', 'name' => 'housing'])];
            }
            return $this->redirect(['/profile', 'name' => 'housing']);
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'เขียนคำร้องขอใช้บ้านพัก',
                'content' => $this->renderAjax('_request_modal', ['model' => $model]),
            ];
        }
        return $this->render('request-form', ['model' => $model]);
    }

    public function actionSubmit(int $id)
    {
        $context = (new HousingContextService())->forUser((int)Yii::$app->user->id);
        if (!$context['employee'] || (string)$context['employee']->status !== '1') {
            throw new \DomainException('ไม่สามารถส่งคำขอได้ เนื่องจากสถานะบุคลากรไม่ได้อยู่ระหว่างปฏิบัติงาน');
        }
        $model = HousingRequest::findOne(['id' => $id, 'emp_id' => $context['employee']->id ?? 0, 'status' => HousingRequest::STATUS_DRAFT]);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบคำขอที่ส่งได้');
        }
        (new RequestWorkflowService())->transition($model, HousingRequest::STATUS_SUBMITTED, 'ผู้ใช้ส่งคำขอ');
        return $this->redirect(['/profile', 'name' => 'housing']);
    }
}
