<?php

namespace app\modules\pm\services;

use Yii;
use app\modules\pm\models\{StrategyIndicatorBaseline, StrategyIndicatorPeriod, StrategyIndicatorScore, StrategyIndicatorYear, StrategyPlan};

/**
 * คัดลอกชุดตัวชี้วัดจากปีงบประมาณหนึ่งไปยังอีกปีหนึ่ง
 * คัดลอกเฉพาะรายการที่ยังใช้งาน ข้ามรายการที่ปีปลายทางมีอยู่แล้ว และไม่คัดลอกผลงานจริง
 */
class StrategyIndicatorYearCopier
{
    /** @var string[] */
    public array $errors = [];
    public int $copied = 0;
    public int $skipped = 0;

    public function __construct(private StrategyPlan $plan) {}

    /** ปีที่มีข้อมูลตัวชี้วัดอยู่แล้วในชุดแผนนี้ */
    public function yearsWithData(): array
    {
        return array_map('intval', StrategyIndicatorYear::find()
            ->select('fiscal_year')->distinct()
            ->joinWith('indicator', false)
            ->where(['pm_strategy_indicator.plan_id' => $this->plan->id])
            ->orderBy(['fiscal_year' => SORT_ASC])
            ->column());
    }

    public function copy(int $fromYear, int $toYear): bool
    {
        if ($fromYear === $toYear) {
            $this->errors[] = 'ปีต้นทางและปีปลายทางต้องไม่ใช่ปีเดียวกัน';
            return false;
        }
        if (!$this->plan->coversYear($toYear)) {
            $this->errors[] = "ปี {$toYear} อยู่นอกช่วงของแผน (พ.ศ. {$this->plan->start_year}–{$this->plan->end_year})";
            return false;
        }

        $source = StrategyIndicatorYear::find()
            ->joinWith('indicator', false)
            ->where([
                'pm_strategy_indicator.plan_id' => $this->plan->id,
                'pm_strategy_indicator.is_active' => true,
                'pm_strategy_indicator_year.fiscal_year' => $fromYear,
                'pm_strategy_indicator_year.status' => StrategyIndicatorYear::STATUS_ACTIVE,
            ])
            ->orderBy(['pm_strategy_indicator_year.sort_order' => SORT_ASC, 'pm_strategy_indicator_year.id' => SORT_ASC])
            ->all();

        if (!$source) {
            $this->errors[] = "ไม่พบตัวชี้วัดที่ใช้งานอยู่ในปี {$fromYear}";
            return false;
        }

        $existing = StrategyIndicatorYear::find()
            ->select('indicator_id')
            ->where(['fiscal_year' => $toYear, 'indicator_id' => array_column($source, 'indicator_id')])
            ->column();
        $existing = array_flip(array_map('intval', $existing));

        $tx = Yii::$app->db->beginTransaction();
        try {
            foreach ($source as $row) {
                if (isset($existing[(int) $row->indicator_id])) { $this->skipped++; continue; }
                $copy = new StrategyIndicatorYear($row->attributes);
                $copy->id = null;
                $copy->ref = null;
                $copy->isNewRecord = true;
                $copy->fiscal_year = $toYear;
                $copy->status = StrategyIndicatorYear::STATUS_ACTIVE;
                $copy->copied_from_id = $row->id;
                // ค่าฐานของปีใหม่คือผลงานจริงของปีก่อน ถ้ายังไม่มีจึงคงค่าฐานเดิมไว้
                $copy->baseline_value = $row->actual_value ?? $row->baseline_value;
                $copy->actual_value = null;
                $copy->cancelled_at = null;
                $copy->cancelled_by = null;
                $copy->cancelled_reason = null;
                if (!$copy->save()) {
                    $this->errors[] = $row->displayName() . ': ' . implode(' ', array_merge(...array_values($copy->getErrors())));
                    $tx->rollBack();
                    return false;
                }
                $this->copyChildren($row, $copy);
                $this->copied++;
            }
            $tx->commit();
            return true;
        } catch (\Throwable $e) {
            $tx->rollBack();
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * คัดลอกนิยามที่ผูกกับตัวชี้วัดรายปี — เกณฑ์คะแนน รอบการประเมิน และข้อมูลพื้นฐาน
     * ผลงานของปีต้นทาง (ผลรายรอบ ระดับคะแนน ผลรายเดือน) ไม่ถูกคัดลอกมา
     */
    private function copyChildren(StrategyIndicatorYear $source, StrategyIndicatorYear $target): void
    {
        foreach ($source->scores as $score) {
            $copy = new StrategyIndicatorScore($score->attributes);
            $copy->id = null; $copy->ref = null; $copy->isNewRecord = true;
            $copy->indicator_year_id = $target->id;
            $copy->save(false);
        }
        foreach ($source->periods as $period) {
            $copy = new StrategyIndicatorPeriod($period->attributes);
            $copy->id = null; $copy->ref = null; $copy->isNewRecord = true;
            $copy->indicator_year_id = $target->id;
            $copy->actual_value = null; $copy->score_level = null;
            $copy->save(false);
        }
        foreach ($source->baselines as $baseline) {
            $copy = new StrategyIndicatorBaseline($baseline->attributes);
            $copy->id = null; $copy->ref = null; $copy->isNewRecord = true;
            $copy->indicator_year_id = $target->id;
            $copy->save(false);
        }
    }

    public function summary(): string
    {
        $text = "คัดลอกตัวชี้วัดแล้ว {$this->copied} รายการ";
        return $this->skipped ? $text . " (ข้าม {$this->skipped} รายการที่ปีปลายทางมีอยู่แล้ว)" : $text;
    }
}
