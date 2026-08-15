<?php

namespace app\modules\accounting\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\modules\finance\models\FinanceInbox;
use app\modules\finance\models\FinancePayable;
use app\modules\finance\models\FinancePayableReview;
use app\modules\finance\services\FinancePayableDraftService;
use app\modules\finance\services\FinancePayableApprovalService;
use app\modules\sm\models\Vendor;

class PayableController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'create' => ['GET', 'POST'], 'update' => ['GET', 'POST'], 'submit' => ['POST'], 'review' => ['POST'],
            ]],
        ]);
    }

    public function getViewPath()
    {
        return Yii::getAlias('@app/modules/finance/views/payable');
    }

    public function actionIndex()
    {
        return $this->render('index', ['dataProvider' => new ActiveDataProvider([
            'query' => FinancePayable::find()->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]),
            'pagination' => ['pageSize' => 30],
        ])]);
    }

    public function actionCreate($inbox_id)
    {
        $inbox = $this->findInbox($inbox_id);
        $existing = FinancePayable::findOne(['finance_inbox_id' => $inbox->id]);
        if ($existing) {
            return $this->redirect(['view', 'id' => $existing->id]);
        }
        $service = new FinancePayableDraftService();
        $model = $service->prepare($inbox);
        if ($model->load(Yii::$app->request->post())) {
            try {
                $service->create($inbox, $model);
                Yii::$app->session->setFlash('success', 'สร้างร่างทะเบียนเจ้าหนี้เรียบร้อยแล้ว');
                return $this->redirect(['view', 'id' => $model->id]);
            } catch (\DomainException $e) {
                $model->addError('invoice_no', $e->getMessage());
            } catch (\Throwable $e) {
                Yii::error($e, __METHOD__);
                $model->addError('invoice_no', 'สร้างร่างทะเบียนเจ้าหนี้ไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');
            }
        }
        return $this->renderForm($model, $inbox);
    }

    public function actionView($id)
    {
        return $this->render('view', ['model' => $this->findPayable($id)]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findPayable($id);
        if (!in_array($model->status, [FinancePayable::STATUS_DRAFT, FinancePayable::STATUS_NEEDS_REVISION], true)) {
            Yii::$app->session->setFlash('warning', 'รายการสถานะนี้ไม่สามารถแก้ไขได้');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        if ($model->load(Yii::$app->request->post())) {
            try {
                (new FinancePayableDraftService())->update($model);
                Yii::$app->session->setFlash('success', 'บันทึกการแก้ไขร่างทะเบียนเจ้าหนี้แล้ว');
                return $this->redirect(['view', 'id' => $model->id]);
            } catch (\DomainException $e) {
                $model->addError('invoice_no', $e->getMessage());
            } catch (\Throwable $e) {
                Yii::error($e, __METHOD__);
                $model->addError('invoice_no', 'บันทึกการแก้ไขไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');
            }
        }
        return $this->renderForm($model, $model->inbox);
    }

    public function actionSubmit($id)
    {
        $model = $this->findPayable($id);
        try {
            (new FinancePayableApprovalService())->decide($model, FinancePayableReview::DECISION_SUBMIT);
            Yii::$app->session->setFlash('success', 'ส่งรายการให้ผู้ตรวจอนุมัติแล้ว');
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('warning', $e->getMessage());
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'ส่งรายการตรวจอนุมัติไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionReview($id)
    {
        $model = $this->findPayable($id);
        $decision = (string) Yii::$app->request->post('decision');
        try {
            (new FinancePayableApprovalService())->decide($model, $decision, (string) Yii::$app->request->post('note'));
            Yii::$app->session->setFlash('success', $decision === FinancePayableReview::DECISION_APPROVE
                ? 'อนุมัติรายการเข้าสู่ทะเบียนเจ้าหนี้แล้ว' : 'ส่งรายการกลับให้ผู้จัดทำแก้ไขแล้ว');
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('warning', $e->getMessage());
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'บันทึกผลการตรวจอนุมัติไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    private function renderForm(FinancePayable $model, FinanceInbox $inbox)
    {
        $vendors = Vendor::find()->where(['name' => 'vendor', 'active' => 1])->orderBy(['title' => SORT_ASC])->all();
        return $this->render('create', [
            'model' => $model,
            'inbox' => $inbox,
            'vendors' => ArrayHelper::map($vendors, 'id', static fn(Vendor $vendor) => $vendor->title . ' (' . $vendor->code . ')'),
        ]);
    }

    private function findPayable($id): FinancePayable
    {
        $model = FinancePayable::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบทะเบียนเจ้าหนี้');
        }
        return $model;
    }

    private function findInbox($id): FinanceInbox
    {
        $model = FinanceInbox::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการในกล่องรับบัญชี');
        }
        return $model;
    }
}
