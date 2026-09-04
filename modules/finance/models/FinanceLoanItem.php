<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;

/**
 * บรรทัดประมาณการค่าใช้จ่ายของใบยืมหนึ่งใบ
 *
 * เก็บ จำนวน × อัตรา ไม่ใช่แค่ยอดรวม เพราะใบประมาณการของจริงเขียนว่า
 * “เบี้ยเลี้ยง จำนวน 1 คน จำนวน 1 วันๆละ 80.00 บาท เป็นเงิน 80.00 บาท”
 * ถ้าเก็บแต่ยอด 80 จะพิมพ์ใบประมาณการไม่ได้ และตรวจย้อนกลับไม่ได้ว่ามาจากไหน
 *
 * @property FinanceLoan $loan
 * @property FinanceLoanItemKind|null $kind
 */
class FinanceLoanItem extends ActiveRecord
{
    use LoanAuditTrait;

    public static function tableName()
    {
        return '{{%finance_loan_item}}';
    }

    public function rules()
    {
        return [
            [['loan_id'], 'required'],
            [['loan_id', 'item_kind_id', 'sort_order', 'created_by', 'updated_by'], 'integer'],
            [['persons', 'units', 'rate', 'amount'], 'number', 'min' => 0],
            [['label'], 'string', 'max' => 255],
            [['note'], 'string', 'max' => 255],
            [['sort_order'], 'default', 'value' => 0],
            [['amount'], 'default', 'value' => 0],
            [['item_kind_id'], 'exist', 'targetClass' => FinanceLoanItemKind::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            // skipOnEmpty ต้องเป็น false ไม่งั้น Yii ข้ามตัวตรวจนี้ตอนที่ทั้งสองช่องว่าง
            // ซึ่งคือกรณีเดียวที่ต้องจับพอดี — บรรทัดเปล่าจะหลุดลงฐานข้อมูลแล้วไปโป่งที่ยอดรวม
            [['label'], 'validateHasName', 'skipOnEmpty' => false],
        ];
    }

    /** บรรทัดต้องมีชื่อจากที่ใดที่หนึ่ง ไม่งั้นใบประมาณการจะมีบรรทัดเปล่า */
    public function validateHasName($attribute): void
    {
        if (!$this->item_kind_id && trim((string) $this->label) === '') {
            $this->addError($attribute, 'เลือกประเภทรายการ หรือพิมพ์ชื่อรายการอย่างใดอย่างหนึ่ง');
        }
    }

    public function attributeLabels()
    {
        return [
            'item_kind_id' => 'ประเภทรายการ',
            'label' => 'ชื่อรายการ',
            'persons' => 'จำนวน',
            'units' => 'จำนวนหน่วย',
            'rate' => 'อัตราต่อหน่วย',
            'amount' => 'เป็นเงิน',
            'note' => 'หมายเหตุ',
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        // คำนวณยอดจาก จำนวน × หน่วย × อัตรา ให้เอง แต่ถ้ากรอกยอดมาเองโดยไม่ใส่อัตรา
        // ก็ปล่อยตามที่กรอก เพราะบางรายการมีแต่ยอดก้อนเดียว เช่น ค่าน้ำมันรถราชการ
        $rate = $this->money($this->rate);
        if ($rate > 0) {
            $multiplier = max(1, $this->money($this->persons)) * max(1, $this->money($this->units));
            $this->amount = round($rate * $multiplier, 2);
        } else {
            $this->amount = round($this->money($this->amount), 2);
        }
        return true;
    }

    public function getLoan()
    {
        return $this->hasOne(FinanceLoan::class, ['id' => 'loan_id']);
    }

    public function getKind()
    {
        return $this->hasOne(FinanceLoanItemKind::class, ['id' => 'item_kind_id']);
    }

    /** ชื่อที่จะพิมพ์ลงใบประมาณการ — ชื่อเฉพาะกิจมาก่อนชื่อจากตั้งค่า */
    public function displayName(): string
    {
        $label = trim((string) $this->label);
        return $label !== '' ? $label : (string) ($this->kind->name ?? 'ไม่ระบุรายการ');
    }

    /** ช่องในทะเบียนคุมที่บรรทัดนี้ไปรวมยอด */
    public function registerColumn(): string
    {
        return (string) ($this->kind->register_column ?? FinanceLoanItemKind::COL_OTHER);
    }

    /** บรรยายวิธีคิดแบบที่เขียนในใบประมาณการ เช่น “50 คน × 2 มื้อ × 80.00 บาท” */
    public function calculationText(): string
    {
        $rate = $this->money($this->rate);
        if ($rate <= 0) {
            return '';
        }
        $parts = [];
        if ($this->money($this->persons) > 0) {
            $parts[] = $this->numberText($this->persons) . ' ' . ($this->kind->person_unit_name ?? 'คน');
        }
        if ($this->money($this->units) > 0) {
            $parts[] = $this->numberText($this->units) . ' ' . ($this->kind->unit_name ?? 'หน่วย');
        }
        $parts[] = number_format($rate, 2) . ' บาท';
        return implode(' × ', $parts);
    }

    private function numberText($value): string
    {
        $number = $this->money($value);
        return $number == (int) $number ? number_format($number) : number_format($number, 2);
    }
}
