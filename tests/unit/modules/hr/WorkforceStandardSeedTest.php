<?php

namespace tests\unit\modules\hr;

use Codeception\Test\Unit;

/**
 * ล็อกตัวเลขจากเอกสารเกณฑ์ สป.สธ. ไว้กับซอร์ส
 *
 * ตัวเลขพวกนี้ถอดมาจากเอกสารราชการด้วยมือ ถ้าใครแก้โดยไม่ตั้งใจ กรอบทั้งระบบจะเพี้ยน
 * โดยไม่มีอะไรฟ้อง เทสต์นี้จึงตรึงค่าที่ตรวจสอบแล้วเอาไว้
 */
class WorkforceStandardSeedTest extends Unit
{
    private function seed(): string
    {
        return file_get_contents(__DIR__ . '/../../../../migrations/m260819_090100_seed_workforce_standard_moph.php');
    }

    public function testKeepsRatiosFromTheOfficialCriteria(): void
    {
        $seed = $this->seed();

        // นักโภชนาการ 1 คน : 50 Active bed + ขั้นต่ำ 2 คนเมื่อ Active bed < 60
        $this->assertStringContainsString("'driver' => 'active_bed'", $seed);
        $this->assertStringContainsString("'per' => 50", $seed);
        $this->assertStringContainsString("['max' => 59, 'min_qty' => 2, 'max_qty' => 2]", $seed);

        // สัดส่วนประชากร
        $this->assertStringContainsString("'per' => 1250", $seed);
        $this->assertStringContainsString("'per' => 7500", $seed);

        // งานบริการพื้นฐาน
        $this->assertStringContainsString("'per' => 3", $seed);      // เกษตร 3 ไร่/คน
        $this->assertStringContainsString("'per' => 800", $seed);    // ทำความสะอาด 800 ตร.ม./คน
        $this->assertStringContainsString("'multiply' => 4", $seed); // ทำความสะอาด 1 หอ : 4 คน
        $this->assertStringContainsString("'per' => 150", $seed);    // ซักฟอก 150 กก./คน/วัน
        $this->assertStringContainsString("'multiply' => 0.7", $seed); // ขับรถ 70% ของจำนวนรถ
    }

    public function testMarksPopulationRatiosAsCupScope(): void
    {
        // อัตราส่วนประชากรคิดที่ระดับ CUP ไม่ใช่ระดับโรงพยาบาล
        // ถ้าลบ scope ทิ้ง ระบบจะคืนกรอบ 40 คนให้โรงพยาบาลทันทีซึ่งผิด
        $this->assertStringContainsString("'scope' => 'cup'", $this->seed());
    }

    public function testCountsOnlyTheFiveEmploymentTypes(): void
    {
        $seed = $this->seed();

        // เกณฑ์นับเฉพาะ ขรก. พนง.ราชการ พกส. ลจ.ประจำ ลจ.รายเดือน — รายวันไป Outsource
        $this->assertStringContainsString("['counts_in_frame' => 1], ['id' => [1, 2, 3, 4, 6]]", $seed);
        $this->assertStringContainsString("['counts_in_frame' => 0], ['id' => [5]]", $seed);
    }

    public function testLeavesUnreadableCellsUnverifiedInsteadOfZero(): void
    {
        $seed = $this->seed();

        // ช่องที่อ่านจากเอกสารไม่ชัด ต้องเป็น NULL (ยังไม่ยืนยัน) ไม่ใช่ 0 (ไม่มีกรอบ)
        $this->assertStringContainsString('$eligible = null;', $seed);
        $this->assertStringContainsString("['F1', 'F2', 'F3']", $seed);
    }

    public function testCalculatorNeverInventsAZeroFrame(): void
    {
        $calculator = file_get_contents(__DIR__ . '/../../../../modules/hr/services/WorkforceFrameCalculator.php');

        // คำนวณไม่ได้ ต้องคืน null พร้อมเหตุผล ไม่ใช่ 0 เพราะ 0 แปลว่า "ไม่มีกรอบ"
        $this->assertStringContainsString('STATUS_MISSING_DRIVER', $calculator);
        $this->assertStringContainsString('STATUS_CUP_SPLIT', $calculator);
        $this->assertStringContainsString('$this->outcome(null, self::STATUS_MISSING_DRIVER', $calculator);
        $this->assertStringContainsString('$this->outcome(null, self::STATUS_CUP_SPLIT', $calculator);
    }
}
