<?php

declare(strict_types=1);

namespace tests\unit\modules\ai;

use app\modules\ai\datasets\DatasetDefinition;
use app\modules\ai\services\QueryResult;
use Codeception\Test\Unit;

class QueryResultTest extends Unit
{
    public function testSerializesQueryResultShape(): void
    {
        $dataset = DatasetDefinition::fromArray('hr_department_summary', [
            'name' => 'HR Department Summary',
            'view_name' => 'ai_hr_department_summary',
            'permission_name' => 'ai.hr.summary',
            'fields' => [
                ['field_name' => 'department_name', 'label' => 'Department', 'data_type' => 'string'],
                ['field_name' => 'employee_count', 'label' => 'Employees', 'data_type' => 'integer'],
            ],
        ]);

        $result = new QueryResult(
            $dataset,
            [
                ['department_name' => 'HR', 'employee_count' => 12],
                ['department_name' => 'Finance', 'employee_count' => 8],
            ],
            ['department_name', 'employee_count'],
            ['department_name' => 'Department', 'employee_count' => 'Employees'],
            35
        );

        $payload = $result->toArray();

        $this->assertSame(2, $result->rowCount());
        $this->assertSame('hr_department_summary', $payload['dataset']);
        $this->assertSame('HR Department Summary', $payload['dataset_name']);
        $this->assertSame(2, $payload['row_count']);
        $this->assertSame(35, $payload['duration_ms']);
        $this->assertCount(2, $payload['rows']);
        $this->assertSame([], $result->toArray(false)['rows']);
    }
}
