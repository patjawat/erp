<?php

declare(strict_types=1);

namespace tests\unit\modules\ai;

use app\modules\ai\datasets\DatasetDefinition;
use app\modules\ai\services\DatasetRegistry;
use Codeception\Test\Unit;

class DatasetRegistryTest extends Unit
{
    public function testLoadsDefaultDatasetCatalog(): void
    {
        $registry = new DatasetRegistry();
        $datasets = $registry->all();

        $this->assertArrayHasKey('hr_department_summary', $datasets);
        $this->assertArrayHasKey('leave_overview', $datasets);
        $this->assertArrayHasKey('stock_balance', $datasets);
        $this->assertInstanceOf(DatasetDefinition::class, $datasets['stock_balance']);
        $this->assertSame('ai_stock_balances', $datasets['stock_balance']->viewName);
    }

    public function testPromptCatalogDoesNotExposeViewNames(): void
    {
        $catalog = (new DatasetRegistry())->asPromptCatalog();
        $firstDataset = $catalog[0] ?? [];

        $this->assertNotEmpty($catalog);
        $this->assertArrayHasKey('code', $firstDataset);
        $this->assertArrayHasKey('fields', $firstDataset);
        $this->assertArrayNotHasKey('view_name', $firstDataset);
    }
}
