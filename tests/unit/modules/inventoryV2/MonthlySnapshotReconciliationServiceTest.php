<?php

namespace tests\unit\modules\inventoryV2;

use app\modules\inventoryV2\services\MonthlySnapshotReconciliationService as Service;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 4) . '/modules/inventoryV2/services/MonthlySnapshotReconciliationService.php';

class MonthlySnapshotReconciliationServiceTest extends TestCase
{
    private function workbook(): Spreadsheet
    {
        $w = new Spreadsheet();
        $s = $w->getActiveSheet()->setTitle('สรุปวัสดุคงคลัง');
        $s->setCellValue('A2', 'เดือน กรกฎาคม  2569');
        $s->setCellValue('B6', 'วัสดุสำนักงาน')->setCellValue('I6', 90);
        $s->setCellValue('B7', 'รวม')->setCellValue('I7', 90);
        $d = $w->createSheet()->setTitle('สรุปรายการ');
        $d->fromArray([['ที่', 'รหัส', 'รายการสินค้า', 'ประเภท', 'หน่วย', 'จำนวนคงเหลือ', 'มูลค่าคงเหลือ',
            'จำนวนรับใหม่', 'มูลค่ารับใหม่', 'จำนวนจ่ายใหม่', 'มูลค่าจ่ายใหม่', 'จำนวนคงเหลือ', 'มูลค่าคงเหลือ'],
            [1, '01-001', 'กระดาษ', 'วัสดุสำนักงาน', 'รีม', 10, 100, 2, 20, 3, 30, 9, 90]], null, 'A2', true);
        return $w;
    }

    public function testValidWorkbookReconcilesLiteralAmounts(): void
    {
        $result = Service::readWorkbook($this->workbook(), 2026, 7);
        self::assertSame(90.0, $result['source_total']);
        self::assertSame([], $result['errors']);
        self::assertSame([], $result['rows'][0]['issues']);
    }

    public function testWrongPeriodCannotBeSilentlyRelabelled(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Service::readWorkbook($this->workbook(), 2026, 8);
    }

    public function testSummaryCachedFormulaIsCheckedWithoutRecalculatingSource(): void
    {
        $w = $this->workbook();
        $w->getSheetByName('สรุปวัสดุคงคลัง')->getCell('I6')->setValue('=1/0')->setCalculatedValue(90);
        $result = Service::readWorkbook($w, 2026, 7);
        self::assertSame([], $result['errors']);
        $w->getSheetByName('สรุปวัสดุคงคลัง')->getCell('I6')->setCalculatedValue(80);
        self::assertNotEmpty(Service::readWorkbook($w, 2026, 7)['errors']);
    }

    public function testThaiIdentifierIsPreservedForCatalogReview(): void
    {
        $w = $this->workbook();
        $w->getSheetByName('สรุปรายการ')->setCellValue('B3', 'หลอดนีออน สีขาว ขนาด 18 w');
        $result = Service::readWorkbook($w, 2026, 7);
        self::assertSame('หลอดนีออน สีขาว ขนาด 18 w', $result['rows'][0]['item_code']);
    }

    public function testFormulaInAuthoritativeClosingAmountIsRejected(): void
    {
        $w = $this->workbook(); $w->getSheetByName('สรุปรายการ')->setCellValue('M3', '=G3+I3-K3');
        $this->expectException(\InvalidArgumentException::class);
        Service::readWorkbook($w, 2026, 7);
    }

    public function testMissingAmountIsNotTreatedAsZero(): void
    {
        $w = $this->workbook(); $w->getSheetByName('สรุปรายการ')->setCellValue('M3', null);
        $this->expectException(\InvalidArgumentException::class);
        Service::readWorkbook($w, 2026, 7);
    }

    public function testDuplicateRowsRemainSeparateAndQuantityConflictIsPreserved(): void
    {
        $w = $this->workbook();
        $d = $w->getSheetByName('สรุปรายการ');
        $d->fromArray([[2, '01-001', 'กระดาษ', 'วัสดุสำนักงาน', 'รีม', 2, 20, 0, 0, 0, 0, 1, 20]], null, 'A4', true);
        $result = Service::readWorkbook($w, 2026, 7);
        self::assertCount(2, $result['rows']);
        self::assertSame([3, 4], $result['duplicates']['01-001']);
        self::assertContains('จำนวนไม่ตรงสูตร', $result['rows'][1]['issues']);
        self::assertSame(1.0, $result['rows'][1]['closing_qty']);
        self::assertNotEmpty($result['errors'], 'Detail/summary discrepancy must be exposed');
    }

    public function testMultipleWarehousesAreCandidatesNotAutomaticAssignments(): void
    {
        $source = Service::readWorkbook($this->workbook(), 2026, 7);
        $result = Service::reconcile($source, [
            ['item_code' => '01-001', 'warehouse_id' => 1, 'closing_qty' => 5, 'closing_value' => 50],
            ['item_code' => '01-001', 'warehouse_id' => 2, 'closing_qty' => 5, 'closing_value' => 50],
            ['item_code' => 'OTHER', 'warehouse_id' => 2, 'closing_qty' => 2, 'closing_value' => 20],
        ], [['code' => '01-001']], [], [1 => ['warehouse_name' => 'A'], 2 => ['warehouse_name' => 'B']]);
        self::assertSame([1 => 'A', 2 => 'B'], $result['rows'][0]['candidates']);
        self::assertContains('ต้องระบุคลัง', $result['rows'][0]['issues']);
        self::assertArrayNotHasKey('warehouse_id', $result['rows'][0]);
        self::assertSame(30.0, $result['delta']);
        self::assertSame(10.0, $result['changes'][0]['delta']);
        self::assertSame('OTHER', $result['database_only'][0]['item_code']);
    }
}
