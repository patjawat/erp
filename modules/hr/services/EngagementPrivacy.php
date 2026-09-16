<?php
namespace app\modules\hr\services;

use Yii;

/** Request-local protection. Keep operational errors, never response context or trace arguments. */
class EngagementPrivacy
{
    public static function protect(): void
    {
        $dispatcher=Yii::$app->log;
        $logger=Yii::getLogger();
        $logger->traceLevel=0;
        $logger->messages=[];
        foreach($dispatcher->targets as $target) {
            $target->logVars=[];
            $target->prefix=static fn()=>'';
            $target->messages=[];
            if ($target instanceof \yii\debug\LogTarget) $target->enabled=false;
        }
        Yii::$app->db->enableLogging=false;
        Yii::$app->db->enableProfiling=false;
        if (Yii::$app->hasModule('debug')) Yii::$app->getModule('debug')->panels=[];
    }
}
