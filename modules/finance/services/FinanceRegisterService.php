<?php

namespace app\modules\finance\services;

use app\components\AppHelper;
use app\components\SiteHelper;
use app\modules\finance\models\FinanceCashTxn;
use app\modules\finance\models\FinanceCashVoucher;
use app\modules\finance\models\FinanceCheque;
use app\modules\finance\models\FinanceArFund;
use app\modules\finance\models\FinanceArInvoice;
use app\modules\finance\models\FinanceArSettlement;
use app\modules\finance\models\FinanceBudgetAllotment;
use app\modules\finance\models\FinanceBudgetReturn;
use app\modules\finance\models\FinanceBudgetTxn;
use app\modules\finance\models\FinanceCashAccount;
use app\modules\finance\models\FinanceCashProject;
use app\modules\finance\models\FinanceCashTransfer;
use app\modules\finance\models\FinanceInbox;
use app\modules\finance\models\FinancePatientDeposit;
use app\modules\finance\models\FinancePayable;
use app\modules\finance\models\FinancePettyCash;
use app\modules\finance\models\FinancePettyCashTxn;
use app\modules\finance\models\FinanceTreasuryRemit;
use app\modules\finance\models\FinancePayableSettlement;
use app\modules\finance\models\RegisterCatalog;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use yii\db\Query;

/**
 * Register Layer — engine กลางฉายธุรกรรมในระบบเป็น "ทะเบียนคุม" รูปแบบมาตรฐาน
 * (เฟส 0 ตาม docs/finance/control-registry-plan.md)
 *
 * build()       → โครงสร้างข้อมูลให้ view เรนเดอร์
 *   columns       : นิยามคอลัมน์ (key,label,align,w,money)
 *   rows          : ข้อมูลแต่ละแถว (assoc ตาม key คอลัมน์)
 *   totals        : ยอดรวมต่อคอลัมน์ (assoc: colKey => value)
 *   totalLabelKey : คอลัมน์ที่ให้ขึ้นคำว่า "รวม"
 *   opening       : ยอดยกมา (เฉพาะทะเบียนแบบ running)
 *   runningKey    : คอลัมน์ยอดคงเหลือ running
 *   vendorOptions : ตัวเลือกกรองผู้ขาย (เฉพาะทะเบียนเจ้าหนี้)
 *   period        : ช่วง/ตัวกรองที่ใช้จริง
 *
 * spreadsheet() → ไฟล์ Excel โครงเดียวกับหน้าจอ
 *
 * เล่มที่ยังไม่ต่อ builder จะคืน null → view แสดงโครง placeholder
 */
class FinanceRegisterService
{
    /** ทะเบียนแบบสมุดเงินสด running balance */
    private const CASHBOOK_KEYS = ['hospital_fund', 'nonbudget_cashbook'];
    /** ทะเบียนแบบ log (เจ้าหนี้) */
    private const CREDITOR_KEYS = ['creditor'];
    /** ทะเบียนคุมการจ่ายเช็ค/โอน */
    private const CHEQUE_KEYS = ['cheque_payment'];
    /** ทะเบียนคุมเงินประกันซอง/ประกันสัญญา */
    private const GUARANTEE_KEYS = ['guarantee'];
    /** ทะเบียนคุมภาษีหัก ณ ที่จ่าย */
    private const WHT_KEYS = ['wht'];
    /** ทะเบียนคุมหลักฐานขอเบิก (ฎีกา/ใบสำคัญ) — read-only จาก inbox */
    private const DISBURSEMENT_KEYS = ['disbursement_voucher'];
    /** ทะเบียนคุมเงินสดย่อย/เงินทดรองจ่าย (running per กอง) */
    private const PETTY_KEYS = ['petty_cash'];
    /** ทะเบียนคุมลูกหนี้ค่ารักษาแยกสิทธิ */
    private const AR_FUND_KEYS = ['ar_by_fund'];
    /** ทะเบียนคุมรายได้ค่ารักษาค้างรับ */
    private const AR_ACCRUED_KEYS = ['ar_accrued'];
    /** ทะเบียนคุมเงินมัดจำ/รับฝากผู้ป่วย */
    private const PATIENT_DEPOSIT_KEYS = ['patient_deposit'];
    /** ทะเบียนคุมเงินฝากธนาคาร/เงินฝากคลัง (running per บัญชี) */
    private const BANK_DEPOSIT_KEYS = ['bank_deposit'];
    /** ทะเบียนคุมเงินนอกงบฯ จำแนกตามโครงการ (running per โครงการ) */
    private const FUND_BY_PROJECT_KEYS = ['fund_by_project'];
    /** หมวด 1 เงินงบประมาณ */
    private const BUDGET_ALLOTMENT_KEYS = ['budget_allotment'];
    private const BUDGET_CASHBOOK_KEYS = ['budget_cashbook'];
    private const TREASURY_REMIT_KEYS = ['treasury_remit'];
    private const BUDGET_RETURN_KEYS = ['budget_return'];
    private const CENTRAL_FUND_KEYS = ['central_fund'];

    public static function build(string $key, array $filters = []): ?array
    {
        if (in_array($key, self::CASHBOOK_KEYS, true)) {
            return self::buildCashbook($filters);
        }
        if (in_array($key, self::CREDITOR_KEYS, true)) {
            return self::buildCreditor($filters);
        }
        if (in_array($key, self::CHEQUE_KEYS, true)) {
            return self::buildCheque($filters);
        }
        if (in_array($key, self::GUARANTEE_KEYS, true)) {
            return self::buildGuarantee($filters);
        }
        if (in_array($key, self::WHT_KEYS, true)) {
            return self::buildWht($filters);
        }
        if (in_array($key, self::DISBURSEMENT_KEYS, true)) {
            return self::buildDisbursement($filters);
        }
        if (in_array($key, self::PETTY_KEYS, true)) {
            return self::buildPettyCash($filters);
        }
        if (in_array($key, self::AR_FUND_KEYS, true)) {
            return self::buildArByFund($filters);
        }
        if (in_array($key, self::AR_ACCRUED_KEYS, true)) {
            return self::buildArAccrued($filters);
        }
        if (in_array($key, self::PATIENT_DEPOSIT_KEYS, true)) {
            return self::buildPatientDeposit($filters);
        }
        if (in_array($key, self::BANK_DEPOSIT_KEYS, true)) {
            return self::buildBankDeposit($filters);
        }
        if (in_array($key, self::FUND_BY_PROJECT_KEYS, true)) {
            return self::buildFundByProject($filters);
        }
        if (in_array($key, self::BUDGET_ALLOTMENT_KEYS, true)) {
            return self::buildBudgetAllotment($filters);
        }
        if (in_array($key, self::BUDGET_CASHBOOK_KEYS, true)) {
            return self::buildBudgetCashbook($filters);
        }
        if (in_array($key, self::TREASURY_REMIT_KEYS, true)) {
            return self::buildTreasuryRemit($filters);
        }
        if (in_array($key, self::BUDGET_RETURN_KEYS, true)) {
            return self::buildBudgetReturn($filters);
        }
        if (in_array($key, self::CENTRAL_FUND_KEYS, true)) {
            return self::buildCentralFund($filters);
        }
        return null;
    }

    public static function isImplemented(string $key): bool
    {
        return in_array($key, array_merge(
            self::CASHBOOK_KEYS,
            self::CREDITOR_KEYS,
            self::CHEQUE_KEYS,
            self::GUARANTEE_KEYS,
            self::WHT_KEYS,
            self::DISBURSEMENT_KEYS,
            self::PETTY_KEYS,
            self::AR_FUND_KEYS,
            self::AR_ACCRUED_KEYS,
            self::PATIENT_DEPOSIT_KEYS,
            self::BANK_DEPOSIT_KEYS,
            self::FUND_BY_PROJECT_KEYS,
            self::BUDGET_ALLOTMENT_KEYS,
            self::BUDGET_CASHBOOK_KEYS,
            self::TREASURY_REMIT_KEYS,
            self::BUDGET_RETURN_KEYS,
            self::CENTRAL_FUND_KEYS
        ), true);
    }

    /** ปีงบให้เลือกใน filter — รวมปีที่มีข้อมูลจริง + ปีปัจจุบันย้อนหลัง 3 ปี */
    public static function availableFiscalYears(): array
    {
        $years = array_map('intval', FinanceCashTxn::find()
            ->select('fiscal_year')->distinct()->column());
        $current = FinanceCashTxn::currentFiscalYear();
        for ($i = 0; $i <= 3; $i++) {
            $years[] = $current - $i;
        }
        $years = array_values(array_unique($years));
        rsort($years);
        return $years;
    }

    /** ผู้ขายที่มีในทะเบียนเจ้าหนี้ (สำหรับ dropdown กรองรายตัว) */
    public static function availableVendors(): array
    {
        return FinancePayable::find()
            ->select(['vendor_name_snapshot'])
            ->indexBy('vendor_id')
            ->orderBy(['vendor_name_snapshot' => SORT_ASC])
            ->column();
    }

    /** ช่วงวันที่ ค.ศ. ของปีงบ (พ.ศ.) — เต็มปีหรือเฉพาะเดือน */
    private static function fiscalRange(int $buddhaYear, ?int $month): array
    {
        $gy = $buddhaYear - 543;
        if ($month) {
            $calYear = $month >= 10 ? $gy - 1 : $gy; // ต.ค.-ธ.ค. อยู่ปีปฏิทินก่อนหน้า
            $start = sprintf('%04d-%02d-01', $calYear, $month);
            $end = date('Y-m-t', strtotime($start));
        } else {
            $start = sprintf('%04d-10-01', $gy - 1);
            $end = sprintf('%04d-09-30', $gy);
        }
        return [$start, $end];
    }

    // ---------- สมุดเงินสดเงินบำรุง (2.1 / 2.2) ----------

    private static function cashbookOpening(int $fy, string $start): float
    {
        $base = FinanceCashTxn::find()->where(['fiscal_year' => $fy])->andWhere(['<', 'doc_date', $start]);
        $in = (float) (clone $base)->andWhere(['txn_type' => FinanceCashTxn::TYPE_IN])->sum('amount');
        $out = (float) (clone $base)->andWhere(['txn_type' => FinanceCashTxn::TYPE_OUT])->sum('amount');
        return $in - $out;
    }

    private static function buildCashbook(array $filters): array
    {
        $fy = (int) ($filters['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());
        $month = !empty($filters['month']) ? (int) $filters['month'] : null;
        [$start, $end] = self::fiscalRange($fy, $month);

        $opening = self::cashbookOpening($fy, $start);
        $balance = $opening;
        $sumIn = 0.0;
        $sumOut = 0.0;
        $rows = [];
        $seq = 0;

        $txns = FinanceCashTxn::find()->with('category')
            ->where(['fiscal_year' => $fy])
            ->andWhere(['between', 'doc_date', $start, $end])
            ->orderBy(['doc_date' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        foreach ($txns as $t) {
            $isIn = $t->txn_type === FinanceCashTxn::TYPE_IN;
            $debit = $isIn ? (float) $t->amount : 0.0;
            $credit = $isIn ? 0.0 : (float) $t->amount;
            $balance += $debit - $credit;
            $sumIn += $debit;
            $sumOut += $credit;

            $desc = $t->category ? $t->category->name : '';
            if ($t->party_name) {
                $desc .= ($desc ? ' — ' : '') . ($isIn ? 'รับจาก ' : 'จ่ายให้ ') . $t->party_name;
            }

            $rows[] = [
                'seq' => ++$seq,
                'date' => AppHelper::convertToThai($t->doc_date),
                'doc_no' => $t->doc_no ?: '-',
                'description' => $desc ?: '-',
                'debit' => $debit ?: null,
                'credit' => $credit ?: null,
                'balance' => $balance,
                'method' => $t->payMethodLabel(),
                'note' => $t->note ?: '',
            ];
        }

        return [
            'mode' => 'running',
            'columns' => [
                ['key' => 'seq', 'label' => 'ลำดับ', 'align' => 'center', 'w' => '3rem'],
                ['key' => 'date', 'label' => 'วันที่', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'doc_no', 'label' => 'เลขที่เอกสาร', 'w' => '9rem'],
                ['key' => 'description', 'label' => 'รายการ'],
                ['key' => 'debit', 'label' => 'รับ', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'credit', 'label' => 'จ่าย', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'balance', 'label' => 'คงเหลือ', 'align' => 'end', 'w' => '9rem', 'money' => true],
                ['key' => 'method', 'label' => 'วิธี', 'align' => 'center', 'w' => '6rem'],
                ['key' => 'note', 'label' => 'หมายเหตุ', 'w' => '8rem'],
            ],
            'opening' => $opening,
            'runningKey' => 'balance',
            'totalLabelKey' => 'description',
            'rows' => $rows,
            'totals' => ['debit' => $sumIn, 'credit' => $sumOut, 'balance' => $balance],
            'period' => ['fiscal_year' => $fy, 'month' => $month, 'start' => $start, 'end' => $end],
        ];
    }

    // ---------- ทะเบียนคุมเจ้าหนี้ (4.2) — รวม / กรองรายตัว ----------

    private static function buildCreditor(array $filters): array
    {
        $fy = (int) ($filters['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());
        $month = !empty($filters['month']) ? (int) $filters['month'] : null;
        $vendorId = !empty($filters['vendor_id']) ? (int) $filters['vendor_id'] : null;
        [$start, $end] = self::fiscalRange($fy, $month);

        $query = FinancePayable::find()
            // ยึดวันตั้งหนี้ (billing_date) ไม่มีก็ใช้วันใบแจ้งหนี้
            ->andWhere('COALESCE(billing_date, invoice_date) BETWEEN :s AND :e', [':s' => $start, ':e' => $end])
            ->orderBy(['billing_date' => SORT_ASC, 'invoice_date' => SORT_ASC, 'id' => SORT_ASC]);
        if ($vendorId) {
            $query->andWhere(['vendor_id' => $vendorId]);
        }
        $payables = $query->all();

        // ยอดจ่ายแล้วรวมทีเดียว กัน N+1
        $paidMap = [];
        $ids = array_map(fn ($p) => $p->id, $payables);
        if ($ids) {
            foreach (FinancePayableSettlement::find()
                ->select(['payable_id', 'paid' => 'SUM(amount)'])
                ->where(['payable_id' => $ids])
                ->groupBy('payable_id')->asArray()->all() as $r) {
                $paidMap[(int) $r['payable_id']] = (float) $r['paid'];
            }
        }

        $statusLabels = FinancePayable::statusOptions();
        $sumNet = 0.0;
        $sumPaid = 0.0;
        $sumOut = 0.0;
        $rows = [];
        $seq = 0;

        foreach ($payables as $p) {
            $net = (float) $p->net_amount;
            $paid = $paidMap[(int) $p->id] ?? 0.0;
            $out = max(0.0, $net - $paid);
            $sumNet += $net;
            $sumPaid += $paid;
            $sumOut += $out;

            $rows[] = [
                'seq' => ++$seq,
                'date' => AppHelper::convertToThai($p->billing_date ?: $p->invoice_date),
                'doc_no' => $p->payable_no ?: $p->ref,
                'vendor' => $p->vendor_name_snapshot ?: ('#' . $p->vendor_id),
                'invoice_no' => $p->invoice_no ?: '-',
                'due_date' => $p->due_date ? AppHelper::convertToThai($p->due_date) : '-',
                'net' => $net,
                'paid' => $paid ?: null,
                'outstanding' => $out,
                'status' => $statusLabels[$p->status] ?? $p->status,
            ];
        }

        return [
            'mode' => 'log',
            'columns' => [
                ['key' => 'seq', 'label' => 'ลำดับ', 'align' => 'center', 'w' => '3rem'],
                ['key' => 'date', 'label' => 'วันที่ตั้งหนี้', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'doc_no', 'label' => 'เลขทะเบียน', 'w' => '8rem'],
                ['key' => 'vendor', 'label' => 'ผู้ขาย'],
                ['key' => 'invoice_no', 'label' => 'เลขใบแจ้งหนี้', 'w' => '8rem'],
                ['key' => 'due_date', 'label' => 'ครบกำหนด', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'net', 'label' => 'ยอดตั้งหนี้', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'paid', 'label' => 'จ่ายแล้ว', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'outstanding', 'label' => 'คงค้าง', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'status', 'label' => 'สถานะ', 'align' => 'center', 'w' => '7rem'],
            ],
            'totalLabelKey' => 'invoice_no',
            'rows' => $rows,
            'totals' => ['net' => $sumNet, 'paid' => $sumPaid, 'outstanding' => $sumOut],
            'filterSelect' => [
                'param' => 'vendor_id',
                'label' => 'ผู้ขาย',
                'allLabel' => 'ทุกราย (ทะเบียนคุมเจ้าหนี้รวม)',
                'options' => self::availableVendors(),
                'selected' => $vendorId,
            ],
            'period' => ['fiscal_year' => $fy, 'month' => $month, 'vendor_id' => $vendorId, 'start' => $start, 'end' => $end],
        ];
    }

    // ---------- ทะเบียนคุมภาษีหัก ณ ที่จ่าย (5.1) ----------

    private static function buildWht(array $filters): array
    {
        $fy = (int) ($filters['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());
        $month = !empty($filters['month']) ? (int) $filters['month'] : null;
        $whtType = !empty($filters['wht_type']) ? (string) $filters['wht_type'] : null;

        $query = FinanceCashVoucher::find()
            ->where(['fiscal_year' => $fy])
            ->andWhere(['>', 'wht_amount', 0])
            ->orderBy(['pay_date' => SORT_ASC, 'id' => SORT_ASC]);
        if ($month) {
            [$s, $e] = self::fiscalRange($fy, $month);
            $query->andWhere(['between', 'pay_date', $s, $e]);
        }
        if ($whtType) {
            $query->andWhere(['wht_type' => $whtType]);
        }

        $sumBase = 0.0;
        $sumWht = 0.0;
        $sumNet = 0.0;
        $rows = [];
        $seq = 0;
        foreach ($query->all() as $v) {
            $base = (float) $v->subtotal;
            $wht = (float) $v->wht_amount;
            $net = (float) $v->net_amount;
            $sumBase += $base;
            $sumWht += $wht;
            $sumNet += $net;
            $rows[] = [
                'seq' => ++$seq,
                'date' => $v->pay_date ? AppHelper::convertToThai($v->pay_date) : '-',
                'doc_no' => $v->doc_no ?: '-',
                'payee' => $v->payee_name ?: '-',
                'wht_label' => $v->whtLabel(),
                'base' => $base,
                'wht' => $wht,
                'net' => $net,
            ];
        }

        // ตัวเลือกกรองประเภท ภ.ง.ด.
        $typeOptions = [];
        foreach (FinanceCashVoucher::WHT_TYPES as $k => $meta) {
            $typeOptions[$k] = $meta['label'];
        }

        return [
            'mode' => 'log',
            'columns' => [
                ['key' => 'seq', 'label' => 'ลำดับ', 'align' => 'center', 'w' => '3rem'],
                ['key' => 'date', 'label' => 'วันที่จ่าย', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'doc_no', 'label' => 'เลขใบสำคัญ', 'w' => '8rem'],
                ['key' => 'payee', 'label' => 'จ่ายให้ / ผู้ถูกหัก'],
                ['key' => 'wht_label', 'label' => 'ประเภท ภ.ง.ด.', 'align' => 'center', 'w' => '10rem'],
                ['key' => 'base', 'label' => 'ยอดก่อนหัก', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'wht', 'label' => 'ภาษีหัก', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'net', 'label' => 'จ่ายสุทธิ', 'align' => 'end', 'w' => '8rem', 'money' => true],
            ],
            'totalLabelKey' => 'wht_label',
            'rows' => $rows,
            'totals' => ['base' => $sumBase, 'wht' => $sumWht, 'net' => $sumNet],
            'filterSelect' => [
                'param' => 'wht_type',
                'label' => 'ประเภท ภ.ง.ด.',
                'allLabel' => 'ทุกประเภท',
                'options' => $typeOptions,
                'selected' => $whtType,
            ],
            'period' => ['fiscal_year' => $fy, 'month' => $month, 'wht_type' => $whtType],
        ];
    }

    // ---------- ทะเบียนคุมเงินฝากธนาคาร/เงินฝากคลัง (2.3) — running per บัญชี ----------

    private static function buildBankDeposit(array $filters): array
    {
        $fy = (int) ($filters['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());
        $month = !empty($filters['month']) ? (int) $filters['month'] : null;
        $accounts = FinanceCashAccount::activeList();
        $accountId = !empty($filters['account_id']) ? (int) $filters['account_id'] : (int) (array_key_first($accounts) ?? 0);

        [$start, $end] = self::fiscalRange($fy, $month);
        [$fyStart] = self::fiscalRange($fy, null);

        $opening = 0.0;
        $balance = 0.0;
        $sumIn = 0.0;
        $sumOut = 0.0;
        $rows = [];

        if ($accountId) {
            $account = FinanceCashAccount::findOne($accountId);
            $baseOpening = $account ? $account->balanceFor($fy) : 0.0;

            // การเคลื่อนไหวก่อนช่วง (ภายในปีงบ) เพื่อคำนวณยอดยกมา
            $movBefore = self::bankMovements($accountId, $fyStart, self::dayBefore($start));
            $opening = $baseOpening;
            foreach ($movBefore as $m) {
                $opening += $m['in'] - $m['out'];
            }
            $balance = $opening;

            $movements = self::bankMovements($accountId, $start, $end);
            usort($movements, fn ($a, $b) => strcmp((string) $a['date'], (string) $b['date']) ?: ($a['seq'] <=> $b['seq']));
            $seq = 0;
            foreach ($movements as $m) {
                $balance += $m['in'] - $m['out'];
                $sumIn += $m['in'];
                $sumOut += $m['out'];
                $rows[] = [
                    'seq' => ++$seq,
                    'date' => AppHelper::convertToThai($m['date']),
                    'doc_no' => $m['ref'] ?: '-',
                    'description' => $m['desc'],
                    'debit' => $m['in'] ?: null,
                    'credit' => $m['out'] ?: null,
                    'balance' => $balance,
                    'note' => '',
                ];
            }
        }

        return [
            'mode' => 'running',
            'columns' => [
                ['key' => 'seq', 'label' => 'ลำดับ', 'align' => 'center', 'w' => '3rem'],
                ['key' => 'date', 'label' => 'วันที่', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'doc_no', 'label' => 'เลขที่เอกสาร', 'w' => '9rem'],
                ['key' => 'description', 'label' => 'รายการ'],
                ['key' => 'debit', 'label' => 'เงินเข้า', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'credit', 'label' => 'เงินออก', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'balance', 'label' => 'คงเหลือ', 'align' => 'end', 'w' => '9rem', 'money' => true],
                ['key' => 'note', 'label' => 'หมายเหตุ', 'w' => '7rem'],
            ],
            'opening' => $opening,
            'runningKey' => 'balance',
            'totalLabelKey' => 'description',
            'rows' => $rows,
            'totals' => ['debit' => $sumIn, 'credit' => $sumOut, 'balance' => $balance],
            'filterSelect' => [
                'param' => 'account_id',
                'label' => 'บัญชีเงินฝาก',
                'allLabel' => $accounts ? '— เลือกบัญชี —' : 'ยังไม่มีบัญชี',
                'options' => $accounts,
                'selected' => $accountId,
            ],
            'period' => ['fiscal_year' => $fy, 'month' => $month, 'account_id' => $accountId],
        ];
    }

    /** การเคลื่อนไหวของบัญชีในช่วง: โอนเข้า/ออก + ใบสำคัญจ่าย */
    private static function bankMovements(int $accountId, string $start, string $end): array
    {
        $out = [];
        $seq = 0;
        foreach (FinanceCashTransfer::find()->with('fromAccount', 'toAccount')
            ->where(['and', ['between', 'transfer_date', $start, $end],
                ['or', ['from_account_id' => $accountId], ['to_account_id' => $accountId]]])
            ->all() as $tr) {
            $isOut = (int) $tr->from_account_id === $accountId;
            $other = $isOut ? ($tr->toAccount ? $tr->toAccount->label() : '') : ($tr->fromAccount ? $tr->fromAccount->label() : '');
            $out[] = [
                'seq' => ++$seq,
                'date' => $tr->transfer_date,
                'ref' => $tr->doc_ref,
                'desc' => $isOut ? ('โอนไป ' . $other) : ('รับโอนจาก ' . $other),
                'in' => $isOut ? 0.0 : (float) $tr->amount,
                'out' => $isOut ? (float) $tr->amount : 0.0,
            ];
        }
        foreach (FinanceCashVoucher::find()
            ->where(['account_id' => $accountId])->andWhere(['between', 'pay_date', $start, $end])
            ->all() as $v) {
            $out[] = [
                'seq' => ++$seq,
                'date' => $v->pay_date,
                'ref' => $v->cheque_no ?: $v->doc_no,
                'desc' => 'จ่าย: ' . ($v->payee_name ?: ('ใบสำคัญ ' . $v->doc_no)),
                'in' => 0.0,
                'out' => (float) $v->net_amount,
            ];
        }
        return $out;
    }

    private static function dayBefore(string $date): string
    {
        return date('Y-m-d', strtotime($date . ' -1 day'));
    }

    // ---------- ลูกหนี้ค่ารักษา (หมวด 3) ----------

    /** ยอดที่ปิดหนี้แล้วต่อใบ (bulk กัน N+1) */
    private static function arSettledMap(array $ids): array
    {
        $map = [];
        if ($ids) {
            foreach (FinanceArSettlement::find()
                ->select(['ar_invoice_id', 's' => 'SUM(amount)'])
                ->where(['ar_invoice_id' => $ids])
                ->groupBy('ar_invoice_id')->asArray()->all() as $r) {
                $map[(int) $r['ar_invoice_id']] = (float) $r['s'];
            }
        }
        return $map;
    }

    private static function arPatientLabel(FinanceArInvoice $inv): string
    {
        if ($inv->patient_name) {
            return $inv->patient_name;
        }
        if ($inv->hn) {
            return 'HN ' . $inv->hn;
        }
        return '-';
    }

    /** 3.1 ลูกหนี้ค่ารักษาแยกสิทธิ */
    private static function buildArByFund(array $filters): array
    {
        $fy = (int) ($filters['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());
        $month = !empty($filters['month']) ? (int) $filters['month'] : null;
        $fundId = !empty($filters['fund_id']) ? (int) $filters['fund_id'] : null;

        $fundNames = FinanceArFund::find()->select('name')->indexBy('id')->column();

        $query = FinanceArInvoice::find()->where(['fiscal_year' => $fy]);
        if ($fundId) {
            $query->andWhere(['ar_fund_id' => $fundId]);
        }
        if ($month) {
            $query->andWhere(['period_month' => $month]);
        }
        $invoices = $query->orderBy(['ar_fund_id' => SORT_ASC, 'service_date' => SORT_ASC, 'id' => SORT_ASC])->all();
        $settled = self::arSettledMap(array_map(fn ($i) => $i->id, $invoices));

        $statusLabels = FinanceArInvoice::statusOptions();
        $sumB = 0.0;
        $sumS = 0.0;
        $sumO = 0.0;
        $rows = [];
        $seq = 0;
        foreach ($invoices as $inv) {
            $billed = (float) $inv->billed_amount;
            $paid = $settled[$inv->id] ?? 0.0;
            $out = max(0.0, $billed - $paid);
            $sumB += $billed;
            $sumS += $paid;
            $sumO += $out;
            $rows[] = [
                'seq' => ++$seq,
                'date' => $inv->service_date ? AppHelper::convertToThai($inv->service_date) : '-',
                'doc_no' => $inv->doc_no ?: '-',
                'fund' => $fundNames[$inv->ar_fund_id] ?? ('#' . $inv->ar_fund_id),
                'patient' => self::arPatientLabel($inv),
                'billed' => $billed,
                'settled' => $paid ?: null,
                'outstanding' => $out,
                'status' => $statusLabels[$inv->status] ?? $inv->status,
            ];
        }

        return [
            'mode' => 'log',
            'columns' => [
                ['key' => 'seq', 'label' => 'ลำดับ', 'align' => 'center', 'w' => '3rem'],
                ['key' => 'date', 'label' => 'วันที่บริการ', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'doc_no', 'label' => 'เลขอ้างอิง', 'w' => '8rem'],
                ['key' => 'fund', 'label' => 'สิทธิ', 'w' => '11rem'],
                ['key' => 'patient', 'label' => 'ผู้ป่วย / HN'],
                ['key' => 'billed', 'label' => 'ตั้งเบิก', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'settled', 'label' => 'รับ/ตัดแล้ว', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'outstanding', 'label' => 'คงค้าง', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'status', 'label' => 'สถานะ', 'align' => 'center', 'w' => '8rem'],
            ],
            'totalLabelKey' => 'patient',
            'rows' => $rows,
            'totals' => ['billed' => $sumB, 'settled' => $sumS, 'outstanding' => $sumO],
            'filterSelect' => [
                'param' => 'fund_id',
                'label' => 'สิทธิ',
                'allLabel' => 'ทุกสิทธิ (รวม)',
                'options' => FinanceArFund::activeList(),
                'selected' => $fundId,
            ],
            'period' => ['fiscal_year' => $fy, 'month' => $month, 'fund_id' => $fundId],
        ];
    }

    /** 3.2 รายได้ค่ารักษาค้างรับ (เฉพาะที่ยังคงค้าง + อายุหนี้) */
    private static function buildArAccrued(array $filters): array
    {
        $fy = (int) ($filters['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());
        $month = !empty($filters['month']) ? (int) $filters['month'] : null;
        $fundId = !empty($filters['fund_id']) ? (int) $filters['fund_id'] : null;

        $fundNames = FinanceArFund::find()->select('name')->indexBy('id')->column();

        $query = FinanceArInvoice::find()
            ->where(['fiscal_year' => $fy])
            ->andWhere(['status' => [FinanceArInvoice::STATUS_BILLED, FinanceArInvoice::STATUS_SUBMITTED, FinanceArInvoice::STATUS_PARTIAL]]);
        if ($fundId) {
            $query->andWhere(['ar_fund_id' => $fundId]);
        }
        if ($month) {
            $query->andWhere(['period_month' => $month]);
        }
        $invoices = $query->orderBy(['service_date' => SORT_ASC, 'id' => SORT_ASC])->all();
        $settled = self::arSettledMap(array_map(fn ($i) => $i->id, $invoices));

        $today = new \DateTime('today');
        $sumB = 0.0;
        $sumS = 0.0;
        $sumO = 0.0;
        $rows = [];
        $seq = 0;
        foreach ($invoices as $inv) {
            $billed = (float) $inv->billed_amount;
            $paid = $settled[$inv->id] ?? 0.0;
            $out = max(0.0, $billed - $paid);
            if ($out <= 0.005) {
                continue;
            }
            $aging = '-';
            if ($inv->service_date) {
                $diff = $today->diff(new \DateTime($inv->service_date))->days;
                $aging = (int) $diff;
            }
            $sumB += $billed;
            $sumS += $paid;
            $sumO += $out;
            $rows[] = [
                'seq' => ++$seq,
                'date' => $inv->service_date ? AppHelper::convertToThai($inv->service_date) : '-',
                'doc_no' => $inv->doc_no ?: '-',
                'fund' => $fundNames[$inv->ar_fund_id] ?? ('#' . $inv->ar_fund_id),
                'patient' => self::arPatientLabel($inv),
                'billed' => $billed,
                'settled' => $paid ?: null,
                'outstanding' => $out,
                'aging' => $aging,
            ];
        }

        return [
            'mode' => 'log',
            'columns' => [
                ['key' => 'seq', 'label' => 'ลำดับ', 'align' => 'center', 'w' => '3rem'],
                ['key' => 'date', 'label' => 'วันที่บริการ', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'doc_no', 'label' => 'เลขอ้างอิง', 'w' => '8rem'],
                ['key' => 'fund', 'label' => 'สิทธิ', 'w' => '11rem'],
                ['key' => 'patient', 'label' => 'ผู้ป่วย / HN'],
                ['key' => 'billed', 'label' => 'ตั้งเบิก', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'settled', 'label' => 'รับ/ตัดแล้ว', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'outstanding', 'label' => 'ค้างรับ', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'aging', 'label' => 'ค้าง (วัน)', 'align' => 'center', 'w' => '6rem'],
            ],
            'totalLabelKey' => 'patient',
            'rows' => $rows,
            'totals' => ['billed' => $sumB, 'settled' => $sumS, 'outstanding' => $sumO],
            'filterSelect' => [
                'param' => 'fund_id',
                'label' => 'สิทธิ',
                'allLabel' => 'ทุกสิทธิ (รวม)',
                'options' => FinanceArFund::activeList(),
                'selected' => $fundId,
            ],
            'period' => ['fiscal_year' => $fy, 'month' => $month, 'fund_id' => $fundId],
        ];
    }

    /** 3.3 เงินมัดจำ/รับฝากผู้ป่วย */
    private static function buildPatientDeposit(array $filters): array
    {
        $fy = (int) ($filters['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());
        $month = !empty($filters['month']) ? (int) $filters['month'] : null;
        $status = !empty($filters['status']) ? (string) $filters['status'] : null;
        [$start, $end] = self::fiscalRange($fy, $month);

        $query = FinancePatientDeposit::find()
            ->where(['between', 'deposit_date', $start, $end])
            ->orderBy(['deposit_date' => SORT_ASC, 'id' => SORT_ASC]);
        if ($status) {
            $query->andWhere(['status' => $status]);
        }

        $statusLabels = FinancePatientDeposit::statusOptions();
        $sumA = 0.0;
        $sumU = 0.0;
        $sumR = 0.0;
        $sumRem = 0.0;
        $rows = [];
        $seq = 0;
        foreach ($query->all() as $d) {
            $rem = $d->remaining();
            $sumA += (float) $d->amount;
            $sumU += (float) $d->used_amount;
            $sumR += (float) $d->refunded_amount;
            $sumRem += $rem;
            $rows[] = [
                'seq' => ++$seq,
                'date' => AppHelper::convertToThai($d->deposit_date),
                'receipt_no' => $d->receipt_no ?: '-',
                'patient' => $d->patient_name ?: ($d->hn ? 'HN ' . $d->hn : '-'),
                'amount' => (float) $d->amount,
                'used' => (float) $d->used_amount ?: null,
                'refunded' => (float) $d->refunded_amount ?: null,
                'remaining' => $rem,
                'status' => $statusLabels[$d->status] ?? $d->status,
            ];
        }

        return [
            'mode' => 'log',
            'columns' => [
                ['key' => 'seq', 'label' => 'ลำดับ', 'align' => 'center', 'w' => '3rem'],
                ['key' => 'date', 'label' => 'วันที่รับฝาก', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'receipt_no', 'label' => 'เลขที่ใบรับ', 'w' => '8rem'],
                ['key' => 'patient', 'label' => 'ผู้ป่วย / HN'],
                ['key' => 'amount', 'label' => 'รับฝาก', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'used', 'label' => 'หักชำระ', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'refunded', 'label' => 'คืน', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'remaining', 'label' => 'คงเหลือ', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'status', 'label' => 'สถานะ', 'align' => 'center', 'w' => '8rem'],
            ],
            'totalLabelKey' => 'patient',
            'rows' => $rows,
            'totals' => ['amount' => $sumA, 'used' => $sumU, 'refunded' => $sumR, 'remaining' => $sumRem],
            'filterSelect' => [
                'param' => 'status',
                'label' => 'สถานะ',
                'allLabel' => 'ทุกสถานะ',
                'options' => $statusLabels,
                'selected' => $status,
            ],
            'period' => ['fiscal_year' => $fy, 'month' => $month, 'status' => $status],
        ];
    }

    // ---------- ทะเบียนคุมเงินสดย่อย/เงินทดรองจ่าย (4.4) — running per กอง ----------

    private static function buildPettyCash(array $filters): array
    {
        $fy = (int) ($filters['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());
        $month = !empty($filters['month']) ? (int) $filters['month'] : null;
        [$start, $end] = self::fiscalRange($fy, $month);

        $funds = FinancePettyCash::activeList(); // [id => name]
        $fundId = !empty($filters['fund_id']) ? (int) $filters['fund_id'] : (int) (array_key_first($funds) ?? 0);

        $opening = 0.0;
        $balance = 0.0;
        $sumIn = 0.0;
        $sumOut = 0.0;
        $rows = [];
        $seq = 0;

        if ($fundId) {
            // ยอดยกมา ก่อนช่วงที่เลือก
            $before = FinancePettyCashTxn::find()->where(['petty_cash_id' => $fundId])->andWhere(['<', 'doc_date', $start]);
            $inB = (float) (clone $before)->andWhere(['txn_type' => FinancePettyCashTxn::INFLOW])->sum('amount');
            $outB = (float) (clone $before)->andWhere(['not in', 'txn_type', FinancePettyCashTxn::INFLOW])->sum('amount');
            $opening = $inB - $outB;
            $balance = $opening;

            $txns = FinancePettyCashTxn::find()
                ->where(['petty_cash_id' => $fundId])
                ->andWhere(['between', 'doc_date', $start, $end])
                ->orderBy(['doc_date' => SORT_ASC, 'id' => SORT_ASC])
                ->all();

            foreach ($txns as $t) {
                $in = $t->isInflow() ? (float) $t->amount : 0.0;
                $out = $t->isInflow() ? 0.0 : (float) $t->amount;
                $balance += $in - $out;
                $sumIn += $in;
                $sumOut += $out;
                $desc = $t->typeLabel();
                if ($t->description) {
                    $desc .= ' — ' . $t->description;
                }
                if ($t->payee) {
                    $desc .= ' (จ่ายให้ ' . $t->payee . ')';
                }
                $rows[] = [
                    'seq' => ++$seq,
                    'date' => AppHelper::convertToThai($t->doc_date),
                    'doc_no' => $t->doc_no ?: '-',
                    'description' => $desc,
                    'debit' => $in ?: null,
                    'credit' => $out ?: null,
                    'balance' => $balance,
                    'note' => $t->note ?: '',
                ];
            }
        }

        return [
            'mode' => 'running',
            'columns' => [
                ['key' => 'seq', 'label' => 'ลำดับ', 'align' => 'center', 'w' => '3rem'],
                ['key' => 'date', 'label' => 'วันที่', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'doc_no', 'label' => 'เลขที่เอกสาร', 'w' => '8rem'],
                ['key' => 'description', 'label' => 'รายการ'],
                ['key' => 'debit', 'label' => 'รับ', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'credit', 'label' => 'จ่าย', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'balance', 'label' => 'คงเหลือ', 'align' => 'end', 'w' => '9rem', 'money' => true],
                ['key' => 'note', 'label' => 'หมายเหตุ', 'w' => '8rem'],
            ],
            'opening' => $opening,
            'runningKey' => 'balance',
            'totalLabelKey' => 'description',
            'rows' => $rows,
            'totals' => ['debit' => $sumIn, 'credit' => $sumOut, 'balance' => $balance],
            'filterSelect' => [
                'param' => 'fund_id',
                'label' => 'กองเงินสดย่อย',
                'allLabel' => $funds ? '— เลือกกอง —' : 'ยังไม่มีกอง',
                'options' => $funds,
                'selected' => $fundId,
            ],
            'period' => ['fiscal_year' => $fy, 'month' => $month, 'fund_id' => $fundId],
        ];
    }

    // ---------- ทะเบียนคุมหลักฐานขอเบิก (4.1) — read-only จาก finance_inbox ----------

    private static function buildDisbursement(array $filters): array
    {
        $fy = (int) ($filters['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());
        $month = !empty($filters['month']) ? (int) $filters['month'] : null;
        $status = !empty($filters['status']) ? (string) $filters['status'] : null;
        [$start, $end] = self::fiscalRange($fy, $month);

        $query = FinanceInbox::find()
            ->where(['between', 'document_date', $start, $end])
            ->orderBy(['document_date' => SORT_ASC, 'id' => SORT_ASC]);
        if ($status) {
            $query->andWhere(['status' => $status]);
        }

        $sourceLabels = ['purchase' => 'พัสดุ', 'payroll' => 'เงินเดือน', 'manual' => 'บันทึกเอง'];
        $statusLabels = FinanceInbox::statusOptions();
        $sum = 0.0;
        $rows = [];
        $seq = 0;
        foreach ($query->all() as $x) {
            $amount = (float) $x->amount;
            $sum += $amount;
            $rows[] = [
                'seq' => ++$seq,
                'date' => $x->document_date ? AppHelper::convertToThai($x->document_date) : '-',
                'doc_no' => $x->source_document_no ?: $x->ref,
                'source' => $sourceLabels[$x->source_system] ?? $x->source_system,
                'vendor' => $x->vendor_name_snapshot ?: '-',
                'amount' => $amount ?: null,
                'status' => $statusLabels[$x->status] ?? $x->status,
            ];
        }

        return [
            'mode' => 'log',
            'columns' => [
                ['key' => 'seq', 'label' => 'ลำดับ', 'align' => 'center', 'w' => '3rem'],
                ['key' => 'date', 'label' => 'วันที่เอกสาร', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'doc_no', 'label' => 'เลขที่เอกสาร', 'w' => '9rem'],
                ['key' => 'source', 'label' => 'แหล่งที่มา', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'vendor', 'label' => 'รับจาก / ผู้ขอเบิก'],
                ['key' => 'amount', 'label' => 'จำนวนเงิน', 'align' => 'end', 'w' => '9rem', 'money' => true],
                ['key' => 'status', 'label' => 'สถานะ', 'align' => 'center', 'w' => '8rem'],
            ],
            'totalLabelKey' => 'vendor',
            'rows' => $rows,
            'totals' => ['amount' => $sum],
            'filterSelect' => [
                'param' => 'status',
                'label' => 'สถานะ',
                'allLabel' => 'ทุกสถานะ',
                'options' => $statusLabels,
                'selected' => $status,
            ],
            'period' => ['fiscal_year' => $fy, 'month' => $month, 'status' => $status],
        ];
    }

    // ---------- ทะเบียนคุมการจ่ายเช็ค/โอน (4.3) ----------

    private static function buildCheque(array $filters): array
    {
        $fy = (int) ($filters['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());
        $month = !empty($filters['month']) ? (int) $filters['month'] : null;
        [$start, $end] = self::fiscalRange($fy, $month);

        $cheques = FinanceCheque::find()->with('cashAccount')
            ->where(['between', 'cheque_date', $start, $end])
            ->orderBy(['cheque_date' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        $statusLabels = FinanceCheque::statusOptions();
        $sum = 0.0;
        $rows = [];
        $seq = 0;
        foreach ($cheques as $c) {
            $amount = (float) $c->amount;
            $sum += $amount;
            $rows[] = [
                'seq' => ++$seq,
                'date' => $c->cheque_date ? AppHelper::convertToThai($c->cheque_date) : '-',
                'cheque_no' => trim(($c->cheque_book_no ? $c->cheque_book_no . '/' : '') . $c->cheque_no),
                'payee' => $c->payee_name ?: '-',
                'account' => $c->cashAccount ? $c->cashAccount->label() : '-',
                'amount' => $amount,
                'status' => $statusLabels[$c->status] ?? $c->status,
            ];
        }

        return [
            'mode' => 'log',
            'columns' => [
                ['key' => 'seq', 'label' => 'ลำดับ', 'align' => 'center', 'w' => '3rem'],
                ['key' => 'date', 'label' => 'วันที่เช็ค', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'cheque_no', 'label' => 'เล่ม/เลขที่เช็ค', 'w' => '9rem'],
                ['key' => 'payee', 'label' => 'จ่ายให้'],
                ['key' => 'account', 'label' => 'บัญชีจ่าย', 'w' => '12rem'],
                ['key' => 'amount', 'label' => 'จำนวนเงิน', 'align' => 'end', 'w' => '9rem', 'money' => true],
                ['key' => 'status', 'label' => 'สถานะ', 'align' => 'center', 'w' => '7rem'],
            ],
            'totalLabelKey' => 'account',
            'rows' => $rows,
            'totals' => ['amount' => $sum],
            'period' => ['fiscal_year' => $fy, 'month' => $month, 'start' => $start, 'end' => $end],
        ];
    }

    // ---------- ทะเบียนคุมเงินประกันซอง/ประกันสัญญา (5.2) ----------

    private static function buildGuarantee(array $filters): array
    {
        $fy = (int) ($filters['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());
        $month = !empty($filters['month']) ? (int) $filters['month'] : null;

        // อ่านอย่างเดียวจากตารางโมดูล purchase (ไม่ผูกโค้ด) — จัดกลุ่มตามปี พ.ศ.
        $query = (new Query())->from('purchase_bond')
            ->where(['thai_year' => $fy])
            ->orderBy(['place_date' => SORT_ASC, 'id' => SORT_ASC]);
        if ($month) {
            [$s, $e] = self::fiscalRange($fy, $month);
            $query->andWhere(['between', 'place_date', $s, $e]);
        }

        $typeLabels = ['contract' => 'ประกันสัญญา', 'bid' => 'ประกันซอง', 'performance' => 'ประกันผลงาน', 'advance' => 'ประกันเงินล่วงหน้า', 'other' => 'อื่น ๆ'];
        $formLabels = ['cash' => 'เงินสด', 'bank_guarantee' => 'หนังสือค้ำประกันธนาคาร', 'gov_bond' => 'พันธบัตรรัฐบาล', 'cheque' => 'เช็ค', 'other' => 'อื่น ๆ'];
        $statusLabels = ['pending' => 'รอวาง', 'active' => 'ถืออยู่', 'returned' => 'คืนแล้ว', 'seized' => 'ริบ', 'exempt' => 'ยกเว้น'];

        $sum = 0.0;
        $rows = [];
        $seq = 0;
        foreach ($query->all() as $b) {
            $amount = (float) ($b['amount'] ?? 0);
            $sum += $amount;
            $rows[] = [
                'seq' => ++$seq,
                'date' => !empty($b['place_date']) ? AppHelper::convertToThai($b['place_date']) : '-',
                'doc_no' => $b['doc_no'] ?: ($b['ref'] ?: '-'),
                'vendor' => $b['vendor_name'] ?: '-',
                'type' => $typeLabels[$b['bond_type']] ?? $b['bond_type'],
                'form' => $formLabels[$b['bond_form']] ?? $b['bond_form'],
                'amount' => $amount,
                'expiry' => !empty($b['expiry_date']) ? AppHelper::convertToThai($b['expiry_date']) : '-',
                'status' => $statusLabels[$b['status']] ?? $b['status'],
                'return_date' => !empty($b['return_date']) ? AppHelper::convertToThai($b['return_date']) : '-',
            ];
        }

        return [
            'mode' => 'log',
            'columns' => [
                ['key' => 'seq', 'label' => 'ลำดับ', 'align' => 'center', 'w' => '3rem'],
                ['key' => 'date', 'label' => 'วันที่วาง', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'doc_no', 'label' => 'เลขที่เอกสาร', 'w' => '8rem'],
                ['key' => 'vendor', 'label' => 'คู่สัญญา/ผู้ขาย'],
                ['key' => 'type', 'label' => 'ประเภท', 'align' => 'center', 'w' => '8rem'],
                ['key' => 'form', 'label' => 'รูปแบบ', 'w' => '11rem'],
                ['key' => 'amount', 'label' => 'จำนวนเงิน', 'align' => 'end', 'w' => '9rem', 'money' => true],
                ['key' => 'expiry', 'label' => 'ครบกำหนด', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'status', 'label' => 'สถานะ', 'align' => 'center', 'w' => '6rem'],
                ['key' => 'return_date', 'label' => 'วันคืน', 'align' => 'center', 'w' => '7rem'],
            ],
            'totalLabelKey' => 'form',
            'rows' => $rows,
            'totals' => ['amount' => $sum],
            'period' => ['fiscal_year' => $fy, 'month' => $month],
        ];
    }

    // ---------- ทะเบียนคุมเงินนอกงบฯ จำแนกตามโครงการ (2.4) — running per โครงการ ----------

    private static function buildFundByProject(array $filters): array
    {
        $fy = (int) ($filters['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());
        $month = !empty($filters['month']) ? (int) $filters['month'] : null;
        $projects = FinanceCashProject::activeList();
        $projectId = !empty($filters['project_id']) ? (int) $filters['project_id'] : (int) (array_key_first($projects) ?? 0);

        $opening = 0.0;
        $balance = 0.0;
        $sumIn = 0.0;
        $sumOut = 0.0;
        $rows = [];

        if ($projectId) {
            $base = FinanceCashTxn::find()->where(['fiscal_year' => $fy, 'project_id' => $projectId]);
            if ($month) {
                [$start, $end] = self::fiscalRange($fy, $month);
                $before = (clone $base)->andWhere(['<', 'doc_date', $start]);
                $inB = (float) (clone $before)->andWhere(['txn_type' => FinanceCashTxn::TYPE_IN])->sum('amount');
                $outB = (float) (clone $before)->andWhere(['txn_type' => FinanceCashTxn::TYPE_OUT])->sum('amount');
                $opening = $inB - $outB;
                $base->andWhere(['between', 'doc_date', $start, $end]);
            }
            $balance = $opening;

            $txns = $base->with('category')->orderBy(['doc_date' => SORT_ASC, 'id' => SORT_ASC])->all();
            $seq = 0;
            foreach ($txns as $t) {
                $isIn = $t->txn_type === FinanceCashTxn::TYPE_IN;
                $in = $isIn ? (float) $t->amount : 0.0;
                $out = $isIn ? 0.0 : (float) $t->amount;
                $balance += $in - $out;
                $sumIn += $in;
                $sumOut += $out;
                $desc = $t->category ? $t->category->name : '';
                if ($t->party_name) {
                    $desc .= ($desc ? ' — ' : '') . $t->party_name;
                }
                $rows[] = [
                    'seq' => ++$seq,
                    'date' => AppHelper::convertToThai($t->doc_date),
                    'doc_no' => $t->doc_no ?: '-',
                    'description' => $desc ?: '-',
                    'debit' => $in ?: null,
                    'credit' => $out ?: null,
                    'balance' => $balance,
                ];
            }
        }

        return [
            'mode' => 'running',
            'columns' => [
                ['key' => 'seq', 'label' => 'ลำดับ', 'align' => 'center', 'w' => '3rem'],
                ['key' => 'date', 'label' => 'วันที่', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'doc_no', 'label' => 'เลขที่เอกสาร', 'w' => '9rem'],
                ['key' => 'description', 'label' => 'รายการ'],
                ['key' => 'debit', 'label' => 'รับ', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'credit', 'label' => 'จ่าย', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'balance', 'label' => 'คงเหลือ', 'align' => 'end', 'w' => '9rem', 'money' => true],
            ],
            'opening' => $opening,
            'runningKey' => 'balance',
            'totalLabelKey' => 'description',
            'rows' => $rows,
            'totals' => ['debit' => $sumIn, 'credit' => $sumOut, 'balance' => $balance],
            'filterSelect' => [
                'param' => 'project_id',
                'label' => 'โครงการ',
                'allLabel' => $projects ? '— เลือกโครงการ —' : 'ยังไม่มีโครงการ',
                'options' => $projects,
                'selected' => $projectId,
            ],
            'period' => ['fiscal_year' => $fy, 'month' => $month, 'project_id' => $projectId],
        ];
    }

    // ---------- หมวด 1 เงินงบประมาณ ----------

    private static function budgetCategoryFilter(?string $selected): array
    {
        return [
            'param' => 'category',
            'label' => 'งบรายจ่าย',
            'allLabel' => 'ทุกงบรายจ่าย',
            'options' => FinanceBudgetTxn::CATEGORIES,
            'selected' => $selected,
        ];
    }

    /** 1.1 เงินประจำงวด */
    private static function buildBudgetAllotment(array $filters): array
    {
        $fy = (int) ($filters['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());
        $category = !empty($filters['category']) ? (string) $filters['category'] : null;

        $query = FinanceBudgetAllotment::find()->where(['fiscal_year' => $fy]);
        if ($category) {
            $query->andWhere(['budget_category' => $category]);
        }
        $rowsDb = $query->orderBy(['period_no' => SORT_ASC, 'budget_category' => SORT_ASC, 'id' => SORT_ASC])->all();

        $sumA = 0.0;
        $sumD = 0.0;
        $sumR = 0.0;
        $rows = [];
        $seq = 0;
        foreach ($rowsDb as $a) {
            $amt = (float) $a->amount;
            $dis = $a->getDisbursed();
            $rem = $amt - $dis;
            $sumA += $amt;
            $sumD += $dis;
            $sumR += $rem;
            $rows[] = [
                'seq' => ++$seq,
                'date' => $a->allotment_date ? AppHelper::convertToThai($a->allotment_date) : '-',
                'doc_no' => $a->allotment_no ?: '-',
                'period' => $a->period_no ?: '-',
                'category' => $a->categoryLabel(),
                'amount' => $amt,
                'disbursed' => $dis ?: null,
                'remaining' => $rem,
            ];
        }

        return [
            'mode' => 'log',
            'columns' => [
                ['key' => 'seq', 'label' => 'ลำดับ', 'align' => 'center', 'w' => '3rem'],
                ['key' => 'date', 'label' => 'วันที่จัดสรร', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'doc_no', 'label' => 'เลขที่หนังสือ', 'w' => '9rem'],
                ['key' => 'period', 'label' => 'งวดที่', 'align' => 'center', 'w' => '5rem'],
                ['key' => 'category', 'label' => 'งบรายจ่าย'],
                ['key' => 'amount', 'label' => 'ยอดจัดสรร', 'align' => 'end', 'w' => '9rem', 'money' => true],
                ['key' => 'disbursed', 'label' => 'เบิกจ่ายแล้ว', 'align' => 'end', 'w' => '9rem', 'money' => true],
                ['key' => 'remaining', 'label' => 'คงเหลือ', 'align' => 'end', 'w' => '9rem', 'money' => true],
            ],
            'totalLabelKey' => 'category',
            'rows' => $rows,
            'totals' => ['amount' => $sumA, 'disbursed' => $sumD, 'remaining' => $sumR],
            'filterSelect' => self::budgetCategoryFilter($category),
            'period' => ['fiscal_year' => $fy, 'category' => $category],
        ];
    }

    /** 1.2 รับ-จ่ายเงินงบประมาณ (running) */
    private static function buildBudgetCashbook(array $filters): array
    {
        $fy = (int) ($filters['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());
        $month = !empty($filters['month']) ? (int) $filters['month'] : null;
        $category = !empty($filters['category']) ? (string) $filters['category'] : null;

        // ยึด fiscal_year column เป็นหลัก (กรองช่วงวันที่เฉพาะเมื่อเลือกเดือน)
        $base = FinanceBudgetTxn::find()->where(['fiscal_year' => $fy]);
        if ($category) {
            $base->andWhere(['budget_category' => $category]);
        }
        $opening = 0.0;
        if ($month) {
            [$start] = self::fiscalRange($fy, $month);
            $before = (clone $base)->andWhere(['<', 'doc_date', $start]);
            $inB = (float) (clone $before)->andWhere(['txn_type' => FinanceBudgetTxn::TYPE_RECEIVE])->sum('amount');
            $outB = (float) (clone $before)->andWhere(['txn_type' => FinanceBudgetTxn::TYPE_DISBURSE])->sum('amount');
            $opening = $inB - $outB;
        }
        $balance = $opening;

        $txnQuery = clone $base;
        if ($month) {
            [$start, $end] = self::fiscalRange($fy, $month);
            $txnQuery->andWhere(['between', 'doc_date', $start, $end]);
        }
        $txns = $txnQuery->orderBy(['doc_date' => SORT_ASC, 'id' => SORT_ASC])->all();
        $sumIn = 0.0;
        $sumOut = 0.0;
        $rows = [];
        $seq = 0;
        foreach ($txns as $t) {
            $isIn = $t->txn_type === FinanceBudgetTxn::TYPE_RECEIVE;
            $in = $isIn ? (float) $t->amount : 0.0;
            $out = $isIn ? 0.0 : (float) $t->amount;
            $balance += $in - $out;
            $sumIn += $in;
            $sumOut += $out;
            $desc = $t->categoryLabel();
            if ($t->description) {
                $desc .= ' — ' . $t->description;
            }
            if ($t->payee) {
                $desc .= ' (จ่ายให้ ' . $t->payee . ')';
            }
            $rows[] = [
                'seq' => ++$seq,
                'date' => AppHelper::convertToThai($t->doc_date),
                'doc_no' => $t->doc_no ?: '-',
                'description' => $desc,
                'debit' => $in ?: null,
                'credit' => $out ?: null,
                'balance' => $balance,
            ];
        }

        return [
            'mode' => 'running',
            'columns' => [
                ['key' => 'seq', 'label' => 'ลำดับ', 'align' => 'center', 'w' => '3rem'],
                ['key' => 'date', 'label' => 'วันที่', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'doc_no', 'label' => 'เลขที่เอกสาร', 'w' => '9rem'],
                ['key' => 'description', 'label' => 'รายการ'],
                ['key' => 'debit', 'label' => 'รับจากคลัง', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'credit', 'label' => 'จ่าย', 'align' => 'end', 'w' => '8rem', 'money' => true],
                ['key' => 'balance', 'label' => 'คงเหลือ', 'align' => 'end', 'w' => '9rem', 'money' => true],
            ],
            'opening' => $opening,
            'runningKey' => 'balance',
            'totalLabelKey' => 'description',
            'rows' => $rows,
            'totals' => ['debit' => $sumIn, 'credit' => $sumOut, 'balance' => $balance],
            'filterSelect' => self::budgetCategoryFilter($category),
            'period' => ['fiscal_year' => $fy, 'month' => $month, 'category' => $category],
        ];
    }

    /** 1.3 รับและนำส่งเงิน (นส.02) */
    private static function buildTreasuryRemit(array $filters): array
    {
        $fy = (int) ($filters['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());
        $month = !empty($filters['month']) ? (int) $filters['month'] : null;

        $query = FinanceTreasuryRemit::find()->where(['fiscal_year' => $fy]);
        if ($month) {
            [$s, $e] = self::fiscalRange($fy, $month);
            $query->andWhere(['between', 'collect_date', $s, $e]);
        }
        $rowsDb = $query->orderBy(['collect_date' => SORT_ASC, 'id' => SORT_ASC])->all();

        $sum = 0.0;
        $rows = [];
        $seq = 0;
        foreach ($rowsDb as $r) {
            $sum += (float) $r->collected_amount;
            $rows[] = [
                'seq' => ++$seq,
                'date' => $r->collect_date ? AppHelper::convertToThai($r->collect_date) : '-',
                'revenue_type' => $r->revenue_type ?: '-',
                'collected' => (float) $r->collected_amount,
                'remit_date' => $r->remit_date ? AppHelper::convertToThai($r->remit_date) : '-',
                'remit_no' => $r->remit_no ?: '-',
                'status' => $r->isRemitted() ? 'นำส่งแล้ว' : 'ค้างนำส่ง',
            ];
        }

        return [
            'mode' => 'log',
            'columns' => [
                ['key' => 'seq', 'label' => 'ลำดับ', 'align' => 'center', 'w' => '3rem'],
                ['key' => 'date', 'label' => 'วันที่จัดเก็บ', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'revenue_type', 'label' => 'ประเภทรายได้แผ่นดิน'],
                ['key' => 'collected', 'label' => 'ยอดจัดเก็บ', 'align' => 'end', 'w' => '9rem', 'money' => true],
                ['key' => 'remit_date', 'label' => 'วันนำส่งคลัง', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'remit_no', 'label' => 'เลขที่ นส.02', 'w' => '8rem'],
                ['key' => 'status', 'label' => 'สถานะ', 'align' => 'center', 'w' => '7rem'],
            ],
            'totalLabelKey' => 'revenue_type',
            'rows' => $rows,
            'totals' => ['collected' => $sum],
            'period' => ['fiscal_year' => $fy, 'month' => $month],
        ];
    }

    /** 1.4 เบิกเกินส่งคืนคลัง */
    private static function buildBudgetReturn(array $filters): array
    {
        $fy = (int) ($filters['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());
        $month = !empty($filters['month']) ? (int) $filters['month'] : null;

        $query = FinanceBudgetReturn::find()->where(['fiscal_year' => $fy]);
        if ($month) {
            [$s, $e] = self::fiscalRange($fy, $month);
            $query->andWhere(['between', 'return_date', $s, $e]);
        }
        $rowsDb = $query->orderBy(['return_date' => SORT_ASC, 'id' => SORT_ASC])->all();

        $sum = 0.0;
        $rows = [];
        $seq = 0;
        foreach ($rowsDb as $r) {
            $sum += (float) $r->amount;
            $rows[] = [
                'seq' => ++$seq,
                'date' => $r->return_date ? AppHelper::convertToThai($r->return_date) : '-',
                'doc_no' => $r->return_no ?: '-',
                'category' => $r->categoryLabel(),
                'source_ref' => $r->source_ref ?: '-',
                'amount' => (float) $r->amount,
            ];
        }

        return [
            'mode' => 'log',
            'columns' => [
                ['key' => 'seq', 'label' => 'ลำดับ', 'align' => 'center', 'w' => '3rem'],
                ['key' => 'date', 'label' => 'วันที่ส่งคืน', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'doc_no', 'label' => 'เลขที่เอกสาร', 'w' => '9rem'],
                ['key' => 'category', 'label' => 'งบรายจ่าย', 'w' => '11rem'],
                ['key' => 'source_ref', 'label' => 'อ้างการเบิกเดิม'],
                ['key' => 'amount', 'label' => 'ยอดส่งคืน', 'align' => 'end', 'w' => '9rem', 'money' => true],
            ],
            'totalLabelKey' => 'source_ref',
            'rows' => $rows,
            'totals' => ['amount' => $sum],
            'period' => ['fiscal_year' => $fy, 'month' => $month],
        ];
    }

    /** 1.5 ค่าใช้จ่ายงบกลาง (สวัสดิการ) — การจ่ายในหมวด central */
    private static function buildCentralFund(array $filters): array
    {
        $fy = (int) ($filters['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());
        $month = !empty($filters['month']) ? (int) $filters['month'] : null;

        $q = FinanceBudgetTxn::find()
            ->where(['fiscal_year' => $fy, 'budget_category' => 'central', 'txn_type' => FinanceBudgetTxn::TYPE_DISBURSE]);
        if ($month) {
            [$start, $end] = self::fiscalRange($fy, $month);
            $q->andWhere(['between', 'doc_date', $start, $end]);
        }
        $rowsDb = $q->orderBy(['doc_date' => SORT_ASC, 'id' => SORT_ASC])->all();

        $sum = 0.0;
        $rows = [];
        $seq = 0;
        foreach ($rowsDb as $t) {
            $sum += (float) $t->amount;
            $rows[] = [
                'seq' => ++$seq,
                'date' => AppHelper::convertToThai($t->doc_date),
                'doc_no' => $t->doc_no ?: '-',
                'description' => $t->description ?: '-',
                'payee' => $t->payee ?: '-',
                'amount' => (float) $t->amount,
            ];
        }

        return [
            'mode' => 'log',
            'columns' => [
                ['key' => 'seq', 'label' => 'ลำดับ', 'align' => 'center', 'w' => '3rem'],
                ['key' => 'date', 'label' => 'วันที่', 'align' => 'center', 'w' => '7rem'],
                ['key' => 'doc_no', 'label' => 'เลขที่เอกสาร', 'w' => '9rem'],
                ['key' => 'description', 'label' => 'รายการสวัสดิการ'],
                ['key' => 'payee', 'label' => 'จ่ายให้', 'w' => '12rem'],
                ['key' => 'amount', 'label' => 'จำนวนเงิน', 'align' => 'end', 'w' => '9rem', 'money' => true],
            ],
            'totalLabelKey' => 'payee',
            'rows' => $rows,
            'totals' => ['amount' => $sum],
            'period' => ['fiscal_year' => $fy, 'month' => $month],
        ];
    }

    // ---------- Excel ----------

    public static function spreadsheet(string $key, array $filters = []): ?Spreadsheet
    {
        $data = self::build($key, $filters);
        if ($data === null) {
            return null;
        }
        $reg = RegisterCatalog::find($key);
        $info = SiteHelper::getInfo();
        $cols = $data['columns'];
        $lastCol = self::colLetter(count($cols));

        $book = new Spreadsheet();
        $s = $book->getActiveSheet();
        $s->setTitle('ทะเบียนคุม');

        $s->mergeCells("A1:{$lastCol}1")->setCellValue('A1', (string) ($info['company_name'] ?? 'โรงพยาบาล'));
        $s->mergeCells("A2:{$lastCol}2")->setCellValue('A2', 'ทะเบียนคุม' . ($reg['label'] ?? ''));
        $s->mergeCells("A3:{$lastCol}3")->setCellValue('A3', self::periodLabel($data['period']));
        foreach (['A1', 'A2', 'A3'] as $c) {
            $s->getStyle($c)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $headRow = 5;
        $ci = 1;
        foreach ($cols as $col) {
            $s->setCellValue(self::colLetter($ci) . $headRow, $col['label']);
            $ci++;
        }
        $s->getStyle("A{$headRow}:{$lastCol}{$headRow}")->getFont()->setBold(true);

        $r = $headRow + 1;
        // ยอดยกมา (เฉพาะแบบ running)
        if (isset($data['opening'])) {
            self::writeSummaryRow($s, $r, $cols, $data['totalLabelKey'] ?? '', 'ยอดยกมา',
                [($data['runningKey'] ?? 'balance') => (float) $data['opening']]);
            $r++;
        }
        foreach ($data['rows'] as $row) {
            $ci = 1;
            foreach ($cols as $col) {
                $val = $row[$col['key']] ?? null;
                if (!empty($col['money'])) {
                    $s->setCellValue(self::colLetter($ci) . $r, $val === null ? null : (float) $val);
                } else {
                    $s->setCellValue(self::colLetter($ci) . $r, $val);
                }
                $ci++;
            }
            $r++;
        }
        // รวม
        self::writeSummaryRow($s, $r, $cols, $data['totalLabelKey'] ?? '', 'รวม', $data['totals']);
        $s->getStyle("A{$r}:{$lastCol}{$r}")->getFont()->setBold(true);

        for ($i = 1; $i <= count($cols); $i++) {
            $s->getColumnDimension(self::colLetter($i))->setAutoSize(true);
        }
        // จัดรูปแบบตัวเลขเงินให้มีคอมม่าคั่นหลักพัน (#,##0.00) ตั้งแต่แถวยอดยกมา/ข้อมูล ถึงแถวรวม
        $ci = 1;
        foreach ($cols as $col) {
            if (!empty($col['money'])) {
                $letter = self::colLetter($ci);
                $s->getStyle("{$letter}" . ($headRow + 1) . ":{$letter}{$r}")
                    ->getNumberFormat()->setFormatCode('#,##0.00');
            }
            $ci++;
        }
        return $book;
    }

    /** เขียนแถวสรุป (ยอดยกมา/รวม): ป้ายที่ labelKey + ค่าตามคอลัมน์ใน $values */
    private static function writeSummaryRow($sheet, int $r, array $cols, string $labelKey, string $label, array $values): void
    {
        $ci = 1;
        foreach ($cols as $col) {
            $cell = self::colLetter($ci) . $r;
            if ($col['key'] === $labelKey) {
                $sheet->setCellValue($cell, $label);
            } elseif (array_key_exists($col['key'], $values)) {
                $sheet->setCellValue($cell, (float) $values[$col['key']]);
            }
            $ci++;
        }
    }

    private static function colLetter(int $index): string
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index);
    }

    public static function periodLabel(array $period): string
    {
        $months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
            'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        $base = !empty($period['month'])
            ? 'ประจำเดือน ' . ($months[$period['month']] ?? '') . ' ปีงบประมาณ ' . $period['fiscal_year']
            : 'ปีงบประมาณ ' . $period['fiscal_year'];
        return $base;
    }
}
