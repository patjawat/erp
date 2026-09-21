<?php

namespace app\modules\pm\components;

/**
 * รูปแบบข้อมูลกลางของตัวชี้วัดหนึ่งแถวสำหรับ dashboard
 * รวมข้อมูลจาก 2 แหล่ง (strategy = ทะเบียนยุทธศาสตร์, standalone = ทะเบียน KPI นอกแผน)
 * ให้เป็นโครงเดียวกัน เพื่อให้ฝั่งแสดงผลไม่ต้องรู้ว่ามาจากไหน
 */
final class KpiRow
{
    public string $source;          // strategy | standalone
    public int $id;                 // id ในตารางต้นทาง
    public ?int $groupId = null;
    public string $groupName = '';
    public string $name = '';
    public ?string $unit = null;
    public ?int $orgUnitId = null;
    public ?string $operator = null;
    public ?float $target = null;
    public ?float $actual = null;
    public string $status = KpiStatus::NODATA;
    /** @var array<int,?float> fiscal_year => actual_value (ค่าจริงรายปี สำหรับ sparkline/เทรนด์) */
    public array $series = [];
    /** @var array<int,?float> fiscal_year => target_value */
    public array $targetSeries = [];
    /** @var int[] รายการ ha_part_id ที่ตัวชี้วัดนี้ map ตอบ (เฟส 5) */
    public array $haParts = [];

    public static function make(string $source, int $id): self
    {
        $row = new self();
        $row->source = $source;
        $row->id = $id;
        return $row;
    }
}
