<?php

namespace app\modules\purchase\models;

use Yii;
use yii\db\Expression;
use app\components\AppHelper;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * เอกสารที่สร้างจากแม่แบบแล้ว 1 ฉบับ
 *
 * body_html ของแถวนี้เป็น "ฉบับจริง" ไม่ใช่การอ้างอิงกลับไปที่แม่แบบ ตอนสร้างเอกสาร
 * ระบบ merge ค่าจากเรื่องต้นทางลงในข้อความของแม่แบบแล้วหยุดไว้ที่นี่ การแก้แม่แบบ
 * ภายหลังจึงไม่ย้อนไปเปลี่ยนข้อความในหนังสือที่ลงนามไปแล้ว ซึ่งเป็นเงื่อนไขที่ทำให้
 * เอกสารใช้เป็นหลักฐานได้
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $doc_no
 * @property int $thai_year
 * @property string|null $doc_date
 * @property int|null $template_id
 * @property string|null $template_code
 * @property string $title
 * @property string $ref_type order|contract|bond|none
 * @property int|null $ref_id
 * @property string|null $body_html
 * @property string $orientation
 * @property string $emblem
 * @property int $font_size
 * @property array|string|null $margin_json
 * @property string $status draft|final
 * @property string|null $printed_at
 * @property int $print_count
 * @property string|null $note
 * @property int|null $department_id
 * @property int|null $emp_id
 */
class Doc extends \yii\db\ActiveRecord
{
    /** ช่องค้นหาอิสระ (ไม่ใช่คอลัมน์) */
    public $q;

    const STATUS_DRAFT = 'draft';
    const STATUS_FINAL = 'final';

    public static function tableName()
    {
        return 'purchase_doc';
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
            [['title'], 'required', 'message' => 'กรุณากรอกชื่อเอกสาร'],
            [['thai_year', 'template_id', 'ref_id', 'department_id', 'emp_id', 'print_count'], 'integer'],
            [['font_size'], 'integer', 'min' => 10, 'max' => 26],
            [['doc_date'], 'date', 'format' => 'php:Y-m-d'],
            [['ref_type'], 'in', 'range' => array_keys(DocTemplate::refTypeList())],
            [['status'], 'in', 'range' => array_keys(self::statusList())],
            [['emblem'], 'in', 'range' => array_keys(DocTemplate::emblemList())],
            [['orientation'], 'in', 'range' => ['portrait', 'landscape']],
            [['title'], 'string', 'max' => 255],
            [['doc_no'], 'string', 'max' => 50],
            [['template_code'], 'string', 'max' => 50],
            [['note', 'body_html'], 'string'],
            [['margin_json', 'data_json', 'q'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'doc_no' => 'เลขที่หนังสือ',
            'doc_date' => 'วันที่หนังสือ',
            'thai_year' => 'ปีงบประมาณ',
            'title' => 'ชื่อเอกสาร',
            'ref_type' => 'ผูกกับเรื่อง',
            'ref_id' => 'เรื่องต้นทาง',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
            'emblem' => 'ตราครุฑ',
            'font_size' => 'ขนาดฟอนต์ (pt)',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // กรอง HTML ที่ส่งมาจากหน้าแก้ไขทุกครั้ง — เนื้อหาที่นี่มาจาก contenteditable
        // ในเบราว์เซอร์ผู้ใช้ ซึ่งวางอะไรลงไปก็ได้รวมทั้ง HTML ที่ก๊อปมาจากเว็บอื่น
        if (!empty($this->body_html)) {
            $this->body_html = \yii\helpers\HtmlPurifier::process($this->body_html, [
                'HTML.Allowed' => DocTemplate::ALLOWED_HTML,
                'AutoFormat.RemoveEmpty' => false,
            ]);

            // เก็บตราครุฑเป็น {{emblem}} เสมอ ไม่เก็บ <img> ที่หน้าแก้ไขส่งกลับมา
            // เพื่อให้ชั้นเรนเดอร์แต่ละตัวเลือก path ที่ใช้ได้ของตัวเอง (จอใช้ URL,
            // mPDF กับ PhpWord ต้องใช้ path ในเครื่อง)
            $this->body_html = \app\modules\purchase\components\DocRenderer::normalize($this->body_html);
        }

        if (empty($this->thai_year)) {
            $this->thai_year = (int) AppHelper::YearBudget();
        }
        if (empty($this->doc_date)) {
            $this->doc_date = date('Y-m-d');
        }
        if ($this->isNewRecord && empty($this->ref)) {
            $this->ref = 'purchase_doc_' . uniqid();
        }

        return true;
    }

    /**
     * ปีงบประมาณที่มีเอกสารอยู่ — ชุดเดียวกับที่ Bond::listThaiYear() ทำ
     *
     * ใส่ปีปัจจุบันเข้าไปเสมอแม้ยังไม่มีเอกสารของปีนั้น ไม่งั้นตัวกรองปีจะไม่มีปี
     * ที่ผู้ใช้กำลังทำงานอยู่ให้เลือกในวันแรกของปีงบใหม่
     */
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

    public static function statusList(): array
    {
        return [
            self::STATUS_DRAFT => 'ร่าง',
            self::STATUS_FINAL => 'ออกเลขแล้ว',
        ];
    }

    public function statusName(): string
    {
        return self::statusList()[$this->status] ?? $this->status;
    }

    /** สีป้ายสถานะบนทะเบียน — ร่างยังแก้ได้จึงเป็นกลาง ออกเลขแล้วถือว่าปิด */
    public function statusColor(): string
    {
        return $this->status === self::STATUS_FINAL ? 'success' : 'secondary';
    }

    public function getTemplate()
    {
        return $this->hasOne(DocTemplate::class, ['id' => 'template_id']);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    /**
     * เรคคอร์ดต้นทางที่เอกสารนี้ผูกอยู่
     *
     * คืน null เมื่อไม่ผูกเรื่อง หรือเมื่อเรื่องต้นทางถูกลบไปแล้ว — เอกสารที่ออกไปแล้ว
     * ต้องยังพิมพ์ซ้ำได้แม้ใบขอซื้อจะถูกลบ เพราะค่าที่ merge แล้วอยู่ใน body_html
     * ไม่ได้อ่านสดจากเรื่องต้นทางตอนพิมพ์
     */
    public function refModel()
    {
        if (empty($this->ref_id)) {
            return null;
        }

        switch ($this->ref_type) {
            case DocTemplate::REF_ORDER:
                return Order::findOne(['id' => $this->ref_id]);
            case DocTemplate::REF_CONTRACT:
                return Contract::findOne(['id' => $this->ref_id]);
            case DocTemplate::REF_BOND:
                return Bond::findOne(['id' => $this->ref_id]);
        }

        return null;
    }

    /** ข้อความสั้น ๆ บอกว่าเอกสารนี้ออกจากเรื่องไหน ใช้บนทะเบียน */
    public function refLabel(): string
    {
        if ($this->ref_type === DocTemplate::REF_NONE || empty($this->ref_id)) {
            return '-';
        }

        $model = $this->refModel();
        if ($model === null) {
            return DocTemplate::refTypeList()[$this->ref_type] . ' #' . $this->ref_id . ' (ถูกลบแล้ว)';
        }

        switch ($this->ref_type) {
            case DocTemplate::REF_ORDER:
                return 'ใบขอซื้อ ' . ($model->pr_number ?: $model->po_number ?: '#' . $model->id);
            case DocTemplate::REF_CONTRACT:
                return 'สัญญา ' . ($model->contract_no ?: $model->doc_no ?: '#' . $model->id);
            case DocTemplate::REF_BOND:
                return 'หลักประกัน ' . ($model->doc_no ?: '#' . $model->id);
        }

        return '-';
    }

    /** ค่าขอบกระดาษหน่วยมิลลิเมตร — เติมค่าที่หายไปด้วยขอบมาตรฐานงานสารบรรณ */
    public function margins(): array
    {
        $default = ['top' => 25, 'right' => 20, 'bottom' => 20, 'left' => 30];
        $saved = is_array($this->margin_json)
            ? $this->margin_json
            : (json_decode((string) $this->margin_json, true) ?: []);

        return array_map('intval', array_merge($default, array_intersect_key($saved, $default)));
    }

    /**
     * บันทึกว่าถูกพิมพ์แล้ว
     *
     * ใช้ updateAttributes ไม่ใช่ save() เพราะการพิมพ์ไม่ควรวิ่งผ่าน validation
     * ของทั้งเรคคอร์ด — ถ้าเอกสารเก่ามีข้อมูลที่ไม่ผ่านกฎที่เพิ่มมาภายหลัง
     * ผู้ใช้จะพิมพ์ซ้ำไม่ได้ทั้งที่แค่ต้องการกระดาษอีกใบ
     */
    public function markPrinted(): void
    {
        $this->updateAttributes([
            'printed_at' => date('Y-m-d H:i:s'),
            'print_count' => (int) $this->print_count + 1,
        ]);
    }

    /** ชื่อไฟล์ที่ปลอดภัยกับทุกระบบไฟล์ ใช้ตอนส่งออก */
    public function safeFileName(string $extension): string
    {
        $name = preg_replace('/[\\\\\/:*?"<>|]/u', '', $this->title);
        if ($this->doc_no) {
            $name .= '_' . preg_replace('/[\\\\\/:*?"<>|]/u', '-', $this->doc_no);
        }

        return trim($name) . '.' . $extension;
    }
}
