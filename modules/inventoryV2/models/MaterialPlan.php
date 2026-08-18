<?php

namespace app\modules\inventoryV2\models;

use Yii;

/**
 * แผนวัสดุประจำปีที่งานพัสดุบันทึกไว้เป็นฉบับอ้างอิง
 *
 * หน้าคำนวณคิดสดทุกครั้ง ตัวเลขจึงขยับตามวันที่กด แผนที่บันทึกแล้วหยุดขยับ
 * และเมื่อปิดค่า (locked) จะกลายเป็นฉบับที่ส่ง สสจ. รวมถึงเป็นที่มาของอัตราเผื่อ
 * ที่หน่วยงานดึงไปใช้ตั้งงบ
 *
 * @property int $id
 * @property string|null $ref
 * @property int $fiscal_year ปีงบประมาณที่จะจัดซื้อ
 * @property int $base_year ปีงบที่ใช้ยอดจริงเป็นฐาน
 * @property int|null $warehouse_id คลังหลัก null = ทุกคลังหลัก
 * @property float $growth_pct อัตราปรับเพิ่ม/ลด (%)
 * @property int $months_covered จำนวนเดือนที่ปีฐานมีข้อมูล
 * @property float $annual_factor ตัวคูณปรับเต็มปี
 * @property string|null $data_cutoff_date
 * @property string|null $balance_source
 * @property int $item_count
 * @property float $plan_value
 * @property string $status draft | locked
 * @property string|null $locked_at
 * @property int|null $locked_by
 * @property string|null $note
 * @property array|null $data_json
 *
 * @property MaterialPlanItem[] $items
 */
class MaterialPlan extends \yii\db\ActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_LOCKED = 'locked';

    public static function tableName()
    {
        return 'material_plan';
    }

    public function rules()
    {
        return [
            [['fiscal_year', 'base_year'], 'required'],
            [['fiscal_year', 'base_year', 'warehouse_id', 'months_covered', 'item_count', 'locked_by', 'created_by', 'updated_by'], 'integer'],
            [['growth_pct', 'annual_factor', 'plan_value'], 'number'],
            [['note', 'data_json'], 'safe'],
            [['data_cutoff_date', 'locked_at', 'created_at', 'updated_at'], 'safe'],
            [['ref', 'balance_source'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_LOCKED]],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fiscal_year' => 'ปีงบประมาณที่จะจัดซื้อ',
            'base_year' => 'ปีฐาน',
            'warehouse_id' => 'คลังหลัก',
            'growth_pct' => 'อัตราปรับเพิ่ม/ลด (%)',
            'months_covered' => 'เดือนที่มีข้อมูล',
            'plan_value' => 'มูลค่าประมาณการรวม',
            'status' => 'สถานะ',
            'note' => 'บันทึกผู้จัดทำ',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        if ($insert) {
            $this->ref = $this->ref ?: substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $this->created_at = $now;
            $this->created_by = Yii::$app->user->id;
        }
        $this->updated_at = $now;
        $this->updated_by = Yii::$app->user->id;

        return true;
    }

    public function getItems()
    {
        return $this->hasMany(MaterialPlanItem::class, ['material_plan_id' => 'id']);
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED;
    }

    public function statusLabel(): string
    {
        return $this->isLocked() ? 'ปิดค่าแล้ว' : 'ร่าง';
    }

    /**
     * แผนของปีงบและขอบเขตคลังที่กำหนด (1 ปีงบ ต่อ 1 ขอบเขตคลัง มีได้ฉบับเดียว)
     */
    public static function findForScope(int $fiscalYear, $warehouseId = null): ?self
    {
        return self::findOne([
            'fiscal_year' => $fiscalYear,
            'warehouse_id' => $warehouseId ? (int) $warehouseId : null,
        ]);
    }

    /**
     * อัตราเผื่อที่พัสดุกำหนดไว้สำหรับปีงบนั้น ให้ทั้งระบบใช้ค่าเดียวกัน
     * ยังไม่มีแผนบันทึกไว้ให้ถอยไปใช้ค่าตั้งต้น
     */
    public static function growthPctForYear(int $fiscalYear, float $default): float
    {
        $plan = self::find()
            ->select('growth_pct')
            ->where(['fiscal_year' => $fiscalYear])
            // 'locked' > 'draft' ตามลำดับตัวอักษร เรียงมากไปน้อยจึงได้ฉบับที่ปิดค่าแล้วก่อน
            ->orderBy(['status' => SORT_DESC, 'id' => SORT_DESC])
            ->scalar();

        return $plan === false || $plan === null ? $default : (float) $plan;
    }
}
