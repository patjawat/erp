<?php

namespace app\modules\finance\controllers;

use app\components\AppHelper;
use app\modules\finance\models\FinanceBudgetAllotment;
use app\modules\finance\models\FinanceBudgetReturn;
use app\modules\finance\models\FinanceBudgetTxn;
use app\modules\finance\models\FinanceTreasuryRemit;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * เงินงบประมาณ + การนำส่งคลัง (หมวด 1) — บันทึกข้อมูล
 * ทะเบียนคุมทางการ (พิมพ์/Excel) อยู่ที่ /finance/register
 */
class BudgetController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'allotments', 'transactions', 'treasury', 'returns'], 'roles' => ['financeView']],
                    ['allow' => true, 'actions' => ['delete-allotment', 'delete-transaction', 'delete-treasury', 'delete-return'], 'roles' => ['financeOperate']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete-allotment' => ['post'], 'delete-transaction' => ['post'],
                    'delete-treasury' => ['post'], 'delete-return' => ['post'],
                ],
            ],
        ]);
    }

    private static function currentFiscalYear(): int
    {
        $year = (int) date('Y') + 543;
        return (int) date('n') >= 10 ? $year + 1 : $year;
    }

    private function fy(): int
    {
        return (int) Yii::$app->request->get('fiscal_year', self::currentFiscalYear());
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

    public function actionIndex()
    {
        $fy = $this->fy();
        $allot = (new Query())->from('{{%finance_budget_allotment}}')
            ->select(['budget_category', 'a' => 'SUM(amount)'])
            ->where(['fiscal_year' => $fy])->groupBy('budget_category')->indexBy('budget_category')->all();
        $disb = (new Query())->from('{{%finance_budget_txn}}')
            ->select(['budget_category', 'd' => 'SUM(amount)'])
            ->where(['fiscal_year' => $fy, 'txn_type' => FinanceBudgetTxn::TYPE_DISBURSE])
            ->groupBy('budget_category')->indexBy('budget_category')->all();

        $rows = [];
        $tot = ['allot' => 0.0, 'disb' => 0.0];
        foreach (FinanceBudgetTxn::CATEGORIES as $key => $label) {
            $a = (float) ($allot[$key]['a'] ?? 0);
            $d = (float) ($disb[$key]['d'] ?? 0);
            if ($a == 0 && $d == 0) {
                continue;
            }
            $rows[] = ['key' => $key, 'label' => $label, 'allot' => $a, 'disb' => $d, 'remain' => $a - $d];
            $tot['allot'] += $a;
            $tot['disb'] += $d;
        }

        return $this->render('index', ['fy' => $fy, 'rows' => $rows, 'tot' => $tot, 'fiscalYears' => $this->fiscalYearOptions()]);
    }

    public function actionAllotments()
    {
        $fy = $this->fy();
        $model = new FinanceBudgetAllotment(['fiscal_year' => $fy]);
        if ($this->saveWithDate($model, ['allotment_date'])) {
            return $this->refreshTo('allotments', $model->fiscal_year);
        }
        return $this->render('allotments', [
            'fy' => $fy,
            'model' => $model,
            'list' => FinanceBudgetAllotment::find()->where(['fiscal_year' => $fy])
                ->orderBy(['period_no' => SORT_ASC, 'id' => SORT_ASC])->all(),
            'fiscalYears' => $this->fiscalYearOptions(),
        ]);
    }

    public function actionTransactions()
    {
        $fy = $this->fy();
        $model = new FinanceBudgetTxn(['fiscal_year' => $fy, 'doc_date' => date('Y-m-d'), 'txn_type' => FinanceBudgetTxn::TYPE_DISBURSE]);
        if ($this->saveWithDate($model, ['doc_date'])) {
            return $this->refreshTo('transactions', $model->fiscal_year);
        }
        return $this->render('transactions', [
            'fy' => $fy,
            'model' => $model,
            'allotments' => FinanceBudgetAllotment::find()->where(['fiscal_year' => $fy])->all(),
            'list' => FinanceBudgetTxn::find()->where(['fiscal_year' => $fy])
                ->orderBy(['doc_date' => SORT_DESC, 'id' => SORT_DESC])->limit(500)->all(),
            'fiscalYears' => $this->fiscalYearOptions(),
        ]);
    }

    public function actionTreasury()
    {
        $fy = $this->fy();
        $model = new FinanceTreasuryRemit(['fiscal_year' => $fy]);
        if ($this->saveWithDate($model, ['collect_date', 'remit_date'])) {
            return $this->refreshTo('treasury', $model->fiscal_year);
        }
        return $this->render('treasury', [
            'fy' => $fy,
            'model' => $model,
            'list' => FinanceTreasuryRemit::find()->where(['fiscal_year' => $fy])
                ->orderBy(['collect_date' => SORT_DESC, 'id' => SORT_DESC])->all(),
            'fiscalYears' => $this->fiscalYearOptions(),
        ]);
    }

    public function actionReturns()
    {
        $fy = $this->fy();
        $model = new FinanceBudgetReturn(['fiscal_year' => $fy]);
        if ($this->saveWithDate($model, ['return_date'])) {
            return $this->refreshTo('returns', $model->fiscal_year);
        }
        return $this->render('returns', [
            'fy' => $fy,
            'model' => $model,
            'list' => FinanceBudgetReturn::find()->where(['fiscal_year' => $fy])
                ->orderBy(['return_date' => SORT_DESC, 'id' => SORT_DESC])->all(),
            'fiscalYears' => $this->fiscalYearOptions(),
        ]);
    }

    public function actionDeleteAllotment(int $id)
    {
        return $this->deleteRow(FinanceBudgetAllotment::findOne($id), 'allotments');
    }

    public function actionDeleteTransaction(int $id)
    {
        return $this->deleteRow(FinanceBudgetTxn::findOne($id), 'transactions');
    }

    public function actionDeleteTreasury(int $id)
    {
        return $this->deleteRow(FinanceTreasuryRemit::findOne($id), 'treasury');
    }

    public function actionDeleteReturn(int $id)
    {
        return $this->deleteRow(FinanceBudgetReturn::findOne($id), 'returns');
    }

    // ---------- helpers ----------

    private function saveWithDate($model, array $dateFields): bool
    {
        if (!Yii::$app->request->isPost || !Yii::$app->user->can('financeOperate')) {
            return false;
        }
        $post = Yii::$app->request->post();
        if (!$model->load($post)) {
            return false;
        }
        $formName = $model->formName();
        foreach ($dateFields as $f) {
            $raw = $post[$formName][$f] ?? null;
            $model->$f = $raw ? (AppHelper::normalizeDateToDb($raw) ?: null) : null;
        }
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกเรียบร้อย');
            return true;
        }
        Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        return false;
    }

    private function refreshTo(string $action, int $fy)
    {
        return $this->redirect([$action, 'fiscal_year' => $fy]);
    }

    private function deleteRow($model, string $action)
    {
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบรายการ');
        }
        $fy = $model->fiscal_year;
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบรายการเรียบร้อย');
        return $this->redirect([$action, 'fiscal_year' => $fy]);
    }
}
