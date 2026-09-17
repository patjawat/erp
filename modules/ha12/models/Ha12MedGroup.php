<?php

namespace app\modules\ha12\models;

/**
 * หัวข้อหลักความคลาดเคลื่อนทางยา (1-5) — มีตัวหาร/หน่วย/ฐานอัตรา
 * ยอดหัวข้อหลักรวมจากแถวย่อย (ไม่กรอกซ้ำ)
 *
 * @property int $id
 * @property int $report_id
 * @property int $group_no
 * @property int|null $divisor
 * @property string|null $divisor_unit
 * @property int|null $rate_base
 * @property string|null $note
 * @property string $ref
 */
class Ha12MedGroup extends Ha12ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%ha12_med_group}}';
    }

    public function rules(): array
    {
        return [
            [['report_id', 'group_no'], 'required'],
            [['report_id', 'group_no', 'divisor', 'rate_base'], 'integer'],
            [['divisor'], 'integer', 'min' => 1, 'when' => fn ($m) => $m->divisor !== null && $m->divisor !== ''],
            [['divisor_unit'], 'in', 'range' => array_keys(Ha12MedTemplate::DIVISOR_UNITS)],
            [['rate_base'], 'in', 'range' => Ha12MedTemplate::RATE_BASES],
            [['note'], 'string'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'divisor' => 'ตัวหาร',
            'divisor_unit' => 'หน่วยตัวหาร',
            'rate_base' => 'ฐานอัตรา',
        ];
    }

    public function getReport()
    {
        return $this->hasOne(Ha12MedReport::class, ['id' => 'report_id']);
    }

    public function getCounts()
    {
        return $this->hasMany(Ha12MedCount::class, ['group_id' => 'id'])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function groupName(): string
    {
        return Ha12MedTemplate::groupName((int) $this->group_no);
    }

    /** ยอดรวมของหัวข้อ = ผลรวมจำนวนครั้งของทุกแถวย่อย */
    public function groupTotal(): int
    {
        $sum = 0;
        foreach ($this->counts as $c) {
            $sum += (int) $c->total_count;
        }
        return $sum;
    }

    /**
     * อัตราต่อฐาน = จำนวนครั้ง ÷ ตัวหาร × ฐาน
     * คืน null ถ้าไม่พร้อมคำนวณ (ตัวหารว่าง/<=0 หรือไม่เลือกฐาน)
     */
    public function rate(): ?float
    {
        if (!$this->divisor || $this->divisor <= 0 || !$this->rate_base) {
            return null;
        }
        return $this->groupTotal() / $this->divisor * $this->rate_base;
    }
}
