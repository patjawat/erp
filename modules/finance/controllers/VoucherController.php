<?php

namespace app\modules\finance\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\modules\finance\models\FinancePayable;
use app\modules\finance\models\FinanceVoucher;
use app\modules\finance\services\FinanceVoucherDraftService;

class VoucherController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), ['access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['financeOperate']]]]]);
    }

    public function actionIndex()
    {
        $ready = FinancePayable::find()->alias('p')->joinWith('voucher v')->where(['p.status' => FinancePayable::STATUS_APPROVED, 'v.id' => null])->orderBy(['p.due_date' => SORT_ASC, 'p.id' => SORT_ASC])->all();
        $drafts = FinanceVoucher::find()->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])->all();
        return $this->render('index', compact('ready', 'drafts'));
    }

    public function actionCreate($payableId)
    {
        $payable = FinancePayable::findOne($payableId);
        if (!$payable) throw new NotFoundHttpException('ไม่พบรายการเจ้าหนี้');
        $service = new FinanceVoucherDraftService();
        try { $model = $service->prepare($payable); }
        catch (\DomainException $e) { Yii::$app->session->setFlash('warning', $e->getMessage()); return $this->redirect(['index']); }

        if ($model->load(Yii::$app->request->post())) {
            try {
                $service->create($payable, $model);
                Yii::$app->session->setFlash('success', 'สร้างร่างฎีกาแล้ว โดยยังไม่มีการอนุมัติหรือจ่ายเงินจริง');
                return $this->redirect(['view', 'id' => $model->id]);
            } catch (\Throwable $e) { $model->addError('funding_source', $e->getMessage()); }
        }
        return $this->render('create', compact('model', 'payable'));
    }

    public function actionView($id)
    {
        $model = FinanceVoucher::findOne($id);
        if (!$model) throw new NotFoundHttpException('ไม่พบร่างฎีกา');
        return $this->render('view', compact('model'));
    }
}
