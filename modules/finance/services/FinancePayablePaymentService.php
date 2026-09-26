<?php

namespace app\modules\finance\services;

use Yii;
use app\modules\finance\models\FinanceCashAccount;
use app\modules\finance\models\FinanceCashCategory;
use app\modules\finance\models\FinanceCashTxn;
use app\modules\finance\models\FinanceCashVoucher;
use app\modules\finance\models\FinanceCheque;
use app\modules\finance\models\FinancePayable;
use app\modules\finance\models\FinancePayablePayment;
use app\modules\finance\models\FinancePayableSettlement;
use app\modules\sm\models\Vendor;

/**
 * รอบจ่ายเจ้าหนี้ → ตัดหนี้ (settlement) + ใบสำคัญจ่ายเงินบำรุง (mophcash) + เช็ค ในทรานแซกชันเดียว
 *
 * ใบสำคัญจ่าย: 1 บรรทัดต่อบิล = ยอดก่อนหักภาษี (ยอดตัด + WHT ส่วนของบิล) ลงหมวดรายจ่ายที่เลือก
 * WHT แยกไว้ที่หัวใบ, net_amount = เงินที่ออกจริง = ผลรวมยอดตัดหนี้
 * จ่ายบางส่วน → เฉลี่ย WHT ตามสัดส่วนยอดตัด/ยอดสุทธิของบิล
 */
class FinancePayablePaymentService
{
    /** กุญแจใน vendor.data_json สำหรับจำหมวดรายจ่ายที่ใช้ล่าสุดของผู้ขาย */
    public const VENDOR_CATEGORY_KEY = 'cash_out_category_id';

    /**
     * @param array $lines [payable_id => ['amount' => float, 'category_id' => int]]
     * @param array $head  pay_date(Y-m-d), cash_account_id, pay_method, cheque_no, bank_name, bank_branch,
     *                     doc_no, subject, note, template_id, cheque_book_no, is_ac_payee, vendor
     * @throws \DomainException
     */
    public function pay(array $lines, array $head): FinancePayablePayment
    {
        $selected = [];
        foreach ($lines as $pid => $ln) {
            $amt = round((float) ($ln['amount'] ?? 0), 2);
            if ($amt <= 0) {
                continue;
            }
            $p = FinancePayable::findOne(['id' => (int) $pid, 'status' => FinancePayable::STATUS_APPROVED]);
            if (!$p || !$p->isBilled()) {
                continue;
            }
            $amt = min($amt, round($p->getOutstanding(), 2));
            if ($amt <= 0) {
                continue;
            }
            $categoryId = (int) ($ln['category_id'] ?? 0);
            if (!isset(self::leafCategoryIds()[$categoryId])) {
                throw new \DomainException('กรุณาเลือกหมวดรายจ่ายของบิล ' . ($p->invoice_no ?: $p->payable_no));
            }
            // WHT ส่วนของยอดที่จ่ายครั้งนี้ (จ่ายครบ = WHT เต็มบิล)
            $net = (float) $p->net_amount;
            $whtShare = $net > 0 ? round((float) $p->withholding_tax_amount * $amt / $net, 2) : 0.0;
            $selected[] = ['p' => $p, 'amt' => $amt, 'wht' => $whtShare, 'category_id' => $categoryId];
        }
        if (!$selected) {
            throw new \DomainException('ยังไม่ได้เลือกบิลที่จะจ่าย');
        }
        $accountId = (int) ($head['cash_account_id'] ?? 0);
        $account = $accountId ? FinanceCashAccount::findOne($accountId) : null;
        if (!$account) {
            throw new \DomainException('กรุณาเลือกบัญชีจ่าย (ใช้ลงใบสำคัญจ่ายและตัดยอดเงินคงเหลือ)');
        }
        $payDate = (string) $head['pay_date'];
        $payMethod = (string) ($head['pay_method'] ?? 'cheque');
        $chequeNo = trim((string) ($head['cheque_no'] ?? '')) ?: null;
        $first = $selected[0]['p'];
        $vendorName = trim((string) ($head['vendor'] ?? '')) ?: $first->vendor_name_snapshot;

        $net = array_sum(array_column($selected, 'amt'));
        $wht = array_sum(array_column($selected, 'wht'));
        $gross = $net + $wht;

        $pay = new FinancePayablePayment([
            'vendor_name_snapshot' => $vendorName,
            'vendor_id' => (int) $first->vendor_id,
            'cash_account_id' => $account->id,
            'pay_date' => $payDate,
            'pay_method' => $payMethod,
            'bank_name' => $account->bank_name ?: (trim((string) ($head['bank_name'] ?? '')) ?: null),
            'bank_branch' => $account->branch ?: (trim((string) ($head['bank_branch'] ?? '')) ?: null),
            'cheque_no' => $chequeNo,
            'doc_no' => trim((string) ($head['doc_no'] ?? '')) ?: null,
            'subject' => trim((string) ($head['subject'] ?? '')) ?: null,
            'gross_total' => round($gross, 2),
            'wht_total' => round($wht, 2),
            'net_total' => round($net, 2),
            'note' => trim((string) ($head['note'] ?? '')) ?: null,
        ]);
        $pay->save(false);

        $voucher = $this->createVoucher($pay, $selected);

        foreach ($selected as $s) {
            (new FinancePayableSettlement([
                'payable_id' => $s['p']->id,
                'payment_id' => $pay->id,
                'cash_voucher_id' => $voucher->id,
                'amount' => $s['amt'],
                'settle_date' => $payDate,
                'note' => $chequeNo ? ('เช็ค ' . $chequeNo) : null,
            ]))->save(false);
        }

        // จ่ายด้วยเช็ค → บันทึกเช็คเข้าทะเบียนคุมเช็คอัตโนมัติ
        if ($payMethod === 'cheque' && $chequeNo) {
            $cheque = FinanceCheque::fromPayment($pay);
            $cheque->template_id = (int) ($head['template_id'] ?? 0) ?: null;
            $cheque->cheque_book_no = trim((string) ($head['cheque_book_no'] ?? '')) ?: null;
            $cheque->is_ac_payee = !empty($head['is_ac_payee']) ? 1 : 0;
            $cheque->status = FinanceCheque::STATUS_DRAFT;
            $cheque->save(false);
        }

        $this->rememberVendorCategory($first, (int) $selected[0]['category_id']);
        return $pay;
    }

    /** ใบสำคัญจ่าย (header) + บรรทัด OUT ต่อบิล */
    private function createVoucher(FinancePayablePayment $pay, array $selected): FinanceCashVoucher
    {
        $subtotal = round((float) $pay->gross_total, 2);
        $v = new FinanceCashVoucher([
            'fiscal_year' => self::fiscalYearOf($pay->pay_date),
            'pay_date' => $pay->pay_date,
            'doc_no' => $pay->doc_no,
            'pay_method' => isset(FinanceCashVoucher::PAY_METHODS[$pay->pay_method]) ? $pay->pay_method : 'cheque',
            'cheque_no' => $pay->cheque_no,
            'account_id' => $pay->cash_account_id,
            'payee_id' => $pay->vendor_id ?: null,
            'payee_name' => $pay->vendor_name_snapshot,
            'subtotal' => $subtotal,
            'vat_amount' => 0,
            'total_amount' => $subtotal,
            'wht_type' => null,
            'wht_amount' => round((float) $pay->wht_total, 2),
            'net_amount' => round((float) $pay->net_total, 2),
            'note' => 'จ่ายเจ้าหนี้ (รอบจ่าย #' . $pay->id . ')' . ($pay->subject ? ' ' . $pay->subject : ''),
        ]);
        if (!$v->save()) {
            throw new \RuntimeException('สร้างใบสำคัญจ่ายไม่สำเร็จ: ' . implode(' ', $v->getFirstErrors()));
        }
        foreach ($selected as $s) {
            /** @var FinancePayable $p */
            $p = $s['p'];
            (new FinanceCashTxn([
                'txn_type' => FinanceCashCategory::TYPE_OUT,
                'fiscal_year' => $v->fiscal_year,
                'category_id' => $s['category_id'],
                'voucher_id' => $v->id,
                'money_account_id' => $v->account_id,
                'doc_date' => $v->pay_date,
                'doc_no' => $v->cheque_no ?: $v->doc_no,
                'pay_method' => $v->pay_method,
                'amount' => round($s['amt'] + $s['wht'], 2),
                'party_name' => $v->payee_name,
                'note' => trim('เจ้าหนี้ ' . $p->payable_no . ' ใบแจ้งหนี้ ' . $p->invoice_no),
                'is_closed' => 0,
            ]))->save(false);
        }
        return $v;
    }

    /** ปีงบประมาณ (พ.ศ.) ของวันที่ — ต.ค. ขึ้นปีงบใหม่ */
    public static function fiscalYearOf(string $date): int
    {
        $ts = strtotime($date) ?: time();
        $year = (int) date('Y', $ts) + 543;
        return (int) date('n', $ts) >= 10 ? $year + 1 : $year;
    }

    /**
     * หมวดรายจ่ายที่เลือกลงบรรทัดได้ (ปลายกิ่ง: ไม่ใช่ group และไม่มีลูก) จัดกลุ่มตามหมวดใหญ่
     * @return array [ชื่อกลุ่ม => [id => ชื่อหมวด]]
     */
    public static function categoryOptions(): array
    {
        $all = FinanceCashCategory::find()->where(['txn_type' => FinanceCashCategory::TYPE_OUT, 'is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
        $byId = [];
        $hasChild = [];
        foreach ($all as $c) {
            $byId[$c->id] = $c;
            if ($c->parent_id) {
                $hasChild[$c->parent_id] = true;
            }
        }
        $out = [];
        foreach ($all as $c) {
            if ($c->level === FinanceCashCategory::LEVEL_GROUP || isset($hasChild[$c->id])) {
                continue;
            }
            // หาชื่อกลุ่มบนสุด + ชื่อหมวดกลาง (ถ้าเป็นระดับ account)
            $label = $c->name;
            $node = $c;
            $guard = 0;
            while ($node->parent_id && isset($byId[$node->parent_id]) && $guard++ < 5) {
                $node = $byId[$node->parent_id];
                if ($node->level !== FinanceCashCategory::LEVEL_GROUP) {
                    $label = $node->name . ' › ' . $label;
                }
            }
            $out[$node->name][$c->id] = $label;
        }
        return $out;
    }

    /** @return array<int,true> */
    public static function leafCategoryIds(): array
    {
        static $ids = null;
        if ($ids === null) {
            $ids = [];
            foreach (self::categoryOptions() as $items) {
                foreach (array_keys($items) as $id) {
                    $ids[$id] = true;
                }
            }
        }
        return $ids;
    }

    /** หมวดรายจ่ายที่ผู้ขายรายนี้ใช้ครั้งล่าสุด (null = ยังไม่เคย) */
    public static function vendorCategoryId(?int $vendorId): ?int
    {
        $vendor = $vendorId ? Vendor::find()->where(['id' => $vendorId, 'name' => 'vendor'])->one() : null;
        $data = $vendor && is_array($vendor->data_json) ? $vendor->data_json : [];
        $id = (int) ($data[self::VENDOR_CATEGORY_KEY] ?? 0);
        return $id ?: null;
    }

    private function rememberVendorCategory(FinancePayable $p, int $categoryId): void
    {
        $vendor = $p->vendor_id ? Vendor::find()->where(['id' => $p->vendor_id, 'name' => 'vendor'])->one() : null;
        if (!$vendor || $categoryId <= 0) {
            return;
        }
        $data = is_array($vendor->data_json) ? $vendor->data_json : [];
        if ((int) ($data[self::VENDOR_CATEGORY_KEY] ?? 0) === $categoryId) {
            return;
        }
        $data[self::VENDOR_CATEGORY_KEY] = $categoryId;
        $vendor->data_json = $data;
        $vendor->save(false, ['data_json']);
    }
}
