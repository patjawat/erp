<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\modules\pm\models\StrategyImportBatch;
use app\modules\pm\services\StrategyImportCommitService;

class PmStrategyController extends Controller
{
    public function actionImport(int $batchId): int
    {
        $batch=StrategyImportBatch::findOne($batchId);
        if(!$batch){$this->stderr("Import batch not found\n");return ExitCode::DATAERR;}
        $result=(new StrategyImportCommitService())->commit($batch);
        $this->stdout("Imported {$result['rows']} staging rows into {$result['goals']} goals.\n");
        return ExitCode::OK;
    }
}
