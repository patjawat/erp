<?php

namespace tests\unit\services;

use Codeception\Test\Unit;
use app\modules\am\models\DepreciationProfile as P;
use app\modules\am\services\DepreciationRunService as R;

/**
 * ทดสอบการรวมค่าจาก "snapshot บนทรัพย์สิน" กับ "เกณฑ์ที่ผูกไว้" (pure — ไม่พึ่ง DB)
 *
 * โจทย์หลัก: ทรัพย์สินเดิมที่ไม่มีอายุ/มูลค่าซากของตัวเอง ต้องคิดค่าเสื่อมได้
 * จากเกณฑ์ที่ผูกไว้ที่ประเภท/หมวด/รายการ
 */
class DepreciationRunParamsTest extends Unit
{
    /** เกณฑ์มาตรฐาน: ครุภัณฑ์การแพทย์ 10 ปี ซาก 1 บาท */
    private function stdProfile(array $overrides = []): array
    {
        return array_merge([
            'useful_life_months' => 120,
            'annual_rate' => null,
            'salvage_value_type' => P::SALVAGE_ONE_BAHT,
            'salvage_value' => 1,
            'calculation_basis' => P::BASIS_MONTHLY,
            'start_rule' => P::START_READY_DATE,
            'rounding_scale' => 2,
            'method' => P::METHOD_STRAIGHT_LINE,
            'rate_tiers' => [],
        ], $overrides);
    }

    /** ทรัพย์สินเปล่า (ทะเบียนเดิม): มีแค่ราคาทุนกับวันรับเข้า */
    private function bareAsset(array $overrides = []): array
    {
        return array_merge([
            'price' => 120000,
            'useful_life_months' => null,
            'useful_life' => null,
            'residual_value' => null,
            'depreciation_rate' => null,
            'depreciation_method' => null,
            'depreciation_calculation_basis' => null,
            'depreciation_start_rule' => null,
            'depreciation_start_date' => null,
            'receive_date' => '2024-10-15',
        ], $overrides);
    }

    /** ไม่มีเกณฑ์เลย → คำนวณไม่ได้ (ยืนยันว่าปัญหาเดิมยังถูกตรวจจับ) */
    public function testBareAssetWithoutProfileCannotCalculate()
    {
        $p = R::mergeParams($this->bareAsset(), null);
        $this->assertSame(0, $p['useful_life_months']);
        $this->assertNull($p['annual_rate']);
    }

    /** ทรัพย์สินเปล่า + เกณฑ์ที่ผูกไว้ → ได้อายุและมูลค่าซากจากเกณฑ์ */
    public function testProfileSuppliesLifeAndSalvage()
    {
        $p = R::mergeParams($this->bareAsset(), $this->stdProfile());

        $this->assertSame(120, $p['useful_life_months']);
        $this->assertEqualsWithDelta(1.0, $p['salvage_value'], 0.001);
        $this->assertSame(P::SALVAGE_AMOUNT, $p['salvage_value_type']);
        $this->assertSame('2024-10-15', $p['acquisition_date']);
    }

    /** มูลค่าซากแบบร้อยละต้องถูกแปลงเป็นจำนวนเงินตามราคาทุน */
    public function testPercentSalvageConvertedToAmount()
    {
        $p = R::mergeParams(
            $this->bareAsset(['price' => 200000]),
            $this->stdProfile(['salvage_value_type' => P::SALVAGE_PERCENT, 'salvage_value' => 5])
        );

        $this->assertEqualsWithDelta(10000.0, $p['salvage_value'], 0.001);
    }

    /** snapshot บนทรัพย์สินต้องชนะเกณฑ์เสมอ (ตรึงเกณฑ์ ณ วันขึ้นทะเบียน) */
    public function testAssetSnapshotWinsOverProfile()
    {
        $p = R::mergeParams(
            $this->bareAsset(['useful_life_months' => 60, 'residual_value' => 500]),
            $this->stdProfile()
        );

        $this->assertSame(60, $p['useful_life_months']);
        $this->assertEqualsWithDelta(500.0, $p['salvage_value'], 0.001);
    }

    /** เกณฑ์ต้องชนะคอลัมน์อายุเป็นปีแบบเดิม (ข้อมูลเก่าที่ไม่รู้ที่มา) */
    public function testProfileWinsOverLegacyYears()
    {
        $p = R::mergeParams($this->bareAsset(['useful_life' => 5]), $this->stdProfile());

        $this->assertSame(120, $p['useful_life_months']);
    }

    /** ไม่มีเกณฑ์ แต่มีอายุเป็นปีเดิม → ยังใช้ได้ (ไม่ทำของเดิมพัง) */
    public function testLegacyYearsStillUsedWhenNoProfile()
    {
        $p = R::mergeParams($this->bareAsset(['useful_life' => 5]), null);

        $this->assertSame(60, $p['useful_life_months']);
        $this->assertEqualsWithDelta(R::DEFAULT_SALVAGE_BAHT, $p['salvage_value'], 0.001);
    }

    /** start_rule ของเกณฑ์ต้องมีผลจริงเมื่อทรัพย์สินยังไม่มี snapshot วันเริ่ม */
    public function testProfileStartRuleApplies()
    {
        $p = R::mergeParams(
            $this->bareAsset(),
            $this->stdProfile(['start_rule' => P::START_NEXT_MONTH])
        );

        $this->assertSame(P::START_NEXT_MONTH, $p['start_rule']);
        $this->assertSame('2024-10-15', $p['acquisition_date']);
    }

    /** มี snapshot วันเริ่มแล้ว → ห้าม resolve ซ้ำด้วย start_rule (กันเลื่อนเดือนซ้อน) */
    public function testSnapshotStartDateIsNotReResolved()
    {
        $p = R::mergeParams(
            $this->bareAsset(['depreciation_start_date' => '2024-11-01']),
            $this->stdProfile(['start_rule' => P::START_NEXT_MONTH])
        );

        $this->assertSame('2024-11-01', $p['acquisition_date']);
        $this->assertSame(P::START_READY_DATE, $p['start_rule']);
    }

    /** เกณฑ์ที่คิดด้วยอัตราต่อปี (ไม่มีอายุ) ต้องส่งอัตรามาให้ตัวคำนวณ */
    public function testProfileAnnualRateUsedWhenNoLife()
    {
        $p = R::mergeParams(
            $this->bareAsset(),
            $this->stdProfile(['useful_life_months' => null, 'annual_rate' => 20])
        );

        $this->assertSame(0, $p['useful_life_months']);
        $this->assertEqualsWithDelta(20.0, $p['annual_rate'], 0.001);
    }

    /** ฐานการคำนวณและทศนิยมมาจากเกณฑ์ */
    public function testBasisAndScaleComeFromProfile()
    {
        $p = R::mergeParams(
            $this->bareAsset(),
            $this->stdProfile(['calculation_basis' => P::BASIS_DAILY, 'rounding_scale' => 4])
        );

        $this->assertSame(P::BASIS_DAILY, $p['calculation_basis']);
        $this->assertSame(4, $p['rounding_scale']);
    }

    /** ฐานและกติกาที่ snapshot ไว้ต้องไม่เปลี่ยนตามการแก้ profile ภายหลัง */
    public function testSnapshotBasisAndStartRuleWinOverProfile()
    {
        $p = R::mergeParams(
            $this->bareAsset([
                'depreciation_calculation_basis' => P::BASIS_MONTHLY,
                'depreciation_start_rule' => P::START_DAY_15_CUTOFF,
            ]),
            $this->stdProfile([
                'calculation_basis' => P::BASIS_DAILY,
                'start_rule' => P::START_READY_DATE,
            ])
        );

        $this->assertSame(P::BASIS_MONTHLY, $p['calculation_basis']);
        $this->assertSame(P::START_DAY_15_CUTOFF, $p['start_rule']);
    }
}
