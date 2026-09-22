<?php

namespace app\modules\finance\controllers;

use app\components\AppHelper;
use app\modules\finance\models\FinanceArFund;
use app\modules\finance\models\FinanceArInvoice;
use app\modules\finance\models\FinanceArSettlement;
use app\modules\finance\models\FinancePatientDeposit;
use app\modules\finance\services\FinanceArImportService;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * ลูกหนี้ค่ารักษา (AR) — dashboard, ทะเบียนลูกหนี้, นำเข้า HIS, รับชำระ, เงินมัดจำ
 * ทะเบียนคุมรูปแบบทางการ (พิมพ์/Excel) อยู่ที่ /finance/register (ar_by_fund/ar_accrued/patient_deposit)
 */
class ArController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'invoices', 'deposits', 'template'], 'roles' => ['financeView']],
                    ['allow' => true, 'actions' => ['import', 'add-settlement', 'delete-invoice', 'deposit-form', 'delete-deposit'], 'roles' => ['financeOperate']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['add-settlement' => ['post'], 'delete-invoice' => ['post'], 'delete-deposit' => ['post']],
            ],
        ]);
    }

    private static function currentFiscalYear(): int
    {
        $year = (int) date('Y') + 543;
        return (int) date('n') >= 10 ? $year + 1 : $year;
    }

    public function actionIndex()
    {
        $fy = (int) Yii::$app->request->get('fiscal_year', self::currentFiscalYear());

        // ยอดตั้งเบิกต่อสิทธิ
        $billed = (new Query())->from('{{%finance_ar_invoice}}')
            ->select(['ar_fund_id', 'b' => 'SUM(billed_amount)', 'c' => 'COUNT(*)'])
            ->where(['fiscal_year' => $fy])->groupBy('ar_fund_id')->indexBy('ar_fund_id')->all();
        // ยอดปิดหนี้ต่อสิทธิ
        $settled = (new Query())->from('{{%finance_ar_settlement}} s')
            ->innerJoin('{{%finance_ar_invoice}} i', 'i.id = s.ar_invoice_id')
            ->select(['i.ar_fund_id', 's' => 'SUM(s.amount)'])
            ->where(['i.fiscal_year' => $fy])->groupBy('i.ar_fund_id')->indexBy('ar_fund_id')->all();

        $funds = FinanceArFund::find()->orderBy(['sort_order' => SORT_ASC])->all();
        $rows = [];
        $tot = ['billed' => 0.0, 'settled' => 0.0, 'outstanding' => 0.0, 'count' => 0];
        foreach ($funds as $f) {
            $b = (float) ($billed[$f->id]['b'] ?? 0);
            $s = (float) ($settled[$f->id]['s'] ?? 0);
            $cnt = (int) ($billed[$f->id]['c'] ?? 0);
            if ($b == 0 && $cnt == 0) {
                continue;
            }
            $out = max(0.0, $b - $s);
            $rows[] = ['fund' => $f, 'billed' => $b, 'settled' => $s, 'outstanding' => $out, 'count' => $cnt];
            $tot['billed'] += $b;
            $tot['settled'] += $s;
            $tot['outstanding'] += $out;
            $tot['count'] += $cnt;
        }

        return $this->render('index', [
            'fy' => $fy,
            'rows' => $rows,
            'tot' => $tot,
            'fiscalYears' => $this->fiscalYearOptions(),
        ]);
    }

    public function actionInvoices()
    {
        $req = Yii::$app->request;
        $fy = (int) $req->get('fiscal_year', self::currentFiscalYear());
        $fundId = (int) $req->get('fund_id', 0);
        $status = $req->get('status');

        $query = FinanceArInvoice::find()->with('fund')->where(['fiscal_year' => $fy]);
        if ($fundId) {
            $query->andWhere(['ar_fund_id' => $fundId]);
        }
        if ($status) {
            $query->andWhere(['status' => $status]);
        }
        $invoices = $query->orderBy(['service_date' => SORT_DESC, 'id' => SORT_DESC])->limit(500)->all();

        return $this->render('invoices', [
            'invoices' => $invoices,
            'fy' => $fy,
            'fundId' => $fundId,
            'status' => $status,
            'funds' => FinanceArFund::activeList(),
            'fiscalYears' => $this->fiscalYearOptions(),
            'newSettlement' => new FinanceArSettlement(['settle_date' => date('Y-m-d')]),
        ]);
    }

    public function actionImport()
    {
        $model = new \yii\base\DynamicModel(['fiscal_year', 'source_label', 'file']);
        $model->addRule(['fiscal_year'], 'integer')->addRule(['source_label'], 'string')
            ->addRule(['file'], 'file', ['extensions' => ['xlsx', 'xls'], 'skipOnEmpty' => false]);
        $model->fiscal_year = self::currentFiscalYear();

        $result = null;
        if (Yii::$app->request->isPost) {
            $model->fiscal_year = (int) Yii::$app->request->post('fiscal_year', $model->fiscal_year);
            $model->source_label = Yii::$app->request->post('source_label');
            $model->file = UploadedFile::getInstanceByName('file');
            if ($model->validate()) {
                $result = FinanceArImportService::import(
                    $model->file->tempName,
                    (int) $model->fiscal_year,
                    $model->source_label ?: null,
                    $model->file->name
                );
                if ($result['imported'] > 0) {
                    Yii::$app->session->setFlash('success', 'นำเข้าลูกหนี้ ' . $result['imported'] . ' รายการ');
                }
            }
        }

        return $this->render('import', [
            'model' => $model,
            'result' => $result,
            'fiscalYears' => $this->fiscalYearOptions(),
        ]);
    }

    public function actionTemplate()
    {
        return FinanceArImportService::writeXlsx(
            FinanceArImportService::templateSpreadsheet(),
            'แม่แบบนำเข้าลูกหนี้ค่ารักษา.xlsx'
        );
    }

    public function actionAddSettlement(int $id)
    {
        $inv = FinanceArInvoice::findOne($id);
        if ($inv === null) {
            throw new NotFoundHttpException('ไม่พบลูกหนี้');
        }
        $st = new FinanceArSettlement(['ar_invoice_id' => $inv->id]);
        $post = Yii::$app->request->post();
        if ($st->load($post)) {
            $st->settle_date = AppHelper::normalizeDateToDb($post['FinanceArSettlement']['settle_date'] ?? null);
            if ($st->save()) {
                Yii::$app->session->setFlash('success', 'บันทึกการรับชำระ/ตัดปรับเรียบร้อย');
            } else {
                Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $st->getFirstErrors()));
            }
        }
        return $this->redirect(['invoices', 'fiscal_year' => $inv->fiscal_year]);
    }

    public function actionDeleteInvoice(int $id)
    {
        $inv = FinanceArInvoice::findOne($id);
        if ($inv === null) {
            throw new NotFoundHttpException('ไม่พบลูกหนี้');
        }
        $fy = $inv->fiscal_year;
        $inv->delete(); // settlements cascade
        Yii::$app->session->setFlash('success', 'ลบลูกหนี้เรียบร้อย');
        return $this->redirect(['invoices', 'fiscal_year' => $fy]);
    }

    public function actionDeposits()
    {
        $req = Yii::$app->request;
        $fy = (int) $req->get('fiscal_year', self::currentFiscalYear());
        $deposits = FinancePatientDeposit::find()
            ->where(['between', 'deposit_date', sprintf('%04d-10-01', $fy - 543 - 1), sprintf('%04d-09-30', $fy - 543)])
            ->orderBy(['deposit_date' => SORT_DESC, 'id' => SORT_DESC])->all();

        return $this->render('deposits', [
            'deposits' => $deposits,
            'fy' => $fy,
            'fiscalYears' => $this->fiscalYearOptions(),
            'newDeposit' => new FinancePatientDeposit(['deposit_date' => date('Y-m-d'), 'fiscal_year' => $fy]),
        ]);
    }

    public function actionDepositForm(?int $id = null)
    {
        $model = $id ? FinancePatientDeposit::findOne($id) : new FinancePatientDeposit();
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบรายการ');
        }
        $post = Yii::$app->request->post();
        if ($model->load($post)) {
            $model->deposit_date = AppHelper::normalizeDateToDb($post['FinancePatientDeposit']['deposit_date'] ?? null);
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'บันทึกเงินมัดจำเรียบร้อย');
                return $this->redirect(['deposits']);
            }
        }
        return $this->render('deposit-form', ['model' => $model]);
    }

    public function actionDeleteDeposit(int $id)
    {
        $model = FinancePatientDeposit::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบรายการ');
        }
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบรายการเรียบร้อย');
        return $this->redirect(['deposits']);
    }

    private function fiscalYearOptions(): array
    {
        $cur = self::currentFiscalYear();
        $years = [];
        for ($i = 0; $i <= 3; $i++) {
            $years[] = $cur - $i;
        }
        return $years;
    }
}
