<?php

namespace app\modules\finance\models;

use app\modules\hr\models\Employees;
use Yii;
use yii\db\ActiveRecord;

/**
 * หัวสัญญาเงินยืม — หนึ่งแถวต่อหนึ่งใบยืม
 *
 * ยอดเงินทั้งสี่ช่อง (approved / voucher / cash_return / outstanding) เป็นยอดสรุปที่
 * คำนวณจากตารางลูกแล้วเขียนกลับ ไม่ใช่ช่องที่ผู้ใช้กรอกเอง ทำแบบนี้เพื่อให้หน้าทะเบียน
 * และตัวไล่ลูกหนี้ค้างค้นได้เร็วโดยไม่ต้อง join ทุกครั้ง โดยแหล่งความจริงยังเป็นตารางลูก
 * เรียก recalcTotals() ทุกครั้งที่บรรทัดประมาณการหรือรายการส่งใช้เปลี่ยน
 *
 * @property FinanceLoanExpenseType|null $expenseType
 * @property FinanceLoanAccount|null $account
 * @property Employees|null $borrower
 * @property FinanceLoanItem[] $items
 * @property FinanceLoanSettlement[] $settlements
 * @property FinanceLoanFollowup[] $followups
 */
class FinanceLoan extends ActiveRecord
{
    use LoanAuditTrait;

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CLEARED = 'cleared';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    /** สถานะที่ถือว่าจบแล้ว ไม่ต้องไล่ทวง */
    public const CLOSED_STATUSES = [self::STATUS_CLEARED, self::STATUS_COMPLETED, self::STATUS_CANCELLED];

    public static function tableName()
    {
        return '{{%finance_loan}}';
    }

    public function rules()
    {
        return [
            [['contract_no', 'fiscal_year', 'status', 'borrowed_at', 'borrower_name', 'purpose'], 'required'],
            [['fiscal_year', 'contract_seq', 'expense_type_id', 'account_id', 'borrower_emp_id',
                'due_days', 'followup_count', 'import_row', 'created_by', 'updated_by'], 'integer'],
            [['borrowed_at', 'received_at', 'activity_start_at', 'activity_end_at', 'due_at',
                'first_settled_at', 'last_settled_at', 'evidence_sent_at', 'request_document_date',
                'last_followup_at'], 'date', 'format' => 'php:Y-m-d'],
            [['purpose', 'note'], 'string'],
            [['approved_amount', 'voucher_amount', 'cash_return_amount', 'outstanding_amount'], 'number', 'min' => 0],
            [['approved_amount', 'voucher_amount', 'cash_return_amount', 'outstanding_amount', 'followup_count'], 'default', 'value' => 0],
            [['due_is_manual'], 'boolean'],
            [['due_is_manual'], 'default', 'value' => false],
            [['contract_no'], 'string', 'max' => 50],
            [['contract_no'], 'unique'],
            [['request_document_no', 'disbursement_document_no'], 'string', 'max' => 100],
            [['borrower_name', 'borrower_position'], 'string', 'max' => 255],
            [['source_ref_id', 'import_batch'], 'string', 'max' => 64],
            [['source_event_key'], 'string', 'max' => 128],
            [['status', 'due_basis', 'source_ref_type'], 'string', 'max' => 30],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['source_ref_type'], 'in', 'range' => array_keys(self::sourceOptions())],
            [['source_ref_type'], 'default', 'value' => 'manual'],
            [['fiscal_year'], 'integer', 'min' => 2500, 'max' => 2700],
            [['expense_type_id'], 'exist', 'targetClass' => FinanceLoanExpenseType::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['account_id'], 'exist', 'targetClass' => FinanceLoanAccount::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['received_at', 'activity_start_at', 'activity_end_at', 'due_at'], 'validateNotBeforeBorrow'],
            [['activity_end_at'], 'validateActivityRange'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'contract_no' => 'เลขที่สัญญา',
            'fiscal_year' => 'ปีงบประมาณ',
            'status' => 'สถานะ',
            'expense_type_id' => 'ประเภทค่าใช้จ่าย',
            'account_id' => 'ยืมจากบัญชี',
            'borrower_emp_id' => 'ผู้ยืม',
            'borrower_name' => 'ชื่อ-สกุล ผู้ยืม',
            'borrower_position' => 'ตำแหน่ง ผู้ยืม',
            'purpose' => 'วัตถุประสงค์ในการยืม',
            'request_document_no' => 'เลขที่บันทึกขออนุมัติ',
            'request_document_date' => 'ลงวันที่',
            'borrowed_at' => 'วันที่ยืม',
            'received_at' => 'วันที่รับเงิน',
            'activity_start_at' => 'วันที่เริ่มดำเนินการ',
            'activity_end_at' => 'วันที่ดำเนินการเสร็จ / วันกลับ',
            'due_at' => 'กำหนดการคืน',
            'due_is_manual' => 'กำหนดวันคืนเอง',
            'approved_amount' => 'จำนวนเงินยืม',
            'voucher_amount' => 'ใบสำคัญ',
            'cash_return_amount' => 'เงินสดคืน',
            'outstanding_amount' => 'คงเหลือ',
            'evidence_sent_at' => 'วันที่ส่งเงินและหลักฐาน',
            'disbursement_document_no' => 'เลขที่ บร./บค.',
            'note' => 'หมายเหตุ',
        ];
    }

    // ── ความสัมพันธ์ ───────────────────────────────────────────────

    public function getExpenseType()
    {
        return $this->hasOne(FinanceLoanExpenseType::class, ['id' => 'expense_type_id']);
    }

    public function getAccount()
    {
        return $this->hasOne(FinanceLoanAccount::class, ['id' => 'account_id']);
    }

    public function getBorrower()
    {
        return $this->hasOne(Employees::class, ['id' => 'borrower_emp_id']);
    }

    public function getItems()
    {
        return $this->hasMany(FinanceLoanItem::class, ['loan_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getSettlements()
    {
        return $this->hasMany(FinanceLoanSettlement::class, ['loan_id' => 'id'])
            ->orderBy(['seq' => SORT_ASC]);
    }

    public function getFollowups()
    {
        return $this->hasMany(FinanceLoanFollowup::class, ['loan_id' => 'id'])
            ->orderBy(['notified_at' => SORT_DESC, 'id' => SORT_DESC]);
    }

    // ── การตรวจสอบ ────────────────────────────────────────────────

    public function validateNotBeforeBorrow($attribute): void
    {
        if ($this->$attribute && $this->borrowed_at && $this->$attribute < $this->borrowed_at) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . 'ต้องไม่ก่อนวันที่ยืม');
        }
    }

    public function validateActivityRange(): void
    {
        if ($this->activity_start_at && $this->activity_end_at && $this->activity_end_at < $this->activity_start_at) {
            $this->addError('activity_end_at', 'วันที่ดำเนินการเสร็จต้องไม่ก่อนวันที่เริ่มดำเนินการ');
        }
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        $this->contract_seq = self::parseSeq((string) $this->contract_no);
        $this->applyDueRule();
        return true;
    }

    // ── เลขที่สัญญา ───────────────────────────────────────────────

    /**
     * เลขที่สัญญาใบถัดไปของปีงบประมาณ
     *
     * ดูจากเลขสูงสุดที่มีอยู่จริง ไม่ใช้ตัวนับแยก จึงเริ่มใช้กลางปีได้ทันที —
     * กรอก BOR69-0058 เป็นใบแรก ใบต่อไปได้ BOR69-0059 เอง โดยไม่ต้องตั้งค่าที่ไหน
     * และไม่มีทางที่ตัวนับกับข้อมูลจริงจะเพี้ยนจากกัน
     */
    public static function nextContractNo(int $fiscalYear): string
    {
        $latest = self::find()
            ->where(['fiscal_year' => $fiscalYear])
            ->andWhere(['not', ['contract_seq' => null]])
            ->orderBy(['contract_seq' => SORT_DESC])
            ->one();
        $prefix = $latest ? self::parsePrefix($latest->contract_no) : self::defaultPrefix($fiscalYear);
        $next = $latest ? ((int) $latest->contract_seq + 1) : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public static function defaultPrefix(int $fiscalYear): string
    {
        return 'BOR' . substr((string) $fiscalYear, -2) . '-';
    }

    /** ตัวเลขท้ายสุดของเลขที่สัญญา ใช้เรียงลำดับและหาเลขถัดไป */
    public static function parseSeq(string $contractNo): ?int
    {
        return preg_match('/(\d+)\s*$/', trim($contractNo), $m) ? (int) $m[1] : null;
    }

    /** ทุกอย่างที่อยู่หน้าตัวเลขท้ายสุด เช่น "BOR69-" */
    public static function parsePrefix(string $contractNo): string
    {
        $trimmed = trim($contractNo);
        return preg_match('/^(.*?)(\d+)\s*$/', $trimmed, $m) ? $m[1] : $trimmed;
    }

    // ── วันครบกำหนด ───────────────────────────────────────────────

    /**
     * คำนวณวันครบกำหนดจากกติกาของประเภทค่าใช้จ่าย
     *
     * กติกาถูกคัดลอกมาเก็บไว้กับตัวสัญญา (due_days / due_basis) เพราะถ้าวันหลัง
     * มีคนแก้ค่าตั้งค่าจาก 15 เป็น 20 วัน สัญญาเก่าที่ออกหนังสือทวงไปแล้วต้องไม่ถูก
     * คำนวณใหม่จนวันครบกำหนดขยับเอง ซึ่งจะทำให้กระดาษกับหน้าจอไม่ตรงกัน
     */
    public function applyDueRule(): void
    {
        $type = $this->expenseType;
        if ($type) {
            $this->due_days = $this->due_days ?: (int) $type->due_days;
            $this->due_basis = $this->due_basis ?: (string) $type->due_basis;
        }
        if ($this->due_is_manual || !$this->due_days || !$this->due_basis) {
            return;
        }
        $anchor = $this->dueAnchorDate();
        $this->due_at = $anchor ? date('Y-m-d', strtotime($anchor . ' +' . (int) $this->due_days . ' day')) : null;
    }

    /** วันที่ใช้เป็นจุดตั้งต้นนับ ตามกติกาของประเภทค่าใช้จ่าย */
    public function dueAnchorDate(): ?string
    {
        $date = match ($this->due_basis) {
            FinanceLoanExpenseType::BASIS_RECEIVED => $this->received_at,
            FinanceLoanExpenseType::BASIS_BORROWED => $this->borrowed_at,
            default => $this->activity_end_at,
        };
        return $date ?: null;
    }

    /** อธิบายกติกาให้ผู้ใช้เห็นข้างช่องกำหนดการคืน */
    public function dueRuleText(): string
    {
        if (!$this->due_days || !$this->due_basis) {
            return 'ยังไม่ได้เลือกประเภทค่าใช้จ่าย';
        }
        $basis = FinanceLoanExpenseType::basisOptions()[$this->due_basis] ?? $this->due_basis;
        $text = 'ส่งใช้ภายใน ' . (int) $this->due_days . ' วัน นับจาก' . $basis;
        if ($this->due_is_manual) {
            return $text . ' (กำหนดเอง ระบบไม่คำนวณทับ)';
        }
        return $this->dueAnchorDate() ? $text : $text . ' — ยังไม่ได้กรอกวันดังกล่าว จึงยังไม่มีวันครบกำหนด';
    }

    // ── ยอดสรุป ───────────────────────────────────────────────────

    /**
     * คำนวณยอดสรุปใหม่จากตารางลูก แล้วเขียนกลับโดยไม่ผ่าน validation
     *
     * ใช้ updateAttributes เพราะเป็นการปรับยอดที่คำนวณเอง ไม่ใช่ข้อมูลที่ผู้ใช้กรอก
     * และไม่ควรถูกกฎการตรวจสอบของฟอร์มขวางไว้
     */
    public function recalcTotals(bool $advanceStatus = true): void
    {
        $approved = (float) $this->getItems()->sum('amount');
        $voucher = (float) $this->getSettlements()->sum('voucher_amount');
        $cash = (float) $this->getSettlements()->sum('cash_amount');
        $dates = $this->getSettlements()->select(['MIN(settled_at) AS first_at', 'MAX(settled_at) AS last_at'])->asArray()->one();

        $changes = [
            'approved_amount' => round($approved, 2),
            'voucher_amount' => round($voucher, 2),
            'cash_return_amount' => round($cash, 2),
            'outstanding_amount' => round(max(0, $approved - $voucher - $cash), 2),
            'first_settled_at' => $dates['first_at'] ?? null,
            'last_settled_at' => $dates['last_at'] ?? null,
        ];

        // ปิดยอดให้เองเมื่อส่งใช้ครบ แทนที่จะรอให้คนเลือกสถานะจาก dropdown
        // กันข้อมูลไม่สอดคล้องแบบที่เคยเจอในโมดูลวันลา
        if ($advanceStatus && !in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_COMPLETED], true)) {
            $settled = $changes['voucher_amount'] + $changes['cash_return_amount'];
            if ($settled > 0 && $changes['outstanding_amount'] <= 0.01) {
                $changes['status'] = self::STATUS_CLEARED;
            } elseif ($this->status === self::STATUS_CLEARED && $changes['outstanding_amount'] > 0.01) {
                $changes['status'] = $this->received_at ? self::STATUS_RECEIVED : self::STATUS_APPROVED;
            }
        }

        $this->updateAttributes($changes);
    }

    /** ยอดรวมรายบรรทัดแยกตามช่องของทะเบียนคุม (เบี้ยเลี้ยง/ที่พัก/พาหนะ/อื่นๆ) */
    public function registerTotals(): array
    {
        $totals = array_fill_keys(array_keys(FinanceLoanItemKind::registerColumnOptions()), 0.0);
        foreach ($this->items as $item) {
            $column = $item->registerColumn();
            $totals[$column] = ($totals[$column] ?? 0) + (float) $item->amount;
        }
        return $totals;
    }

    // ── สถานะและกำหนดเวลา ─────────────────────────────────────────

    public static function statusOptions(): array
    {
        return [
            self::STATUS_REQUESTED => 'ร้องขอ',
            self::STATUS_REVIEWED => 'เห็นชอบ',
            self::STATUS_APPROVED => 'อนุมัติ',
            self::STATUS_RECEIVED => 'รับเช็ค',
            self::STATUS_CLEARED => 'ล้างใบยืม',
            self::STATUS_CANCELLED => 'ยกเลิก',
            self::STATUS_COMPLETED => 'สิ้นสุด',
        ];
    }

    public static function sourceOptions(): array
    {
        return [
            'manual' => 'บันทึกในระบบ',
            'development' => 'ทะเบียนเดินทางไปราชการ',
            'project' => 'โครงการ',
            'import' => 'นำเข้าจากไฟล์ทะเบียน',
        ];
    }

    /** แปลงคำสถานะภาษาไทยจากไฟล์ทะเบียนเดิมกลับเป็นรหัส */
    public static function fromLegacyStatus(?string $status): string
    {
        $key = trim((string) $status);
        return array_search($key, self::statusOptions(), true) ?: self::STATUS_REQUESTED;
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    /**
     * สถานะที่เดินต่อจากสถานะปัจจุบันได้ พร้อมคำบนปุ่ม
     *
     * ทำเป็นตารางแทนที่จะปล่อยให้เลือกจาก dropdown ได้ทุกค่า เพราะลำดับงานจริงเดินทางเดียว
     * และการข้ามขั้น เช่น กระโดดจาก “ร้องขอ” ไป “รับเช็ค” เลย แปลว่ามีคนลืมกดอนุมัติ
     * ซึ่งพอเกิดแล้วตามย้อนหลังไม่ได้ว่าใครอนุมัติเมื่อไร
     *
     * “ล้างใบยืม” ไม่อยู่ในตารางนี้ เพราะระบบเลื่อนให้เองเมื่อส่งใช้ครบ ไม่ใช่ปุ่มที่คนกด
     */
    public function allowedTransitions(): array
    {
        return match ($this->status) {
            self::STATUS_REQUESTED => [
                self::STATUS_REVIEWED => 'เห็นชอบ',
                self::STATUS_CANCELLED => 'ยกเลิกใบยืม',
            ],
            self::STATUS_REVIEWED => [
                self::STATUS_APPROVED => 'อนุมัติให้ยืม',
                self::STATUS_REQUESTED => 'ส่งกลับแก้ไข',
                self::STATUS_CANCELLED => 'ยกเลิกใบยืม',
            ],
            self::STATUS_APPROVED => [
                self::STATUS_RECEIVED => 'บันทึกรับเช็ค',
                self::STATUS_REVIEWED => 'ถอนการอนุมัติ',
                self::STATUS_CANCELLED => 'ยกเลิกใบยืม',
            ],
            self::STATUS_RECEIVED => [
                self::STATUS_CANCELLED => 'ยกเลิกใบยืม',
            ],
            self::STATUS_CLEARED => [
                self::STATUS_COMPLETED => 'ปิดเรื่อง (สิ้นสุด)',
            ],
            self::STATUS_CANCELLED => [
                self::STATUS_REQUESTED => 'นำกลับมาดำเนินการ',
            ],
            default => [],
        };
    }

    /**
     * เหตุผลที่เดินไปสถานะนั้นไม่ได้ คืน null ถ้าเดินได้
     *
     * แยกจาก allowedTransitions() เพราะเงื่อนไขบางข้อไม่ได้ขึ้นกับสถานะเดิมอย่างเดียว
     * แต่ขึ้นกับความครบถ้วนของข้อมูล และผู้ใช้ควรเห็นว่าต้องไปกรอกอะไรก่อน
     */
    public function transitionBlocker(string $target): ?string
    {
        if (!isset($this->allowedTransitions()[$target])) {
            return 'ไม่สามารถเปลี่ยนจาก “' . $this->statusLabel() . '” เป็นสถานะที่เลือกได้';
        }
        if ($target === self::STATUS_APPROVED && (float) $this->approved_amount <= 0) {
            return 'อนุมัติไม่ได้ เพราะยังไม่มีบรรทัดประมาณการ ยอดเงินยืมจึงเป็น 0';
        }
        if ($target === self::STATUS_CANCELLED && $this->getSettlements()->exists()) {
            return 'ยกเลิกไม่ได้ เพราะมีการส่งใช้บันทึกไว้แล้ว ให้ลบรายการส่งใช้ก่อน';
        }
        return null;
    }

    public function isClosed(): bool
    {
        return in_array($this->status, self::CLOSED_STATUSES, true);
    }

    /**
     * สถานะทางเวลา แยกจากสถานะเอกสาร เพราะ “เกินกำหนด” ไม่ใช่ขั้นตอนที่คนกด
     * แต่เป็นผลของวันที่เทียบกับวันนี้ ตัวแจ้งเตือนใช้ฟังก์ชันนี้ตัวเดียวกับหน้าจอ
     */
    public function dueState(): string
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return 'cancelled';
        }
        if (in_array($this->status, [self::STATUS_CLEARED, self::STATUS_COMPLETED], true)) {
            return 'closed';
        }
        // ต้องมียอดยืมก่อนจึงจะพูดได้ว่าปิดยอดแล้ว ไม่งั้นใบยืมที่เพิ่งสร้างและยังไม่ได้
        // ใส่บรรทัดประมาณการ (คงเหลือ 0 เพราะยังไม่มียอด) จะขึ้นว่า “ปิดยอดแล้ว” ทันที
        if ((float) $this->approved_amount > 0 && (float) $this->outstanding_amount <= 0) {
            return 'closed';
        }
        if (!$this->due_at) {
            return 'missing';
        }
        $days = $this->daysToDue();
        if ($days < 0) {
            return 'overdue';
        }
        return $days <= 7 ? 'due_soon' : 'open';
    }

    /** จำนวนวันจนถึงวันครบกำหนด ติดลบคือเกินมาแล้ว */
    public function daysToDue(): int
    {
        if (!$this->due_at) {
            return 0;
        }
        return (int) floor((strtotime($this->due_at) - strtotime(date('Y-m-d'))) / 86400);
    }

    public function daysOverdue(): int
    {
        return max(0, -$this->daysToDue());
    }

    public function dueLabel(): string
    {
        return [
            'closed' => 'ปิดยอดแล้ว',
            'cancelled' => 'ยกเลิก',
            'missing' => 'ไม่ระบุวันครบกำหนด',
            'overdue' => 'เกินกำหนด ' . $this->daysOverdue() . ' วัน',
            'due_soon' => 'ใกล้ครบกำหนด',
            'open' => 'อยู่ระหว่างดำเนินการ',
        ][$this->dueState()];
    }

    public function dueBadgeClass(): string
    {
        return [
            'closed' => 'bg-success-subtle text-success-emphasis',
            'cancelled' => 'bg-secondary-subtle text-secondary-emphasis',
            'missing' => 'bg-secondary-subtle text-secondary-emphasis',
            'overdue' => 'bg-danger-subtle text-danger-emphasis',
            'due_soon' => 'bg-warning-subtle text-warning-emphasis',
            'open' => 'bg-info-subtle text-info-emphasis',
        ][$this->dueState()];
    }

    /** ปีงบประมาณไทยปัจจุบัน (รอบ 1 ต.ค. – 30 ก.ย.) */
    public static function currentFiscalYear(): int
    {
        $year = (int) date('Y') + 543;
        return (int) date('n') >= 10 ? $year + 1 : $year;
    }

    /** ใบยืมที่ยังค้างและมีวันครบกำหนดแล้ว — ฐานของตัวแจ้งเตือนและหน้าลูกหนี้ค้าง */
    public static function findOutstanding(): \yii\db\ActiveQuery
    {
        return self::find()
            ->where(['not in', 'status', self::CLOSED_STATUSES])
            ->andWhere(['>', 'outstanding_amount', 0]);
    }
}
