<?php

namespace app\modules\purchase\models;

use Yii;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use app\modules\purchase\components\ContractCalculator;

/**
 * การตรวจรับรายงวดของสัญญา — 1 แถว = 1 งวด = 1 รายการที่จะส่งการเงินตั้งหนี้
 *
 * ใช้กับสัญญาที่ออกใบสั่งซื้อเต็มวงเงิน แต่ตรวจรับ/เรียกเก็บเป็นรายเดือน (Contract::isInstallment())
 * ยอดงวด = ผลรวมรายการในงวด (ปริมาณจริง × ราคาต่อหน่วย) แล้วแยก VAT ตาม vat_type
 * ยอดคงเหลือของสัญญาคำนวณจากงวดที่ไม่ถูกยกเลิก ไม่เก็บซ้ำ
 *
 * สเปก: docs/purchase/contract-installment-receipt-spec.md
 *
 * @property int $id
 * @property string|null $ref
 * @property int $contract_id
 * @property int|null $order_id
 * @property int|null $milestone_id
 * @property int $seq
 * @property string|null $period_start
 * @property string|null $period_end
 * @property int|null $thai_year
 * @property string|null $invoice_no
 * @property string|null $invoice_date
 * @property string|null $delivered_date
 * @property string|null $receive_date
 * @property string|null $vat_type IN|EX|NONE
 * @property float $amount_before_vat
 * @property float $vat_amount
 * @property float $amount ยอดหลัง VAT = ยอดตั้งหนี้
 * @property int $fine_days
 * @property float $fine_amount
 * @property float $wht_amount
 * @property string $status draft|received|sent_finance|cancelled
 * @property string|null $sent_finance_at
 * @property string|null $note
 * @property array|null $data_json
 *
 * @property Contract $contract
 * @property ContractReceiptItem[] $items
 */
class ContractReceipt extends \yii\db\ActiveRecord
{
    const STATUS_DRAFT = 'draft';
    const STATUS_RECEIVED = 'received';
    const STATUS_SENT_FINANCE = 'sent_finance';
    const STATUS_CANCELLED = 'cancelled';

    const VAT_IN = 'IN';
    const VAT_EX = 'EX';
    const VAT_NONE = 'NONE';

    public static function tableName()
    {
        return 'purchase_contract_receipt';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    public function rules()
    {
        return [
            [['contract_id', 'seq'], 'required'],
            [['contract_id', 'order_id', 'milestone_id', 'seq', 'thai_year', 'fine_days'], 'integer'],
            [['amount_before_vat', 'vat_amount', 'amount', 'wht_amount'], 'number'],
            [['fine_amount'], 'number', 'min' => 0],
            [['period_start', 'period_end', 'invoice_date', 'delivered_date', 'receive_date'], 'date', 'format' => 'php:Y-m-d'],
            [['period_end'], 'validatePeriod'],
            [['vat_type'], 'in', 'range' => array_keys(self::vatTypeList())],
            [['status'], 'in', 'range' => array_keys(self::statusList())],
            [['invoice_no'], 'string', 'max' => 100],
            [['note'], 'string', 'max' => 500],
            [['fine_amount', 'fine_days', 'wht_amount'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            // ตรวจรับแล้วต้องมีวันตรวจรับ — วันนี้กำหนดปีงบของงวดและเดือนที่ขึ้นกราฟ
            [['receive_date'], 'required', 'when' => fn($m) => in_array($m->status, [self::STATUS_RECEIVED, self::STATUS_SENT_FINANCE], true),
                'message' => 'งวดที่ตรวจรับแล้วต้องระบุวันที่ตรวจรับ'],
        ];
    }

    public function validatePeriod($attribute)
    {
        if ($this->period_start && $this->period_end && $this->period_end < $this->period_start) {
            $this->addError($attribute, 'วันสิ้นสุดผลงานต้องไม่ก่อนวันเริ่ม');
        }
    }

    public function attributeLabels()
    {
        return [
            'seq' => 'งวดที่',
            'period_start' => 'ผลงานตั้งแต่',
            'period_end' => 'ผลงานถึง',
            'invoice_no' => 'เลขที่ใบแจ้งหนี้',
            'invoice_date' => 'วันที่ใบแจ้งหนี้',
            'delivered_date' => 'วันที่ส่งมอบงาน',
            'receive_date' => 'วันที่ตรวจรับ',
            'vat_type' => 'ภาษีมูลค่าเพิ่ม',
            'amount_before_vat' => 'ยอดก่อน VAT',
            'vat_amount' => 'VAT',
            'amount' => 'ยอดเรียกเก็บ',
            'fine_amount' => 'ค่าปรับ',
            'wht_amount' => 'ภาษีหัก ณ ที่จ่าย',
            'milestone_id' => 'งวดตามสัญญา',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
        ];
    }

    public static function statusList()
    {
        return [
            self::STATUS_DRAFT => 'ร่าง',
            self::STATUS_RECEIVED => 'ตรวจรับแล้ว',
            self::STATUS_SENT_FINANCE => 'ส่งการเงินแล้ว',
            self::STATUS_CANCELLED => 'ยกเลิก',
        ];
    }

    public static function statusBadge($status)
    {
        $colors = [
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_RECEIVED => 'success',
            self::STATUS_SENT_FINANCE => 'primary',
            self::STATUS_CANCELLED => 'danger',
        ];
        return ['label' => self::statusList()[$status] ?? $status, 'color' => $colors[$status] ?? 'secondary'];
    }

    public static function vatTypeList()
    {
        return [
            self::VAT_IN => 'ราคารวม VAT แล้ว',
            self::VAT_EX => 'ราคายังไม่รวม VAT (บวกเพิ่ม 7%)',
            self::VAT_NONE => 'ไม่มี VAT',
        ];
    }

    /** สถานะที่นับเป็นยอดใช้วงเงินของสัญญา (ทุกสถานะยกเว้นยกเลิก — ร่างก็กันวงเงินไว้) */
    public static function activeStatuses(): array
    {
        return [self::STATUS_DRAFT, self::STATUS_RECEIVED, self::STATUS_SENT_FINANCE];
    }

    public function getContract()
    {
        return $this->hasOne(Contract::class, ['id' => 'contract_id']);
    }

    public function getItems()
    {
        return $this->hasMany(ContractReceiptItem::class, ['receipt_id' => 'id'])->orderBy(['id' => SORT_ASC]);
    }

    public function getMilestone()
    {
        return $this->hasOne(ContractMilestone::class, ['id' => 'milestone_id']);
    }

    /** แก้ไขได้จนกว่าจะส่งการเงิน/ยกเลิก */
    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_RECEIVED], true);
    }

    /**
     * คำนวณยอดงวดจากผลรวมรายการ: แยก VAT ตาม vat_type + ภาษีหัก ณ ที่จ่ายต่อการจ่ายครั้งนี้
     * @param float $lineTotal ผลรวม qty × unit_price ของทุกรายการ
     */
    public function applyTotals(float $lineTotal): void
    {
        $rate = ContractCalculator::VAT_RATE;
        $lineTotal = round($lineTotal, 2);
        switch ($this->vat_type) {
            case self::VAT_EX:
                $before = $lineTotal;
                $vat = round($lineTotal * $rate / 100, 2);
                $after = $before + $vat;
                break;
            case self::VAT_IN:
                $after = $lineTotal;
                $before = round($lineTotal * 100 / (100 + $rate), 2);
                $vat = $after - $before;
                break;
            default:
                $before = $after = $lineTotal;
                $vat = 0.0;
        }
        $this->amount_before_vat = $before;
        $this->vat_amount = round($vat, 2);
        $this->amount = round($after, 2);

        // ภาษีหัก ณ ที่จ่ายคิดต่อการจ่ายแต่ละครั้ง (เกณฑ์ขั้นต่ำเทียบกับยอดงวด ไม่ใช่วงเงินทั้งสัญญา)
        $contract = $this->contract;
        $wht = ContractCalculator::wht(
            (float) $this->amount,
            $contract ? $contract->contract_type : null,
            $contract ? $contract->party_type : null,
            $this->vat_type !== self::VAT_NONE
        );
        $this->wht_amount = $wht['amount'];
    }

    /** ปีงบประมาณ (พ.ศ.) ของวันที่ ค.ศ. Y-m-d — ต.ค. ขึ้นปีงบใหม่ */
    public static function fiscalYearOf(?string $date): ?int
    {
        if (!$date || ($ts = strtotime($date)) === false) {
            return null;
        }
        return (int) date('Y', $ts) + 543 + ((int) date('n', $ts) >= 10 ? 1 : 0);
    }

    /**
     * งวดของ "พัสดุที่ต้องรับเข้าคลัง" (วัสดุ/ยา แบ่งส่งหลายงวด) — งานจ้าง/บริการไม่ต้องรับเข้าคลัง
     * จำแนกจากใบสั่งซื้อ: ประเภท M25 (บริการ) หรือชื่อประเภทขึ้นต้น "จ้าง" = งานจ้าง
     */
    public function isGoods(): bool
    {
        $order = $this->order_id ? Order::findOne($this->order_id) : null;
        if (!$order) {
            return false;
        }
        $typeName = is_array($order->data_json) ? (string) ($order->data_json['order_type_name'] ?? '') : '';
        return (string) $order->category_id !== 'M25' && mb_strpos($typeName, 'จ้าง') !== 0;
    }

    /** รับเข้าคลังแล้ว (ใบรับเข้า inventoryV2 ผูกงวดนี้ไว้) */
    public function isStocked(): bool
    {
        $json = is_array($this->data_json) ? $this->data_json : [];
        return !empty($json['stock_order_id']);
    }

    public function stockOrderNo(): ?string
    {
        $json = is_array($this->data_json) ? $this->data_json : [];
        return $json['stock_order_no'] ?? null;
    }

    /** บันทึก/ล้างการผูกใบรับเข้าคลัง — เรียกจาก inventoryV2 ReceiveController */
    public function markStocked(?int $stockOrderId, ?string $stockOrderNo): void
    {
        $json = is_array($this->data_json) ? $this->data_json : [];
        if ($stockOrderId) {
            $json['stock_order_id'] = $stockOrderId;
            $json['stock_order_no'] = $stockOrderNo;
            $json['stocked_at'] = date('Y-m-d H:i:s');
        } else {
            unset($json['stock_order_id'], $json['stock_order_no'], $json['stocked_at']);
        }
        $this->data_json = $json;
        $this->save(false, ['data_json', 'updated_at', 'updated_by']);
    }

    /** ยอดสุทธิที่จะจ่ายผู้รับจ้าง = ยอดเรียกเก็บ − ค่าปรับ − ภาษีหัก ณ ที่จ่าย */
    public function netPayable(): float
    {
        return round((float) $this->amount - (float) $this->fine_amount - (float) $this->wht_amount, 2);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->thai_year = self::fiscalYearOf($this->receive_date);
        if (empty($this->ref)) {
            $this->ref = Yii::$app->security->generateRandomString(32);
        }
        return true;
    }
}
