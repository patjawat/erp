<?php

namespace tests\unit\modules\pm;

use Codeception\Test\Unit;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use app\modules\pm\services\StrategySpreadsheetParser;

class StrategySpreadsheetParserTest extends Unit
{
    public function testBundledTemplateMatchesImporter(): void
    {
        $path = \Yii::getAlias('@app/modules/pm/resources/pm-strategy-template-2568-2572.xlsx');
        $result = (new StrategySpreadsheetParser())->parse($path);
        $this->assertSame(6, $result['summary']['sheets']);
        $this->assertSame(1, $result['summary']['rows']);
        $this->assertSame('HOS.01', $result['rows'][0]['payload']['indicator']['code']);
    }

    public function testParsesStrategicWorkbookLayout(): void
    {
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('M1.S1.');
        $sheet->setCellValue('B2', 'M1-พัฒนาบุคลากร');
        $sheet->setCellValue('B3', 'M1.S1-องค์กรแห่งการเรียนรู้');
        $sheet->setCellValue('A7', 'M1.S1.G1-บุคลากรมีสมรรถนะ');
        $sheet->setCellValue('B7', 'HOS.01');
        $sheet->setCellValue('C7', 'อัตราบุคลากรมีสมรรถนะ');
        $sheet->setCellValue('D7', 'ระบบพัฒนาบุคลากร');
        $sheet->setCellValue('F7', 'จัดทำแผนพัฒนารายบุคคล');
        $sheet->setCellValue('G7', 'โครงการพัฒนาสมรรถนะ');
        $sheet->setCellValue('U7', 70);
        $sheet->setCellValue('V7', 80);
        $sheet->setCellValue('W7', 75);
        $hidden = $book->createSheet();
        $hidden->setTitle('M1.S1(AI)');
        $hidden->setSheetState('hidden');
        $path = tempnam(sys_get_temp_dir(), 'strategy_parser_') . '.xlsx';
        (new Xlsx($book))->save($path);
        try {
            $result = (new StrategySpreadsheetParser())->parse($path);
            $this->assertSame(1, $result['summary']['sheets']);
            $this->assertCount(1, $result['rows']);
            $payload = $result['rows'][0]['payload'];
            $this->assertSame('M1', $payload['mission']['code']);
            $this->assertSame('M1.S1', $payload['issue']['code']);
            $this->assertSame('HOS.01', $payload['indicator']['code']);
            $this->assertSame(80.0, $payload['values'][2568]['target']);
            $this->assertSame('โครงการพัฒนาสมรรถนะ', $payload['annual'][2568]['program']);
        } finally {
            @unlink($path);
        }
    }
}
