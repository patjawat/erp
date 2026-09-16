<?php

namespace app\commands;

use app\modules\inventoryV2\services\MonthlySnapshotReconciliationService;
use yii\console\Controller;
use yii\console\ExitCode;

/** Read-only reconciliation: php yii monthly-snapshot/check /app/report.xlsx 2026 7 */
class MonthlySnapshotController extends Controller
{
    public function actionCheck(string $file, int $year, int $month): int
    {
        try {
            $result = MonthlySnapshotReconciliationService::inspectDatabase(
                MonthlySnapshotReconciliationService::readFile($file, $year, $month)
            );
            $this->stdout(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
            return ExitCode::OK;
        } catch (\InvalidArgumentException $e) {
            $this->stderr($e->getMessage() . PHP_EOL);
            return ExitCode::DATAERR;
        }
    }
}
