<?php

namespace tests\unit\services;

use Codeception\Test\Unit;
use app\modules\am\models\DepreciationProfile as P;
use app\modules\am\services\DepreciationCalculator as C;

/**
 * ทดสอบเครื่องคำนวณค่าเสื่อม (pure logic ไม่พึ่ง DB)
 * ครอบคลุม cases: 5 (10 ปีเส้นตรง), 6 (กลางเดือน), 7 (เดือนถัดไป),
 * 8 (มูลค่าซาก), 9 (อัตราหลายช่วง), 13 (ไม่เกินฐาน),
 * 14 (สะสม/NBV ถูกต้อง), 15 (รวมรายเดือน=ไตรมาส=ปี)
 */
class DepreciationCalculatorTest extends Unit
{
    private function sumDep(array $schedule): float
    {
        $s = 0.0;
        foreach ($schedule as $r) {
            $s = round($s + $r['depreciation'], 4);
        }
        return $s;
    }

    /** Case 5: อายุ 10 ปี วิธีเส้นตรง มูลค่าซาก 1 บาท */
    public function testTenYearStraightLine()
    {
        $res = C::buildMonthlySchedule([
            'cost' => 120000,
            'salvage_value_type' => P::SALVAGE_ONE_BAHT,
            'useful_life_months' => 120,
            'calculation_basis' => P::BASIS_MONTHLY,
            'acquisition_date' => '2024-10-01',
            'start_rule' => P::START_READY_MONTH,
        ]);

        $this->assertTrue($res['can_calculate']);
        $this->assertEqualsWithDelta(119999.0, $res['depreciable_base'], 0.001);
        $this->assertCount(120, $res['schedule']);
        $this->assertEqualsWithDelta(999.99, $res['schedule'][0]['depreciation'], 0.005);
        // งวดสุดท้าย: มูลค่าสุทธิ = มูลค่าซาก (1 บาท)
        $last = end($res['schedule']);
        $this->assertEqualsWithDelta(1.0, $last['remaining_value'], 0.005);
        // ผลรวมค่าเสื่อม = ฐานค่าเสื่อมพอดี
        $this->assertEqualsWithDelta(119999.0, $this->sumDep($res['schedule']), 0.005);
    }

    /** Case 6: เริ่มใช้งานกลางเดือน คิดตามจำนวนวัน (15 ม.ค. → 17 วัน) */
    public function testMidMonthDailyProration()
    {
        $res = C::buildMonthlySchedule([
            'cost' => 12000,
            'salvage_value_type' => P::SALVAGE_ONE_BAHT,
            'useful_life_months' => 12,
            'calculation_basis' => P::BASIS_DAILY,
            'start_rule' => P::START_READY_DATE,
            'acquisition_date' => '2024-01-15',
        ]);

        $this->assertTrue($res['can_calculate']);
        $first = $res['schedule'][0];
        $this->assertSame(17, $first['days_used']); // 31 - 15 + 1
        // (11999/12)/30*17 = 566.62
        $this->assertEqualsWithDelta(566.62, $first['depreciation'], 0.005);
        // เดือนถัดไปเป็นเดือนเต็ม
        $this->assertEqualsWithDelta(999.92, $res['schedule'][1]['depreciation'], 0.005);
    }

    /** Case 7: เริ่มคิดเดือนถัดไป */
    public function testNextMonthStart()
    {
        $res = C::buildMonthlySchedule([
            'cost' => 12000,
            'salvage_value_type' => P::SALVAGE_ONE_BAHT,
            'useful_life_months' => 12,
            'calculation_basis' => P::BASIS_MONTHLY,
            'start_rule' => P::START_NEXT_MONTH,
            'acquisition_date' => '2024-01-15',
        ]);

        $this->assertSame('2024-02-01', $res['schedule'][0]['period_date']);
        // เดือนแรกเป็นเดือนเต็ม (ไม่ prorate) = 11999/12 = 999.92
        $this->assertEqualsWithDelta(999.92, $res['schedule'][0]['depreciation'], 0.005);
    }

    /** Case 8: มีมูลค่าซากแบบจำนวนเงิน */
    public function testWithSalvageValue()
    {
        $res = C::buildMonthlySchedule([
            'cost' => 10000,
            'salvage_value_type' => P::SALVAGE_AMOUNT,
            'salvage_value' => 1000,
            'useful_life_months' => 12,
            'calculation_basis' => P::BASIS_MONTHLY,
            'start_rule' => P::START_READY_MONTH,
            'acquisition_date' => '2024-10-01',
        ]);

        $this->assertEqualsWithDelta(9000.0, $res['depreciable_base'], 0.001);
        $this->assertEqualsWithDelta(750.0, $res['schedule'][0]['depreciation'], 0.005);
        $last = end($res['schedule']);
        $this->assertEqualsWithDelta(1000.0, $last['remaining_value'], 0.005);
        $this->assertEqualsWithDelta(9000.0, $this->sumDep($res['schedule']), 0.005);
    }

    /** Case 9: อัตราหลายช่วง (1-36 เดือน 5%, 37-60 เดือน 3%) */
    public function testMultiTierRates()
    {
        $res = C::buildMonthlySchedule([
            'cost' => 100000,
            'salvage_value_type' => P::SALVAGE_ONE_BAHT,
            'useful_life_months' => 60,
            'calculation_basis' => P::BASIS_MONTHLY,
            'start_rule' => P::START_READY_MONTH,
            'acquisition_date' => '2024-10-01',
            'rate_tiers' => [
                ['start_month' => 1, 'end_month' => 36, 'rate_percent' => 5.0],
                ['start_month' => 37, 'end_month' => 60, 'rate_percent' => 3.0],
            ],
        ]);

        $this->assertTrue($res['can_calculate']);
        // base = 99999 ; เดือน 1: 99999*5%/12 = 416.66
        $this->assertEqualsWithDelta(5.0, $res['schedule'][0]['rate_percent'], 0.001);
        $this->assertEqualsWithDelta(416.66, $res['schedule'][0]['depreciation'], 0.005);
        // เดือน 37: อัตราเปลี่ยนเป็น 3% → 99999*3%/12 = 250.00
        $this->assertEqualsWithDelta(3.0, $res['schedule'][36]['rate_percent'], 0.001);
        $this->assertEqualsWithDelta(250.0, $res['schedule'][36]['depreciation'], 0.005);
    }

    /** Case 13 & 14: ค่าเสื่อมสะสมไม่เกินฐาน และ NBV = ทุน - สะสม, ไม่ต่ำกว่าซาก */
    public function testNeverExceedsBaseAndNbvConsistent()
    {
        $cost = 1000.0;
        $salvage = 1.0;
        $base = $cost - $salvage;
        $res = C::buildMonthlySchedule([
            'cost' => $cost,
            'salvage_value_type' => P::SALVAGE_ONE_BAHT,
            'useful_life_months' => 3,
            'calculation_basis' => P::BASIS_MONTHLY,
            'start_rule' => P::START_READY_MONTH,
            'acquisition_date' => '2024-10-01',
        ]);

        foreach ($res['schedule'] as $r) {
            $this->assertLessThanOrEqual($base + 0.005, $r['accumulated_depreciation']);
            $this->assertGreaterThanOrEqual($salvage - 0.005, $r['remaining_value']);
            $this->assertEqualsWithDelta($cost - $r['accumulated_depreciation'], $r['remaining_value'], 0.005);
        }
        $last = end($res['schedule']);
        $this->assertEqualsWithDelta($salvage, $last['remaining_value'], 0.005);
        $this->assertEqualsWithDelta($base, $this->sumDep($res['schedule']), 0.005);
    }

    /** Case 15: ผลรวมรายเดือน = รายไตรมาส = ปีงบประมาณ (ชุดข้อมูลเดียวกัน) */
    public function testMonthlyEqualsQuarterAndFiscalYear()
    {
        $res = C::buildMonthlySchedule([
            'cost' => 120000,
            'salvage_value_type' => P::SALVAGE_ONE_BAHT,
            'useful_life_months' => 36,
            'calculation_basis' => P::BASIS_MONTHLY,
            'start_rule' => P::START_READY_MONTH,
            'acquisition_date' => '2024-10-01', // เริ่มต้นปีงบพอดี
        ]);

        $monthlyTotal = $this->sumDep($res['schedule']);
        $byQuarter = C::aggregateByFiscal($res['schedule'], 'quarter');
        $byYear = C::aggregateByFiscal($res['schedule'], 'fiscal_year');

        $qTotal = 0.0;
        foreach ($byQuarter as $q) {
            $qTotal = round($qTotal + $q['depreciation'], 4);
        }
        $yTotal = 0.0;
        foreach ($byYear as $y) {
            $yTotal = round($yTotal + $y['depreciation'], 4);
        }

        $this->assertEqualsWithDelta($monthlyTotal, $qTotal, 0.005);
        $this->assertEqualsWithDelta($monthlyTotal, $yTotal, 0.005);

        // เริ่ม ต.ค. → ไตรมาสแรก (ต.ค.-ธ.ค.) = 3 เดือนแรก
        $firstQuarter = $byQuarter[array_key_first($byQuarter)];
        $this->assertSame(3, $firstQuarter['months']);
        $this->assertSame(1, $firstQuarter['quarter']);
    }
}
