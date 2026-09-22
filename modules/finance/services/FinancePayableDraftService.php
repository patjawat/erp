<?php

namespace app\modules\finance\services;

use app\modules\finance\models\FinanceInbox;
use app\modules\finance\models\FinancePayable;
use app\modules\sm\models\Vendor;
use app\modules\accounting\models\AccountingChartAccount;
use app\modules\accounting\models\AccountingChartVersion;

class FinancePayableDraftService
{
    public function prepare(FinanceInbox $inbox): FinancePayable
    {
        $payload = $this->payload($inbox);
        $vendor = $this->resolveVendor($inbox);
        $billingDate = date('Y-m-d');
        $creditDays = (int) ($payload['credit_days'] ?? 0);
        $gross = round((float) $inbox->amount, 2);

        return new FinancePayable([
            'finance_inbox_id' => $inbox->id,
            'vendor_id' => $vendor ? (int) $vendor->id : null,
            'vendor_code_snapshot' => $inbox->vendor_code_snapshot,
            'vendor_name_snapshot' => $vendor ? $vendor->title : (string) $inbox->vendor_name_snapshot,
            'invoice_date' => $inbox->document_date ?: $billingDate,
            'billing_date' => $billingDate,
            'due_date_basis' => FinancePayable::DUE_BASIS_BILLING_DATE,
            'credit_days' => $creditDays,
            'due_date' => self::calculateDueDate($billingDate, $creditDays),
            'gross_amount' => $gross,
            'vat_amount' => round((float) ($payload['vat']['vat_amount'] ?? 0), 2),
            'withholding_tax_amount' => 0,
            'net_amount' => $gross,
            'source_document_no' => $inbox->source_document_no,
            'status' => FinancePayable::STATUS_DRAFT,
        ]);
    }

    /** @throws \DomainException|\RuntimeException */
    public function create(FinanceInbox $inbox, FinancePayable $model): FinancePayable
    {
        if ($inbox->status !== FinanceInbox::STATUS_ACCEPTED) {
            throw new \DomainException('สร้างร่างเจ้าหนี้ได้เฉพาะรายการที่ฝ่ายบัญชีรับรองแล้ว');
        }
        if (FinancePayable::find()->where(['finance_inbox_id' => $inbox->id])->exists()) {
            throw new \DomainException('รายการนี้สร้างร่างทะเบียนเจ้าหนี้แล้ว');
        }

        $vendor = Vendor::find()->where([
            'id' => $model->vendor_id,
            'name' => 'vendor',
            'active' => 1,
        ])->one();
        if (!$vendor) {
            throw new \DomainException('กรุณาจับคู่ผู้แทนจำหน่ายกับทะเบียนผู้ขายที่ใช้งานอยู่');
        }

        $invoiceNo = self::normalizeInvoiceNo((string) $model->invoice_no);
        if ($invoiceNo === '') {
            throw new \DomainException('กรุณาระบุเลขที่ใบแจ้งหนี้');
        }
        if (FinancePayable::find()->where(['vendor_id' => $vendor->id, 'invoice_no' => $invoiceNo])->exists()) {
            throw new \DomainException('พบเลขที่ใบแจ้งหนี้ซ้ำสำหรับผู้แทนจำหน่ายรายนี้');
        }

        $gross = round((float) $inbox->amount, 2);
        $withholding = round((float) $model->withholding_tax_amount, 2);
        if ($withholding > $gross) {
            throw new \DomainException('ภาษีหัก ณ ที่จ่ายต้องไม่มากกว่ายอดหนี้');
        }

        $model->finance_inbox_id = $inbox->id;
        $model->vendor_id = (int) $vendor->id;
        $model->vendor_code_snapshot = $vendor->code;
        $model->vendor_name_snapshot = $vendor->title;
        $model->invoice_no = $invoiceNo;
        $model->due_date_basis = FinancePayable::DUE_BASIS_BILLING_DATE;
        $model->due_date = self::calculateDueDate((string) $model->billing_date, (int) $model->credit_days);
        $model->gross_amount = $gross;
        $model->net_amount = round($gross - $withholding, 2);
        $model->source_document_no = $inbox->source_document_no;
        $model->status = FinancePayable::STATUS_DRAFT;
        $this->assignAccountingSnapshot($model);

        if (!$model->save()) {
            throw new \RuntimeException('สร้างร่างทะเบียนเจ้าหนี้ไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        }
        $model->payable_no = sprintf('AP-DRAFT-%06d', $model->id);
        if (!$model->save(false, ['payable_no', 'updated_at', 'updated_by'])) {
            throw new \RuntimeException('กำหนดเลขร่างทะเบียนเจ้าหนี้ไม่สำเร็จ');
        }
        return $model;
    }

    /** @throws \DomainException|\RuntimeException */
    public function update(FinancePayable $model): FinancePayable
    {
        if (!in_array($model->status, [FinancePayable::STATUS_DRAFT, FinancePayable::STATUS_NEEDS_REVISION], true)) {
            throw new \DomainException('แก้ไขได้เฉพาะรายการร่างหรือรายการที่ผู้ตรวจส่งกลับ');
        }
        $vendor = Vendor::find()->where(['id' => $model->vendor_id, 'name' => 'vendor', 'active' => 1])->one();
        if (!$vendor) {
            throw new \DomainException('กรุณาเลือกผู้แทนจำหน่ายจากทะเบียนที่ใช้งานอยู่');
        }
        $invoiceNo = self::normalizeInvoiceNo((string) $model->invoice_no);
        $duplicate = FinancePayable::find()->where(['vendor_id' => $vendor->id, 'invoice_no' => $invoiceNo])
            ->andWhere(['<>', 'id', $model->id])->exists();
        if ($invoiceNo === '' || $duplicate) {
            throw new \DomainException($invoiceNo === '' ? 'กรุณาระบุเลขที่ใบแจ้งหนี้' : 'พบเลขที่ใบแจ้งหนี้ซ้ำสำหรับผู้แทนจำหน่ายรายนี้');
        }
        $withholding = round((float) $model->withholding_tax_amount, 2);
        $gross = round((float) $model->gross_amount, 2);
        if ($withholding > $gross) {
            throw new \DomainException('ภาษีหัก ณ ที่จ่ายต้องไม่มากกว่ายอดหนี้');
        }
        $model->vendor_code_snapshot = $vendor->code;
        $model->vendor_name_snapshot = $vendor->title;
        $model->invoice_no = $invoiceNo;
        $model->due_date = self::calculateDueDate((string) $model->billing_date, (int) $model->credit_days);
        $model->net_amount = round($gross - $withholding, 2);
        $this->assignAccountingSnapshot($model);
        if (!$model->save()) {
            throw new \RuntimeException('บันทึกการแก้ไขร่างทะเบียนเจ้าหนี้ไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        }
        return $model;
    }

    public function resolveVendor(FinanceInbox $inbox): ?Vendor
    {
        $code = trim((string) $inbox->vendor_code_snapshot);
        if ($code === '') {
            return null;
        }
        return Vendor::find()->where(['name' => 'vendor', 'code' => $code, 'active' => 1])->one();
    }

    public static function calculateDueDate(string $billingDate, int $creditDays): string
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $billingDate);
        if (!$date || $date->format('Y-m-d') !== $billingDate) {
            throw new \DomainException('วันที่รับวางบิลไม่ถูกต้อง');
        }
        if ($creditDays < 0 || $creditDays > 3650) {
            throw new \DomainException('จำนวนวันเครดิตต้องอยู่ระหว่าง 0 ถึง 3650 วัน');
        }
        return $date->modify('+' . $creditDays . ' days')->format('Y-m-d');
    }

    public static function normalizeInvoiceNo(string $value): string
    {
        return mb_strtoupper(preg_replace('/\s+/', '', trim($value)), 'UTF-8');
    }

    public static function fiscalYearForDate(string $date): int
    {
        $value = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$value || $value->format('Y-m-d') !== $date) {
            throw new \DomainException('วันที่ใบแจ้งหนี้ไม่ถูกต้อง');
        }
        return (int) $value->format('Y') + ((int) $value->format('n') >= 10 ? 544 : 543);
    }

    public function activeHospitalChart(string $invoiceDate): ?AccountingChartVersion
    {
        return AccountingChartVersion::findOne([
            'fiscal_year' => self::fiscalYearForDate($invoiceDate),
            'scope' => AccountingChartVersion::SCOPE_HOSPITAL,
            'status' => AccountingChartVersion::STATUS_ACTIVE,
        ]);
    }

    public function eligibleAccounts(string $invoiceDate): array
    {
        $version = $this->activeHospitalChart($invoiceDate);
        if (!$version) return [];
        return AccountingChartAccount::find()
            ->where(['version_id' => $version->id, 'category' => ['1', '5'], 'is_active' => 1])
            ->orderBy('code')->all();
    }

    public function assertAccountingReady(FinancePayable $model): void
    {
        // บัญชีเดบิตเป็น "ทางเลือก" ที่ขั้นการเงิน — ถ้ายังไม่เลือก (เช่นยังไม่เปิดผังบัญชี)
        // ให้ส่งตรวจ/อนุมัติออกเลขทะเบียนคุมได้ ส่วนการผูกผังบัญชี/ลง GL เป็นงานบัญชีขั้นถัดไป
        // ถ้าเลือกบัญชีมาแล้ว ยังตรวจความถูกต้องกับผังที่เปิดใช้ตามเดิม
        $this->assignAccountingSnapshot($model);
    }

    private function assignAccountingSnapshot(FinancePayable $model): void
    {
        if (!$model->accounting_chart_account_id) {
            $model->accounting_chart_version_id = null;
            $model->account_code_snapshot = null;
            $model->account_name_snapshot = null;
            return;
        }
        $version = $this->activeHospitalChart((string) $model->invoice_date);
        $account = AccountingChartAccount::findOne((int) $model->accounting_chart_account_id);
        if (!$version) {
            throw new \DomainException('ยังไม่มีผังบัญชีโรงพยาบาลที่เปิดใช้สำหรับปีงบประมาณของใบแจ้งหนี้');
        }
        if (!$account || (int) $account->version_id !== (int) $version->id || !$account->is_active || !in_array($account->category, ['1', '5'], true)) {
            throw new \DomainException('บัญชีที่เลือกไม่อยู่ในผังโรงพยาบาลที่เปิดใช้ หรือไม่ใช่หมวดสินทรัพย์/ค่าใช้จ่าย');
        }
        $model->accounting_chart_version_id = $version->id;
        $model->account_code_snapshot = $account->code;
        $model->account_name_snapshot = $account->name;
    }

    private function payload(FinanceInbox $inbox): array
    {
        $payload = $inbox->payload_json;
        if (is_string($payload)) {
            $payload = json_decode($payload, true);
        }
        return is_array($payload) ? $payload : [];
    }
}
