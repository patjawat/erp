<?php

namespace app\commands;

use app\modules\inventoryV2\components\StockHealthService;
use app\modules\inventoryV2\models\Warehouse;
use yii\console\Controller;
use yii\console\ExitCode;

/** Read-only stock health command for cron/monitoring. */
class InventoryHealthController extends Controller
{
    public $warehouseId;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['warehouseId']);
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), ['w' => 'warehouseId']);
    }

    public function actionScan(): int
    {
        $warehouseIds = $this->warehouseId
            ? [(int) $this->warehouseId]
            : array_map('intval', Warehouse::find()->select('id')->where(['warehouse_type' => 'MAIN'])->column());
        $result = StockHealthService::scan($warehouseIds, ['include_healthy' => false]);
        $payload = [
            'generated_at' => $result['generated_at'],
            'summary' => $result['summary'],
            'items' => array_map(static fn(array $row) => [
                'warehouse_id' => $row['warehouse_id'], 'warehouse_name' => $row['warehouse_name'],
                'item_code' => $row['item_code'], 'lot_number' => $row['lot_number'],
                'status' => $row['status'], 'issues' => $row['issues'],
                'ledger_qty' => $row['ledger_qty'], 'balance_qty' => $row['balance_qty'], 'fifo_qty' => $row['fifo_qty'],
            ], $result['rows']),
        ];
        $this->stdout(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL);
        return empty($result['rows']) ? ExitCode::OK : ExitCode::UNSPECIFIED_ERROR;
    }
}
