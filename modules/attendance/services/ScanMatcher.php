<?php
namespace app\modules\attendance\services;

/** Match to a unique nearest boundary, not an alternating sequence of scans. */
class ScanMatcher
{
    public static function match(string $at, array $shifts): array
    {
        $time = new \DateTimeImmutable($at, new \DateTimeZone('Asia/Bangkok'));
        $matches = [];
        foreach ($shifts as $shift) {
            foreach (['in'=>'start','out'=>'end'] as $type=>$key) {
                $boundary = new \DateTimeImmutable($shift[$key], new \DateTimeZone('Asia/Bangkok'));
                $distance = abs($time->getTimestamp() - $boundary->getTimestamp());
                if ($distance <= (int)($shift['window_minutes'] ?? 240) * 60) $matches[] = ['type'=>$type,'shift'=>$shift,'distance'=>$distance];
            }
        }
        usort($matches, fn($a,$b)=>$a['distance'] <=> $b['distance']);
        if (!$matches || (isset($matches[1]) && $matches[0]['distance'] === $matches[1]['distance'])) return ['type'=>'scan','shift'=>null];
        return $matches[0];
    }

    /** Pick the shift this scan belongs to: one that contains the time, else the nearest by boundary. */
    public static function nearestShift(string $at, array $shifts): ?array
    {
        $time = strtotime($at);
        $best = null; $bestDistance = null;
        foreach ($shifts as $shift) {
            $start = strtotime($shift['start']); $end = strtotime($shift['end']);
            if ($time >= $start && $time <= $end) return $shift;
            $distance = min(abs($time - $start), abs($time - $end));
            if ($bestDistance === null || $distance < $bestDistance) { $bestDistance = $distance; $best = $shift; }
        }
        return $best;
    }

    /** สแกนอยู่ในกรอบของเวรนี้ไหม (ช่วง start..end บวกช่วงผ่อนผัน window นาที ทั้งก่อนเริ่มและหลังเลิก) */
    public static function withinWindow(string $at, array $shift): bool
    {
        $time = strtotime($at);
        $window = (int)($shift['window_minutes'] ?? 240) * 60;
        return $time >= strtotime($shift['start']) - $window && $time <= strtotime($shift['end']) + $window;
    }
}
