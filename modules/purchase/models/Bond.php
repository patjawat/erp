<?php

namespace app\modules\purchase\models;

use Yii;
use yii\db\Expression;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\purchase\components\BondCalculator;

/**
 * หลักประกัน 1 ใบ
 *
 * ผูกกับเอกสารต้นทางด้วย source_type + source_id ไม่ใช่คอลัมน์แยกต่อชนิดเอกสาร
 * เพราะหลักประกันเกิดได้ทั้งกับสัญญา กับใบสั่งซื้อ และเกิดก่อนจะมีทั้งสองอย่าง
 * (หลักประกันซองวางตั้งแต่ยื่นข้อเสนอ) การย้ายว่าผูกกับอะไรจึงเป็นการแก้สองคอลัมน์นี้
 * ไม่ใช่การคัดลอกข้อมูลไปไว้อีกที่ ซึ่งเป็นต้นเหตุที่ทำให้ยอดรวมในทะเบียนของ
 * โปรแกรมต้นแบบบวกซ้ำ
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $doc_no
 * @property int $thai_year
 * @property string $title
 * @property string $source_type contract|order|none
 * @property int|null $source_id
 * @property string|null $vendor_id
 * @property string|null $vendor_name
 * @property string $bond_type contract|bid|performance|advance|other
 * @property string $bond_form cash|bank_guarantee|gov_bond|cheque|other
 * @property string|null $doc_ref
 * @property string|null $issuer
 * @property float|null $base_amount
 * @property float|null $rate
 * @property float $amount
 * @property string|null $place_date
 * @property string|null $expiry_date
 * @property string $status
 * @property string|null $exempt_reason
 * @property string|null $return_date
 * @property string|null $return_doc_no
 * @property string|null $return_note
 * @property string|null $note
 */
class Bond extends \yii\db\ActiveRecord
{
    /** ช่องค้นหาอิสระ (ไม่ใช่คอลัมน์) */
    public $q;

    const SOURCE_CONTRACT = 'contract';
    const SOURCE_ORDER = 'order';
    const SOURCE_NONE = 'none';

    const TYPE_CONTRACT = 'contract';
    const TYPE_BID = 'bid';
    const TYPE_PERFORMANCE = 'performance';
    const TYPE_ADVANCE = 'advance';
    const TYPE_OTHER = 'other';

    const FORM_CASH = 'cash';
    const FORM_BANK_GUARANTEE = 'bank_guarantee';
    const FORM_GOV_BOND = 'gov_bond';
    const FORM_CHEQUE = 'cheque';
    const FORM_OTHER = 'other';

    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_RETURNED = 'returned';
    const STATUS_SEIZED = 'seized';
    const STATUS_EXEMPT = 'exempt';

    public static function tableName()
    {
        return 'purchase_bond';
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
            [['title'], 'required', 'message' => 'กรุณากรอกรายการ/โครงการที่หลักประกันนี้ค้ำอยู่'],
            [['thai_year', 'source_id', 'department_id', 'emp_id'], 'integer'],
            [['base_amount', 'amount'], 'number', 'min' => 0],
            [['rate'], 'number', 'min' => 0, 'max' => 100],
            [['place_date', 'expiry_date', 'return_date'], 'date', 'format' => 'php:Y-m-d'],
            [['source_type'], 'in', 'range' => array_keys(self::sourceTypeList())],
            [['bond_type'], 'in', 'range' => array_keys(self::typeList())],
            [['bond_form'], 'in', 'range' => array_keys(self::formList())],
            [['status'], 'in', 'range' => array_keys(self::statusList())],
            [['title', 'vendor_id', 'vendor_name', 'issuer', 'exempt_reason'], 'string', 'max' => 255],
            [['doc_ref', 'return_doc_no'], 'string', 'max' => 100],
            [['doc_no'], 'string', 'max' => 50],
            [['note', 'return_note'], 'string'],
            [['data_json', 'q'], 'safe'],
            // skipOnEmpty=false จำเป็นทุกข้อ เพราะตัวตรวจชุดนี้มีหน้าที่ทักเมื่อ "ไม่ได้กรอก"
            // ค่าปริยายของ inline validator คือข้ามให้เมื่อช่องว่าง ซึ่งจะทำให้ไม่มีอะไรทักเลย
            [['amount'], 'validateAmount', 'skipOnEmpty' => false],
            [['exempt_reason'], 'validateExemptReason', 'skipOnEmpty' => false],
            [['expiry_date'], 'validateExpiry', 'skipOnEmpty' => false],
            [['return_date'], 'validateReturn', 'skipOnEmpty' => false],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['source_type'], 'default', 'value' => self::SOURCE_NONE],
            [['bond_type'], 'default', 'value' => self::TYPE_CONTRACT],
            [['bond_form'], 'default', 'value' => self::FORM_BANK_GUARANTEE],
            [['amount'], 'default', 'value' => 0],
        ];
    }

    /** หลักประกันที่มีตัวตนจริงต้องมีวงเงิน ยกเว้นใบที่บันทึกไว้ว่าได้รับการยกเว้น */
    public function validateAmount($attribute)
    {
        if ($this->status === self::STATUS_EXEMPT) {
            return;
        }
        if ((float) $this->amount <= 0) {
            $this->addError($attribute, 'กรุณากรอกวงเงินหลักประกัน');
        }
    }

    /**
     * การยกเว้นต้องมีเหตุผลกำกับเสมอ
     * เกณฑ์ในตารางบอกได้แค่ว่า "ยกเว้นได้" ส่วนการใช้ดุลพินิจยกเว้นจริงเป็นเรื่องที่
     * ต้องอธิบายได้ตอนถูกตรวจสอบ ทะเบียนจึงต้องเก็บเหตุผลไว้ ไม่ใช่แค่สถานะ
     */
    public function validateExemptReason($attribute)
    {
        if ($this->status !== self::STATUS_EXEMPT) {
            return;
        }
        if (trim((string) $this->exempt_reason) === '') {
            $this->addError($attribute, 'กรุณาระบุเหตุผลที่ได้รับการยกเว้น');
        }
    }

    public function validateExpiry($attribute)
    {
        if (empty($this->expiry_date) || empty($this->place_date)) {
            return;
        }
        if (strtotime($this->expiry_date) < strtotime($this->place_date)) {
            $this->addError($attribute, 'วันสิ้นอายุต้องไม่มาก่อนวันที่วางหลักประกัน');
        }
    }

    public function validateReturn($attribute)
    {
        if ($this->status === self::STATUS_RETURNED && empty($this->return_date)) {
            $this->addError($attribute, 'กรุณาระบุวันที่คืนหลักประกัน');
        }
        if (!empty($this->return_date) && !empty($this->place_date)
            && strtotime($this->return_date) < strtotime($this->place_date)) {
            $this->addError($attribute, 'วันที่คืนต้องไม่มาก่อนวันที่วางหลักประกัน');
        }
    }

    public function attributeLabels()
    {
        return [
            'doc_no' => 'เลขที่ในระบบ',
            'thai_year' => 'ปีงบประมาณ',
            'title' => 'รายการ/โครงการ',
            'source_type' => 'ผูกกับเอกสาร',
            'source_id' => 'เอกสารต้นทาง',
            'vendor_id' => 'ผู้วางหลักประกัน',
            'vendor_name' => 'ชื่อผู้วางหลักประกัน',
            'bond_type' => 'ประเภทหลักประกัน',
            'bond_form' => 'รูปแบบหลักประกัน',
            'doc_ref' => 'เลขที่หนังสือ/หลักฐาน',
            'issuer' => 'ธนาคาร/ผู้ออก',
            'base_amount' => 'วงเงินที่ใช้เป็นฐาน',
            'rate' => 'อัตรา (%)',
            'amount' => 'วงเงินหลักประกัน',
            'place_date' => 'วันที่วางหลักประกัน',
            'expiry_date' => 'วันสิ้นอายุ',
            'status' => 'สถานะ',
            'exempt_reason' => 'เหตุผลที่ยกเว้น',
            'return_date' => 'วันที่คืน',
            'return_doc_no' => 'เลขที่หนังสือคืน',
            'return_note' => 'บันทึกการคืน/การยึด',
            'note' => 'หมายเหตุภายใน',
            'department_id' => 'หน่วยงาน',
            'emp_id' => 'ผู้บันทึก',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (empty($this->thai_year)) {
            $this->thai_year = (int) AppHelper::YearBudget();
        }
        if ($insert && empty($this->doc_no)) {
            $this->doc_no = \mdm\autonumber\AutoNumber::generate('BD-' . $this->thai_year . '-????');
        }
        if (empty($this->ref)) {
            $this->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        }

        // ไม่ผูกเอกสารต้นทาง ก็ต้องไม่มี id ค้างอยู่ ไม่งั้นทะเบียนจะโยงไปหาเอกสารผิดฉบับ
        if ($this->source_type === self::SOURCE_NONE) {
            $this->source_id = null;
        }

        // ชื่อผู้วางเก็บเป็น snapshot ด้วยเหตุผลเดียวกับสัญญา — ทะเบียนผู้ขายถูกแก้ทีหลัง
        // แล้วทะเบียนหลักประกันที่พิมพ์ไปแล้วต้องคงชื่อเดิม
        if (!empty($this->vendor_id) && empty($this->vendor_name)) {
            $this->vendor_name = (string) Categorise::find()
                ->select('title')
                ->where(['name' => 'vendor', 'code' => $this->vendor_id])
                ->scalar() ?: null;
        }

        if ($this->status === self::STATUS_EXEMPT) {
            $this->amount = 0;
        } else {
            $this->exempt_reason = null;
        }

        // ข้อมูลการคืนต้องหายไปพร้อมกับการถอนสถานะคืน ไม่งั้นทะเบียนจะมีใบที่ยัง
        // ไม่ได้คืนแต่มีวันที่คืนติดอยู่ ซึ่งอ่านแล้วไม่รู้ว่าอันไหนจริง
        if (!in_array($this->status, [self::STATUS_RETURNED, self::STATUS_SEIZED], true)) {
            $this->return_date = null;
            $this->return_doc_no = null;
        }

        return true;
    }

    // ── relation ────────────────────────────────────────────────────────────

    /**
     * เอกสารต้นทางฝั่งสัญญา
     *
     * เงื่อนไข source_type ต้องตัดสินในฝั่ง PHP ไม่ใช่ใส่เป็น onCondition ที่อ้าง
     * คอลัมน์ของ purchase_bond เพราะตอน lazy load Yii ยิงคิวรีไปที่ตารางปลายทาง
     * ตารางเดียว คอลัมน์ของตารางนี้จึงไม่มีอยู่ใน SQL นั้น
     */
    public function getContract()
    {
        $query = $this->hasOne(Contract::class, ['id' => 'source_id'])
            ->andOnCondition(['purchase_contract.deleted_at' => null]);

        if ($this->source_type !== self::SOURCE_CONTRACT) {
            $query->andOnCondition('0 = 1');
        }

        return $query;
    }

    public function getOrder()
    {
        $query = $this->hasOne(Order::class, ['id' => 'source_id'])
            ->andOnCondition(['orders.name' => 'order', 'orders.deleted_at' => null]);

        if ($this->source_type !== self::SOURCE_ORDER) {
            $query->andOnCondition('0 = 1');
        }

        return $query;
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    // ── ค่าคงที่และรายการอ้างอิง ─────────────────────────────────────────────

    public static function sourceTypeList(): array
    {
        return [
            self::SOURCE_NONE => 'ไม่ผูกกับเอกสารในระบบ',
            self::SOURCE_CONTRACT => 'สัญญา',
            self::SOURCE_ORDER => 'ใบสั่งซื้อ/จ้าง',
        ];
    }

    /** ประเภทตามกฎหมาย — เป็นตัวบอกว่าคืนเมื่อไร */
    public static function typeList(): array
    {
        return [
            self::TYPE_CONTRACT => 'หลักประกันสัญญา',
            self::TYPE_BID => 'หลักประกันซอง',
            self::TYPE_PERFORMANCE => 'หลักประกันผลงาน',
            self::TYPE_ADVANCE => 'หลักประกันการรับเงินล่วงหน้า',
            self::TYPE_OTHER => 'อื่น ๆ',
        ];
    }

    /** รูปแบบที่วาง — เป็นตัวบอกว่าคืนอย่างไร */
    public static function formList(): array
    {
        return [
            self::FORM_CASH => 'เงินสด',
            self::FORM_BANK_GUARANTEE => 'หนังสือค้ำประกันของธนาคาร',
            self::FORM_GOV_BOND => 'พันธบัตรรัฐบาลไทย',
            self::FORM_CHEQUE => 'เช็คที่ธนาคารรับรอง',
            self::FORM_OTHER => 'อื่น ๆ',
        ];
    }

    public static function statusList(): array
    {
        return [
            self::STATUS_PENDING => 'ยังไม่วาง',
            self::STATUS_ACTIVE => 'วางแล้ว',
            self::STATUS_RETURNED => 'คืนแล้ว',
            self::STATUS_SEIZED => 'ยึดเป็นรายได้แผ่นดิน',
            self::STATUS_EXEMPT => 'ได้รับการยกเว้น',
        ];
    }

    public static function statusBadge($status): array
    {
        $map = [
            self::STATUS_PENDING => ['label' => 'ยังไม่วาง', 'color' => 'warning'],
            self::STATUS_ACTIVE => ['label' => 'วางแล้ว', 'color' => 'primary'],
            self::STATUS_RETURNED => ['label' => 'คืนแล้ว', 'color' => 'success'],
            self::STATUS_SEIZED => ['label' => 'ยึดแล้ว', 'color' => 'dark'],
            self::STATUS_EXEMPT => ['label' => 'ยกเว้น', 'color' => 'secondary'],
        ];
        return $map[$status] ?? ['label' => $status ?: '-', 'color' => 'secondary'];
    }

    /**
     * สถานะที่ถือว่าเรื่องยังเดินอยู่ — ใช้ตัดสินว่าสัญญาฉบับหนึ่งมีหลักประกันแล้วหรือยัง
     * ใบที่คืนหรือยึดไปแล้วไม่นับ เพราะของถูกส่งคืน/ตกเป็นรายได้แผ่นดินไปแล้ว
     */
    public static function openStatuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_ACTIVE, self::STATUS_EXEMPT];
    }

    /** สถานะที่ถือว่าเรื่องปิดแล้ว ไม่ต้องเตือนเรื่องวันหมดอายุอีก */
    public static function closedStatuses(): array
    {
        return [self::STATUS_RETURNED, self::STATUS_SEIZED, self::STATUS_EXEMPT];
    }

    public function typeName(): string
    {
        return self::typeList()[$this->bond_type] ?? $this->bond_type;
    }

    /**
     * ชื่อรูปแบบหลักประกันที่ใช้แสดง
     *
     * ห้ามตั้งชื่อเมธอดนี้ว่า formName() เพราะไปทับ yii\base\Model::formName() ซึ่ง Yii
     * ใช้เป็นชื่อฟอร์มตอน load() และตอนตั้งชื่อ input ทุกช่อง การทับทำให้ทั้งฟอร์มบันทึก
     * และฟอร์มค้นหาพังทั้งหน้า
     */
    public function bondFormName(): string
    {
        return self::formList()[$this->bond_form] ?? (string) $this->bond_form;
    }

    public function statusName(): string
    {
        return self::statusBadge($this->status)['label'];
    }

    public static function listVendor(): array
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
    public static function listThaiYear(): array
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

    // ── อายุของหลักประกัน ───────────────────────────────────────────────────

    public function expiryState(): string
    {
        return BondCalculator::expiryState($this->expiry_date, $this->status, self::closedStatuses());
    }

    public function daysToExpiry(): ?int
    {
        return BondCalculator::daysToExpiry($this->expiry_date);
    }

    public function isExpired(): bool
    {
        return $this->expiryState() === BondCalculator::STATE_EXPIRED;
    }

    public function isNearExpiry(): bool
    {
        return $this->expiryState() === BondCalculator::STATE_NEAR;
    }

    // ── เอกสารต้นทาง ────────────────────────────────────────────────────────

    /** ข้อความสั้น ๆ บอกว่าผูกอยู่กับเอกสารฉบับไหน */
    public function sourceLabel(): string
    {
        if ($this->source_type === self::SOURCE_CONTRACT) {
            $contract = $this->contract;
            return $contract
                ? ($contract->contract_no ?: ($contract->doc_no ?: ('สัญญา id ' . $this->source_id)))
                : 'สัญญาที่ถูกลบไปแล้ว';
        }
        if ($this->source_type === self::SOURCE_ORDER) {
            $order = $this->order;
            return $order
                ? ($order->po_number ?: ('ใบสั่งซื้อ id ' . $this->source_id))
                : 'ใบสั่งซื้อที่ถูกลบไปแล้ว';
        }
        return '—';
    }

    /** ลิงก์ไปเอกสารต้นทาง คืน null เมื่อไม่ได้ผูกหรือหาไม่พบ */
    public function sourceUrl(): ?array
    {
        if ($this->source_type === self::SOURCE_CONTRACT && $this->source_id) {
            return ['/purchase/contract/view', 'id' => $this->source_id];
        }
        return null;
    }

    public function empName(): string
    {
        try {
            return $this->employee ? $this->employee->fullname() : '-';
        } catch (\Throwable $e) {
            return '-';
        }
    }

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

    /** หลักประกันของเอกสารฉบับหนึ่ง เรียงใบที่ยังเดินอยู่ขึ้นก่อน */
    public static function forSource(string $sourceType, $sourceId): array
    {
        if (empty($sourceId)) {
            return [];
        }
        return self::find()
            ->where([
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'deleted_at' => null,
            ])
            ->orderBy(['id' => SORT_ASC])
            ->all();
    }

    /** ไฟล์แนบของหลักประกันใบนี้ (ใช้ ref เดียวกับระบบไฟล์กลาง) */
    public function upload($name = null)
    {
        return \app\modules\filemanager\components\FileManagerHelper::FileUpload($this->ref, $name);
    }
}
