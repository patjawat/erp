<?php

declare(strict_types=1);

namespace tests\unit\modules\ai;

use app\modules\ai\datasets\DatasetDefinition;
use Codeception\Test\Unit;

class DatasetDefinitionTest extends Unit
{
    public function testBuildsDefinitionFromArray(): void
    {
        $definition = DatasetDefinition::fromArray('stock_balance', [
            'name' => 'Stock Balance',
            'view_name' => 'ai_stock_balances',
            'permission_name' => 'ai.stock.summary',
            'max_rows' => 250,
            'is_exportable' => true,
            'fields' => [
                [
                    'field_name' => 'item_name',
                    'label' => 'Item',
                    'data_type' => 'string',
                    'is_filterable' => true,
                    'is_sortable' => true,
                    'is_selectable' => true,
                    'allowed_operators' => ['=', 'like'],
                ],
                [
                    'field_name' => 'internal_note',
                    'label' => 'Internal Note',
                    'data_type' => 'string',
                    'is_filterable' => false,
                    'is_sortable' => false,
                    'is_selectable' => false,
                ],
            ],
        ]);

        $this->assertSame('stock_balance', $definition->code);
        $this->assertSame('ai_stock_balances', $definition->viewName);
        $this->assertSame('ai.stock.summary', $definition->permissionName);
        $this->assertSame(250, $definition->maxRows);
        $this->assertSame(['item_name'], $definition->selectableFields());
        $this->assertTrue($definition->hasField('item_name'));
        $this->assertFalse($definition->hasField('missing_field'));
    }

    public function testFieldPolicyHelpersRespectRegistryFlags(): void
    {
        $definition = DatasetDefinition::fromArray('leave_overview', [
            'view_name' => 'ai_leave_overview',
            'permission_name' => 'ai.leave.summary',
            'fields' => [
                [
                    'field_name' => 'employee_name',
                    'label' => 'Employee',
                    'data_type' => 'string',
                    'is_filterable' => true,
                    'is_sortable' => false,
                    'allowed_operators' => ['=', 'like'],
                ],
                [
                    'field_name' => 'start_date',
                    'label' => 'Start Date',
                    'data_type' => 'date',
                    'is_filterable' => true,
                    'is_sortable' => true,
                    'allowed_operators' => '>=,<=,between',
                ],
            ],
        ]);

        $this->assertTrue($definition->isFilterable('employee_name'));
        $this->assertFalse($definition->isSortable('employee_name'));
        $this->assertTrue($definition->isSortable('start_date'));
        $this->assertSame(['=', 'like'], $definition->allowedOperators('employee_name'));
        $this->assertSame(['>=', '<=', 'between'], $definition->allowedOperators('start_date'));
        $this->assertSame([], $definition->allowedOperators('unknown'));
    }
}
