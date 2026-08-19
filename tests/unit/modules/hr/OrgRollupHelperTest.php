<?php

namespace tests\unit\modules\hr;

use Codeception\Test\Unit;

/**
 * กันไม่ให้ roll-up กลุ่มงานถอยกลับไปใช้ทะเบียน categorise เดิมที่ไม่มีข้อมูลแล้ว
 *
 * ตรวจจากซอร์สแบบเดียวกับเทสต์อื่นในโปรเจกต์ เพราะ suite unit ไม่ได้ต่อฐานจริง
 * ส่วนความถูกต้องของยอดรวมตรวจด้วยการรันจริงกับฐาน (271 คน ตรงกับยอดดิบ)
 */
class OrgRollupHelperTest extends Unit
{
    private function helperSource(): string
    {
        return file_get_contents(__DIR__ . '/../../../../modules/hr/helpers/OrgRollupHelper.php');
    }

    /** ตัดคอมเมนต์ออก เพื่อให้ assert ตรวจเฉพาะโค้ดที่ทำงานจริง */
    private function helperCode(): string
    {
        $code = '';
        foreach (token_get_all($this->helperSource()) as $token) {
            if (is_array($token) && in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }
            $code .= is_array($token) ? $token[1] : $token;
        }

        return $code;
    }

    public function testRollsUpThroughNestedSetNotCategorise(): void
    {
        $source = $this->helperSource();

        // ต้องหา ancestor ระดับกลุ่มงานด้วย nested set (lft/rgt/root)
        $this->assertStringContainsString('g.root = t.root AND g.lft <= t.lft AND g.rgt >= t.rgt', $source);
        $this->assertStringContainsString("':wglvl' => self::WORKGROUP_LEVEL", $source);

        // ห้าม join ทะเบียนเดิมที่ตอนนี้ไม่มีแถว name=workgroup/department เหลืออยู่
        $this->assertStringNotContainsString('categorise', $this->helperCode());
    }

    public function testKeepsStaffAttachedAboveWorkgroupLevel(): void
    {
        // คนที่ผูกไว้ที่ node ระดับ 0 ไม่มี ancestor lvl=1 ให้รวมขึ้นไป
        // ต้อง fallback เป็นตัวเอง ไม่งั้นยอดรวมจะขาดคนไปเงียบ ๆ
        $this->assertStringContainsString('COALESCE(g.id, t.id)', $this->helperSource());
    }

    public function testSharesOneDefinitionOfActiveEmployee(): void
    {
        $source = $this->helperSource();

        // นิยามเดียวกับ Dashboard บุคลากร — ต้องรวมไว้ที่เดียว ไม่กระจายไปเขียนซ้ำ
        $this->assertStringContainsString('function activeCondition', $source);
        $this->assertStringContainsString("BRANCH_MAIN = 'MAIN'", $source);
        $this->assertStringContainsString('SYSTEM_EMPLOYEE_ID = 1', $source);
    }

    public function testDoesNotHardcodeHospitalRootId(): void
    {
        // ต้องใช้ได้กับโรงพยาบาลอื่นที่ผังคนละชุด — root หาจากข้อมูลจริง ไม่ fix ค่า
        $this->assertStringContainsString('function mainRootIds', $this->helperSource());
    }
}
