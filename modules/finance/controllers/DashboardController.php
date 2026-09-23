<?php

namespace app\modules\finance\controllers;

use app\modules\finance\models\FinanceInbox;
use app\modules\finance\models\FinancePayable;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;

class DashboardController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['financeView']]]],
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
        $today = date('Y-m-d');

        return $this->render('index', [
            'fy' => $fy,
            'cash' => $this->cashSummary($fy, $today),
            'ar' => $this->arSummary($fy),
            'ap' => $this->apSummary(),
            'budget' => $this->budgetSummary($fy),
            'petty' => $this->pettyOnHand(),
            'queue' => $this->workQueue($fy),
            'fiscalYears' => $this->fiscalYearOptions(),
        ]);
    }

    /** รับ-จ่ายเงินบำรุง ปีงบ + วันนี้ */
    private function cashSummary(int $fy, string $today): array
    {
        $sum = function (array $cond) {
            return (float) (new Query())->from('{{%finance_cash_txn}}')->where($cond)->sum('amount');
        };
        $inYear = $sum(['fiscal_year' => $fy, 'txn_type' => 'IN']);
        $outYear = $sum(['fiscal_year' => $fy, 'txn_type' => 'OUT']);
        $inToday = $sum(['fiscal_year' => $fy, 'txn_type' => 'IN', 'doc_date' => $today]);
        $outToday = $sum(['fiscal_year' => $fy, 'txn_type' => 'OUT', 'doc_date' => $today]);
        return [
            'in_year' => $inYear, 'out_year' => $outYear, 'balance_year' => $inYear - $outYear,
            'in_today' => $inToday, 'out_today' => $outToday, 'net_today' => $inToday - $outToday,
        ];
    }

    /** ลูกหนี้ค่ารักษาคงค้าง (billed − settled) เฉพาะที่ยังไม่ปิด */
    private function arSummary(int $fy): array
    {
        $openStatus = ['billed', 'submitted', 'partial'];
        $billed = (float) (new Query())->from('{{%finance_ar_invoice}}')
            ->where(['fiscal_year' => $fy, 'status' => $openStatus])->sum('billed_amount');
        $count = (int) (new Query())->from('{{%finance_ar_invoice}}')
            ->where(['fiscal_year' => $fy, 'status' => $openStatus])->count();
        $settled = (float) (new Query())->from('{{%finance_ar_settlement}} s')
            ->innerJoin('{{%finance_ar_invoice}} i', 'i.id = s.ar_invoice_id')
            ->where(['i.fiscal_year' => $fy, 'i.status' => $openStatus])->sum('s.amount');
        return ['outstanding' => max(0.0, $billed - $settled), 'count' => $count];
    }

    /** เจ้าหนี้คงค้าง (net − paid) */
    private function apSummary(): array
    {
        $net = (float) (new Query())->from('{{%finance_payable}}')->sum('net_amount');
        $paid = (float) (new Query())->from('{{%finance_payable_settlement}}')->sum('amount');
        return ['outstanding' => max(0.0, $net - $paid)];
    }

    /** งบประมาณ: จัดสรร/เบิก/คงเหลือ */
    private function budgetSummary(int $fy): array
    {
        $allot = (float) (new Query())->from('{{%finance_budget_allotment}}')->where(['fiscal_year' => $fy])->sum('amount');
        $disb = (float) (new Query())->from('{{%finance_budget_txn}}')
            ->where(['fiscal_year' => $fy, 'txn_type' => 'disburse'])->sum('amount');
        return ['allot' => $allot, 'disbursed' => $disb, 'remaining' => $allot - $disb];
    }

    /** เงินสดย่อยคงเหลือในมือรวมทุกกอง */
    private function pettyOnHand(): float
    {
        $in = (float) (new Query())->from('{{%finance_petty_cash_txn}}')
            ->where(['txn_type' => ['establish', 'replenish']])->sum('amount');
        $out = (float) (new Query())->from('{{%finance_petty_cash_txn}}')
            ->where(['txn_type' => ['disburse', 'return']])->sum('amount');
        return $in - $out;
    }

    /** งานที่ต้องดำเนินการ */
    private function workQueue(int $fy): array
    {
        return [
            'inbox_pending' => (int) (new Query())->from('{{%finance_inbox}}')
                ->where(['status' => FinanceInbox::STATUS_PENDING_REVIEW])->count(),
            'payable_pending' => (int) (new Query())->from('{{%finance_payable}}')
                ->where(['status' => FinancePayable::STATUS_PENDING_APPROVAL])->count(),
            'ar_open' => (int) (new Query())->from('{{%finance_ar_invoice}}')
                ->where(['fiscal_year' => $fy, 'status' => ['billed', 'submitted', 'partial']])->count(),
        ];
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
