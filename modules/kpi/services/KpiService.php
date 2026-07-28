<?php

namespace app\modules\kpi\services;

use app\modules\hr\models\Employees;
use app\modules\jd\models\JdEmployee;
use app\modules\kpi\models\KpiCycle;
use app\modules\kpi\models\KpiItem;
use Yii;

/**
 * ตรรกะกลางของโมดูล KPI: ปีงบประมาณ, seed จาก JD, สิทธิ์การเข้าถึง
 */
class KpiService
{
    /** เดือนงบประมาณเรียงจาก ต.ค. → ก.ย. (ปฏิทิน) */
    public const FISCAL_MONTHS = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];

    public const MONTH_LABELS_TH = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
        7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.',
    ];

    /** ปีงบประมาณ (พ.ศ.) ปัจจุบัน — เช่น ต.ค.2568–ก.ย.2569 = 2569 */
    public static function currentFiscalYear(): int
    {
        $m = (int) date('n');
        $buddhistYear = (int) date('Y') + 543;
        return $m >= 10 ? $buddhistYear + 1 : $buddhistYear;
    }

    /** แปลงเดือนปฏิทิน (1–12) เป็นลำดับเดือนงบประมาณ (ต.ค.=1 … ก.ย.=12) */
    public static function calendarToFiscalIndex(int $calendarMonth): int
    {
        return (($calendarMonth + 2) % 12) + 1;
    }

    /** ช่วง พ.ศ. ที่แสดงคู่กับปีงบ เช่น 2569 → "ต.ค. 2568 – ก.ย. 2569" */
    public static function fiscalRangeLabel(int $fiscalYear): string
    {
        return 'ต.ค. ' . ($fiscalYear - 1) . ' – ก.ย. ' . $fiscalYear;
    }

    /**
     * สร้างชุด KPI ประจำปี พร้อม seed KPI จาก JD ฉบับปัจจุบันของพนักงาน
     * @throws \DomainException ถ้ามีชุดปีนี้อยู่แล้ว
     */
    public static function createCycleFromJd(int $empId, int $fiscalYear): KpiCycle
    {
        if (KpiCycle::find()->where(['emp_id' => $empId, 'fiscal_year' => $fiscalYear])->exists()) {
            throw new \DomainException('มีชุด KPI ของปีงบประมาณ ' . $fiscalYear . ' อยู่แล้ว');
        }

        $jd = JdEmployee::findCurrent($empId);

        $cycle = new KpiCycle([
            'emp_id' => $empId,
            'fiscal_year' => $fiscalYear,
            'jd_employee_id' => $jd?->id,
            'status' => KpiCycle::STATUS_DRAFT,
        ]);
        if (!$cycle->save()) {
            throw new \RuntimeException('บันทึกชุด KPI ไม่สำเร็จ: ' . json_encode($cycle->getErrors(), JSON_UNESCAPED_UNICODE));
        }

        if ($jd) {
            $sort = 10;
            foreach ($jd->sections as $section) {
                $isKpi = ($section->section_code === 'kpi') || ($section->block_type === 'kpi');
                if (!$isKpi) {
                    continue;
                }
                $data = $section->getData() + ['items' => []];
                foreach ((array) $data['items'] as $row) {
                    $indicator = trim((string) ($row['indicator'] ?? ''));
                    if ($indicator === '') {
                        continue;
                    }
                    $item = new KpiItem([
                        'cycle_id' => $cycle->id,
                        'source_type' => KpiItem::SOURCE_JD,
                        'source_jd_section_id' => $section->id,
                        'indicator' => $indicator,
                        'target_text' => trim((string) ($row['target'] ?? '')) ?: null,
                        'value_type' => KpiItem::TYPE_NUMERIC,
                        'frequency' => KpiItem::FREQ_MONTHLY,
                        'weight' => 0,
                        'sort_order' => $sort,
                        'status' => KpiItem::STATUS_ACTIVE,
                    ]);
                    $item->save();
                    $sort += 10;
                }
            }
        }

        return $cycle;
    }

    /**
     * ผู้ใช้ปัจจุบันเป็น HR/admin หรือไม่ (จัดการ KPI ได้ทุกคน)
     */
    public static function isHrOrAdmin(): bool
    {
        $user = Yii::$app->user;
        return !$user->isGuest && ($user->can('hr') || $user->can('admin'));
    }

    /** เจ้าของชุด KPI (พนักงานที่ user login เป็นเจ้าของ emp record) หรือไม่ */
    public static function isOwner(KpiCycle $cycle): bool
    {
        return self::isOwnerEmp((int) $cycle->emp_id);
    }

    /** ผู้ใช้ปัจจุบันเป็นเจ้าของ emp record นี้หรือไม่ */
    public static function isOwnerEmp(int $empId): bool
    {
        $emp = Employees::findOne($empId);
        return $emp && (int) $emp->user_id === (int) Yii::$app->user->id;
    }

    /** สิทธิ์เข้าดูหน้าจัดการ KPI ของพนักงานคนนี้: เจ้าของ / หัวหน้า / HR-admin */
    public static function canViewEmp(int $empId): bool
    {
        return self::isOwnerEmp($empId) || self::isHrOrAdmin() || self::isSupervisorOf($empId);
    }

    /**
     * ผู้ใช้ปัจจุบันเป็นหัวหน้าหน่วยงานของพนักงานเจ้าของชุด KPI หรือไม่
     * ใช้ leader1 ของ Organization node ที่พนักงานสังกัด (รวมกลุ่มงานแม่)
     */
    public static function isSupervisorOf(int $empId): bool
    {
        $me = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        if (!$me) {
            return false;
        }
        $target = Employees::findOne($empId);
        if (!$target) {
            return false;
        }
        $units = $target->orgUnits();
        foreach (['unit', 'group'] as $key) {
            if (!empty($units[$key]) && $me->isOrgLeader($units[$key])) {
                return true;
            }
        }
        return false;
    }

    /** สิทธิ์จัดการชุด (เพิ่ม/ลด/อนุมัติ KPI): หัวหน้า หรือ HR/admin */
    public static function canManage(KpiCycle $cycle): bool
    {
        return self::isHrOrAdmin() || self::isSupervisorOf($cycle->emp_id);
    }

    /** สิทธิ์บันทึกผลรายงวด: เจ้าของ (หรือ HR/admin ช่วยกรอก) */
    public static function canRecord(KpiCycle $cycle): bool
    {
        return self::isOwner($cycle) || self::isHrOrAdmin();
    }
}
