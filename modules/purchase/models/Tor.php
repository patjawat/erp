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

/**
 * TOR / ข้อกำหนดคุณลักษณะ — หัวเอกสาร
 *
 * @property int $id
 * @property string|null $doc_no เลขที่เอกสาร
 * @property int $thai_year ปีงบประมาณ
 * @property string $title ชื่อโครงการ/รายการพัสดุ
 * @property string|null $asset_type_id ประเภทพัสดุ
 * @property string|null $purchase_method วิธีจัดซื้อจัดจ้าง
 * @property float $budget วงเงินงบประมาณ
 * @property string|null $tor_date วันที่จัดทำ
 * @property string|null $egp_no เลขที่โครงการ e-GP
 * @property string $status draft|submitted|approved|cancelled
 * @property string|null $purpose วัตถุประสงค์ (HTML)
 * @property float|null $qty จำนวน
 * @property string|null $unit_name หน่วยนับ
 * @property string|null $spec คุณลักษณะเฉพาะ (HTML)
 * @property string|null $standard มาตรฐาน/การรับรอง (HTML)
 * @property string|null $warranty การรับประกัน (HTML)
 * @property int|null $delivery_days ระยะเวลาส่งมอบ
 * @property string|null $delivery_place สถานที่ส่งมอบ
 * @property string|null $delivery_term เงื่อนไขการส่งมอบ (HTML)
 * @property string|null $payment_term เงื่อนไขการชำระเงิน (HTML)
 * @property string|null $vendor_qualification คุณสมบัติผู้เสนอราคา (HTML)
 * @property float $mid_price ราคากลาง
 * @property string|null $mid_method วิธีกำหนดราคากลาง
 * @property string|null $mid_note หมายเหตุราคากลาง
 * @property int|null $department_id หน่วยงาน
 * @property int|null $emp_id ผู้จัดทำ
 */
class Tor extends \yii\db\ActiveRecord
{
    /** ช่องค้นหาอิสระ (ไม่ใช่คอลัมน์) */
    public $q;

    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_APPROVED = 'approved';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * ช่องเนื้อความที่ผู้ใช้จัดรูปแบบเองได้ (เก็บ HTML)
     * ทุกช่องต้องผ่าน HtmlPurifier ก่อนบันทึกเสมอ — เนื้อหานี้ถูกนำไปแสดงบนหน้าจอผู้ใช้คนอื่น
     * (หัวหน้า/จนท.พัสดุ) ถ้าไม่กรองจะเปิดช่อง stored XSS
     */
    const HTML_FIELDS = [
        'purpose',
        'spec',
        'standard',
        'warranty',
        'delivery_term',
        'payment_term',
        'vendor_qualification',
    ];

    /**
     * แท็กที่อนุญาต — จำกัดเฉพาะที่ PhpWord แปลงลงไฟล์ Word ได้จริง
     * เพื่อไม่ให้ผู้ใช้จัดรูปแบบสวยบนเว็บแล้วพิมพ์ออกมาไม่เหมือน
     */
    const ALLOWED_HTML = 'p,br,strong,b,em,i,u,s,ol,ul,li,table,thead,tbody,tr,td[colspan|rowspan],th[colspan|rowspan],span,h4,h5,h6,sub,sup';

    public static function tableName()
    {
        return 'purchase_tor';
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
            [['title'], 'required', 'message' => 'กรุณากรอกชื่อโครงการ/รายการพัสดุ'],
            [['spec'], 'required', 'message' => 'กรุณากรอกคุณลักษณะเฉพาะ'],
            [['thai_year', 'delivery_days', 'department_id', 'emp_id'], 'integer'],
            [['budget', 'qty', 'mid_price'], 'number', 'min' => 0],
            [['tor_date'], 'date', 'format' => 'php:Y-m-d'],
            [['status'], 'in', 'range' => array_keys(self::statusList())],
            [['title', 'delivery_place'], 'string', 'max' => 255],
            [['doc_no', 'asset_type_id', 'purchase_method', 'egp_no', 'unit_name'], 'string', 'max' => 50],
            [['mid_method'], 'string', 'max' => 100],
            [array_merge(self::HTML_FIELDS, ['mid_note']), 'string'],
            [['data_json', 'q'], 'safe'],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['budget', 'mid_price'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'doc_no' => 'เลขที่ TOR',
            'thai_year' => 'ปีงบประมาณ',
            'title' => 'ชื่อโครงการ/รายการพัสดุ',
            'asset_type_id' => 'ประเภทพัสดุ',
            'purchase_method' => 'วิธีจัดซื้อจัดจ้าง',
            'budget' => 'วงเงินงบประมาณ',
            'tor_date' => 'วันที่จัดทำ TOR',
            'egp_no' => 'เลขที่โครงการ e-GP',
            'status' => 'สถานะ',
            'purpose' => 'วัตถุประสงค์และความจำเป็น',
            'qty' => 'จำนวน',
            'unit_name' => 'หน่วยนับ',
            'spec' => 'คุณลักษณะเฉพาะ',
            'standard' => 'มาตรฐาน/การรับรองคุณภาพ',
            'warranty' => 'เงื่อนไขการรับประกัน',
            'delivery_days' => 'ระยะเวลาส่งมอบ (วันทำการ)',
            'delivery_place' => 'สถานที่ส่งมอบ',
            'delivery_term' => 'เงื่อนไขการส่งมอบ',
            'payment_term' => 'เงื่อนไขการชำระเงิน',
            'vendor_qualification' => 'คุณสมบัติผู้เสนอราคา',
            'mid_price' => 'ราคากลาง',
            'mid_method' => 'วิธีกำหนดราคากลาง',
            'mid_note' => 'หมายเหตุ',
            'department_id' => 'หน่วยงาน',
            'emp_id' => 'ผู้จัดทำ',
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
        if (empty($this->tor_date)) {
            $this->tor_date = date('Y-m-d');
        }
        if ($insert && empty($this->doc_no)) {
            $this->doc_no = \mdm\autonumber\AutoNumber::generate('TOR-' . $this->thai_year . '-????');
        }

        return true;
    }

    // ── relation ────────────────────────────────────────────────────────────

    public function getPrices()
    {
        return $this->hasMany(TorPrice::class, ['tor_id' => 'id'])->orderBy(['seq' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getAssetType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'asset_type_id'])->andOnCondition(['name' => 'asset_type']);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    // ── ข้อมูลอ้างอิง (ใช้ทะเบียนกลางที่มีอยู่ ไม่สร้างชุดใหม่) ────────────────

    public static function statusList()
    {
        return [
            self::STATUS_DRAFT => 'ร่าง',
            self::STATUS_SUBMITTED => 'รออนุมัติ',
            self::STATUS_APPROVED => 'อนุมัติแล้ว',
            self::STATUS_CANCELLED => 'ยกเลิก',
        ];
    }

    /** สี badge ของสถานะ — ให้หน้า list/view ใช้ร่วมกัน จะได้ไม่มี palette แยกรายหน้า */
    public static function statusBadge($status)
    {
        $map = [
            self::STATUS_DRAFT => ['label' => 'ร่าง', 'color' => 'secondary'],
            self::STATUS_SUBMITTED => ['label' => 'รออนุมัติ', 'color' => 'warning'],
            self::STATUS_APPROVED => ['label' => 'อนุมัติแล้ว', 'color' => 'success'],
            self::STATUS_CANCELLED => ['label' => 'ยกเลิก', 'color' => 'danger'],
        ];
        return $map[$status] ?? ['label' => $status ?: '-', 'color' => 'secondary'];
    }

    public function statusName()
    {
        return self::statusBadge($this->status)['label'];
    }

    /** ประเภทพัสดุ — categorise name='asset_type' */
    public static function listAssetType()
    {
        return ArrayHelper::map(
            Categorise::find()->where(['name' => 'asset_type'])->orderBy(['title' => SORT_ASC])->all(),
            'code',
            'title'
        );
    }

    /** วิธีจัดซื้อจัดจ้าง — ชุดเดียวกับที่ใบขอซื้อใช้ (categorise name='purchase') */
    public static function listPurchaseMethod()
    {
        return ArrayHelper::map(
            Categorise::find()->where(['name' => 'purchase'])->all(),
            'code',
            'title'
        );
    }

    /** หน่วยนับ — categorise name='unit' เก็บเป็น title ตามแบบที่โมดูล sm ใช้อยู่ */
    public static function listUnit()
    {
        return ArrayHelper::map(
            Categorise::find()->where(['name' => 'unit'])->all(),
            'title',
            'title'
        );
    }

    public static function listMidMethod()
    {
        $items = [
            'ค่าเฉลี่ยของราคาที่สืบได้',
            'ราคาต่ำสุดที่สืบได้',
            'อ้างอิงราคามาตรฐานที่ทางราชการกำหนด',
            'อ้างอิงราคาที่เคยซื้อ/จ้างครั้งหลังสุด',
            'อ้างอิงราคาของหน่วยงานอื่น',
        ];
        return array_combine($items, $items);
    }

    public function assetTypeName()
    {
        return $this->assetType->title ?? '-';
    }

    public function purchaseMethodName()
    {
        if (empty($this->purchase_method)) {
            return '-';
        }
        return (string) Categorise::find()
            ->select('title')
            ->where(['name' => 'purchase', 'code' => $this->purchase_method])
            ->scalar() ?: '-';
    }

    public function empName()
    {
        try {
            return $this->employee ? $this->employee->fullname() : '-';
        } catch (\Throwable $e) {
            return '-';
        }
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

    /**
     * ราคากลางที่คำนวณจากใบสืบราคา — ใช้ตรวจซ้ำฝั่งเซิร์ฟเวอร์
     * นับเฉพาะแถวที่มีราคามากกว่า 0 (แถวว่างไม่ถือเป็นการสืบราคา)
     */
    public function calcMidPrice($method = null)
    {
        $prices = array_values(array_filter(
            ArrayHelper::getColumn($this->prices, 'price'),
            fn($p) => (float) $p > 0
        ));
        if (!$prices) {
            return 0.0;
        }
        $method = $method ?? $this->mid_method;
        if (strpos((string) $method, 'ต่ำสุด') !== false) {
            return round((float) min($prices), 2);
        }
        return round(array_sum($prices) / count($prices), 2);
    }

    /** จำนวนแหล่งที่สืบราคาแล้วจริง — ระเบียบกำหนดไม่น้อยกว่า 3 */
    public function countPriceSources()
    {
        return count(array_filter(
            ArrayHelper::getColumn($this->prices, 'price'),
            fn($p) => (float) $p > 0
        ));
    }
}
