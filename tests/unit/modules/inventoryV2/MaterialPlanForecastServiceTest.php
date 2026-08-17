<?php

namespace tests\unit\modules\inventoryV2;

use app\modules\inventoryV2\services\MaterialPlanForecastService as Service;
use Codeception\Test\Unit;

class MaterialPlanForecastServiceTest extends Unit
{
    public function testFiscalYearStartsInOctober(): void
    {
        $this->assertSame(2569, Service::thaiFiscalYear(new \DateTimeImmutable('2026-08-17')));
        $this->assertSame(2570, Service::thaiFiscalYear(new \DateTimeImmutable('2026-10-01')));
    }

    public function testPlanUsesThePreviousFiscalYearAsItsBase(): void
    {
        $this->assertSame(2569, Service::baseFiscalYear(2570));
    }

    public function testFiscalRangeCoversOctoberThroughSeptember(): void
    {
        $this->assertSame(
            ['2025-10-01 00:00:00', '2026-09-30 23:59:59'],
            Service::fiscalRange(2569)
        );
    }

    public function testCoverageCountsOnlyMonthsThatFinishedBeforeTheLastRecord(): void
    {
        // ปีงบ 2569 = ต.ค.2568 - ก.ย.2569; ข้อมูลถึงสิ้น ก.ค. คือครบ 10 เดือน
        $this->assertSame(10, Service::monthsCovered(2569, '2026-07-31 00:00:00'));
    }

    public function testCoverageIgnoresAMonthThatIsStillBeingCollected(): void
    {
        // ข้อมูลถึง 3 ส.ค. — ส.ค. ยังเก็บไม่ครบ จึงยังนับได้แค่ 10 เดือน
        $this->assertSame(10, Service::monthsCovered(2569, '2026-08-03 13:00:00'));
    }

    public function testCoverageIsFullWhenTheFiscalYearHasEnded(): void
    {
        $this->assertSame(12, Service::monthsCovered(2569, '2026-09-30 23:59:59'));
        $this->assertSame(12, Service::monthsCovered(2569, '2027-01-15 00:00:00'));
    }

    public function testCoverageFallsBackToAFullYearWhenThereIsNoData(): void
    {
        $this->assertSame(12, Service::monthsCovered(2569, null));
    }

    public function testAnnualizeScalesPartialYearUsageToTwelveMonths(): void
    {
        // เก็บได้ 6 เดือนใช้ 60 หน่วย อัตราเต็มปีคือ 120 หน่วย
        $this->assertSame(120.0, Service::annualizeUsage(60.0, 6));
        $this->assertSame(1359.6, round(Service::annualizeUsage(1133.0, 10), 2));
    }

    public function testAnnualizeLeavesACompleteYearUntouched(): void
    {
        $this->assertSame(1133.0, Service::annualizeUsage(1133.0, 12));
    }

    public function testForecastAddsGrowthToTheActualUsage(): void
    {
        // แบบฟอร์มราชการ: ประมาณการใช้ = ใช้จริง + 5% แล้วปัดเป็นจำนวนเต็มหน่วย
        $this->assertSame(1190, Service::forecastUsage(1133.0, 5.0));
    }

    public function testHistoryBackcastsOlderYearsFromTheActualYear(): void
    {
        $history = Service::historyUsage(2569, 1133.0, 5.0);

        $this->assertSame([2567, 2568, 2569], array_keys($history));
        $this->assertSame(1133, $history[2569]);
        $this->assertSame(1076, $history[2568]);
        $this->assertSame(1023, $history[2567]);
    }

    public function testHistoryWithZeroGrowthRepeatsTheActualYear(): void
    {
        $history = Service::historyUsage(2569, 400.0, 0.0);

        $this->assertSame([400, 400, 400], array_values($history));
    }

    public function testPlanQtySubtractsStockOnHand(): void
    {
        $this->assertSame(130, Service::planQty(1189.65, 1060.0));
    }

    public function testPlanQtyNeverGoesNegativeWhenStockCoversTheForecast(): void
    {
        $this->assertSame(0, Service::planQty(50.0, 200.0));
    }

    public function testPlanQtyRoundsUpSoLowUsageItemsStayInThePlan(): void
    {
        // ของที่ใช้ปีละชิ้นเดียวต้องได้แผน 1 หน่วย ไม่ใช่ 0.05 หน่วยแบบไฟล์ต้นแบบ
        $this->assertSame(1, Service::planQty(1.05, 1.0));
    }

    public function testQuartersSplitEvenlyIntoFour(): void
    {
        $this->assertSame([44, 44, 44, 44], Service::splitQuarters(176.0));
    }

    public function testQuartersPutTheRemainderInTheEarlyQuarters(): void
    {
        $this->assertSame([58, 57, 57, 57], Service::splitQuarters(229.0));
        $this->assertSame([1, 1, 0, 0], Service::splitQuarters(2.0));
    }

    public function testQuartersAlwaysSumBackToThePlanQuantity(): void
    {
        foreach ([0, 1, 3, 7, 99, 1190] as $total) {
            $this->assertSame($total, array_sum(Service::splitQuarters((float) $total)), "total {$total}");
        }
    }

    public function testQuarterLabelsFollowTheFiscalYear(): void
    {
        $this->assertSame(
            ['ต.ค.-ธ.ค.', 'ม.ค.-มี.ค.', 'เม.ย.-มิ.ย.', 'ก.ค.-ก.ย.'],
            Service::quarterLabels()
        );
    }

    public function testFiscalYearInputAcceptsBothBuddhistAndGregorian(): void
    {
        $this->assertSame(2570, Service::normalizeFiscalYear(2570));
        $this->assertSame(2570, Service::normalizeFiscalYear(2027));
    }

    public function testDepartmentForecastAnnualizesThenAddsGrowth(): void
    {
        // เบิกจริง 10 อันใน 10 เดือน -> เต็มปี 12 -> บวกเผื่อ 5% -> 13
        $this->assertSame(13, Service::departmentForecastQty(10.0, 10, 5.0));
    }

    public function testDepartmentForecastIgnoresStockOnHand(): void
    {
        // งบของหน่วยงานคิดจากปริมาณที่จะใช้ล้วน ๆ การหักคงคลังเป็นขั้นของพัสดุ (planQty)
        $usage = 100.0;
        $this->assertSame(105, Service::departmentForecastQty($usage, 12, 5.0));
        $this->assertSame(5, Service::planQty(Service::forecastUsage($usage, 5.0), 100.0));
    }

    public function testDepartmentForecastLeavesACompleteYearAlone(): void
    {
        $this->assertSame(100, Service::departmentForecastQty(100.0, 12, 0.0));
    }

    public function testGrowthPctFallsBackToTheFormDefault(): void
    {
        $this->assertSame(5.0, Service::normalizeGrowthPct(''));
        $this->assertSame(12.5, Service::normalizeGrowthPct('12.5'));
        $this->assertSame(500.0, Service::normalizeGrowthPct(9999));
    }
}
