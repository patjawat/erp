<?php

declare(strict_types=1);

namespace app\modules\housing\controllers;

use app\modules\housing\models\MonthlyAccount;
use app\modules\housing\models\Payment;
use app\modules\housing\models\Receipt;
use app\modules\housing\services\PaymentService;
use Yii;
use yii\base\DynamicModel;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

final class PaymentController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['cancel' => ['POST']]],
        ]);
    }

    public function actionIndex()
    {
        return $this->render('index', ['dataProvider' => new ActiveDataProvider([
            'query' => Payment::find()->with(['receipt', 'allocations.invoice.monthlyAccount.period'])->orderBy(['paid_at' => SORT_DESC, 'id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ])]);
    }

    public function actionReceive(int $account_id)
    {
        $account = MonthlyAccount::find()->with(['period', 'items.chargeType'])->where(['id' => $account_id])->one();
        if (!$account) {
            throw new NotFoundHttpException('ไม่พบรายการค่าใช้จ่าย');
        }
        if (
            $account->status !== MonthlyAccount::STATUS_SAVED
            || !$account->occupancy_id
            || (float)$account->balance_amount <= 0
            || $account->period->status !== 'closed'
        ) {
            throw new \DomainException('รายการนี้ยังไม่พร้อมรับชำระหรือไม่มียอดคงเหลือ');
        }
        $form = new DynamicModel(['amount' => $account->balance_amount, 'payment_method' => 'cash', 'reference_no' => '', 'note' => '', 'paid_at' => date('Y-m-d\TH:i')]);
        $form->addRule(['amount', 'payment_method', 'paid_at'], 'required')
            ->addRule('amount', 'number', ['min' => 0.01, 'max' => (float)$account->balance_amount])
            ->addRule('payment_method', 'in', ['range' => ['cash', 'transfer']])
            ->addRule('reference_no', 'string', ['max' => 150])
            ->addRule('note', 'string')
            ->addRule('paid_at', 'datetime', ['format' => 'php:Y-m-d\TH:i']);
        $form->setAttributeLabels(['amount' => 'ยอดรับชำระ', 'payment_method' => 'วิธีชำระ', 'reference_no' => 'เลขอ้างอิง', 'note' => 'หมายเหตุ', 'paid_at' => 'วันที่รับชำระ']);

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $service = new PaymentService();
                $invoice = $service->invoiceForAccount($account);
                $receipt = $service->receive(
                    $invoice,
                    (float)$form->amount,
                    (string)$form->payment_method,
                    trim((string)$form->reference_no) ?: null,
                    trim((string)$form->note) ?: null,
                    date('Y-m-d H:i:s', strtotime((string)$form->paid_at))
                );
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'success', 'message' => 'รับชำระและออกใบเสร็จแล้ว', 'redirect' => Url::to(['view', 'id' => $receipt->payment_id])];
            } catch (\Throwable $e) {
                $form->addError('amount', $e->getMessage());
            }
        }
        if (Yii::$app->request->isPost && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['errors' => ActiveForm::validate($form)];
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['title' => 'รับชำระค่าใช้จ่าย', 'content' => $this->renderAjax('_receive_form', compact('form', 'account'))];
        }
        return $this->render('receive', compact('form', 'account'));
    }

    public function actionView(int $id)
    {
        return $this->render('view', ['model' => $this->findPayment($id)]);
    }

    public function actionPrint(int $id)
    {
        $this->layout = false;
        return $this->render('print', ['model' => $this->findPayment($id)]);
    }

    public function actionCancel(int $id)
    {
        $model = $this->findPayment($id);
        try {
            (new PaymentService())->cancel($model, (string)Yii::$app->request->post('cancel_reason'));
            Yii::$app->session->setFlash('success', 'ยกเลิกรายการรับชำระและคืนยอดคงเหลือแล้ว');
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['view', 'id' => $id]);
    }

    private function findPayment(int $id): Payment
    {
        $model = Payment::find()->with(['receipt', 'allocations.invoice.items', 'allocations.invoice.monthlyAccount.period'])->where(['id' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการรับชำระ');
        }
        return $model;
    }
}
