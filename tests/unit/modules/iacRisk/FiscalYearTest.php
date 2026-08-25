<?php

namespace tests\unit\modules\iacRisk;

use app\modules\iacRisk\models\FiscalYear;
use Codeception\Test\Unit;

class FiscalYearTest extends Unit
{
    public function testDefaultDateRangeFollowsThaiFiscalYear(): void
    {
        $this->assertSame([
            'start' => '2025-10-01',
            'end' => '2026-09-30',
        ], FiscalYear::defaultDateRange(2569));
    }

    public function testDefaultDateRangeHandlesAnotherFiscalYear(): void
    {
        $this->assertSame([
            'start' => '2024-10-01',
            'end' => '2025-09-30',
        ], FiscalYear::defaultDateRange(2568));
    }

    public function testStatusLabelsCoverEveryWorkflowState(): void
    {
        $this->assertSame([
            FiscalYear::STATUS_DRAFT => 'ฉบับร่าง',
            FiscalYear::STATUS_OPEN => 'เปิดใช้งาน',
            FiscalYear::STATUS_CLOSED => 'ปิดรอบปี',
        ], FiscalYear::statusLabels());
    }
}
