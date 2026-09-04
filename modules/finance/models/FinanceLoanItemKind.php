<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * ประเภทรายการค่าใช้จ่ายในใบประมาณการ
 *
 * เพิ่มเองได้จากหน้าตั้งค่า เพราะเงินยืมโครงการมีรายการนอกเหนือจาก
 * เบี้ยเลี้ยง–ที่พัก–พาหนะ อยู่เรื่อย ๆ เช่น ค่าอาหารกลางวัน ค่าวิทยากร
 *
 * register_column คือกาวที่เชื่อมกลับไปทะเบียนคุม ซึ่งมียอดแค่สี่ช่อง
 * รายการใหม่ทุกตัวจึงต้องบอกว่าไปรวมอยู่ช่องไหน ไม่งั้นพิมพ์ทะเบียนคุมไม่ได้
 */
class FinanceLoanItemKind extends ActiveRecord
{
    public const COL_ALLOWANCE = 'allowance';
    public const COL_ACCOMMODATION = 'accommodation';
    public const COL_TRANSPORT = 'transport';
    public const COL_OTHER = 'other';

    public static function tableName()
    {
        return '{{%finance_loan_item_kind}}';
    }

    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['code'], 'string', 'max' => 40],
            [['code'], 'match', 'pattern' => '/^[a-z0-9_]+$/', 'message' => 'ใช้ได้เฉพาะ a-z 0-9 และ _'],
            [['code'], 'unique'],
            [['name'], 'string', 'max' => 255],
            [['register_column'], 'in', 'range' => array_keys(self::registerColumnOptions())],
            [['register_column'], 'default', 'value' => self::COL_OTHER],
            [['has_persons', 'has_units', 'is_active'], 'boolean'],
            [['has_persons', 'has_units'], 'default', 'value' => false],
            [['is_active'], 'default', 'value' => true],
            [['unit_name', 'person_unit_name'], 'string', 'max' => 30],
            [['sort_order'], 'integer'],
            [['sort_order'], 'default', 'value' => 0],
            [['unit_name'], 'required', 'when' => fn(self $m) => (bool) $m->has_units,
                'whenClient' => 'function (attribute, value) { return $("#financeloanitemkind-has_units").is(":checked"); }',
                'message' => 'ระบุชื่อหน่วยเมื่อเปิดใช้ช่องจำนวนหน่วย'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => 'รหัส',
            'name' => 'ชื่อรายการ',
            'register_column' => 'รวมยอดในทะเบียนคุมช่อง',
            'has_persons' => 'มีช่องจำนวนคน/ห้อง',
            'has_units' => 'มีช่องจำนวนหน่วย',
            'person_unit_name' => 'หน่วยของช่องแรก',
            'unit_name' => 'ชื่อหน่วย',
            'is_active' => 'เปิดใช้งาน',
            'sort_order' => 'ลำดับ',
        ];
    }

    /** สี่ช่องยอดในทะเบียนคุมลูกหนี้เงินยืม */
    public static function registerColumnOptions(): array
    {
        return [
            self::COL_ALLOWANCE => 'เบี้ยเลี้ยง',
            self::COL_ACCOMMODATION => 'ค่าที่พัก',
            self::COL_TRANSPORT => 'ค่าพาหนะ',
            self::COL_OTHER => 'อื่น ๆ',
        ];
    }

    public function registerColumnLabel(): string
    {
        return self::registerColumnOptions()[$this->register_column] ?? $this->register_column;
    }

    /** @return array id => name สำหรับ dropdown */
    public static function options(bool $activeOnly = true): array
    {
        $query = self::find()->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
        if ($activeOnly) {
            $query->where(['is_active' => true]);
        }
        return ArrayHelper::map($query->all(), 'id', 'name');
    }

    /**
     * ข้อมูลรูปแบบช่องกรอกของแต่ละรายการ ส่งให้ JavaScript ในฟอร์มประมาณการ
     * เพื่อซ่อน/แสดงช่องจำนวนคนกับจำนวนหน่วย และเปลี่ยนป้ายหน่วยตามรายการที่เลือก
     */
    public static function inputHints(): array
    {
        $hints = [];
        foreach (self::find()->where(['is_active' => true])->all() as $kind) {
            $hints[$kind->id] = [
                'persons' => (bool) $kind->has_persons,
                'units' => (bool) $kind->has_units,
                'personUnit' => (string) ($kind->person_unit_name ?: 'คน'),
                'unit' => (string) ($kind->unit_name ?: 'หน่วย'),
                'column' => (string) $kind->register_column,
            ];
        }
        return $hints;
    }
}
