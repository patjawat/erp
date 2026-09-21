<?php

namespace app\modules\pm\services;

use app\modules\pm\components\KpiRow;
use app\modules\pm\components\KpiStatus;
use app\modules\pm\models\KpiGroup;
use app\modules\pm\models\KpiIndicator;
use app\modules\pm\models\StrategyPlan;

/**
 * ตัวรวมข้อมูลตัวชี้วัดทุกกลุ่มให้เป็นชุดเดียว (adapter)
 * - กลุ่ม strategy → ดึงจากทะเบียนยุทธศาสตร์ (StrategyIndicator + StrategyIndicatorYear)
 * - กลุ่ม standalone → ดึงจากทะเบียน KPI นอกแผน (KpiIndicator + KpiIndicatorYear)
 *
 * ฝั่ง dashboard (โมดูล executive) เรียกใช้ service นี้ในเฟสถัดไป
 */
class KpiRegistry
{
    /** ปีงบประมาณปัจจุบัน (พ.ศ.) — ขึ้นปีงบใหม่เดือนตุลาคม */
    public static function defaultFiscalYear(): int
    {
        return (int) date('Y') + 543 + ((int) date('n') >= 10 ? 1 : 0);
    }

    /**
     * ตัวชี้วัดทุกกลุ่มของปีงบที่ระบุ เป็น KpiRow[]
     * @return KpiRow[]
     */
    public function rows(?int $fiscalYear = null): array
    {
        $year = $fiscalYear ?: self::defaultFiscalYear();
        $rows = [];
        foreach (KpiGroup::activeGroups() as $group) {
            if ($group->isStrategy()) {
                $rows = array_merge($rows, $this->strategyRows($group, $year));
            } else {
                $rows = array_merge($rows, $this->standaloneRows($group, $year));
            }
        }
        return $rows;
    }

    /** ตัวชี้วัดนอกแผนของกลุ่มหนึ่ง */
    private function standaloneRows(KpiGroup $group, int $year): array
    {
        $rows = [];
        $indicators = KpiIndicator::find()->where(['group_id' => $group->id, 'is_active' => true])
            ->with('years')->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
        foreach ($indicators as $ind) {
            $row = KpiRow::make('standalone', (int) $ind->id);
            $row->groupId = (int) $group->id;
            $row->groupName = $group->name;
            $row->name = (string) $ind->name;
            $row->unit = $ind->unit;
            $row->orgUnitId = $ind->org_unit_id !== null ? (int) $ind->org_unit_id : null;
            $row->operator = $ind->operator;
            foreach ($ind->years as $y) {
                $row->series[(int) $y->fiscal_year] = $y->actual_value !== null ? (float) $y->actual_value : null;
                $row->targetSeries[(int) $y->fiscal_year] = $y->target_value !== null ? (float) $y->target_value : null;
                if ((int) $y->fiscal_year === $year) {
                    $row->target = $y->target_value !== null ? (float) $y->target_value : null;
                    $row->actual = $y->actual_value !== null ? (float) $y->actual_value : null;
                }
            }
            $row->status = KpiStatus::evaluate($row->target, $row->actual, $row->operator);
            $rows[] = $row;
        }
        return $rows;
    }

    /** ตัวชี้วัดยุทธศาสตร์จากชุดแผนปัจจุบัน (operator/target/actual อยู่ที่ระดับปี) */
    private function strategyRows(KpiGroup $group, int $year): array
    {
        $plan = StrategyPlan::current();
        if (!$plan) {
            return [];
        }
        $rows = [];
        $indicators = $plan->getIndicators()->andWhere(['is_active' => true])->with('years')->all();
        foreach ($indicators as $ind) {
            $row = KpiRow::make('strategy', (int) $ind->id);
            $row->groupId = (int) $group->id;
            $row->groupName = $group->name;
            $row->name = (string) $ind->name;
            $row->unit = $ind->unit;
            foreach ($ind->years as $y) {
                $row->series[(int) $y->fiscal_year] = $y->actual_value !== null ? (float) $y->actual_value : null;
                $row->targetSeries[(int) $y->fiscal_year] = $y->target_value !== null ? (float) $y->target_value : null;
                if ((int) $y->fiscal_year === $year) {
                    $row->operator = $y->operator;
                    $row->target = $y->target_value !== null ? (float) $y->target_value : null;
                    $row->actual = $y->actual_value !== null ? (float) $y->actual_value : null;
                }
            }
            $row->status = KpiStatus::evaluate($row->target, $row->actual, $row->operator);
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * สรุปภาพรวมของปีงบ: total / pass / gap / nodata / อัตราสำเร็จ + แยกตามกลุ่ม
     */
    public function summary(?int $fiscalYear = null): array
    {
        $rows = $this->rows($fiscalYear);
        $count = ['total' => 0, 'pass' => 0, 'gap' => 0, 'nodata' => 0];
        $byGroup = [];
        foreach ($rows as $row) {
            $count['total']++;
            $count[$row->status]++;
            $g = $row->groupName;
            $byGroup[$g] ??= ['total' => 0, 'pass' => 0, 'gap' => 0, 'nodata' => 0];
            $byGroup[$g]['total']++;
            $byGroup[$g][$row->status]++;
        }
        $judged = $count['pass'] + $count['gap'];
        $count['successRate'] = $judged > 0 ? round($count['pass'] * 100 / $judged) : 0;
        $count['byGroup'] = $byGroup;
        $count['fiscalYear'] = $fiscalYear ?: self::defaultFiscalYear();
        return $count;
    }
}
