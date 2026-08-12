<?php

namespace app\modules\purchase\models;

use yii\db\Expression;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * เกณฑ์หลักประกันตามวงเงิน — ตารางตั้งค่า
 *
 * แยกเป็นทะเบียนด้วยเหตุผลเดียวกับ WhtRate คือเกณฑ์มาจากระเบียบและหนังสือเวียน
 * ที่แก้กันได้ และงานพัสดุต้องแก้เองได้โดยไม่ต้องรอแก้โปรแกรม
 *
 * ข้อความที่ขึ้นบนหน้าจอทุกจุดถูกประกอบจาก title/min_amount/max_amount/rate ของแถว
 * ที่จับคู่ได้ ไม่มีข้อความคงที่ที่อธิบายเกณฑ์ซ้ำไว้ในโค้ดหรือใน view — ที่ต้องยึด
 * แบบนี้เพราะโปรแกรมต้นแบบเขียนป้ายอธิบายเกณฑ์ไว้เป็นข้อความคงที่แล้วป้ายกับ
 * ผลลัพธ์ที่ฟังก์ชันคำนวณให้ไม่ตรงกันที่ยอด 100,000 พอดี
 *
 * @property int $id
 * @property string $proc_kind ตรงกับ Contract::contract_type หรือ 'any'
 * @property string $title
 * @property float $min_amount
 * @property float|null $max_amount
 * @property int $required
 * @property float $rate
 * @property string|null $law_ref
 * @property string|null $note
 * @property int $active
 * @property int $sort_order
 */
class BondPolicy extends \yii\db\ActiveRecord
{
    /** ใช้กับทุกประเภทสัญญา */
    const KIND_ANY = 'any';

    public static function tableName()
    {
        return 'purchase_bond_policy';
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
            [['proc_kind', 'title'], 'required'],
            [['min_amount', 'max_amount'], 'number', 'min' => 0],
            [['rate'], 'number', 'min' => 0, 'max' => 100],
            [['required', 'active', 'sort_order'], 'integer'],
            [['proc_kind'], 'string', 'max' => 20],
            [['title', 'law_ref'], 'string', 'max' => 255],
            [['note'], 'string'],
            [['proc_kind'], 'in', 'range' => array_keys(self::kindList())],
            // ดูเหตุผลของ skipOnEmpty ที่ Bond::rules() — ตัวตรวจชุดนี้ต้องทำงานแม้ช่องว่าง
            [['max_amount'], 'validateRange', 'skipOnEmpty' => false],
            [['required'], 'validateRate', 'skipOnEmpty' => false],
            [['max_amount'], 'default', 'value' => null],
            [['active'], 'default', 'value' => 1],
        ];
    }

    /** ปลายบนต้องไม่ต่ำกว่าปลายล่าง ไม่งั้นแถวนี้จะไม่มีทางจับคู่กับวงเงินใดได้เลย */
    public function validateRange($attribute)
    {
        if ($this->max_amount === null || $this->max_amount === '') {
            return;
        }
        if ((float) $this->max_amount < (float) $this->min_amount) {
            $this->addError($attribute, 'วงเงินถึง ต้องไม่น้อยกว่า วงเงินตั้งแต่');
        }
    }

    /** แถวที่บังคับให้วางหลักประกันแต่ตั้งอัตราไว้ 0 จะทำให้ผู้ใช้ถูกสั่งให้วางเงิน 0 บาท */
    public function validateRate($attribute)
    {
        if ((int) $this->required === 1 && (float) $this->rate <= 0) {
            $this->addError($attribute, 'แถวที่บังคับวางหลักประกันต้องกำหนดอัตราให้มากกว่า 0');
        }
    }

    public function attributeLabels()
    {
        return [
            'proc_kind' => 'ใช้กับประเภท',
            'title' => 'คำอธิบาย',
            'min_amount' => 'วงเงินตั้งแต่ (บาท)',
            'max_amount' => 'วงเงินถึง (บาท)',
            'required' => 'ต้องวางหลักประกัน',
            'rate' => 'อัตรา (%)',
            'law_ref' => 'อ้างอิงระเบียบ',
            'note' => 'หมายเหตุ',
            'active' => 'เปิดใช้งาน',
            'sort_order' => 'ลำดับการจับคู่',
        ];
    }

    /** ประเภทที่เลือกได้ = ประเภทสัญญาในระบบ + any */
    public static function kindList(): array
    {
        return [self::KIND_ANY => 'ทุกประเภท'] + Contract::typeList();
    }

    public function kindName(): string
    {
        return self::kindList()[$this->proc_kind] ?? $this->proc_kind;
    }

    /** ช่วงวงเงินของแถวนี้เป็นข้อความ — ใช้ทั้งหน้าตั้งค่าและคำอธิบายที่ขึ้นในฟอร์ม */
    public function rangeText(): string
    {
        $from = number_format((float) $this->min_amount, 2);
        if ($this->max_amount === null) {
            return 'ตั้งแต่ ' . $from . ' บาทขึ้นไป';
        }
        return $from . ' – ' . number_format((float) $this->max_amount, 2) . ' บาท';
    }

    /**
     * แถวที่ใช้กับวงเงินและประเภทที่ส่งมา คืน null เมื่อไม่มีแถวไหนครอบวงเงินนี้
     * ผู้เรียกต้องรับมือกับ null เอง ระบบต้องไม่เดาเกณฑ์ให้ เพราะตัวเลขนี้เป็นตัว
     * บอกผู้ใช้ว่าต้องเรียกหลักประกันจากคู่สัญญาหรือไม่
     *
     * แถวที่เจาะจงประเภท (เช่น buy) ถูกพิจารณาก่อนแถว any ที่ครอบช่วงเดียวกัน
     * ผ่าน sort_order ที่ตั้งไว้ให้เลขน้อยกว่า
     */
    public static function match(float $amount, ?string $procKind): ?self
    {
        if ($amount <= 0) {
            return null;
        }

        $kinds = [self::KIND_ANY];
        if (!empty($procKind)) {
            array_unshift($kinds, $procKind);
        }

        return self::find()
            ->where(['active' => 1, 'proc_kind' => $kinds])
            ->andWhere(['<=', 'min_amount', $amount])
            ->andWhere(['or', ['max_amount' => null], ['>=', 'max_amount', $amount]])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->one();
    }

    /** true เมื่อยังไม่มีใครแตะค่าที่ระบบ seed ไว้ — ใช้ขึ้นป้ายเตือนในหน้าตั้งค่า */
    public static function needsReview(): bool
    {
        return self::find()->where(['like', 'note', 'ยังไม่ผ่านการยืนยันจากงานพัสดุ'])->exists();
    }
}
