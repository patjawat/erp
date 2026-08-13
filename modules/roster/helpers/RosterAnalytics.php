<?php

namespace app\modules\roster\helpers;

use app\modules\hr\models\Employees;
use app\modules\roster\models\Item;
use app\modules\roster\models\Period;
use app\modules\roster\models\ShiftType;
use app\modules\roster\models\Swap;
use app\modules\roster\models\UnitShift;
use yii\db\Query;

/**
 * ตัวเลขสำหรับผู้ตรวจสอบ — ตอบ 4 คำถามที่หัวหน้ากลุ่มงานถามจริงเวลาตรวจตารางเวร
 *
 *   1 เดือนหน้าหน่วยไหนยังไม่ส่ง          → periodStatusMatrix()
 *   2 วันไหนคนไม่พอ                        → coverageHeatmap()
 *   3 ตารางไหนฝืนกฎ และฝืนกี่ครั้ง         → violationSummary()
 *   4 ในหน่วยเดียวกัน มีใครถูกจัดหนักผิดปกติ → fairness()
 *
 * ข้อ 3 คำนวณสดจากข้อมูลปัจจุบัน ไม่ได้เก็บ snapshot ไว้ตอนอนุมัติ
 * แปลว่าถ้ากฎถูกแก้ภายหลัง ตัวเลขย้อนหลังจะเปลี่ยนตาม — ยอมรับได้ในเฟสนี้
 * เพราะยังไม่ต้องใช้เป็นหลักฐานทางการ ถ้าต้องใช้จริงค่อยเพิ่มตารางเก็บผลตอน submit
 */
class RosterAnalytics
{
    /**
     * สถานะรอบเวรของทุกหน่วย ย้อนหลัง/ล่วงหน้าหลายเดือน
     * @param int[] $unitIds
     * @return array{months: array, matrix: array} matrix[unitId][ym] = Period|null
     */
    /**
     * @param int|null $centerYear  ปีที่ผู้ใช้กำลังดู (null = เดือนปัจจุบัน)
     * @param int|null $centerMonth เดือนที่ผู้ใช้กำลังดู
     */
    public static function periodStatusMatrix(
        array $unitIds,
        int $monthsBack = 2,
        int $monthsAhead = 2,
        ?int $centerYear = null,
        ?int $centerMonth = null
    ): array {
        // ยึดเดือนที่ผู้ใช้กำลังดูเป็นจุดกึ่งกลาง ไม่ใช่เดือนปัจจุบัน
        // ไม่งั้นพอเลื่อนไปดูเดือนอื่น ตารางสถานะจะยังค้างอยู่ที่เดือนนี้ ทำให้อ่านคนละเรื่องกับ heatmap ข้างล่าง
        $base = ($centerYear && $centerMonth)
            ? sprintf('%04d-%02d-01', $centerYear, $centerMonth)
            : date('Y-m-01');

        $months = [];
        for ($i = -$monthsBack; $i <= $monthsAhead; $i++) {
            $ts = strtotime($base . " $i month");
            $months[] = ['y' => (int) date('Y', $ts), 'm' => (int) date('n', $ts), 'key' => date('Y-m', $ts)];
        }
        if (empty($unitIds) || empty($months)) {
            return ['months' => $months, 'matrix' => []];
        }

        $keys = array_column($months, 'key');
        $periods = Period::find()
            ->where(['unit_id' => $unitIds, 'deleted_at' => null])
            ->andWhere(['between', 'year_ce', (int) $months[0]['y'], (int) end($months)['y']])
            ->all();
        $periods = array_filter($periods, static fn(Period $p) => in_array(
            sprintf('%04d-%02d', $p->year_ce, $p->month), $keys, true
        ));

        // เดือนหนึ่งมีได้หลายแผ่น จึงเก็บเป็นรายการ ไม่ใช่ค่าเดียว
        $matrix = [];
        foreach ($periods as $period) {
            $key = sprintf('%04d-%02d', $period->year_ce, $period->month);
            $matrix[(int) $period->unit_id][$key][] = $period;
        }
        return ['months' => $months, 'matrix' => $matrix];
    }

    /**
     * ความครบของกำลังคนรายวัน — [unitId][day] = ['short' => ขาดกี่คนรวม, 'detail' => [typeId => [have, need]]]
     * ใช้วาด heatmap ให้เห็นว่าวันไหนทั้งกลุ่มขาดคนพร้อมกัน
     * @param Period[] $periods รอบของเดือนเดียวกัน
     */
    public static function coverageHeatmap(array $periods): array
    {
        // หน่วยหนึ่งมีหลายแผ่นในเดือนเดียว — รวมยอดขาดของทุกแผ่นเข้าด้วยกัน
        // เพราะผู้ตรวจสอบสนใจว่า "วันนั้นหน่วยนี้ขาดคนรวมกี่คน" ไม่ได้แยกทีละแผ่น
        $result = [];
        foreach ($periods as $period) {
            $unitId = (int) $period->unit_id;
            $sheetShifts = $period->sheetShifts();
            $counts = Item::countByDayShift((int) $period->id);
            $days = $period->daysInMonth();
            $holidays = RosterContext::holidays($period->firstDate(), $period->lastDate());

            for ($d = 1; $d <= $days; $d++) {
                $cell = $result[$unitId][$d] ?? ['short' => 0, 'detail' => [], 'configured' => false];
                $dow = (int) date('w', strtotime($period->dateOfDay($d)));
                foreach ($sheetShifts as $shiftId => $unitShift) {
                    // อัตรากำลังต่างกันตามประเภทวัน — ไม่งั้นเสาร์อาทิตย์จะขึ้นขาดคนทั้งที่จัดครบ
                    $need = $unitShift->requiredFor(isset($holidays[$d]), $dow);
                    if ($need <= 0) {
                        continue;
                    }
                    $have = $counts[$d][$shiftId] ?? 0;
                    $cell['detail'][$shiftId] = [$have, $need];
                    if ($have < $need) {
                        $cell['short'] += $need - $have;
                    }
                }
                $cell['configured'] = $cell['configured'] || !empty($sheetShifts);
                $result[$unitId][$d] = $cell;
            }
        }
        return $result;
    }

    /**
     * นับการละเมิดกฎต่อหน่วย — ตรวจทุกช่องเวรของรอบนั้นซ้ำอีกครั้ง
     * @param Period[] $periods
     * @return array[] [unitId => ['total' => n, 'byRule' => [ข้อความ => n], 'people' => [empId => n]]]
     */
    public static function violationSummary(array $periods): array
    {
        $result = [];
        foreach ($periods as $period) {
            $unitId = (int) $period->unit_id;
            $checker = new RuleChecker($unitId);

            // ดึงเวรทั้งรอบครั้งเดียวแล้วสร้าง map ต่อคน — ไม่ query ต่อช่อง
            $items = Item::find()
                ->where(['period_id' => $period->id])
                ->andWhere(['<>', 'status', Item::STATUS_CANCELLED])
                ->orderBy(['work_date' => SORT_ASC])
                ->all();
            $byEmp = [];
            foreach ($items as $item) {
                $byEmp[(int) $item->emp_id][(string) $item->work_date][] = (int) $item->unit_shift_id;
            }

            $total = 0;
            $byRule = [];
            $people = [];
            foreach ($items as $item) {
                $empId = (int) $item->emp_id;
                $shifts = $byEmp[$empId] ?? [];
                // เอาเวรใบนี้ออกก่อนตรวจ เพื่อไม่ให้ตรวจชนตัวเอง
                $date = (string) $item->work_date;
                $own = $shifts;
                $own[$date] = array_values(array_diff($own[$date] ?? [], [(int) $item->unit_shift_id]));
                foreach ($checker->checkAssignment($date, (int) $item->unit_shift_id, $own) as $warning) {
                    $total++;
                    $byRule[$warning] = ($byRule[$warning] ?? 0) + 1;
                    $people[$empId] = ($people[$empId] ?? 0) + 1;
                }
            }
            // รวมยอดของทุกแผ่นในหน่วยเดียวกัน
            $prev = $result[$unitId] ?? ['total' => 0, 'byRule' => [], 'people' => []];
            $total += $prev['total'];
            foreach ($prev['byRule'] as $k => $v) {
                $byRule[$k] = ($byRule[$k] ?? 0) + $v;
            }
            foreach ($prev['people'] as $k => $v) {
                $people[$k] = ($people[$k] ?? 0) + $v;
            }
            arsort($byRule);
            arsort($people);
            $result[$unitId] = ['total' => $total, 'byRule' => $byRule, 'people' => $people];
        }
        return $result;
    }

    /**
     * ความเป็นธรรมในหน่วย — จำนวนเวรดึก / เวรวันหยุด-สุดสัปดาห์ ต่อคน
     * ส่วนต่างมาก = มีคนถูกจัดหนักกว่าคนอื่นผิดปกติ ซึ่งเป็นต้นตอข้อร้องเรียนจริง
     * @param Period[] $periods
     */
    public static function fairness(array $periods): array
    {
        $result = [];
        foreach ($periods as $period) {
            $unitId = (int) $period->unit_id;
            $holidays = RosterContext::holidays($period->firstDate(), $period->lastDate());
            $weekends = RosterContext::weekends((int) $period->year_ce, (int) $period->month);

            // เวรที่ถือว่า "ดึก" = หมวดเป็นเวรดึก หรือคาบข้ามเที่ยงคืน (บ่ายดึกเข้าข่ายด้วย)
            // ไม่นับเวรรอเรียก เพราะไม่ได้อยู่เวรจริง
            $nightShiftIds = [];
            foreach (UnitShift::listForUnit($unitId) as $unitShift) {
                if ($unitShift->is_standby) {
                    continue;
                }
                if (($unitShift->shiftType && $unitShift->shiftType->is_night) || $unitShift->cross_midnight) {
                    $nightShiftIds[] = (int) $unitShift->id;
                }
            }

            // วันหยุด (OFF) ไม่ใช่การทำงาน ต้องตัดออกก่อนนับ ไม่งั้นคนที่ "หยุด" วันเสาร์
            // จะถูกนับว่า "มาทำงาน" วันเสาร์ แล้วรายงานความเป็นธรรมอ่านกลับด้าน
            $offShiftIds = [];
            foreach (UnitShift::listForUnit($unitId) as $unitShift) {
                if ($unitShift->isOff()) {
                    $offShiftIds[] = (int) $unitShift->id;
                }
            }

            $rows = (new Query())
                ->select(['emp_id', 'work_date', 'unit_shift_id'])
                ->from(Item::tableName())
                ->where(['period_id' => $period->id])
                ->andWhere(['<>', 'status', Item::STATUS_CANCELLED])
                ->all();

            // สะสมต่อจากแผ่นอื่นของหน่วยเดียวกัน — คนหนึ่งอาจอยู่ทั้งแผ่นหลักและแผ่น On call
            $stat = $result[$unitId]['perEmployee'] ?? [];
            foreach ($rows as $row) {
                if (in_array((int) $row['unit_shift_id'], $offShiftIds, true)) {
                    continue;
                }
                $empId = (int) $row['emp_id'];
                $day = (int) date('j', strtotime($row['work_date']));
                $stat[$empId] = $stat[$empId] ?? ['total' => 0, 'night' => 0, 'offday' => 0];
                $stat[$empId]['total']++;
                if (in_array((int) $row['unit_shift_id'], $nightShiftIds, true)) {
                    $stat[$empId]['night']++;
                }
                if (isset($holidays[$day]) || !empty($weekends[$day])) {
                    $stat[$empId]['offday']++;
                }
            }
            if (empty($stat)) {
                continue;
            }

            $names = [];
            foreach (Employees::find()->select(['id', 'prefix', 'fname', 'lname'])
                         ->where(['id' => array_keys($stat)])->asArray()->all() as $emp) {
                $names[(int) $emp['id']] = trim(($emp['prefix'] ?? '') . $emp['fname'] . ' ' . $emp['lname']);
            }

            $nights = array_column($stat, 'night');
            $offdays = array_column($stat, 'offday');
            $result[$unitId] = [
                'perEmployee' => $stat,
                'names' => $names,
                'nightMin' => min($nights),
                'nightMax' => max($nights),
                'nightSpread' => max($nights) - min($nights),
                'offdayMin' => min($offdays),
                'offdayMax' => max($offdays),
                'offdaySpread' => max($offdays) - min($offdays),
            ];
        }
        return $result;
    }

    /** จำนวนใบเปลี่ยนตัวเวรต่อหน่วย — เยอะผิดปกติ = ตารางตั้งต้นอาจไม่ตรงกับหน้างาน */
    public static function swapCounts(array $periods): array
    {
        $ids = array_map(static fn(Period $p) => (int) $p->id, $periods);
        if (empty($ids)) {
            return [];
        }
        $rows = (new Query())
            ->select(['period_id', 'status', 'c' => 'COUNT(*)'])
            ->from(Swap::tableName())
            ->where(['period_id' => $ids])
            ->groupBy(['period_id', 'status'])
            ->all();
        $byPeriod = [];
        foreach ($rows as $row) {
            $byPeriod[(int) $row['period_id']][(string) $row['status']] = (int) $row['c'];
        }
        $result = [];
        foreach ($periods as $period) {
            $unitId = (int) $period->unit_id;
            foreach ($byPeriod[(int) $period->id] ?? [] as $status => $n) {
                $result[$unitId][$status] = ($result[$unitId][$status] ?? 0) + $n;
            }
            $result[$unitId] = $result[$unitId] ?? [];
        }
        return $result;
    }
}
