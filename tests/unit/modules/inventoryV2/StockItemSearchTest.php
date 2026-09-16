<?php

namespace tests\unit\modules\inventoryV2;

use app\modules\inventoryV2\models\StockItemSearch;
use Codeception\Test\Unit;

class StockItemSearchTest extends Unit
{
    public function testInitialPageDoesNotApplyHiddenFilters(): void
    {
        $model = $this->model();
        $query = $model->search([])->query;
        $this->assertNull($model->is_active);
        $this->assertNull($model->is_innovation);
        $this->assertSame([
            'categorise.name' => 'asset_item',
            'categorise.group_id' => 'MATER',
        ], $query->where);
    }

    public function testUncheckedInnovationAndAllStatusesDoNotHideItems(): void
    {
        $model = $this->model();
        $query = $model->search(['StockItemSearch' => [
            'is_innovation' => '0', 'is_active' => '',
        ]])->query;
        $this->assertSame([
            'categorise.name' => 'asset_item',
            'categorise.group_id' => 'MATER',
        ], $query->where);
        $this->assertSame([], $model->data_json);
    }

    public function testExplicitFiltersRemainAvailable(): void
    {
        foreach (['0', '1'] as $status) {
            $model = $this->model();
            $query = $model->search(['StockItemSearch' => [
                'is_innovation' => '1', 'is_active' => $status,
            ]])->query;
            $this->assertSame(1, $query->params[':is_inno']);
            $this->assertContains(['categorise.active' => (int) $status], $query->where);
            $this->assertSame([], $model->data_json);
        }
    }

    private function model(): StockItemSearch
    {
        // Query construction needs no database; supply the record's columns locally.
        return new class extends StockItemSearch {
            public function formName() { return 'StockItemSearch'; }
            public function attributes()
            {
                return ['id', 'code', 'title', 'name', 'group_id', 'category_id',
                    'ref', 'qty_min', 'qty_max', 'active', 'data_json'];
            }
        };
    }
}
