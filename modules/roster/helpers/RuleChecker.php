<?php

namespace app\modules\roster\helpers;

use app\modules\roster\models\Item;
use app\modules\roster\models\UnitShift;

/**
 * ตรวจกฎการจัดเวร แล้วคืน "คำเตือน" — ไม่บล็อกการบันทึก
 *
 * ตั้งใจให้เตือนอย่างเดียว เพราะหน้างานจริงมีวันที่คนไม่พอจนหัวหน้าต้องฝืนกฎ
 * ถ้าระบบห้ามบันทึก หัวหน้าจะเลิกใช้แล้วกลับไปทำใน Excel ซึ่งแย่กว่า
 *
 * เวรที่ตั้ง is_standby (On call / Refer) ถูกยกเว้นจากกฎเวลาพักและวันทำงานติดกัน
 * เพราะไม่ใช่ชั่วโมงทำงานจริง ถ้านับด้วย คนที่รับ on-call ทุกคืนจะโดนเตือนรัวทั้งเดือน
 * จนคำเตือนกลายเป็นสัญญาณรบกวนที่ไม่มีใครอ่าน
 */
class RuleChecker
{
    /** @var array<string, \app\modules\roster\models\UnitRule[]> */
    private array $rules;
    /** @var array<int, UnitShift> index ด้วย unit_shift_id */
    private array $unitShifts;
    /** หน่วยนี้ยังไม่ได้ตั้งกฎเอง กำลังใช้ชุดแนะนำอยู่ */
    private bool $usingDefaults = false;

    public function __construct(int $unitId)
    {
        $this->rules = \app\modules\roster\models\UnitRule::groupedForUnit($unitId);

        // หน่วยที่ยังไม่เคยตั้งกฎ ใช้ชุดแนะนำเป็นตาข่ายนิรภัยไปก่อน
        // ไม่งั้นตารางที่ให้คนอยู่เวรติดกันทั้งเดือนจะผ่านโดยไม่มีคำเตือนสักคำ
        // (ถ้าหน่วยตั้งกฎไว้บ้างแล้ว ถือว่าตั้งใจ — ไม่เติมให้)
        if (empty($this->rules)) {
            $this->usingDefaults = true;
            foreach (\app\modules\roster\models\UnitRule::defaultSet($unitId) as $rule) {
                $this->rules[$rule->rule_key][] = $rule;
            }
        }

        $this->unitShifts = UnitShift::mapForUnit($unitId);
    }

    /** กำลังใช้กฎชุดแนะนำ (หน่วยยังไม่ได้ตั้งเอง) */
    public function usingDefaults(): bool
    {
        return $this->usingDefaults;
    }

    private function shift(?int $unitShiftId): ?UnitShift
    {
        return $this->unitShifts[(int) $unitShiftId] ?? null;
    }

    private function shiftName(?int $unitShiftId): string
    {
        $shift = $this->shift($unitShiftId);
        return $shift ? $shift->displayName() : 'เวร';
    }

    /**
     * เวรนี้ไม่นับเป็นชั่วโมงทำงานจริงหรือไม่
     * ครอบทั้งเวรรอเรียก (On call/Refer) และวันหยุด (OFF)
     * วันหยุดต้อง "ตัด" ช่วงวันทำงานติดต่อกัน ไม่ใช่ต่อให้ยาวขึ้น
     */
    private function isStandby(?int $unitShiftId): bool
    {
        $shift = $this->shift($unitShiftId);
        if (!$shift) {
            return false;
        }
        return (int) $shift->is_standby === 1 || $shift->isOff();
    }

    /** หมวดของเวร — กฎคู่เวรอ้างหมวด ไม่ได้อ้างชื่อเวรของหน่วย */
    private function typeOf(?int $unitShiftId): ?int
    {
        $shift = $this->shift($unitShiftId);
        return $shift ? (int) $shift->shift_type_id : null;
    }

    private function intRule(string $key): ?int
    {
        $rule = $this->rules[$key][0] ?? null;
        return $rule && $rule->int_value !== null ? (int) $rule->int_value : null;
    }

    /**
     * ตรวจการจัดเวร 1 ช่อง โดยดูจากเวรอื่นของคนคนนั้น
     *
     * @param array<string, int[]> $empShifts เวรเดิมของคนนี้ [Y-m-d => [unit_shift_id, ...]]
     * @return string[] ข้อความเตือน (ว่าง = ไม่ผิดกฎ)
     */
    /**
     * ตรวจว่าคนนี้ตรงกับตำแหน่งที่เวรกำหนดไว้ไหม
     * หน่วยงานแยกเวรตามวิชาชีพเพราะอัตราค่าตอบแทนต่างกัน (ชพ/ชป/ชผ)
     * ถ้าจัดคนผิดวิชาชีพลงช่อง เงินจะคิดผิดโดยไม่มีใครเห็น จึงต้องเตือน
     */
    public function checkPosition(int $unitShiftId, int $empId): ?string
    {
        $shift = $this->shift($unitShiftId);
        if (!$shift || !$shift->position_id) {
            return null; // เวรนี้ไม่จำกัดวิชาชีพ
        }
        $empPositionId = (int) (new \yii\db\Query())
            ->select('employee_position_id')->from('employees')->where(['id' => $empId])->scalar();
        if ($empPositionId === (int) $shift->position_id) {
            return null;
        }
        $empPosition = $empPositionId
            ? (string) (new \yii\db\Query())->select('title')->from('employee_position')
                ->where(['id' => $empPositionId])->scalar()
            : 'ไม่ได้ระบุตำแหน่ง';
        return sprintf('%s เป็น%s แต่เวรนี้กำหนดไว้สำหรับ%s — อัตราค่าตอบแทนอาจคิดผิด',
            'คนนี้', $empPosition, $shift->positionName());
    }

    public function checkAssignment(string $workDate, int $unitShiftId, array $empShifts): array
    {
        $warnings = [];

        // วันหยุดไม่ต้องตรวจกฎเวร — แต่เตือนถ้าวันนั้นมีเวรทำงานอยู่ด้วย
        if ($this->shift($unitShiftId) && $this->shift($unitShiftId)->isOff()) {
            foreach ($empShifts[$workDate] ?? [] as $otherShiftId) {
                $other = $this->shift((int) $otherShiftId);
                if ($other && !$other->isOff()) {
                    $warnings[] = sprintf('ทำเครื่องหมายหยุด ทั้งที่วันนี้มี%sอยู่แล้ว', $other->displayName());
                    break;
                }
            }
            return $warnings;
        }
        $prevDate = date('Y-m-d', strtotime($workDate . ' -1 day'));
        $nextDate = date('Y-m-d', strtotime($workDate . ' +1 day'));

        $sameDay = $empShifts[$workDate] ?? [];
        $prevDay = $empShifts[$prevDate] ?? [];
        $nextDay = $empShifts[$nextDate] ?? [];

        $thisType = $this->typeOf($unitShiftId);
        $standby = $this->isStandby($unitShiftId);

        // ── ห้ามอยู่ 2 เวรหมวดนี้ในวันเดียวกัน ──
        foreach ($this->rules[\app\modules\roster\models\UnitRule::KEY_FORBID_SAME_DAY] ?? [] as $rule) {
            $pair = $this->pairOf($rule);
            if (!$pair) {
                continue;
            }
            [$a, $b] = $pair;
            $other = $thisType === $a ? $b : ($thisType === $b ? $a : null);
            if ($other === null) {
                continue;
            }
            foreach ($sameDay as $otherShiftId) {
                if ($this->typeOf($otherShiftId) === $other) {
                    $warnings[] = sprintf('อยู่%sคู่กับ%sในวันเดียวกัน',
                        $this->shiftName($unitShiftId), $this->shiftName($otherShiftId));
                    break;
                }
            }
        }

        // ── ห้ามเวรหมวด a วันนี้ ต่อหมวด b วันถัดไป (คลาสสิก: ดึกติดเช้า) ──
        foreach ($this->rules[\app\modules\roster\models\UnitRule::KEY_FORBID_NEXT_DAY] ?? [] as $rule) {
            $pair = $this->pairOf($rule);
            if (!$pair) {
                continue;
            }
            [$a, $b] = $pair;
            if ($thisType === $b) {
                foreach ($prevDay as $prevShiftId) {
                    if ($this->typeOf($prevShiftId) === $a) {
                        $warnings[] = sprintf('เมื่อวานอยู่%s วันนี้ต่อ%sทันที',
                            $this->shiftName($prevShiftId), $this->shiftName($unitShiftId));
                        break;
                    }
                }
            }
            if ($thisType === $a) {
                foreach ($nextDay as $nextShiftId) {
                    if ($this->typeOf($nextShiftId) === $b) {
                        $warnings[] = sprintf('วันนี้อยู่%s พรุ่งนี้ต่อ%sทันที',
                            $this->shiftName($unitShiftId), $this->shiftName($nextShiftId));
                        break;
                    }
                }
            }
        }

        // ── กฎที่คิดจากชั่วโมงทำงานจริง — เวรรอเรียกไม่เข้าข่าย ──
        if (!$standby) {
            $minRest = $this->intRule(\app\modules\roster\models\UnitRule::KEY_MIN_REST_HOURS);
            if ($minRest !== null) {
                $rest = $this->restHoursBefore($workDate, $unitShiftId, $empShifts);
                if ($rest !== null && $rest < $minRest) {
                    $warnings[] = sprintf('พักจากเวรก่อนหน้าเพียง %s ชม. (ขั้นต่ำ %d ชม.)',
                        rtrim(rtrim(number_format($rest, 1), '0'), '.'), $minRest);
                }
            }

            $maxDays = $this->intRule(\app\modules\roster\models\UnitRule::KEY_MAX_CONSECUTIVE_WORKDAYS);
            if ($maxDays !== null) {
                $run = $this->consecutiveRun($workDate, $empShifts, null);
                if ($run > $maxDays) {
                    $warnings[] = sprintf('ทำงานติดต่อกัน %d วัน (กำหนดไม่เกิน %d)', $run, $maxDays);
                }
            }

            $maxHours = $this->intRule(\app\modules\roster\models\UnitRule::KEY_MAX_HOURS_PER_WEEK);
            if ($maxHours !== null && $maxHours > 0) {
                $hours = $this->weeklyHours($workDate, $unitShiftId, $empShifts);
                if ($hours > $maxHours) {
                    $warnings[] = sprintf('สัปดาห์นี้รวม %s ชม. (เกินเพดาน %d ชม.)',
                        rtrim(rtrim(number_format($hours, 1), '0'), '.'), $maxHours);
                }
            }
        }

        // ── เวรหมวดเดียวกันติดกันเกินกำหนด ──
        foreach ($this->rules[\app\modules\roster\models\UnitRule::KEY_MAX_CONSECUTIVE_SHIFT] ?? [] as $rule) {
            if ((int) $rule->shift_type_id !== $thisType || $rule->int_value === null) {
                continue;
            }
            $run = $this->consecutiveRun($workDate, $empShifts, $thisType);
            if ($run > (int) $rule->int_value) {
                $warnings[] = sprintf('%sติดกัน %d วัน (กำหนดไม่เกิน %d)',
                    $this->shiftName($unitShiftId), $run, (int) $rule->int_value);
            }
        }

        return $warnings;
    }

    /** @return int[]|null [a, b] เป็นรหัสหมวด */
    private function pairOf(\app\modules\roster\models\UnitRule $rule): ?array
    {
        $json = $rule->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        if (!is_array($json) || !isset($json['a'], $json['b'])) {
            return null;
        }
        return [(int) $json['a'], (int) $json['b']];
    }

    /**
     * ชั่วโมงพักจากเวรทำงานล่าสุดก่อนหน้า จนถึงเวลาเริ่มของเวรที่กำลังจะจัด
     * ข้ามเวรรอเรียก เพราะไม่ได้อยู่หน่วยจริง
     */
    private function restHoursBefore(string $workDate, int $unitShiftId, array $empShifts): ?float
    {
        $target = $this->shift($unitShiftId);
        if (!$target || !$target->start_time) {
            return null;
        }
        $startTs = strtotime($workDate . ' ' . $target->start_time);

        $best = null;
        // เวรยาวสุดคือบ่ายดึก 16 ชม. ย้อนดู 2 วันจึงพอ
        foreach ([1, 2] as $back) {
            $date = date('Y-m-d', strtotime($workDate . ' -' . $back . ' day'));
            foreach ($empShifts[$date] ?? [] as $prevShiftId) {
                if ($this->isStandby((int) $prevShiftId)) {
                    continue;
                }
                $prev = $this->shift((int) $prevShiftId);
                if (!$prev || !$prev->end_time) {
                    continue;
                }
                $endTs = strtotime($date . ' ' . $prev->end_time);
                if ($prev->cross_midnight) {
                    $endTs += 86400;
                }
                if ($endTs > $startTs) {
                    continue; // เวรซ้อนกัน — กฎเวรต่อเนื่องจับได้อยู่แล้ว
                }
                $hours = ($startTs - $endTs) / 3600;
                if ($best === null || $hours < $best) {
                    $best = $hours;
                }
            }
        }
        return $best === null ? null : round($best, 2);
    }

    /**
     * ชั่วโมงทำงานรวมของสัปดาห์ปฏิทิน (อาทิตย์–เสาร์) ที่ครอบ $workDate
     * รวมเวรที่กำลังจะจัดเข้าไปด้วย และไม่นับเวรรอเรียก/วันหยุด
     */
    public function weeklyHours(string $workDate, ?int $addingShiftId, array $empShifts): float
    {
        $ts = strtotime($workDate);
        $dow = (int) date('w', $ts);
        $weekStart = strtotime('-' . $dow . ' day', $ts);

        $hours = 0.0;
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime('+' . $i . ' day', $weekStart));
            foreach ($empShifts[$date] ?? [] as $shiftId) {
                $hours += $this->hoursOf((int) $shiftId);
            }
        }
        if ($addingShiftId !== null) {
            $hours += $this->hoursOf($addingShiftId);
        }
        return $hours;
    }

    /** ชั่วโมงของเวรหนึ่ง — เวรรอเรียกและวันหยุดไม่นับเป็นชั่วโมงทำงาน */
    private function hoursOf(int $unitShiftId): float
    {
        $shift = $this->shift($unitShiftId);
        if (!$shift || $this->isStandby($unitShiftId)) {
            return 0.0;
        }
        return (float) ($shift->hours ?? 0);
    }

    /**
     * ความยาวของช่วงวันติดกันที่ครอบ $workDate
     * $typeId = null → นับทุกเวรทำงาน (ไม่นับเวรรอเรียก)
     */
    private function consecutiveRun(string $workDate, array $empShifts, ?int $typeId): int
    {
        $has = function (string $date) use ($empShifts, $typeId): bool {
            foreach ($empShifts[$date] ?? [] as $shiftId) {
                if ($this->isStandby((int) $shiftId)) {
                    continue;
                }
                if ($typeId === null || $this->typeOf((int) $shiftId) === $typeId) {
                    return true;
                }
            }
            return false;
        };

        $run = 1;
        for ($i = 1; $i <= 31; $i++) {
            if (!$has(date('Y-m-d', strtotime($workDate . ' -' . $i . ' day')))) {
                break;
            }
            $run++;
        }
        for ($i = 1; $i <= 31; $i++) {
            if (!$has(date('Y-m-d', strtotime($workDate . ' +' . $i . ' day')))) {
                break;
            }
            $run++;
        }
        return $run;
    }

    /**
     * เวรเดิมของคนหนึ่งในรูปที่ checkAssignment ต้องการ [Y-m-d => [unit_shift_id,...]]
     * ดึงคร่อมขอบเดือน ±3 วัน เพื่อให้กฎข้ามเดือนยังจับได้
     */
    public static function shiftsOfEmployee(int $empId, string $fromDate, string $toDate, ?int $excludeItemId = null): array
    {
        $query = Item::find()
            ->select(['work_date', 'unit_shift_id', 'id'])
            ->where(['emp_id' => $empId])
            ->andWhere(['<>', 'status', Item::STATUS_CANCELLED])
            ->andWhere(['between', 'work_date',
                date('Y-m-d', strtotime($fromDate . ' -3 day')),
                date('Y-m-d', strtotime($toDate . ' +3 day'))]);
        if ($excludeItemId) {
            $query->andWhere(['<>', 'id', $excludeItemId]);
        }
        $map = [];
        foreach ($query->asArray()->all() as $row) {
            $map[(string) $row['work_date']][] = (int) $row['unit_shift_id'];
        }
        return $map;
    }
}
