<?php

namespace app\modules\pm\models;

/**
 * กลยุทธ์ — วิธีการที่จะทำให้ตัวชี้วัดบรรลุค่าเป้าหมาย
 * อยู่ใต้ตัวชี้วัด (หลักหรือรอง) และเป็นที่รวมของมาตรการกับโครงการที่ใช้ขับเคลื่อน
 */
class StrategyTactic extends StrategyRecord
{
    public static function tableName(): string { return '{{%pm_strategy_tactic}}'; }

    public function rules(): array
    {
        return [
            [['goal_id', 'name'], 'required'],
            [['goal_id', 'indicator_id', 'sort_order'], 'integer'],
            ['is_active', 'boolean'],
            ['name', 'string'],
            ['code', 'string', 'max' => 50],
            ['indicator_id', 'exist', 'targetClass' => StrategyIndicator::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true, 'message' => 'ไม่พบตัวชี้วัดที่เลือก'],
            // กันบันทึกซ้ำจากการกดส่งฟอร์มสองครั้ง — รหัสต้องไม่ซ้ำภายในตัวชี้วัดเดียวกัน
            ['code', 'unique', 'targetAttribute' => ['indicator_id', 'code'],
                'message' => 'รหัสกลยุทธ์นี้ถูกใช้แล้วในตัวชี้วัดเดียวกัน',
                'when' => static fn ($model) => trim((string) $model->code) !== '' && !empty($model->indicator_id)],
            ['name', 'unique', 'targetAttribute' => ['indicator_id', 'name'],
                'message' => 'มีกลยุทธ์ชื่อนี้อยู่แล้วในตัวชี้วัดเดียวกัน',
                'when' => static fn ($model) => !empty($model->indicator_id)],
        ];
    }

    public function attributeLabels(): array
    {
        return ['code' => 'รหัสกลยุทธ์', 'name' => 'กลยุทธ์', 'indicator_id' => 'ตัวชี้วัด', 'sort_order' => 'ลำดับ', 'is_active' => 'ใช้งาน'];
    }

    /** เป้าประสงค์ยึดตามตัวชี้วัดเสมอเมื่อระบุตัวชี้วัดไว้ กันข้อมูลสองชั้นไม่ตรงกัน */
    public function beforeSave($insert): bool
    {
        if ($this->indicator_id && ($goalId = $this->indicator?->goal_id)) {
            $this->goal_id = $goalId;
        }
        return parent::beforeSave($insert);
    }

    public function getGoal() { return $this->hasOne(StrategyGoal::class, ['id' => 'goal_id']); }
    public function getIndicator() { return $this->hasOne(StrategyIndicator::class, ['id' => 'indicator_id']); }
    public function getMeasures() { return $this->hasMany(StrategyMeasure::class, ['tactic_id' => 'id'])->orderBy(['fiscal_year' => SORT_ASC, 'sort_order' => SORT_ASC]); }

    /** งานที่ขับเคลื่อนกลยุทธ์นี้ — ทั้งโครงการและแผนงาน/กิจกรรม เรียงโครงการขึ้นก่อน */
    public function getWorks() { return $this->hasMany(Projects::class, ['tactic_id' => 'id'])->andOnCondition(['deleted_at' => null])->orderBy(['work_type' => SORT_DESC, 'thai_year' => SORT_ASC, 'id' => SORT_ASC]); }

    public function label(): string { return trim(($this->code ? $this->code . ' — ' : '') . $this->name); }
}
