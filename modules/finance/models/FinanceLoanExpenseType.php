<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * ประเภทค่าใช้จ่ายของเงินยืม — ตัวกำหนดกติกาวันครบกำหนดและแบบใบประมาณการ
 *
 * เป็นข้อมูลตั้งค่า ไม่ใช่ค่าตายตัวในโค้ด เพราะแต่ละโรงพยาบาลมีประเภทเงินยืมต่างกัน
 * และกติกาวันส่งใช้ก็ต่างกันตามระเบียบที่ผู้อำนวยการวางไว้
 */
class FinanceLoanExpenseType extends ActiveRecord
{
    /** นับวันครบกำหนดจากวันที่กิจกรรมจบ — ไปราชการนับจากวันกลับ โครงการนับจากวันที่งานเสร็จ */
    public const BASIS_ACTIVITY_END = 'activity_end';
    public const BASIS_RECEIVED = 'received';
    public const BASIS_BORROWED = 'borrowed';

    /** ใบประมาณการแบบเดินทางไปราชการ มีหัวข้อ 1-5 ตายตัวตามแบบฟอร์มของงานการเงิน */
    public const FORM_TRAVEL = 'travel';
    /** ใบประมาณการแบบทั่วไป ผู้ใช้เพิ่มบรรทัดเอง */
    public const FORM_GENERAL = 'general';

    public static function tableName()
    {
        return '{{%finance_loan_expense_type}}';
    }

    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['code'], 'string', 'max' => 40],
            [['code'], 'match', 'pattern' => '/^[a-z0-9_]+$/', 'message' => 'ใช้ได้เฉพาะ a-z 0-9 และ _'],
            [['code'], 'unique'],
            [['name'], 'string', 'max' => 255],
            [['due_days'], 'integer', 'min' => 0, 'max' => 365],
            [['due_days'], 'default', 'value' => 30],
            [['due_basis'], 'in', 'range' => array_keys(self::basisOptions())],
            [['due_basis'], 'default', 'value' => self::BASIS_ACTIVITY_END],
            [['estimate_form'], 'in', 'range' => array_keys(self::formOptions())],
            [['estimate_form'], 'default', 'value' => self::FORM_GENERAL],
            [['is_active'], 'boolean'],
            [['is_active'], 'default', 'value' => true],
            [['sort_order'], 'integer'],
            [['sort_order'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => 'รหัส',
            'name' => 'ประเภทค่าใช้จ่าย',
            'due_days' => 'ส่งใช้ภายใน (วัน)',
            'due_basis' => 'นับจาก',
            'estimate_form' => 'แบบใบประมาณการ',
            'is_active' => 'เปิดใช้งาน',
            'sort_order' => 'ลำดับ',
        ];
    }

    public static function basisOptions(): array
    {
        return [
            self::BASIS_ACTIVITY_END => 'วันที่ดำเนินการเสร็จ / วันกลับ',
            self::BASIS_RECEIVED => 'วันที่รับเงิน',
            self::BASIS_BORROWED => 'วันที่ยืม',
        ];
    }

    public static function formOptions(): array
    {
        return [
            self::FORM_TRAVEL => 'เดินทางไปราชการ',
            self::FORM_GENERAL => 'ทั่วไป (กรอกรายการเอง)',
        ];
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

    public function basisLabel(): string
    {
        return self::basisOptions()[$this->due_basis] ?? $this->due_basis;
    }

    public function isTravelForm(): bool
    {
        return $this->estimate_form === self::FORM_TRAVEL;
    }
}
