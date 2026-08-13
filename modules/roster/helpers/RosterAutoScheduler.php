<?php

namespace app\modules\roster\helpers;

use app\modules\hr\models\Employees;
use app\modules\roster\models\Item;
use app\modules\roster\models\Period;
use app\modules\roster\models\UnitShift;
use Yii;
use yii\db\Query;

/**
 * จัดเวรอัตโนมัติ — เติมช่องที่ยังขาดให้ครบตามอัตรากำลัง
 *
 * ไม่ใช่ AI แต่เป็น greedy + ระบบให้คะแนน ทำงานตรงไปตรงมา:
 *   วนทีละวัน → ทีละเวร → เติมทีละที่นั่งจนครบจำนวนที่ต้องการ
 *   แต่ละที่นั่ง: คัดคนที่ใส่ไม่ได้ออก → ให้คะแนนคนที่เหลือ → เลือกคะแนนดีที่สุด
 *
 * หลักการที่ตั้งใจ
 *
 * 1. ไม่ลบของเดิม — เติมเฉพาะช่องที่ขาด เวรที่หัวหน้าจัดมือไว้จะไม่ถูกแตะ
 *    ถ้าต้องการเริ่มใหม่ให้กด "ล้างทั้งเดือน" ก่อน ซึ่งเป็นการกระทำที่ชัดเจนกว่า
 *
 * 2. สองรอบ — รอบแรกถือกฎเป็นข้อห้าม รอบสองผ่อนกฎเฉพาะช่องที่ยังขาด
 *    เพราะเมื่อกำลังคนตึงจริง การมีคนอยู่เวรสำคัญกว่าการรักษากฎให้ครบ
 *    ทุกครั้งที่ผ่อนจะบันทึกคำเตือนไว้ให้หัวหน้าเห็น ไม่ปล่อยผ่านเงียบๆ
 *
 * 3. ความเป็นธรรมมาจากคะแนน — คนที่ทำงานน้อยและได้เวรชนิดนี้น้อยจะถูกเลือกก่อน
 *    ทำให้ภาระงานเกลี่ยกันเองโดยไม่ต้องมีขั้นตอนจัดสมดุลแยก
 *
 * 4. สุ่มลำดับเริ่มต้น — กดซ้ำแล้วได้ตารางต่างกัน หัวหน้าเลือกอันที่พอใจได้
 */
class RosterAutoScheduler
{
    /** คะแนนที่แปลว่า "ห้ามใส่" */
    private const BLOCKED = PHP_INT_MAX;

    private Period $period;
    private int $unitId;
    private RuleChecker $checker;

    /** @var UnitShift[] index ด้วย id */
    private array $shifts;
    /** @var array<int, array> พนักงานที่เลือกได้ */
    private array $employees = [];
    /** @var array<int, int> emp_id => position_id */
    private array $positionOf = [];
    /** @var array<int, array<int, bool>> emp_id => [day => true] วันที่ลา/ไปราชการ */
    private array $unavailable = [];
    /** @var array<int, array<string, int[]>> [emp_id][Y-m-d] => unit_shift_id[] — สถานะเวรระหว่างจัด */
    private array $assigned = [];
    /** @var array<string, array<int, int>> [Y-m-d][unit_shift_id] => จำนวนคนที่จัดแล้ว */
    private array $dayCount = [];
    /** @var array<int, int> emp_id => จำนวนเวรทำงานรวม */
    private array $workload = [];
    /** @var array<string, int> emp_id . '|' . shiftId => จำนวนครั้ง */
    private array $perShift = [];
    /** @var array<int, bool> unit_shift_id => เป็นวันหยุดไหม (กันคิวรีซ้ำในลูปชั้นใน) */
    private array $offShiftCache = [];

    private array $warnings = [];
    private array $shortages = [];
    private int $placed = 0;

    public function __construct(Period $period)
    {
        $this->period = $period;
        $this->unitId = (int) $period->unit_id;
        $this->checker = new RuleChecker($this->unitId);
        $this->shifts = $period->sheetShifts();
    }

    /**
     * @param bool $allowRelax เติมต่อแม้ต้องผ่อนกฎ
     *   true  = เติมให้ครบทุกช่อง แล้วรายงานว่าผ่อนกฎตรงไหนบ้าง
     *   false = หยุดที่กฎ ปล่อยให้ช่องที่เหลือว่างไว้ให้หัวหน้าตัดสินใจเอง
     *
     * @return array{placed:int, warnings:string[], shortages:array, relaxed:int}
     */
    public function run(bool $allowRelax = true): array
    {
        if (empty($this->shifts)) {
            return ['placed' => 0, 'warnings' => ['หน่วยงานนี้ยังไม่ได้ตั้งเวร'], 'shortages' => [], 'relaxed' => 0];
        }

        $this->loadEmployees();
        if (empty($this->employees)) {
            return ['placed' => 0, 'warnings' => ['ไม่พบเจ้าหน้าที่ในหน่วยงานนี้'], 'shortages' => [], 'relaxed' => 0];
        }

        $this->loadContext();
        $this->loadExistingAssignments();

        $holidays = RosterContext::holidays($this->period->firstDate(), $this->period->lastDate());
        $days = $this->period->daysInMonth();
        $relaxed = 0;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // รอบที่ 1 ถือกฎเป็นข้อห้าม · รอบที่ 2 ผ่อนกฎเฉพาะช่องที่ยังขาด
            $passes = $allowRelax ? [true, false] : [true];
            foreach ($passes as $strict) {
                for ($day = 1; $day <= $days; $day++) {
                    $date = $this->period->dateOfDay($day);
                    $dow = (int) date('w', strtotime($date));
                    $isHoliday = isset($holidays[$day]);

                    foreach ($this->shifts as $shiftId => $shift) {
                        if ($shift->isOff()) {
                            continue; // ไม่จัดวันหยุดให้อัตโนมัติ
                        }
                        $need = $shift->requiredFor($isHoliday, $dow);
                        if ($need <= 0) {
                            continue;
                        }
                        $have = $this->countOn($date, (int) $shiftId);
                        for ($i = $have; $i < $need; $i++) {
                            $empId = $this->pick($date, (int) $shiftId, $strict);
                            if ($empId === null) {
                                break;
                            }
                            if (!$strict) {
                                // ต้องเก็บคำเตือนก่อน assign ไม่งั้นเวรที่เพิ่งใส่จะกลายเป็นตัวชนตัวเอง
                                $this->recordWarnings($empId, $date, (int) $shiftId);
                                $relaxed++;
                            }
                            $this->assign($empId, $date, (int) $shiftId);
                        }
                    }
                }
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw new \RuntimeException('จัดเวรอัตโนมัติไม่สำเร็จ: ' . $e->getMessage(), 0, $e);
        }

        $this->collectShortages($holidays, $days);

        if ($relaxed > 0) {
            array_unshift($this->warnings, sprintf(
                'มี %d ช่องที่ต้องผ่อนกฎเพื่อให้มีคนอยู่เวร — กำลังคนอาจไม่พอ ควรตรวจก่อนส่ง',
                $relaxed
            ));
        }
        if ($this->checker->usingDefaults()) {
            array_unshift($this->warnings,
                'หน่วยงานนี้ยังไม่ได้ตั้งกฎการจัดเวร ระบบใช้กฎแนะนำไปก่อน '
                . '(พัก 8 ชม. · ไม่เกิน 6 วันติด · ไม่เกิน 48 ชม./สัปดาห์) — ตั้งกฎเองได้ที่หน้าตั้งค่าเวร');
        }

        return [
            'placed' => $this->placed,
            'warnings' => array_values(array_unique($this->warnings)),
            'shortages' => $this->shortages,
            'relaxed' => $relaxed,
        ];
    }

    // ── เตรียมข้อมูล ─────────────────────────────────────────────────────────

    private function loadEmployees(): void
    {
        $rows = (new Query())
            ->select(['e.id', 'e.prefix', 'e.fname', 'e.lname', 'e.work_shift', 'e.employee_position_id'])
            ->from(['e' => Employees::tableName()])
            ->where(['e.department' => $this->unitId, 'e.status' => 1])
            ->all();

        // ถ้าหน่วยมีคนที่ถูกกำหนดให้ขึ้นเวร ใช้เฉพาะกลุ่มนั้น
        // ไม่งั้นเจ้าหน้าที่ธุรการในหน่วยเดียวกันจะถูกจัดเวรไปด้วย
        $shiftWorkers = array_values(array_filter($rows, static fn($r) => ($r['work_shift'] ?? '') === 'shift'));
        $pool = $shiftWorkers ?: $rows;

        foreach ($pool as $row) {
            $id = (int) $row['id'];
            $this->employees[$id] = $row;
            $this->positionOf[$id] = (int) ($row['employee_position_id'] ?? 0);
            $this->workload[$id] = 0;
        }

        // สุ่มลำดับ เพื่อให้กดซ้ำแล้วได้ตารางต่างกัน (คะแนนเท่ากันจะสลับกันได้)
        $keys = array_keys($this->employees);
        shuffle($keys);
        $shuffled = [];
        foreach ($keys as $k) {
            $shuffled[$k] = $this->employees[$k];
        }
        $this->employees = $shuffled;
    }

    private function loadContext(): void
    {
        $empIds = array_keys($this->employees);
        $from = $this->period->firstDate();
        $to = $this->period->lastDate();

        foreach (RosterContext::leaves($empIds, $from, $to) as $empId => $byDay) {
            foreach ($byDay as $day => $info) {
                $this->unavailable[(int) $empId][(int) $day] = true;
            }
        }
        foreach (RosterContext::trips($empIds, $from, $to) as $empId => $byDay) {
            foreach ($byDay as $day => $info) {
                $this->unavailable[(int) $empId][(int) $day] = true;
            }
        }
        // คำขอหยุดที่หัวหน้ารับแล้ว ถือเป็นวันที่จัดไม่ได้
        $accepted = \app\modules\roster\models\Request::find()
            ->where(['unit_id' => $this->unitId, 'type' => \app\modules\roster\models\Request::TYPE_OFF])
            ->andWhere(['status' => \app\modules\roster\models\Request::STATUS_ACCEPTED])
            ->andWhere(['between', 'work_date', $from, $to])
            ->all();
        foreach ($accepted as $req) {
            $this->unavailable[(int) $req->emp_id][(int) date('j', strtotime($req->work_date))] = true;
        }
    }

    /**
     * อ่านเวรที่มีอยู่แล้วเข้าสถานะ เพื่อไม่จัดซ้ำและให้คะแนนภาระงานถูก
     *
     * ดึงเวรของคนกลุ่มนี้ "ทุกแผ่น" คร่อมขอบเดือน ±3 วัน ไม่ใช่เฉพาะรอบนี้
     * เพราะหน่วยหนึ่งมีหลายแผ่นในเดือนเดียว (บ่ายดึก / Refer / On call)
     * ถ้าดูแค่แผ่นตัวเอง กฎ "ดึกติดเช้า" กับเพดานชั่วโมงจะมองไม่เห็นเวรอีกแผ่น
     */
    private function loadExistingAssignments(): void
    {
        $first = $this->period->firstDate();
        $last = $this->period->lastDate();

        $rows = (new Query())
            ->select(['emp_id', 'work_date', 'unit_shift_id'])
            ->from(Item::tableName())
            ->where(['emp_id' => array_keys($this->employees)])
            ->andWhere(['<>', 'status', Item::STATUS_CANCELLED])
            ->andWhere(['between', 'work_date',
                date('Y-m-d', strtotime($first . ' -3 day')),
                date('Y-m-d', strtotime($last . ' +3 day'))])
            ->all();

        foreach ($rows as $row) {
            $empId = (int) $row['emp_id'];
            $shiftId = (int) $row['unit_shift_id'];
            $date = (string) $row['work_date'];

            $this->assigned[$empId][$date][] = $shiftId;
            $this->dayCount[$date][$shiftId] = ($this->dayCount[$date][$shiftId] ?? 0) + 1;

            // ภาระงานนับเฉพาะในเดือนที่กำลังจัด — วันคร่อมขอบมีไว้ให้กฎดูเท่านั้น
            if ($date >= $first && $date <= $last && !$this->isOffShift($shiftId)) {
                $this->workload[$empId]++;
                $key = $empId . '|' . $shiftId;
                $this->perShift[$key] = ($this->perShift[$key] ?? 0) + 1;
            }
        }
    }

    // ── การเลือกคน ───────────────────────────────────────────────────────────

    /** เลือกคนที่เหมาะที่สุดสำหรับช่องนี้ — null = ไม่มีใครใส่ได้ */
    private function pick(string $date, int $shiftId, bool $strict): ?int
    {
        $best = null;
        $bestScore = self::BLOCKED;

        foreach (array_keys($this->employees) as $empId) {
            $score = $this->score($empId, $date, $shiftId, $strict);
            if ($score === self::BLOCKED) {
                continue;
            }
            if ($score < $bestScore) {
                $bestScore = $score;
                $best = $empId;
            }
        }
        return $best;
    }

    /**
     * คะแนนยิ่งน้อยยิ่งเหมาะ · BLOCKED = ใส่ไม่ได้
     * เงื่อนไขที่ห้ามเสมอไม่ว่ารอบไหน: ลา ไปราชการ ซ้ำเวรเดิม ผิดวิชาชีพ
     * เงื่อนไขที่ผ่อนได้ในรอบสอง: กฎเวรต่อเนื่อง เวลาพัก เพดานชั่วโมง
     */
    private function score(int $empId, string $date, int $shiftId, bool $strict): int
    {
        $day = (int) date('j', strtotime($date));
        $shift = $this->shifts[$shiftId] ?? null;
        if (!$shift) {
            return self::BLOCKED;
        }

        // ── ห้ามเสมอ ──
        if (!empty($this->unavailable[$empId][$day])) {
            return self::BLOCKED; // ลา / ไปราชการ / ขอหยุดที่อนุมัติแล้ว
        }
        $todays = $this->assigned[$empId][$date] ?? [];
        if (in_array($shiftId, $todays, true)) {
            return self::BLOCKED; // มีเวรนี้อยู่แล้ว
        }
        foreach ($todays as $existing) {
            if ($this->isOffShift((int) $existing)) {
                return self::BLOCKED; // วันนั้นทำเครื่องหมายหยุดไว้
            }
        }
        if ($shift->position_id && $this->positionOf[$empId] !== (int) $shift->position_id) {
            return self::BLOCKED; // ผิดวิชาชีพ — อัตราค่าตอบแทนจะผิดตาม
        }

        // ── กฎที่ผ่อนได้ ──
        // ไม่บันทึกคำเตือนตรงนี้ เพราะ score() ถูกเรียกกับ "ผู้สมัคร" ทุกคน
        // คนที่ไม่ได้ถูกเลือกก็จะมีคำเตือนติดมาด้วย — บันทึกหลังเลือกเสร็จแทน
        $violations = $this->violationsFor($empId, $date, $shiftId);
        if ($violations && $strict) {
            return self::BLOCKED;
        }

        // ── คะแนน: ภาระงานน้อยมาก่อน แล้วจึงกระจายชนิดเวร ──
        $score = $this->workload[$empId] * 10;
        $score += ($this->perShift[$empId . '|' . $shiftId] ?? 0) * 4;
        $score += count($todays) * 25;        // เลี่ยงการให้คนเดียวหลายเวรในวันเดียว
        $score += count($violations) * 100;   // รอบผ่อนกฎ — ยิ่งผิดมากยิ่งท้ายแถว

        return $score;
    }

    /** @return string[] */
    private function violationsFor(int $empId, string $date, int $shiftId): array
    {
        return $this->checker->checkAssignment($date, $shiftId, $this->assigned[$empId] ?? []);
    }

    /** บันทึกคำเตือนของคนที่ "ถูกเลือกจริง" ในรอบผ่อนกฎ */
    private function recordWarnings(int $empId, string $date, int $shiftId): void
    {
        $emp = $this->employees[$empId];
        $name = trim(($emp['prefix'] ?? '') . $emp['fname'] . ' ' . $emp['lname']);
        foreach ($this->violationsFor($empId, $date, $shiftId) as $violation) {
            $this->warnings[] = sprintf('%s %s: %s', $name, date('j/n', strtotime($date)), $violation);
        }
    }

    private function assign(int $empId, string $date, int $shiftId): void
    {
        $item = new Item([
            'period_id' => (int) $this->period->id,
            'emp_id' => $empId,
            'work_date' => $date,
            'unit_shift_id' => $shiftId,
        ]);
        if (!$item->save()) {
            // ไม่โยน exception เพราะจะทิ้งทั้งเดือน — บันทึกไว้ให้หัวหน้าเห็นแล้วไปช่องถัดไป
            $this->warnings[] = sprintf('บันทึกเวรวันที่ %s ไม่สำเร็จ: %s',
                date('j/n', strtotime($date)),
                implode(' ', array_merge(...array_values($item->getErrors()))));
            return;
        }
        $this->assigned[$empId][$date][] = $shiftId;
        $this->dayCount[$date][$shiftId] = ($this->dayCount[$date][$shiftId] ?? 0) + 1;
        $this->workload[$empId]++;
        $key = $empId . '|' . $shiftId;
        $this->perShift[$key] = ($this->perShift[$key] ?? 0) + 1;
        $this->placed++;
    }

    private function countOn(string $date, int $shiftId): int
    {
        return $this->dayCount[$date][$shiftId] ?? 0;
    }

    private function isOffShift(int $shiftId): bool
    {
        if (!isset($this->offShiftCache[$shiftId])) {
            $shift = $this->shifts[$shiftId] ?? UnitShift::findOne($shiftId);
            $this->offShiftCache[$shiftId] = $shift ? $shift->isOff() : false;
        }
        return $this->offShiftCache[$shiftId];
    }

    /** วันที่ยังขาดคน หลังจัดเสร็จ */
    private function collectShortages(array $holidays, int $days): void
    {
        for ($day = 1; $day <= $days; $day++) {
            $date = $this->period->dateOfDay($day);
            $dow = (int) date('w', strtotime($date));
            $isHoliday = isset($holidays[$day]);
            foreach ($this->shifts as $shiftId => $shift) {
                if ($shift->isOff()) {
                    continue;
                }
                $need = $shift->requiredFor($isHoliday, $dow);
                if ($need <= 0) {
                    continue;
                }
                $have = $this->countOn($date, (int) $shiftId);
                if ($have < $need) {
                    $this->shortages[] = [
                        'day' => $day,
                        'shift' => $shift->displayName(),
                        'need' => $need,
                        'have' => $have,
                    ];
                }
            }
        }
    }
}
