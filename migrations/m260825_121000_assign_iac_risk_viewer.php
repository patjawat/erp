<?php

use yii\db\Migration;

/** Allow every standard ERP user to open the read-only IAC&Risk workspace. */
class m260825_121000_assign_iac_risk_viewer extends Migration
{
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole('user');
        $permission = $auth->getPermission('iacRiskView');
        if ($role !== null && $permission !== null && !$auth->hasChild($role, $permission)) {
            $auth->addChild($role, $permission);
        }
        $auth->invalidateCache();
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole('user');
        $permission = $auth->getPermission('iacRiskView');
        if ($role !== null && $permission !== null && $auth->hasChild($role, $permission)) {
            $auth->removeChild($role, $permission);
        }
        $auth->invalidateCache();
    }
}
