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
 * รอบจ่ายเจ้าหนี้: บันทึกขอจ่าย (รออนุมัติ) → ผู้อนุมัติอีกคนกดอนุมัติ →
 * ตัดหนี้ (settlement) + ใบสำคัญจ่ายเงินบำรุง (mophcash) + เช็ค ในทรานแซกชันเดียว
 *
 * ใบสำคัญจ่าย: 1 บรรทัดต่อบิล = ยอดก่อนหักภาษี (ยอดตัด + WHT ส่วนของบิล) ลงหมวดรายจ่ายที่เลือก
 * WHT แยกไว้ที่หัวใบ, net_amount = เงินที่ออกจริง = ผลรวมยอดตัดหนี้
 * จ่ายบางส่วน → เฉลี่ย WHT ตามสัดส่วนยอดตัด/ยอดสุทธิของบิล
 */
class FinancePayablePaymentService
{
    /** กุญแจใน vendor.data_json สำหรับจำหมวดรายจ่ายที่ใช้ล่าสุดของผู้ขาย */
    public const VENDOR_CATEGORY_KEY = 'cash_out_category_id';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_REJECTED = 'rejected';

    /**
     * ขอจ่าย (บันทึกรอบจ่าย "รออนุมัติ") — ยังไม่ตัดหนี้ ไม่ออกใบสำคัญ/เช็ค
     * @param array $lines [payable_id => ['amount' => float, 'category_id' => int]]
     * @param array $head  pay_date(Y-m-d), cash_account_id, pay_method, cheque_no, bank_name, bank_branch,
     *                     doc_no, subject, note, template_id, cheque_book_no, is_ac_payee, vendor
     * @throws \DomainException
     */
    public function request(array $lines, array $head): FinancePayablePayment
    {
        $selected = $this->selectLines($lines, false);
        $locked = self::pendingPayableIds();
        foreach ($selected as $s) {
            if (isset($locked[$s['p']->id])) {
                throw new \DomainException('บิล ' . ($s['p']->invoice_no ?: $s['p']->payable_no) . ' อยู่ในรอบจ่ายที่รออนุมัติแล้ว');
            }
        }
        $account = $this->account($head);
        $first = $selected[0]['p'];
        $net = array_sum(array_column($selected, 'amt'));
        $wht = array_sum(array_column($selected, 'wht'));

        $pay = new FinancePayablePayment([
            'vendor_name_snapshot' => trim((string) ($head['vendor'] ?? '')) ?: $first->vendor_name_snapshot,
            'vendor_id' => (int) $first->vendor_id,
            'cash_account_id' => $account->id,
            'pay_date' => (string) $head['pay_date'],
            'pay_method' => (string) ($head['pay_method'] ?? 'cheque'),
            'bank_name' => $account->bank_name ?: (trim((string) ($head['bank_name'] ?? '')) ?: null),
            'bank_branch' => $account->branch ?: (trim((string) ($head['bank_branch'] ?? '')) ?: null),
            'cheque_no' => trim((string) ($head['cheque_no'] ?? '')) ?: null,
            'doc_no' => trim((string) ($head['doc_no'] ?? '')) ?: null,
            'subject' => trim((string) ($head['subject'] ?? '')) ?: null,
            'gross_total' => round($net + $wht, 2),
            'wht_total' => round($wht, 2),
            'net_total' => round($net, 2),
            'note' => trim((string) ($head['note'] ?? '')) ?: null,
            'status' => self::STATUS_PENDING,
            'request_json' => json_encode([
                'lines' => array_map(static fn($s) => ['payable_id' => $s['p']->id, 'amount' => $s['amt'], 'category_id' => $s['category_id']], $selected),
                'template_id' => (int) ($head['template_id'] ?? 0) ?: null,
                'cheque_book_no' => trim((string) ($head['cheque_book_no'] ?? '')) ?: null,
                'is_ac_payee' => !empty($head['is_ac_payee']),
            ], JSON_UNESCAPED_UNICODE),
        ]);
        $pay->save(false);
        $this->rememberVendorCategory($first, (int) $selected[0]['category_id']);
        return $pay;
    }

    /**
     * อนุมัติรอบจ่าย → ตัดหนี้ + ใบสำคัญจ่ายเงินบำรุง + เช็ค (ผู้อนุมัติต้องไม่ใช่ผู้บันทึก)
     * @throws \DomainException
     */
    public function approve(FinancePayablePayment $pay): void
    {
        if ($pay->status !== self::STATUS_PENDING) {
            throw new \DomainException('รอบจ่ายนี้ไม่อยู่ในสถานะรออนุมัติ');
        }
        $userId = Yii::$app->has('user') && !Yii::$app->user->isGuest ? (int) Yii::$app->user->id : null;
        if ($userId !== null && (int) $pay->created_by === $userId) {
            throw new \DomainException('ผู้บันทึกรอบจ่ายอนุมัติรายการของตนเองไม่ได้ — ให้ผู้อนุมัติอีกคนเป็นผู้กด');
        }
        $req = json_decode((string) $pay->request_json, true) ?: [];
        $lines = [];
        foreach ((array) ($req['lines'] ?? []) as $ln) {
            $lines[(int) $ln['payable_id']] = ['amount' => $ln['amount'], 'category_id' => $ln['category_id']];
        }
        $selected = $this->selectLines($lines, true);

        $voucher = $this->createVoucher($pay, $selected);
        foreach ($selected as $s) {
            (new FinancePayableSettlement([
                'payable_id' => $s['p']->id,
                'payment_id' => $pay->id,
                'cash_voucher_id' => $voucher->id,
                'amount' => $s['amt'],
                'settle_date' => $pay->pay_date,
                'note' => $pay->cheque_no ? ('เช็ค ' . $pay->cheque_no) : null,
            ]))->save(false);
        }
        // จ่ายด้วยเช็ค → บันทึกเช็คเข้าทะเบียนคุมเช็คอัตโนมัติ
        if ($pay->pay_method === 'cheque' && $pay->cheque_no) {
            $cheque = FinanceCheque::fromPayment($pay);
            $cheque->template_id = $req['template_id'] ?? null;
            $cheque->cheque_book_no = $req['cheque_book_no'] ?? null;
            // ติ๊ก A/C PAYEE → รูปแบบ ac_payee (ขีดฆ่า+คร่อม+ข้อความ) ไม่ติ๊ก → ระบุชื่อ (ขีดฆ่าอย่างเดียว)
            $cheque->form_type = !empty($req['is_ac_payee']) ? FinanceCheque::FORM_AC_PAYEE : FinanceCheque::FORM_NAMED;
            $cheque->status = FinanceCheque::STATUS_DRAFT;
            $cheque->save(false);
        }
        $pay->status = self::STATUS_PAID;
        $pay->approved_at = time();
        $pay->approved_by = $userId;
        $pay->save(false, ['status', 'approved_at', 'approved_by']);
    }

    /** ไม่อนุมัติรอบจ่าย — บิลกลับไปจ่ายรอบใหม่ได้ */
    public function reject(FinancePayablePayment $pay, string $reason): void
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new \DomainException('กรุณาระบุเหตุผลที่ไม่อนุมัติ');
        }
        if ($pay->status !== self::STATUS_PENDING) {
            throw new \DomainException('รอบจ่ายนี้ไม่อยู่ในสถานะรออนุมัติ');
        }
        $pay->status = self::STATUS_REJECTED;
        $pay->rejected_at = time();
        $pay->rejected_by = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        $pay->reject_reason = mb_substr($reason, 0, 255);
        $pay->save(false, ['status', 'rejected_at', 'rejected_by', 'reject_reason']);
    }

    /** บิลที่อยู่ในรอบจ่ายรออนุมัติ: [payable_id => true] (ห้ามเลือกซ้ำ) */
    public static function pendingPayableIds(): array
    {
        $ids = [];
        foreach (FinancePayablePayment::find()->select('request_json')->where(['status' => self::STATUS_PENDING])->column() as $json) {
            foreach ((array) ((json_decode((string) $json, true) ?: [])['lines'] ?? []) as $ln) {
                $ids[(int) $ln['payable_id']] = true;
            }
        }
        return $ids;
    }

    /**
     * ตรวจบิล/ยอด/หมวด → [['p' => FinancePayable, 'amt', 'wht', 'category_id']]
     * $strict = ตอนอนุมัติ: ยอดขอจ่ายต้องไม่เกินคงค้างปัจจุบัน (ไม่ตัดยอดให้เงียบ ๆ)
     * @throws \DomainException
     */
    private function selectLines(array $lines, bool $strict): array
    {
        $selected = [];
        foreach ($lines as $pid => $ln) {
            $amt = round((float) ($ln['amount'] ?? 0), 2);
            if ($amt <= 0) {
                continue;
            }
            $p = FinancePayable::findOne(['id' => (int) $pid, 'status' => FinancePayable::STATUS_APPROVED]);
            if (!$p) {
                continue;
            }
            $out = round($p->getOutstanding(), 2);
            if ($amt > $out + 0.005) {
                if ($strict) {
                    throw new \DomainException('ยอดคงค้างของบิล ' . ($p->invoice_no ?: $p->payable_no) . ' เปลี่ยนไปแล้ว (คงค้าง ' . number_format($out, 2) . ') — ไม่อนุมัติรอบนี้แล้วบันทึกใหม่');
                }
                $amt = $out;
            }
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
        return $selected;
    }

    private function account(array $head): FinanceCashAccount
    {
        $accountId = (int) ($head['cash_account_id'] ?? 0);
        $account = $accountId ? FinanceCashAccount::findOne($accountId) : null;
        if (!$account) {
            throw new \DomainException('กรุณาเลือกบัญชีจ่าย (ใช้ลงใบสำคัญจ่ายและตัดยอดเงินคงเหลือ)');
        }
        return $account;
    }

    /**
     * ยกเลิกรอบจ่าย: คืนยอดคงค้าง (ลบการตัดหนี้) + ลบใบสำคัญจ่าย + ยกเลิกเช็ค (คงเลขไว้ในทะเบียน)
     * แถวรอบจ่ายเก็บไว้เป็นหลักฐาน พร้อม snapshot บิลที่เคยจ่าย
     * @throws \DomainException
     */
    public function cancel(FinancePayablePayment $pay, string $reason): void
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new \DomainException('กรุณาระบุเหตุผลที่ยกเลิกรอบจ่าย');
        }
        if ($pay->isCancelled()) {
            throw new \DomainException('รอบจ่ายนี้ถูกยกเลิกไปแล้ว');
        }
        if ($pay->status !== self::STATUS_PAID) {
            throw new \DomainException('ยกเลิกได้เฉพาะรอบจ่ายที่อนุมัติแล้ว — รอบที่รออนุมัติให้กด "ไม่อนุมัติ"');
        }
        $voucher = $pay->getVoucher();
        if ($voucher && $voucher->is_closed) {
            throw new \DomainException('ใบสำคัญจ่ายของรอบนี้อยู่ในงวดที่ปิดบัญชีประจำวันแล้ว ยกเลิกไม่ได้');
        }
        $cheque = $pay->getCheque();
        if ($cheque && $cheque->status === FinanceCheque::STATUS_CLEARED) {
            throw new \DomainException('เช็คของรอบนี้ขึ้นเงินแล้ว ยกเลิกไม่ได้');
        }

        $snapshot = [];
        foreach ($pay->paidLines() as $ln) {
            $snapshot[] = [
                'payable_id' => $ln['payable']->id,
                'payable_no' => $ln['payable']->payable_no,
                'invoice_no' => $ln['payable']->invoice_no,
                'amount' => $ln['amount'],
            ];
        }
        FinancePayableSettlement::deleteAll(['payment_id' => $pay->id]);
        if ($voucher) {
            $voucher->delete(); // FK CASCADE ลบบรรทัดใน finance_cash_txn
        }
        if ($cheque && $cheque->status !== FinanceCheque::STATUS_VOID) {
            $cheque->void('ยกเลิกรอบจ่ายเจ้าหนี้: ' . $reason);
        }

        $pay->cancelled_at = time();
        $pay->cancelled_by = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        $pay->cancel_reason = mb_substr($reason, 0, 255);
        $pay->cancel_snapshot = json_encode($snapshot, JSON_UNESCAPED_UNICODE);
        $pay->save(false, ['cancelled_at', 'cancelled_by', 'cancel_reason', 'cancel_snapshot']);
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
