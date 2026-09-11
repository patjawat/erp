<?php

namespace app\modules\attendance\services;

use app\modules\roster\models\Item;
use app\modules\roster\models\Period;

/** One source of shift times for recording, review and reports. */
class RosterAttendance
{
    public static function interval(string $date, string $start, string $end): array
    {
        $zone = new \DateTimeZone('Asia/Bangkok');
        $from = new \DateTimeImmutable($date . ' ' . $start, $zone);
        $to = new \DateTimeImmutable($date . ' ' . $end, $zone);
        if ($to <= $from) {
            $to = $to->modify('+1 day');
        }
        return ['start' => $from->format('Y-m-d H:i:s'), 'end' => $to->format('Y-m-d H:i:s')];
    }

    public static function shifts(array $employeeIds, string $from, string $to): array
    {
        if (!$employeeIds) return [];
        $items = Item::find()->alias('i')->innerJoinWith(['period p'], false)
            ->with(['unitShift.shiftType', 'shiftType'])
            ->where(['i.emp_id' => $employeeIds, 'p.status' => Period::LIVE_STATUSES, 'p.deleted_at' => null])
            ->andWhere(['<>', 'i.status', Item::STATUS_CANCELLED])
            ->andWhere(['between', 'i.work_date', $from, $to])->orderBy(['i.work_date' => SORT_ASC, 'i.id' => SORT_ASC])->all();
        $result = [];
        $occupied = [];
        foreach ($items as $item) {
            // An explicit roster day (including OFF) must never fall back to normal hours.
            $occupied[(int)$item->emp_id][$item->work_date] = true;
            $shift = $item->unitShift;
            if ($item->isOff() || !$shift || !$shift->start_time || !$shift->end_time) continue;
            $result[] = array_merge(self::interval($item->work_date, $shift->start_time, $shift->end_time), [
                'id' => (int)$item->id, 'emp_id' => (int)$item->emp_id,
                'work_date' => $item->work_date, 'name' => $item->shiftName(),
            ]);
        }
        return array_merge($result, WorkScheduleService::shifts($employeeIds, $from, $to, $occupied));
    }

    /** Limit suggestions to shifts on the adjacent work dates; never guess between multiple shifts. */
    public static function candidates(int $employeeId, string $at): array
    {
        return self::shifts([$employeeId], date('Y-m-d', strtotime($at . ' -1 day')), date('Y-m-d', strtotime($at . ' +1 day')));
    }

    /** Canonical identity of a shift (normal hours keyed by work date, roster by item id). */
    public static function shiftKey(array $shift): string
    {
        return $shift['emp_id'] . ':' . (($shift['source'] ?? '') === 'normal' ? 'normal:' . $shift['work_date'] : $shift['id']);
    }

    /** Which directions (in/out) are already recorded for this shift — or the calendar day when no shift. */
    public static function recordedTypes(int $employeeId, ?array $shift, string $at): array
    {
        $has = ['in' => false, 'out' => false];
        if ($shift) {
            $key = self::shiftKey($shift);
            $from = date('Y-m-d H:i:s', strtotime($shift['start'] . ' -1 day'));
            $to = date('Y-m-d H:i:s', strtotime($shift['end'] . ' +1 day'));
        } else {
            $key = null;
            $date = substr($at, 0, 10);
            $from = $date . ' 00:00:00';
            $to = date('Y-m-d H:i:s', strtotime($date . ' +1 day'));
        }
        $records = \app\modules\attendance\models\CheckinRecord::find()->where(['emp_id' => $employeeId])
            ->andWhere(['<>', 'status', 'rejected'])
            ->andWhere(['>=', 'checkin_at', $from])->andWhere(['<', 'checkin_at', $to])->all();
        foreach ($records as $record) {
            if ($key !== null) {
                $recordShift = self::forRecord($record)['shift'];
                if (!$recordShift || self::shiftKey($recordShift) !== $key) continue;
            }
            if ($record->check_type === 'in') $has['in'] = true;
            elseif ($record->check_type === 'out') $has['out'] = true;
        }
        return $has;
    }

    /** Day summaries use work dates, so a night shift's checkout stays on its starting day. */
    public static function summarize(array $shifts, array $records, string $now): array
    {
        $days = [];
        $byShift = [];
        $schedule = [];
        $keyFor = static fn($shift) => $shift['emp_id'] . ':' . (($shift['source'] ?? '') === 'normal' ? 'normal:'.$shift['work_date'] : $shift['id']);
        $snapshots = [];
        foreach ($shifts as $shift) $schedule[$keyFor($shift)] = $shift;
        foreach ($records as $record) {
            if ($record->status === 'rejected') continue;
            $evaluation = self::forRecord($record);
            $shift = $evaluation['shift'];
            if (!$shift) continue;
            $key = $keyFor($shift);
            $byShift[$key][] = $record;
            // A recorded snapshot remains reportable even after a later roster edit/swap.
            if (!isset($schedule[$key]) || (($shift['source'] ?? '') === 'normal' && !isset($snapshots[$key]))) $schedule[$key] = $shift;
            $snapshots[$key] = true;
        }
        foreach ($schedule as $shift) {
            $emp = $shift['emp_id'];
            $date = $shift['work_date'];
            if (!isset($days[$emp][$date])) $days[$emp][$date] = ['time' => null, 'late' => 0, 'missing' => 0, 'pending' => 0, 'shifts' => 0, 'ended' => false];
            $day =& $days[$emp][$date];
            $day['shifts']++;
            $day['ended'] = $day['ended'] || $shift['end'] < $now;
            $entries = $byShift[$keyFor($shift)] ?? [];
            $first = null;
            $hasPending = false;
            foreach ($entries as $record) {
                if ($record->status === 'pending') $hasPending = true;
                if ($record->check_type === 'in' && (!$first || $record->checkin_at < $first->checkin_at)) $first = $record;
            }
            if ($hasPending) $day['pending']++;
            if ($first) {
                $time = substr($first->checkin_at, 11, 5);
                $day['time'] = $day['time'] === null ? $time : $day['time'] . ', ' . $time;
                // Pending time is visible but has no final late finding until approved.
                if ($first->status === 'approved' && self::forRecord($first)['late_minutes'] > 0) $day['late']++;
            } elseif ($shift['end'] < $now && !$hasPending) {
                $day['missing']++;
            }
            unset($day);
        }
        return $days;
    }

    public static function evaluate(string $at, string $type, ?array $shift): array
    {
        if (!$shift) return ['shift' => null, 'late_minutes' => null, 'early_minutes' => null];
        $zone = new \DateTimeZone('Asia/Bangkok');
        $time = (new \DateTimeImmutable($at, $zone))->getTimestamp();
        $start = (new \DateTimeImmutable($shift['start'], $zone))->getTimestamp();
        $end = (new \DateTimeImmutable($shift['end'], $zone))->getTimestamp();
        return [
            'shift' => $shift,
            'late_minutes' => $type === 'in' ? max(0, (int)ceil(($time - $start) / 60) - (int)($shift['grace_minutes'] ?? 0)) : null,
            'early_minutes' => $type === 'out' ? max(0, (int)ceil(($end - $time) / 60)) : null,
        ];
    }

    public static function forRecord($record): array
    {
        $json = is_array($record->data_json) ? $record->data_json : [];
        // Retain the schedule used at recording time even if a later roster changes.
        return self::evaluate($record->checkin_at, $record->check_type, $json['attendance']['shift'] ?? null);
    }
}
