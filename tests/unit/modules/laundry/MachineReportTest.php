<?php

namespace tests\unit\modules\laundry;

use app\modules\laundry\services\MachineReport;
use Codeception\Test\Unit;

class MachineReportTest extends Unit
{
    public function testOnlyCompletedRoundsCountTowardLoadAndOutput(): void
    {
        $rows = [
            ['status' => 'COMPLETED', 'linen_class' => 'SOILED', 'input_kg' => '8.000', 'output_kg' => '7.500', 'started_at' => '2026-09-17 08:00:00', 'ended_at' => '2026-09-17 08:42:00'],
            ['status' => 'COMPLETED', 'linen_class' => 'INFECTIOUS', 'input_kg' => '6.000', 'output_kg' => '5.000', 'started_at' => '2026-09-17 09:00:00', 'ended_at' => '2026-09-17 09:30:00'],
            ['status' => 'ABORTED', 'linen_class' => 'SOILED', 'input_kg' => '9.000', 'output_kg' => null, 'started_at' => '2026-09-17 10:00:00', 'ended_at' => '2026-09-17 10:10:00'],
            ['status' => 'RUNNING', 'linen_class' => 'SOILED', 'input_kg' => '5.000', 'output_kg' => null, 'started_at' => '2026-09-17 11:00:00', 'ended_at' => null],
        ];
        $s = MachineReport::summarize($rows, 10.0);
        $this->assertSame(2, $s['completed']);
        $this->assertSame(1, $s['aborted']);
        $this->assertSame(1, $s['running']);
        $this->assertSame(8.0, $s['soiled_kg']);
        $this->assertSame(6.0, $s['infectious_kg']);
        $this->assertSame(12.5, $s['output_kg']);
        $this->assertSame(9.0, $s['aborted_input_kg']);
        $this->assertSame(72, $s['completed_minutes']);
        $this->assertSame(70.0, $s['load_percent']);
    }

    public function testNoCompletedRoundsHasNoLoadRate(): void
    {
        $this->assertNull(MachineReport::summarize([], 10.0)['load_percent']);
    }
}
