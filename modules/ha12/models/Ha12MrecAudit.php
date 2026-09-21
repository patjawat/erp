<?php

namespace app\modules\ha12\models;

use app\modules\hr\models\Organization;

/**
 * การตรวจความสมบูรณ์เวชระเบียน 1 รอบ (กิจกรรม 9)
 *
 * @property int $id
 * @property int|null $owner_unit_id
 * @property int $fiscal_year
 * @property string|null $period_start
 * @property string|null $period_end
 * @property string|null $review_date
 * @property int|null $total_charts
 * @property string|null $problem
 * @property string|null $result
 * @property string|null $note
 * @property int $deleted
 * @property string $ref
 */
class Ha12MrecAudit extends Ha12ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%ha12_mrec_audit}}';
    }

    public function rules(): array
    {
        return [
            [['fiscal_year'], 'required'],
            [['fiscal_year', 'owner_unit_id', 'total_charts', 'deleted'], 'integer'],
            [['total_charts'], 'integer', 'min' => 0],
            [['period_start', 'period_end', 'review_date'], 'date', 'format' => 'php:Y-m-d'],
            [['problem', 'result', 'note'], 'string'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'owner_unit_id' => 'หน่วยงาน',
            'fiscal_year' => 'ปีงบประมาณ',
            'period_start' => 'เริ่มช่วง',
            'period_end' => 'สิ้นสุดช่วง',
            'review_date' => 'วันที่ทบทวน',
            'total_charts' => 'จำนวนที่ตรวจ',
            'problem' => 'ปัญหาที่พบ',
            'result' => 'ผล/การปรับปรุง',
            'note' => 'บันทึก',
        ];
    }

    public function getOwnerUnit()
    {
        return $this->hasOne(Organization::class, ['id' => 'owner_unit_id']);
    }

    public function getItems()
    {
        return $this->hasMany(Ha12MrecItem::class, ['audit_id' => 'id'])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
    }

    /** ร้อยละความสมบูรณ์รวม = ผลรวมที่ครบ ÷ (จำนวนตรวจ × จำนวนหัวข้อ) */
    public function overallPercent(): ?float
    {
        if (!$this->total_charts) {
            return null;
        }
        $items = $this->items;
        $scored = array_filter($items, static fn ($i) => $i->complete_count !== null);
        if (!$scored) {
            return null;
        }
        $sum = array_sum(array_map(static fn ($i) => (int) $i->complete_count, $scored));
        $denom = $this->total_charts * count($scored);
        return $denom > 0 ? $sum / $denom * 100 : null;
    }
}
