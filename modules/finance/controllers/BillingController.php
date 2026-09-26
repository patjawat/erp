<?php

namespace app\modules\finance\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\modules\finance\models\FinancePayableBilling;
use app\modules\finance\services\FinancePayableBillingService;

/**
 * ทะเบียนรับวางบิล — บริษัทมาวางบิล: วันที่ / บริษัท / เลือกบิล / ผู้วาง / ผู้รับวาง → พิมพ์ใบรับวางบิล
 */
class BillingController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'actions' => ['index', 'view', 'print'], 'roles' => ['financeView']],
                ['allow' => true, 'actions' => ['create', 'cancel'], 'roles' => ['financeOperate']],
            ]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['create' => ['GET', 'POST'], 'cancel' => ['POST']]],
        ]);
    }

    public function actionIndex()
    {
        $q = trim((string) Yii::$app->request->get('q', ''));
        $query = FinancePayableBilling::find()->orderBy(['billing_date' => SORT_DESC, 'id' => SORT_DESC]);
        if ($q !== '') {
            $query->andWhere(['or', ['like', 'vendor_name', $q], ['like', 'billing_no', $q], ['like', 'vendor_ref', $q]]);
        }
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider(['query' => $query, 'pagination' => ['pageSize' => 30]]),
            'q' => $q,
            'openVendors' => FinancePayableBillingService::vendorsWithOpenBills(),
        ]);
    }

    /** เพิ่มการรับวางบิล: เลือกบริษัท (โหลดบิลของบริษัทนั้น) → ติ๊กบิล → บันทึก */
    public function actionCreate()
    {
        $req = Yii::$app->request;
        $vendor = trim((string) ($req->isPost ? $req->post('vendor_name', '') : $req->get('vendor', '')));
        $form = [
            'billing_date' => $req->post('billing_date', AppHelper::convertToThai(date('Y-m-d'))),
            'vendor_ref' => $req->post('vendor_ref', ''),
            'deliverer_name' => $req->post('deliverer_name', ''),
            'receiver_name' => $req->post('receiver_name', $this->currentUserName()),
            'note' => $req->post('note', ''),
        ];
        $selected = array_map('intval', (array) $req->post('ids', []));

        if ($req->isPost) {
            $tx = Yii::$app->db->beginTransaction();
            try {
                $billing = (new FinancePayableBillingService())->create(
                    ['billing_date' => AppHelper::normalizeDateToDb((string) $form['billing_date'])] + $form + ['vendor_name' => $vendor],
                    $selected
                );
                $tx->commit();
                Yii::$app->session->setFlash('success', 'บันทึกรับวางบิล ' . $billing->billing_no . ' แล้ว (' . $billing->bill_count . ' บิล)');
                return $this->redirect(['view', 'id' => $billing->id]);
            } catch (\DomainException $e) {
                $tx->rollBack();
                Yii::$app->session->setFlash('error', $e->getMessage());
            } catch (\Throwable $e) {
                $tx->rollBack();
                Yii::error($e, __METHOD__);
                Yii::$app->session->setFlash('error', 'บันทึกรับวางบิลไม่สำเร็จ');
            }
        }

        return $this->render('create', [
            'vendor' => $vendor,
            'vendors' => FinancePayableBillingService::vendorsWithOpenBills(),
            'bills' => $vendor !== '' ? FinancePayableBillingService::openBills($vendor) : [],
            'form' => $form,
            'selected' => $selected,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    /** ใบรับวางบิล (พิมพ์) */
    public function actionPrint($id)
    {
        $this->layout = false;
        return $this->render('print', ['model' => $this->findModel($id), 'site' => SiteHelper::getInfo()]);
    }

    public function actionCancel($id)
    {
        $model = $this->findModel($id);
        $tx = Yii::$app->db->beginTransaction();
        try {
            (new FinancePayableBillingService())->cancel($model, (string) Yii::$app->request->post('reason', ''));
            $tx->commit();
            Yii::$app->session->setFlash('success', 'ยกเลิกใบรับวางบิล ' . $model->billing_no . ' แล้ว — บิลกลับเป็นรอวางบิล');
        } catch (\DomainException $e) {
            $tx->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    private function currentUserName(): string
    {
        $identity = Yii::$app->user->identity;
        $employee = $identity->employee ?? null;
        if ($employee && method_exists($employee, 'fullname')) {
            return trim((string) $employee->fullname());
        }
        return (string) ($identity->username ?? '');
    }

    private function findModel($id): FinancePayableBilling
    {
        $model = FinancePayableBilling::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบใบรับวางบิล');
        }
        return $model;
    }
}
