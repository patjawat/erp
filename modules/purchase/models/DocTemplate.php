<?php

namespace app\modules\purchase\models;

use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * แม่แบบเอกสาร 1 ฉบับ
 *
 * เก็บ "ข้อความ" ของเอกสารไว้ในฐานข้อมูล ต่างจากระบบพิมพ์เดิมของ ERP
 * (/ms-word/purchase_1..12) ที่เก็บแม่แบบเป็นไฟล์ .docx ใน web/msword แล้วแทนเฉพาะ
 * ค่าด้วย TemplateProcessor ระบบเดิมยังอยู่ครบและไม่ได้ถูกแตะ — ตารางนี้เป็นทางที่สอง
 * ที่เดินขนานกันไป โดยแลกความเหมือน Word เป๊ะ ๆ กับความสามารถแก้ข้อความเองได้
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $category buy|contract|bond|tor|general
 * @property string $ref_type order|contract|bond|none
 * @property string|null $body_html
 * @property string $orientation portrait|landscape
 * @property string $emblem none|1.5|3.0
 * @property int $font_size
 * @property array|string|null $margin_json
 * @property string|null $law_ref
 * @property string|null $note
 * @property int $active
 * @property int $sort_order
 */
class DocTemplate extends \yii\db\ActiveRecord
{
    /** ช่องค้นหาอิสระ (ไม่ใช่คอลัมน์) */
    public $q;

    const REF_ORDER = 'order';
    const REF_CONTRACT = 'contract';
    const REF_BOND = 'bond';
    const REF_NONE = 'none';

    const EMBLEM_NONE = 'none';
    const EMBLEM_SMALL = '1.5';
    const EMBLEM_LARGE = '3.0';

    /**
     * แท็ก HTML ที่ยอมให้อยู่ในแม่แบบและในเอกสาร
     *
     * ชุดเดียวกับ Tor::ALLOWED_HTML แต่ตัด h4-h6 ออกและเพิ่ม table[class] กับ
     * td[class|colspan|rowspan] เข้ามา เพราะเอกสารราชการจัดวางด้วยตารางเป็นหลัก
     * และ class เป็นตัวคุมระยะ/เส้นขอบ ถ้า HTMLPurifier ตัด class ทิ้ง
     * เอกสารจะเหลือแต่ตารางเปล่าไม่มีเส้นและระยะเพี้ยนทั้งฉบับ
     */
    const ALLOWED_HTML = 'p[class],br,strong,b,em,i,u,s,span[class],ol,ul,li,'
        . 'table[class],thead,tbody,tr[class],td[class|colspan|rowspan],th[class|colspan|rowspan],sub,sup';

    /** ช่องที่เก็บ HTML — ต้องผ่าน purifier ก่อนบันทึกทุกครั้ง */
    const HTML_FIELDS = ['body_html'];

    public static function tableName()
    {
        return 'purchase_doc_template';
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
            [['name'], 'required', 'message' => 'กรุณากรอกชื่อเอกสาร'],
            [['code'], 'required', 'message' => 'กรุณากรอกรหัสแม่แบบ'],
            [['code'], 'match', 'pattern' => '/^[a-z0-9_]+$/',
                'message' => 'รหัสแม่แบบใช้ได้เฉพาะ a-z 0-9 และ _ (ห้ามเว้นวรรคและอักษรไทย)'],
            [['code'], 'unique', 'message' => 'รหัสแม่แบบนี้มีอยู่แล้ว'],
            [['code'], 'string', 'max' => 50],
            [['legacy_key'], 'string', 'max' => 30],
            [['name', 'law_ref'], 'string', 'max' => 255],
            [['category'], 'in', 'range' => array_keys(self::categoryList())],
            [['ref_type'], 'in', 'range' => array_keys(self::refTypeList())],
            [['orientation'], 'in', 'range' => ['portrait', 'landscape']],
            [['emblem'], 'in', 'range' => array_keys(self::emblemList())],
            [['font_size'], 'integer', 'min' => 10, 'max' => 26],
            [['active', 'sort_order'], 'integer'],
            [['note', 'body_html'], 'string'],
            [['margin_json', 'data_json', 'q'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => 'รหัสแม่แบบ',
            'name' => 'ชื่อเอกสาร',
            'category' => 'หมวด',
            'ref_type' => 'ดึงข้อมูลจาก',
            'body_html' => 'เนื้อเอกสาร',
            'orientation' => 'แนวกระดาษ',
            'emblem' => 'ตราครุฑ',
            'font_size' => 'ขนาดฟอนต์ (pt)',
            'law_ref' => 'ระเบียบ/หนังสือเวียนที่อ้าง',
            'note' => 'หมายเหตุ',
            'active' => 'เปิดใช้',
            'sort_order' => 'ลำดับ',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // กรอง HTML ก่อนลงฐานเสมอ ไม่ใช่กรองตอนแสดง — เนื้อเอกสารถูกอ่านออกไปใช้
        // ทั้งบนจอ ใน mPDF และใน PhpWord ถ้ากรองตอนแสดงจะต้องไปกรองซ้ำสามที่
        // และที่ไหนลืมก็กลายเป็นช่องให้สคริปต์หลุดเข้าไป
        foreach (self::HTML_FIELDS as $field) {
            if (!empty($this->$field)) {
                $this->$field = \yii\helpers\HtmlPurifier::process($this->$field, [
                    'HTML.Allowed' => self::ALLOWED_HTML,
                    'AutoFormat.RemoveEmpty' => false,
                ]);
            }
        }

        if ($this->isNewRecord && empty($this->sort_order)) {
            $this->sort_order = (int) self::find()->max('sort_order') + 10;
        }

        return true;
    }

    /**
     * ค่าขอบกระดาษหน่วยมิลลิเมตร — เติมค่าที่หายไปด้วยขอบมาตรฐานงานสารบรรณ
     *
     * ขอบซ้ายกว้างกว่าขวาเพราะหนังสือราชการต้องเจาะรูเก็บแฟ้ม
     */
    public function margins(): array
    {
        $default = ['top' => 25, 'right' => 20, 'bottom' => 20, 'left' => 30];
        $saved = is_array($this->margin_json)
            ? $this->margin_json
            : (json_decode((string) $this->margin_json, true) ?: []);

        return array_map('intval', array_merge($default, array_intersect_key($saved, $default)));
    }

    public static function categoryList(): array
    {
        return [
            'buy' => 'จัดซื้อจัดจ้าง',
            'contract' => 'บริหารสัญญา',
            'bond' => 'หลักประกัน',
            'tor' => 'TOR',
            'general' => 'ทั่วไป',
        ];
    }

    public static function refTypeList(): array
    {
        return [
            self::REF_ORDER => 'ใบขอซื้อ/ใบสั่งซื้อ',
            self::REF_CONTRACT => 'สัญญา',
            self::REF_BOND => 'หลักประกัน',
            self::REF_NONE => 'ไม่ผูกเรื่อง (กรอกเองทั้งหมด)',
        ];
    }

    /** ขนาดตราครุฑที่เลือกได้ — คีย์คือความสูงเป็นเซนติเมตร */
    public static function emblemList(): array
    {
        return [
            self::EMBLEM_NONE => 'ไม่แสดงตราครุฑ',
            self::EMBLEM_SMALL => 'ครุฑ 1.5 ซม. (บันทึกข้อความ)',
            self::EMBLEM_LARGE => 'ครุฑ 3 ซม. (หนังสือภายนอก/ประกาศ)',
        ];
    }

    public function categoryName(): string
    {
        return self::categoryList()[$this->category] ?? $this->category;
    }

    public function refTypeName(): string
    {
        return self::refTypeList()[$this->ref_type] ?? $this->ref_type;
    }

    /** แม่แบบที่เปิดใช้ จัดกลุ่มตามหมวดไว้ใช้กับ dropdown เลือกเอกสาร */
    public static function pickList(): array
    {
        $rows = self::find()
            ->where(['active' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row->categoryName()][$row->id] = $row->name;
        }

        return $grouped;
    }

    /**
     * แม่แบบที่ผูกกับเอกสารพิมพ์ชุดเดิม คีย์ด้วย legacy_key
     *
     * ใช้ที่หน้าเมนูพิมพ์เอกสารในรายการจัดซื้อจัดจ้าง เพื่อรู้ว่าเอกสารใบไหนแปลงเป็น
     * แม่แบบ HTML แล้ว (พาไปหน้าแก้ไขได้) และใบไหนยังไม่แปลง (ใช้ทางเดิมต่อ)
     *
     * @return array<string, self> legacy_key => แม่แบบ
     */
    public static function byLegacyKey(): array
    {
        $out = [];
        foreach (self::find()->where(['active' => 1])->andWhere(['not', ['legacy_key' => null]])->all() as $row) {
            $out[$row->legacy_key] = $row;
        }

        return $out;
    }

    /** จำนวนเอกสารที่ออกจากแม่แบบนี้ — ใช้เตือนก่อนลบ */
    public function docCount(): int
    {
        return (int) Doc::find()
            ->where(['template_id' => $this->id, 'deleted_at' => null])
            ->count();
    }
}
