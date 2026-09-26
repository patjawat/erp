<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;
use app\modules\sm\models\Vendor;
use app\modules\accounting\models\AccountingChartAccount;
use app\modules\accounting\models\AccountingChartVersion;
use app\modules\accounting\models\AccountingJournalDraft;

/**
 * Draft creditor register. No accounting entry is generated at this stage.
 *
 * @property int $id
 * @property string $ref
 * @property string|null $payable_no
 * @property int $finance_inbox_id
 * @property int $vendor_id
 * @property string|null $vendor_code_snapshot
 * @property string $vendor_name_snapshot
 * @property string $invoice_no
 * @property string $invoice_date
 * @property string $billing_date
 * @property string $due_date_basis
 * @property int $credit_days
 * @property string $due_date
 * @property string $gross_amount
 * @property string $vat_amount
 * @property string $withholding_tax_amount
 * @property string $net_amount
 * @property string|null $source_document_no
 * @property string $status
 * @property string|null $note
 * @property int|null $billing_id ใบรับวางบิล (null = ยังไม่วางบิล)
 * @property FinancePayableBilling|null $billing
 */
class FinancePayable extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_NEEDS_REVISION = 'needs_revision';
    public const STATUS_APPROVED = 'approved';
    public const DUE_BASIS_BILLING_DATE = 'billing_date';

    public static function tableName()
    {
        return '{{%finance_payable}}';
    }

    public function rules()
    {
        return [
            [[
                'finance_inbox_id', 'vendor_id', 'vendor_name_snapshot',
                'invoice_date', 'billing_date', 'due_date_basis', 'credit_days',
                'due_date', 'gross_amount', 'net_amount', 'status',
            ], 'required'],
            [['finance_inbox_id', 'vendor_id', 'accounting_chart_version_id', 'accounting_chart_account_id', 'credit_days', 'submitted_by', 'approved_by', 'created_by', 'updated_by'], 'integer'],
            [['credit_days'], 'integer', 'min' => 0, 'max' => 3650],
            [['invoice_date', 'billing_date', 'due_date'], 'date', 'format' => 'php:Y-m-d'],
            [['gross_amount', 'vat_amount', 'withholding_tax_amount', 'net_amount'], 'number', 'min' => 0],
            [['note'], 'string'],
            [['payable_no'], 'string', 'max' => 50],
            [['vendor_code_snapshot', 'invoice_no', 'source_document_no'], 'string', 'max' => 100],
            [['vendor_name_snapshot'], 'string', 'max' => 255],
            [['account_code_snapshot'], 'string', 'max' => 30],
            [['account_name_snapshot'], 'string', 'max' => 500],
            [['due_date_basis'], 'in', 'range' => [self::DUE_BASIS_BILLING_DATE]],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['vat_amount', 'withholding_tax_amount'], 'default', 'value' => 0],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $userId = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        if ($insert) {
            $this->ref = $this->ref ?: substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $this->created_at = $this->created_at ?: $now;
            $this->created_by = $this->created_by ?: $userId;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;
        return true;
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'ร่างทะเบียนเจ้าหนี้',
            self::STATUS_PENDING_APPROVAL => 'รอตรวจอนุมัติ',
            self::STATUS_NEEDS_REVISION => 'ส่งกลับแก้ไข',
            self::STATUS_APPROVED => 'อนุมัติเข้าทะเบียนแล้ว',
        ];
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING_APPROVAL => 'bg-warning-subtle text-warning-emphasis',
            self::STATUS_NEEDS_REVISION => 'bg-danger-subtle text-danger-emphasis',
            self::STATUS_APPROVED => 'bg-success-subtle text-success-emphasis',
            default => 'bg-secondary-subtle text-secondary-emphasis',
        };
    }

    public function getInbox()
    {
        return $this->hasOne(FinanceInbox::class, ['id' => 'finance_inbox_id']);
    }

    public function getVendor()
    {
        return $this->hasOne(Vendor::class, ['id' => 'vendor_id'])
            ->andOnCondition(['name' => 'vendor']);
    }

    public function getReviews()
    {
        return $this->hasMany(FinancePayableReview::class, ['finance_payable_id' => 'id'])
            ->orderBy(['created_at' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getVoucher()
    {
        return $this->hasOne(FinanceVoucher::class, ['finance_payable_id' => 'id']);
    }

    public function getAccountingChartVersion()
    {
        return $this->hasOne(AccountingChartVersion::class, ['id' => 'accounting_chart_version_id']);
    }

    public function getAccountingChartAccount()
    {
        return $this->hasOne(AccountingChartAccount::class, ['id' => 'accounting_chart_account_id']);
    }

    public function getJournalDraft()
    {
        return $this->hasOne(AccountingJournalDraft::class, ['source_id' => 'id'])
            ->andOnCondition(['source_type' => AccountingJournalDraft::SOURCE_PAYABLE]);
    }

    // ---- การตัดหนี้/จ่ายชำระ (AP settlement) ------------------------------

    public function getSettlements()
    {
        return $this->hasMany(FinancePayableSettlement::class, ['payable_id' => 'id'])
            ->orderBy(['settle_date' => SORT_ASC, 'id' => SORT_ASC]);
    }

    /** ยอดที่จ่าย/ตัดไปแล้ว */
    public function getPaidAmount(): float
    {
        return (float) FinancePayableSettlement::find()->where(['payable_id' => $this->id])->sum('amount');
    }

    /** ยอดคงค้าง = net_amount - จ่ายแล้ว (ไม่ต่ำกว่า 0) */
    public function getOutstanding(): float
    {
        return max(0.0, (float) $this->net_amount - $this->getPaidAmount());
    }

    /** สถานะการจ่าย (derive จากยอดตัด): unpaid / partial / paid */
    public function paymentStatus(): string
    {
        $paid = $this->getPaidAmount();
        if ($paid <= 0.005) {
            return 'unpaid';
        }
        return $paid + 0.005 < (float) $this->net_amount ? 'partial' : 'paid';
    }

    public static function paymentStatusLabel(string $s): string
    {
        return ['unpaid' => 'ยังไม่จ่าย', 'partial' => 'จ่ายบางส่วน', 'paid' => 'จ่ายครบ'][$s] ?? $s;
    }

    public static function paymentStatusBadgeClass(string $s): string
    {
        return match ($s) {
            'paid' => 'bg-success-subtle text-success-emphasis',
            'partial' => 'bg-warning-subtle text-warning-emphasis',
            default => 'bg-danger-subtle text-danger-emphasis',
        };
    }

    // ---- รับวางบิล ---------------------------------------------------------

    /** บริษัทมาวางบิลแล้วหรือยัง (ผูกกับใบรับวางบิลในทะเบียนรับวางบิล) */
    public function isBilled(): bool
    {
        return !empty($this->billing_id);
    }

    public function getBilling()
    {
        return $this->hasOne(FinancePayableBilling::class, ['id' => 'billing_id']);
    }

    // ---- ส่งต่อบัญชี (การเงิน → บัญชี) ------------------------------------

    /** การเงินส่งให้บัญชีแล้วหรือยัง */
    public function isSentAccounting(): bool
    {
        return !empty($this->sent_accounting_at);
    }

    /** บัญชีลงบันทึก (มีสมุดรายวันร่าง) แล้วหรือยัง */
    public function isJournalized(): bool
    {
        return $this->journalDraft !== null;
    }
}
