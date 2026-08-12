<?php

namespace app\modules\pm\models;

use Yii;

/**
 * ตัวชี้วัดของปีงบประมาณหนึ่ง — ตัวชี้วัดแม่คงที่ตลอดอายุแผน
 * ส่วนนิยาม (KPI Template) ค่าเป้าหมาย และสถานะการใช้งานปรับได้ปีต่อปี
 */
class StrategyIndicatorYear extends StrategyRecord
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELLED = 'cancelled';

    public static function tableName(): string { return '{{%pm_strategy_indicator_year}}'; }

    public function rules(): array
    {
        return [
            [['indicator_id', 'fiscal_year'], 'required'],
            [['indicator_id', 'fiscal_year', 'sort_order', 'copied_from_id'], 'integer'],
            [['baseline_value', 'target_value', 'actual_value', 'weight', 'baseline_average'], 'number'],
            [['note', 'name_override', 'cancelled_reason', 'target_population', 'definition', 'formula', 'evaluation_method', 'data_source'], 'string'],
            ['operator', 'string', 'max' => 10],
            ['unit_override', 'string', 'max' => 100],
            [['owner_team', 'supervisor_name', 'owner_name'], 'string', 'max' => 150],
            [['supervisor_phone', 'owner_phone'], 'string', 'max' => 50],
            ['baseline_label', 'string', 'max' => 255],
            ['status', 'in', 'range' => array_keys(self::statusList())],
            [['indicator_id', 'fiscal_year'], 'unique', 'targetAttribute' => ['indicator_id', 'fiscal_year'], 'message' => 'ตัวชี้วัดนี้มีข้อมูลของปีดังกล่าวแล้ว'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'fiscal_year' => 'ปีงบประมาณ (พ.ศ.)', 'baseline_value' => 'ค่าฐาน', 'target_value' => 'ค่าเป้าหมาย',
            'actual_value' => 'ผลงานจริงทั้งปี', 'operator' => 'เงื่อนไข', 'note' => 'หมายเหตุ',
            'status' => 'สถานะการใช้งานในปีนี้', 'name_override' => 'ชื่อตัวชี้วัดที่ใช้ในปีนี้',
            'unit_override' => 'หน่วยนับที่ใช้ในปีนี้', 'weight' => 'น้ำหนัก (%)', 'sort_order' => 'ลำดับ',
            'cancelled_reason' => 'เหตุผลที่ยกเลิก',
            'owner_team' => 'ทีม/คณะกรรมการที่รับผิดชอบ', 'target_population' => 'ประชากรกลุ่มเป้าหมาย',
            'definition' => 'คำจำกัดความ', 'formula' => 'สูตรคำนวณตัวชี้วัด',
            'evaluation_method' => 'วิธีการประเมินผล', 'data_source' => 'แหล่งข้อมูล',
            'baseline_label' => 'คำอธิบายค่าฐาน', 'baseline_average' => 'ค่าเฉลี่ยข้อมูลพื้นฐาน',
            'supervisor_name' => 'ผู้กำกับดูแลตัวชี้วัด', 'supervisor_phone' => 'เบอร์โทรผู้กำกับดูแล',
            'owner_name' => 'ผู้รับผิดชอบ', 'owner_phone' => 'เบอร์โทรผู้รับผิดชอบ',
        ];
    }

    public function richTextAttributes(): array
    {
        return ['target_population', 'definition', 'formula', 'evaluation_method', 'data_source', 'note'];
    }

    public static function statusList(): array
    {
        return [self::STATUS_ACTIVE => 'ใช้งาน', self::STATUS_CANCELLED => 'ยกเลิกในปีนี้'];
    }

    public static function operatorList(): array
    {
        return ['>=' => 'ไม่น้อยกว่า', '<=' => 'ไม่เกิน', '=' => 'เท่ากับ', '>' => 'มากกว่า', '<' => 'น้อยกว่า'];
    }

    public function getIndicator() { return $this->hasOne(StrategyIndicator::class, ['id' => 'indicator_id']); }
    public function getCopiedFrom() { return $this->hasOne(self::class, ['id' => 'copied_from_id']); }
    public function getScores() { return $this->hasMany(StrategyIndicatorScore::class, ['indicator_year_id' => 'id'])->orderBy(['level' => SORT_DESC]); }
    public function getPeriods() { return $this->hasMany(StrategyIndicatorPeriod::class, ['indicator_year_id' => 'id'])->orderBy(['period_month' => SORT_ASC]); }
    public function getMonths() { return $this->hasMany(StrategyIndicatorMonth::class, ['indicator_year_id' => 'id']); }
    public function getBaselines() { return $this->hasMany(StrategyIndicatorBaseline::class, ['indicator_year_id' => 'id'])->orderBy(['fiscal_year' => SORT_ASC]); }

    public function isCancelled(): bool { return $this->status === self::STATUS_CANCELLED; }

    /** ชื่อ/หน่วยนับที่แสดงในปีนี้ ใช้ค่าที่ปรับเฉพาะปีก่อน ถ้าไม่มีจึงใช้ของตัวชี้วัดแม่ */
    public function displayName(): string { return trim((string) $this->name_override) ?: (string) $this->indicator?->name; }
    public function displayUnit(): ?string { return trim((string) $this->unit_override) ?: $this->indicator?->unit; }

    /** เกณฑ์คะแนนครบ 5 ระดับ เติมระดับที่ยังไม่มีเป็นรายการว่าง เรียงจาก 5 ลง 1 */
    public function scoreRows(): array
    {
        $existing = [];
        foreach ($this->scores as $score) $existing[(int) $score->level] = $score;
        $rows = [];
        foreach (StrategyIndicatorScore::LEVELS as $level) {
            $rows[] = $existing[$level] ?? new StrategyIndicatorScore(['indicator_year_id' => $this->id, 'level' => $level]);
        }
        return $rows;
    }

    /** รอบการประเมินทั้ง 5 รอบ เติมรอบที่ยังไม่มีเป็นรายการว่าง */
    public function periodRows(): array
    {
        $existing = [];
        foreach ($this->periods as $period) $existing[(int) $period->period_month] = $period;
        $rows = [];
        foreach (StrategyIndicatorPeriod::MONTHS as $month) {
            $rows[] = $existing[$month] ?? new StrategyIndicatorPeriod(['indicator_year_id' => $this->id, 'period_month' => $month]);
        }
        return $rows;
    }

    /** ผลงานรายเดือนครบ 12 เดือน เรียงตามปีงบประมาณ ต.ค. → ก.ย. */
    public function monthRows(): array
    {
        $existing = [];
        foreach ($this->months as $month) $existing[(int) $month->month] = $month;
        $rows = [];
        foreach (StrategyIndicatorMonth::FISCAL_MONTHS as $month) {
            $rows[] = $existing[$month] ?? new StrategyIndicatorMonth(['indicator_year_id' => $this->id, 'month' => $month]);
        }
        return $rows;
    }

    /** ผลรวมผลงานรายเดือนที่บันทึกแล้ว ใช้เทียบกับค่าเป้าหมายทั้งปี */
    public function monthlyTotal(): ?float
    {
        $values = array_filter(array_map(fn($m) => $m->value, $this->months), fn($v) => $v !== null);
        return $values ? array_sum(array_map('floatval', $values)) : null;
    }

    public function monthsFilled(): int
    {
        return count(array_filter($this->months, fn($m) => $m->value !== null));
    }

    public function cancel(?string $reason): bool
    {
        $this->status = self::STATUS_CANCELLED;
        $this->cancelled_reason = $reason;
        $this->cancelled_at = date('Y-m-d H:i:s');
        $this->cancelled_by = Yii::$app->has('user') ? Yii::$app->user->id : null;
        return $this->save(false);
    }

    public function restore(): bool
    {
        $this->status = self::STATUS_ACTIVE;
        $this->cancelled_reason = null;
        $this->cancelled_at = null;
        $this->cancelled_by = null;
        return $this->save(false);
    }
}
