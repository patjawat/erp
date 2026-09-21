<?php

namespace app\modules\ha12\models;

use app\modules\hr\models\Organization;

/**
 * รายงานความคลาดเคลื่อนทางยา (กิจกรรม 7) — 1 ช่วงข้อมูล ต่อหน่วยงาน
 *
 * @property int $id
 * @property int|null $owner_unit_id
 * @property int $fiscal_year
 * @property string $period_start
 * @property string $period_end
 * @property string|null $review_date
 * @property string|null $note
 * @property int $deleted
 * @property string $ref
 */
class Ha12MedReport extends Ha12ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%ha12_med_report}}';
    }

    public function rules(): array
    {
        return [
            [['fiscal_year', 'period_start', 'period_end'], 'required'],
            [['fiscal_year', 'owner_unit_id', 'deleted'], 'integer'],
            [['period_start', 'period_end', 'review_date'], 'date', 'format' => 'php:Y-m-d'],
            [['note'], 'string'],
            [['period_end'], 'validatePeriod'],
            [['review_date'], 'validateReviewDate'],
            [['period_start'], 'validateNoOverlap'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'owner_unit_id' => 'หน่วยงาน',
            'fiscal_year' => 'ปีงบประมาณ',
            'period_start' => 'เริ่มช่วงข้อมูล',
            'period_end' => 'สิ้นสุดช่วงข้อมูล',
            'review_date' => 'วันที่ทบทวน',
            'note' => 'บันทึกรวม',
        ];
    }

    /** เริ่มต้องไม่เกินสิ้นสุด */
    public function validatePeriod($attr): void
    {
        if ($this->period_start && $this->period_end && $this->period_start > $this->period_end) {
            $this->addError($attr, 'วันสิ้นสุดต้องไม่ก่อนวันเริ่ม');
        }
    }

    /** วันที่ทบทวนต้องไม่ก่อนสิ้นสุดช่วงข้อมูล (บท 8) */
    public function validateReviewDate($attr): void
    {
        if ($this->review_date && $this->period_end && $this->review_date < $this->period_end) {
            $this->addError($attr, 'วันที่ทบทวนต้องไม่ก่อนวันสิ้นสุดช่วงข้อมูล');
        }
    }

    /** ห้ามสร้างช่วงข้อมูลซ้อนกันในหน่วยงานเดียวกัน (บท 8) */
    public function validateNoOverlap($attr): void
    {
        if (!$this->period_start || !$this->period_end) {
            return;
        }
        $q = static::find()
            ->where(['owner_unit_id' => $this->owner_unit_id, 'deleted' => 0])
            ->andWhere(['<=', 'period_start', $this->period_end])
            ->andWhere(['>=', 'period_end', $this->period_start]);
        if (!$this->isNewRecord) {
            $q->andWhere(['<>', 'id', $this->id]);
        }
        if ($q->exists()) {
            $this->addError($attr, 'ช่วงข้อมูลนี้ซ้อนกับรายงานเดิมของหน่วยงานเดียวกัน');
        }
    }

    public function getOwnerUnit()
    {
        return $this->hasOne(Organization::class, ['id' => 'owner_unit_id']);
    }

    public function getGroups()
    {
        return $this->hasMany(Ha12MedGroup::class, ['report_id' => 'id'])
            ->orderBy(['group_no' => SORT_ASC]);
    }

    /** ยอดรวมทุกหัวข้อ (จำนวนความคลาดเคลื่อนที่จำแนกตามประเภท) */
    public function grandTotal(): int
    {
        $sum = 0;
        foreach ($this->groups as $g) {
            $sum += $g->groupTotal();
        }
        return $sum;
    }
}
