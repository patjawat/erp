<?php

use yii\db\Migration;

class m260824_110000_assign_exit_interview_analytics extends Migration
{
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $hr = $auth->getRole('hr');
        $analytics = $auth->getPermission('exitInterviewViewAnalytics');
        if ($hr && $analytics && !$auth->hasChild($hr, $analytics)) {
            $auth->addChild($hr, $analytics);
        }
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $hr = $auth->getRole('hr');
        $analytics = $auth->getPermission('exitInterviewViewAnalytics');
        if ($hr && $analytics && $auth->hasChild($hr, $analytics)) {
            $auth->removeChild($hr, $analytics);
        }
    }
}
