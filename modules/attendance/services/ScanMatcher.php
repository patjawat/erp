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
}
