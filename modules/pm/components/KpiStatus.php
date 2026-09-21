<?php

namespace app\modules\pm\components;

/**
 * ประเมินสถานะตัวชี้วัด PASS / GAP / ไม่มีข้อมูล จากค่าเป้า-ผลจริง-เงื่อนไข (operator)
 * ใช้ร่วมกันทั้งตัวชี้วัดยุทธศาสตร์ (StrategyIndicatorYear) และนอกแผน (KpiIndicatorYear)
 * เพื่อให้เกณฑ์ตัดสิน PASS/GAP สอดคล้องกันทั้งระบบ
 */
final class KpiStatus
{
    public const PASS = 'pass';
    public const GAP = 'gap';
    public const NODATA = 'nodata';

    /**
     * @param mixed  $target   ค่าเป้าหมาย
     * @param mixed  $actual   ผลงานจริง
     * @param ?string $operator >=, <=, =, >, < (ว่าง/ไม่ระบุ = ยิ่งสูงยิ่งดี >=)
     */
    public static function evaluate($target, $actual, ?string $operator): string
    {
        if ($actual === null || $actual === '' || $target === null || $target === '') {
            return self::NODATA;
        }
        $t = (float) $target;
        $a = (float) $actual;

        return match ($operator) {
            '<=' => $a <= $t ? self::PASS : self::GAP,
            '<'  => $a <  $t ? self::PASS : self::GAP,
            '='  => abs($a - $t) < 1e-9 ? self::PASS : self::GAP,
            '>'  => $a >  $t ? self::PASS : self::GAP,
            default => $a >= $t ? self::PASS : self::GAP, // '>=' และค่าเริ่มต้น = ยิ่งสูงยิ่งดี
        };
    }

    public static function labelList(): array
    {
        return [self::PASS => 'ผ่าน (PASS)', self::GAP => 'ต้องพัฒนา (GAP)', self::NODATA => 'ยังไม่มีข้อมูล'];
    }

    public static function label(string $status): string
    {
        return self::labelList()[$status] ?? $status;
    }

    /** คลาส badge ตามมาตรฐาน ERP (Bootstrap subtle) */
    public static function badgeClass(string $status): string
    {
        return match ($status) {
            self::PASS => 'bg-success-subtle text-success-emphasis',
            self::GAP => 'bg-danger-subtle text-danger-emphasis',
            default => 'bg-secondary-subtle text-secondary-emphasis',
        };
    }
}
