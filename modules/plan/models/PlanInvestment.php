<?php

namespace app\modules\plan\models;

use yii\db\ActiveRecord;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * แผนการลงทุนด้วยเงินบำรุง (แบบฟอร์มเขต เมนู 1.3 แผนลงทุน 1 ปี / 1.4 แผนลงทุน 3 ปี)
 *
 * หนึ่งแถว = หนึ่งรายการในชุดแผน 3 ปีที่เริ่ม plan_year — แผน 1 ปี คือรายการที่มีจำนวนปีแรก (qty_y1)
 * แบบเดียวกับระบบเขตที่เก็บรายการลงทุนชุดเดียวมี qty70/qty71/qty72
 *
 * @property int $id
 * @property int $plan_year
 * @property string $budget_type
 * @property string $name
 * @property string|null $unit
 * @property float $unit_price
 * @property float $qty_y1
 * @property float $qty_y2
 * @property float $qty_y3
 * @property string $source
 * @property int $policy
 * @property string|null $note
 * @property int $sort_order
 */
class PlanInvestment extends ActiveRecord
{
    public const TYPE_EQUIPMENT = 'equipment';
    public const TYPE_CONSTRUCTION = 'construction';

    public const SOURCE_MAINTENANCE = 'maintenance';
    public const SOURCE_DEPRECIATION = 'depreciation';
    public const SOURCE_DONATION = 'donation';

    public static function tableName(): string
    {
        return '{{%plan_investment}}';
    }

    public function behaviors(): array
    {
        return [TimestampBehavior::class, BlameableBehavior::class];
    }

    public function rules(): array
    {
        return [
            [['plan_year', 'budget_type', 'name', 'source', 'policy'], 'required'],
            [['plan_year', 'policy', 'sort_order'], 'integer'],
            [['unit_price', 'qty_y1', 'qty_y2', 'qty_y3'], 'number', 'min' => 0],
            [['budget_type'], 'in', 'range' => array_keys(self::types())],
            [['source'], 'in', 'range' => array_keys(self::sources())],
            [['policy'], 'in', 'range' => array_keys(self::policies())],
            [['name'], 'string', 'max' => 255],
            [['unit'], 'string', 'max' => 50],
            [['note'], 'string', 'max' => 500],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'budget_type' => 'ประเภทงบ',
            'name' => 'ชื่อรายการลงทุน',
            'unit' => 'หน่วยนับ',
            'unit_price' => 'ราคาต่อหน่วย',
            'qty_y1' => 'จำนวนหน่วย ปีที่ 1',
            'qty_y2' => 'จำนวนหน่วย ปีที่ 2',
            'qty_y3' => 'จำนวนหน่วย ปีที่ 3',
            'source' => 'แหล่งเงิน',
            'policy' => 'สอดคล้องนโยบายด้านใด',
            'note' => 'หมายเหตุ',
        ];
    }

    public static function types(): array
    {
        return [self::TYPE_EQUIPMENT => 'ครุภัณฑ์', self::TYPE_CONSTRUCTION => 'สิ่งก่อสร้าง'];
    }

    /** คำตามแม่แบบ Excel ของเขต (ใช้ทั้งแสดงผลและส่งออก) */
    public static function sources(): array
    {
        return [
            self::SOURCE_MAINTENANCE => 'เงินบำรุง',
            self::SOURCE_DEPRECIATION => 'งบค่าเสื่อม',
            self::SOURCE_DONATION => 'งบบริจาค',
        ];
    }

    /** นโยบายการลงทุน Environment, Modernization And Smart Service : EMS (8 ข้อตามระบบเขต) */
    public static function policies(): array
    {
        return [
            1 => '1. EMS : Solar Cell (พลังงานแสงอาทิตย์)',
            2 => '2. EMS : ระบบบำบัดน้ำเสีย',
            3 => '3. EMS : ปรับปรุงภูมิทัศน์',
            4 => '4. Smart OPD (ระบบบริการผู้ป่วยนอกอัจฉริยะ)',
            5 => '5. Smart ER (ห้องฉุกเฉินอัจฉริยะ & EMS)',
            6 => '6. ปรับปรุง/สร้างที่พักอาศัยบุคลากร',
            7 => '7. ปรับปรุง/สร้างอาคารจอดรถ',
            8 => '8. อื่นๆ',
        ];
    }

    public function qty(int $i): float
    {
        return (float) $this->{'qty_y' . $i};
    }

    /** เป็นเงินของปีที่ i (1-3) */
    public function amount(int $i): float
    {
        return $this->qty($i) * (float) $this->unit_price;
    }

    public function totalQty(): float
    {
        return $this->qty(1) + $this->qty(2) + $this->qty(3);
    }

    public function totalAmount(): float
    {
        return $this->amount(1) + $this->amount(2) + $this->amount(3);
    }
}
