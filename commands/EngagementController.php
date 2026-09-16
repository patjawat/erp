<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\modules\hr\services\EngagementRetentionService;

/** Server-operator-only retention. Preview by default; no browser endpoint. */
class EngagementController extends Controller
{
    public $apply=false;
    public $reason='';
    public function options($actionID) { return array_merge(parent::options($actionID),['apply','reason']); }
    public function actionRetention(): int
    {
        $service=new EngagementRetentionService(Yii::$app->db);
        $due=$service->due();
        foreach($due as $round) $this->stdout('round='.$round['id'].' expired_at='.$round['expires_at']." UTC\n");
        if (!filter_var($this->apply,FILTER_VALIDATE_BOOLEAN)) { $this->stdout('Preview only. Eligible rounds: '.count($due)."\n"); return ExitCode::OK; }
        if (trim($this->reason)==='') { $this->stderr("--reason is required.\n"); return ExitCode::USAGE;
        }
        foreach($due as $round) $service->purge($round['id'],$this->reason);
        $this->stdout('Purged expired raw records; retained published aggregates: '.count($due)." rounds\n");
        return ExitCode::OK;
    }
}
