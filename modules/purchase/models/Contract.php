<?php

namespace app\modules\purchase\models;

use Yii;
use yii\db\Expression;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use yii\helpers\HtmlPurifier;
use app\components\AppHelper;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\purchase\components\ContractCalculator;

/**
 * สัญญา/ข้อตกลง — หัวเอกสาร
 *
 * ความสัมพันธ์กับใบสั่งซื้อ (orders)
 * ใบสั่งซื้อเดิมเก็บวันลงนาม/กำหนดส่ง/วันตรวจรับไว้ใน data_json อยู่แล้ว สัญญาจึง
 * "ยืมมาเติมให้ตอนสร้าง" แต่เก็บสำเนาของตัวเองไว้ เพราะค่าที่ใช้คำนวณค่าปรับต้องนิ่ง
 * ไม่เปลี่ยนตามที่ใครไปแก้ใบสั่งซื้อทีหลัง ความไม่ตรงกันที่เกิดขึ้นภายหลังให้
 * diffFromOrder() รายงานออกมาให้ผู้ใช้เห็น แทนที่จะซ่อนไว้
 *
 * @property int $id
 * @property string|null $doc_no เลขที่ในระบบ
 * @property string|null $contract_no เลขที่สัญญาตามที่ออกจริง
 * @property int $thai_year
 * @property string $title
 * @property int|null $order_id
 * @property int|null $tor_id
 * @property string $contract_type buy|hire|lease|agreement
 * @property string|null $vendor_id
 * @property string|null $vendor_name
 * @property string $party_type juristic|personal
 * @property string|null $egp_no
 * @property float $budget
 * @property int $vat_included
 * @property float|null $wht_rate
 * @property float|null $wht_base
 * @property float $wht_amount
 * @property string|null $sign_date
 * @property string|null $start_date
 * @property string|null $end_date
 * @property string|null $delivery_date
 * @property string|null $receive_date
 * @property string|null $warranty_end
 * @property float $fine_rate
 * @property string $fine_base contract|milestone
 * @property int $fine_days
 * @property float $fine_amount
 * @property int $fine_capped
 * @property string|null $extra_term
 * @property string|null $note
 * @property string $status
 */
class Contract extends \yii\db\ActiveRecord
{
    /** ช่องค้นหาอิสระ (ไม่ใช่คอลัมน์) */
    public $q;

    const TYPE_BUY = 'buy';
    const TYPE_HIRE = 'hire';
    const TYPE_LEASE = 'lease';
    const TYPE_AGREEMENT = 'agreement';

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_RECEIVED = 'received';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';

    const FINE_BASE_CONTRACT = 'contract';
    const FINE_BASE_MILESTONE = 'milestone';

    /** ช่องเนื้อความที่ผู้ใช้จัดรูปแบบเองได้ ต้องผ่าน HtmlPurifier ทุกครั้งก่อนบันทึก */
    const HTML_FIELDS = ['extra_term'];

    /** แท็กที่อนุญาต — จำกัดเท่าที่ PhpWord แปลงลงไฟล์ Word ได้จริง (ชุดเดียวกับ TOR) */
    const ALLOWED_HTML = 'p,br,strong,b,em,i,u,s,ol,ul,li,table,thead,tbody,tr,td[colspan|rowspan],th[colspan|rowspan],span,h4,h5,h6,sub,sup';

    public static function tableName()
    {
        return 'purchase_contract';
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
            [['title'], 'required', 'message' => 'กรุณากรอกชื่อสัญญา/รายการ'],
            [['budget'], 'required', 'message' => 'กรุณากรอกวงเงินตามสัญญา'],
            [['thai_year', 'order_id', 'tor_id', 'department_id', 'emp_id', 'vat_included'], 'integer'],
            [['budget', 'fine_rate'], 'number', 'min' => 0],
            [['fine_rate'], 'number', 'max' => 100],
            [
                ['sign_date', 'start_date', 'end_date', 'delivery_date', 'receive_date', 'warranty_end'],
                'date',
                'format' => 'php:Y-m-d',
            ],
            [['end_date'], 'validateEndDate'],
            [['contract_type'], 'in', 'range' => array_keys(self::typeList())],
            [['party_type'], 'in', 'range' => array_keys(WhtRate::partyTypeList())],
            [['status'], 'in', 'range' => array_keys(self::statusList())],
            [['fine_base'], 'in', 'range' => array_keys(self::fineBaseList())],
            [['order_id'], 'unique', 'message' => 'ใบสั่งซื้อนี้มีสัญญาผูกอยู่แล้ว'],
            [['title', 'vendor_id', 'vendor_name'], 'string', 'max' => 255],
            [['contract_no'], 'string', 'max' => 100],
            [['doc_no', 'egp_no'], 'string', 'max' => 50],
            [['extra_term', 'note'], 'string'],
            [['data_json', 'q'], 'safe'],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['contract_type'], 'default', 'value' => self::TYPE_BUY],
            [['party_type'], 'default', 'value' => WhtRate::PARTY_JURISTIC],
            [['fine_base'], 'default', 'value' => self::FINE_BASE_CONTRACT],
            [['vat_included'], 'default', 'value' => 1],
            [['budget'], 'default', 'value' => 0],
            [['fine_rate'], 'default', 'value' => 0.01],
        ];
    }

    /** วันครบกำหนดส่งมอบต้องไม่มาก่อนวันลงนาม ไม่งั้นค่าปรับที่คำนวณได้จะไม่มีความหมาย */
    public function validateEndDate($attribute)
    {
        if (empty($this->end_date) || empty($this->sign_date)) {
            return;
        }
        if (strtotime($this->end_date) < strtotime($this->sign_date)) {
            $this->addError($attribute, 'วันครบกำหนดส่งมอบต้องไม่มาก่อนวันลงนามในสัญญา');
        }
    }

    public function attributeLabels()
    {
        return [
            'doc_no' => 'เลขที่ในระบบ',
            'contract_no' => 'เลขที่สัญญา',
            'thai_year' => 'ปีงบประมาณ',
            'title' => 'ชื่อสัญญา/รายการ',
            'order_id' => 'ใบสั่งซื้อที่ผูก',
            'tor_id' => 'TOR ที่ใช้',
            'contract_type' => 'ประเภทสัญญา',
            'vendor_id' => 'คู่สัญญา',
            'vendor_name' => 'ชื่อคู่สัญญา',
            'party_type' => 'ประเภทผู้รับเงิน',
            'egp_no' => 'เลขที่โครงการ e-GP',
            'budget' => 'วงเงินตามสัญญา',
            'vat_included' => 'วงเงินรวมภาษีมูลค่าเพิ่มแล้ว',
            'wht_rate' => 'อัตราภาษีหัก ณ ที่จ่าย',
            'wht_base' => 'ฐานคำนวณภาษี',
            'wht_amount' => 'ภาษีหัก ณ ที่จ่าย',
            'sign_date' => 'วันลงนามในสัญญา',
            'start_date' => 'วันเริ่มนับระยะเวลา',
            'end_date' => 'วันครบกำหนดส่งมอบ',
            'delivery_date' => 'วันที่ส่งมอบจริง',
            'receive_date' => 'วันที่ตรวจรับ',
            'warranty_end' => 'สิ้นสุดการรับประกัน',
            'fine_rate' => 'อัตราค่าปรับ (%/วัน)',
            'fine_base' => 'ฐานคิดค่าปรับ',
            'fine_days' => 'จำนวนวันที่ล่าช้า',
            'fine_amount' => 'ค่าปรับ',
            'extra_term' => 'เงื่อนไขเพิ่มเติม',
            'note' => 'หมายเหตุภายใน',
            'status' => 'สถานะ',
            'department_id' => 'หน่วยงาน',
            'emp_id' => 'ผู้บันทึก',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        foreach (self::HTML_FIELDS as $field) {
            if ($this->$field === null || $this->$field === '') {
                continue;
            }
            $this->$field = HtmlPurifier::process($this->$field, [
                'HTML.Allowed' => self::ALLOWED_HTML,
                'AutoFormat.RemoveEmpty' => true,
            ]);
        }

        if (empty($this->thai_year)) {
            $this->thai_year = (int) AppHelper::YearBudget();
        }
        if ($insert && empty($this->doc_no)) {
            $this->doc_no = \mdm\autonumber\AutoNumber::generate('CT-' . $this->thai_year . '-????');
        }

        // ชื่อคู่สัญญาเก็บเป็น snapshot — ทะเบียนผู้ขายถูกแก้ทีหลังแล้วเอกสารเก่าต้องคงชื่อเดิม
        if (!empty($this->vendor_id) && empty($this->vendor_name)) {
            $this->vendor_name = (string) Categorise::find()
                ->select('title')
                ->where(['name' => 'vendor', 'code' => $this->vendor_id])
                ->scalar() ?: null;
        }

        $this->applyWht();

        return true;
    }

    // ── relation ────────────────────────────────────────────────────────────

    public function getMilestones()
    {
        return $this->hasMany(ContractMilestone::class, ['contract_id' => 'id'])
            ->orderBy(['seq' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getTor()
    {
        return $this->hasOne(Tor::class, ['id' => 'tor_id']);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    // ── ค่าคงที่และรายการอ้างอิง ─────────────────────────────────────────────

    public static function typeList()
    {
        return [
            self::TYPE_BUY => 'สัญญาซื้อขาย',
            self::TYPE_HIRE => 'สัญญาจ้าง',
            self::TYPE_LEASE => 'สัญญาเช่า',
            self::TYPE_AGREEMENT => 'ข้อตกลง',
        ];
    }

    public function typeName()
    {
        return self::typeList()[$this->contract_type] ?? $this->contract_type;
    }

    public static function statusList()
    {
        return [
            self::STATUS_DRAFT => 'ร่าง',
            self::STATUS_ACTIVE => 'กำลังดำเนินการ',
            self::STATUS_DELIVERED => 'ส่งมอบแล้ว รอตรวจรับ',
            self::STATUS_RECEIVED => 'ตรวจรับแล้ว',
            self::STATUS_OVERDUE => 'ค้างส่ง',
            self::STATUS_CANCELLED => 'ยกเลิก',
        ];
    }

    public static function statusBadge($status)
    {
        $map = [
            self::STATUS_DRAFT => ['label' => 'ร่าง', 'color' => 'secondary'],
            self::STATUS_ACTIVE => ['label' => 'กำลังดำเนินการ', 'color' => 'primary'],
            self::STATUS_DELIVERED => ['label' => 'รอตรวจรับ', 'color' => 'info'],
            self::STATUS_RECEIVED => ['label' => 'ตรวจรับแล้ว', 'color' => 'success'],
            self::STATUS_OVERDUE => ['label' => 'ค้างส่ง', 'color' => 'danger'],
            self::STATUS_CANCELLED => ['label' => 'ยกเลิก', 'color' => 'dark'],
        ];
        return $map[$status] ?? ['label' => $status ?: '-', 'color' => 'secondary'];
    }

    public function statusName()
    {
        return self::statusBadge($this->status)['label'];
    }

    public static function fineBaseList()
    {
        return [
            self::FINE_BASE_CONTRACT => 'คิดจากวงเงินทั้งสัญญา',
            self::FINE_BASE_MILESTONE => 'คิดจากวงเงินของงวดที่ล่าช้า',
        ];
    }

    public static function listVendor()
    {
        return ArrayHelper::map(
            Categorise::find()
                ->where(['name' => 'vendor'])
                ->andWhere(['not', ['title' => null]])
                ->andWhere(['!=', 'title', ''])
                ->orderBy(['title' => SORT_ASC])
                ->all(),
            'code',
            'title'
        );
    }

    /** ปีงบประมาณที่มีข้อมูล (สำหรับ dropdown ค้นหา) — รวมปีปัจจุบันเสมอ */
    public static function listThaiYear()
    {
        $years = self::find()
            ->select('thai_year')
            ->distinct()
            ->where(['deleted_at' => null])
            ->orderBy(['thai_year' => SORT_DESC])
            ->column();
        $current = (int) AppHelper::YearBudget();
        if (!in_array($current, array_map('intval', $years), true)) {
            array_unshift($years, $current);
        }
        return array_combine($years, $years);
    }

    // ── การคำนวณ ────────────────────────────────────────────────────────────

    /**
     * วันที่ใช้ปิดสัญญาสำหรับคิดค่าปรับ — ดูเหตุผลการเลือกวันที่ ContractMilestone::closingDate()
     */
    public function closingDate(): ?string
    {
        return $this->delivery_date ?: ($this->receive_date ?: null);
    }

    /** ผลการคำนวณค่าปรับ ณ ข้อมูลปัจจุบัน (ไม่บันทึก) */
    public function fineInfo(): array
    {
        return ContractCalculator::fine($this, $this->isNewRecord ? [] : $this->milestones);
    }

    /** ผลการคำนวณภาษีหัก ณ ที่จ่าย ณ ข้อมูลปัจจุบัน (ไม่บันทึก) */
    public function whtInfo(): array
    {
        return ContractCalculator::wht(
            (float) $this->budget,
            $this->contract_type,
            $this->party_type,
            (int) $this->vat_included === 1
        );
    }

    /** เขียนผลภาษีลงคอลัมน์ เรียกจาก beforeSave */
    public function applyWht(): void
    {
        $wht = $this->whtInfo();
        $this->wht_rate = $wht['rate'];
        $this->wht_base = $wht['base'];
        $this->wht_amount = $wht['amount'];
    }

    /**
     * คำนวณค่าปรับใหม่แล้วบันทึกลงคอลัมน์
     * แยกจาก beforeSave เพราะต้องอ่านงวดงานที่เพิ่งบันทึกเสร็จในทรานแซกชันเดียวกัน
     * ถ้าทำใน beforeSave จะได้งวดงานชุดเก่า
     */
    public function refreshFine(): void
    {
        $fine = ContractCalculator::fine($this, $this->milestones);
        $this->fine_days = $fine['days'];
        $this->fine_amount = $fine['amount'];
        $this->fine_capped = $fine['capped'] ? 1 : 0;
        $this->save(false, ['fine_days', 'fine_amount', 'fine_capped']);
    }

    /** ค่าปรับคิดเป็นร้อยละของวงเงิน */
    public function fineRatio(): float
    {
        return ContractCalculator::fineRatio((float) $this->fine_amount, (float) $this->budget);
    }

    /** true เมื่อเลยกำหนดส่งมอบแล้วแต่ยังไม่มีการส่งมอบ */
    public function isOverdue(): bool
    {
        if (empty($this->end_date) || $this->closingDate()) {
            return false;
        }
        if (in_array($this->status, [self::STATUS_RECEIVED, self::STATUS_CANCELLED], true)) {
            return false;
        }
        return strtotime($this->end_date) < strtotime(date('Y-m-d'));
    }

    /** จำนวนวันที่เหลือถึงกำหนดส่งมอบ (ติดลบ = เลยกำหนด) คืน null เมื่อไม่ได้กำหนดวัน */
    public function daysToDue(): ?int
    {
        if (empty($this->end_date)) {
            return null;
        }
        $diff = strtotime($this->end_date) - strtotime(date('Y-m-d'));
        return (int) floor($diff / 86400);
    }

    // ── การเทียบกับใบสั่งซื้อที่ผูกไว้ ───────────────────────────────────────

    /**
     * ค่าที่ดึงได้จากใบสั่งซื้อที่ผูก ใช้ทั้งตอนเติมฟอร์มอัตโนมัติและตอนเทียบความต่าง
     * ใบสั่งซื้อเก็บข้อมูลชุดนี้ไว้ใน data_json ซึ่งเป็นข้อความล้วน จึงต้องกันค่าที่
     * กรอกมาไม่เป็นรูปแบบวันที่ (เช่น "-" หรือ "ไม่มี") ไม่ให้หลุดเข้ามาเป็นวันที่
     *
     * @return array<string,mixed>|null null เมื่อไม่ได้ผูกใบสั่งซื้อหรือหาไม่พบ
     */
    public static function orderSnapshot($orderId): ?array
    {
        if (empty($orderId)) {
            return null;
        }
        $order = Order::find()->where(['id' => $orderId, 'name' => 'order'])->one();
        if (!$order) {
            return null;
        }

        $json = is_array($order->data_json) ? $order->data_json : [];
        $date = function ($value) {
            $value = trim((string) $value);
            if ($value === '') {
                return null;
            }
            $ts = strtotime($value);
            return $ts === false ? null : date('Y-m-d', $ts);
        };

        $total = (float) Order::find()
            ->where(['name' => 'order_item', 'category_id' => $order->id])
            ->sum(new Expression('price * qty'));

        // ใบสั่งซื้อจำนวนหนึ่งเก็บ vendor_name เป็น "-" หรือเว้นว่างไว้ใน data_json
        // ทั้งที่มี vendor_id ที่ชี้ทะเบียนผู้ขายได้ จึงถอยไปอ่านชื่อจากทะเบียนแทน
        // ไม่งั้นหน้าต่างเลือกใบสั่งซื้อจะขึ้นชื่อผู้ขายเป็นขีดกลางให้ผู้ใช้เดาเอง
        $vendorName = trim((string) ($json['vendor_name'] ?? ''));
        if (($vendorName === '' || $vendorName === '-') && !empty($order->vendor_id)) {
            $vendorName = (string) Categorise::find()
                ->select('title')
                ->where(['name' => 'vendor', 'code' => $order->vendor_id])
                ->scalar();
        }

        return [
            'order_id' => $order->id,
            'po_number' => $order->po_number,
            'pr_number' => $order->pr_number,
            'gr_number' => $order->gr_number,
            'title' => $json['pq_project_name'] ?? ($json['order_type_name'] ?? ''),
            'vendor_id' => $order->vendor_id,
            'vendor_name' => $vendorName ?: null,
            'egp_no' => $json['pq_egp_number'] ?? null,
            'budget' => $total,
            'sign_date' => $date($json['signing_date'] ?? null),
            'end_date' => $date($json['due_date'] ?? null),
            'delivery_date' => $date($json['delivery_date'] ?? null),
            'receive_date' => $date($json['gr_date'] ?? null),
            'warranty_end' => $date($json['warranty_date'] ?? null),
            'thai_year' => $order->thai_year,
        ];
    }

    /**
     * ช่องที่ค่าในสัญญาไม่ตรงกับใบสั่งซื้อที่ผูกไว้ ณ ตอนนี้
     * สัญญาเก็บสำเนาของตัวเองเพื่อให้ค่าปรับนิ่ง การที่ค่าต่างกันจึงไม่ใช่ข้อผิดพลาด
     * แต่ผู้ใช้ต้องเห็น ไม่ใช่ปล่อยให้มีวันตรวจรับสองชุดที่ไม่มีใครรู้ว่าต่างกัน
     *
     * @return array<int,array{label:string, contract:mixed, order:mixed}>
     */
    public function diffFromOrder(): array
    {
        $snapshot = self::orderSnapshot($this->order_id);
        if ($snapshot === null) {
            return [];
        }

        $compare = [
            'budget' => 'วงเงิน',
            'sign_date' => 'วันลงนามในสัญญา',
            'end_date' => 'วันครบกำหนดส่งมอบ',
            'delivery_date' => 'วันที่ส่งมอบจริง',
            'receive_date' => 'วันที่ตรวจรับ',
        ];

        $diff = [];
        foreach ($compare as $field => $label) {
            $orderValue = $snapshot[$field] ?? null;
            if ($orderValue === null || $orderValue === '') {
                continue;
            }
            $mine = $this->$field;
            $same = $field === 'budget'
                ? abs((float) $mine - (float) $orderValue) < 0.01
                : (string) $mine === (string) $orderValue;
            if (!$same) {
                $diff[] = ['label' => $label, 'contract' => $mine, 'order' => $orderValue];
            }
        }

        return $diff;
    }

    public function empName()
    {
        try {
            return $this->employee ? $this->employee->fullname() : '-';
        } catch (\Throwable $e) {
            return '-';
        }
    }

    /** ชื่อคู่สัญญาที่ใช้แสดง — ยึด snapshot ก่อนเสมอ */
    public function partyName(): string
    {
        if (!empty($this->vendor_name)) {
            return $this->vendor_name;
        }
        if (!empty($this->vendor_id)) {
            return (string) Categorise::find()
                ->select('title')
                ->where(['name' => 'vendor', 'code' => $this->vendor_id])
                ->scalar() ?: $this->vendor_id;
        }
        return '-';
    }
}
