<?php

namespace app\modules\finance\components;

/**
 * แสดงวันที่ตามมาตรฐาน ERP: วว/ดด/พ.ศ. (และ วว/ดด/พ.ศ. HH:mm)
 * รับได้ทั้ง 'Y-m-d', 'Y-m-d H:i:s' และ unix timestamp; ค่าว่าง → $empty
 */
class ThaiDate
{
    public static function date($value, string $empty = '-'): string
    {
        $ts = self::toTimestamp($value);
        return $ts === null ? $empty : date('d/m/', $ts) . ((int) date('Y', $ts) + 543);
    }

    public static function datetime($value, string $empty = '-'): string
    {
        $ts = self::toTimestamp($value);
        return $ts === null ? $empty : self::date($ts) . ' ' . date('H:i', $ts);
    }

    private static function toTimestamp($value): ?int
    {
        if ($value === null || $value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return null;
        }
        if (is_int($value) || (is_string($value) && ctype_digit($value))) {
            return (int) $value;
        }
        $ts = strtotime((string) $value);
        return $ts === false ? null : $ts;
    }
}
