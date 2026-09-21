<?php

use yii\db\Migration;

/** Permission for importing and activating chart-of-account versions. */
class m260916_121000_create_accounting_chart_rbac extends Migration
{
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission('accountingChartManage');
        if ($permission === null) {
            $permission = $auth->createPermission('accountingChartManage');
            $permission->description = 'นำเข้าและจัดการผังบัญชีประจำปี';
            $auth->add($permission);
        }
        foreach (['accountingAdmin', 'admin'] as $roleName) {
            $role = $auth->getRole($roleName);
            if ($role !== null && !$auth->hasChild($role, $permission)) {
                $auth->addChild($role, $permission);
            }
        }
        $auth->invalidateCache();
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        if ($permission = $auth->getPermission('accountingChartManage')) {
            $auth->remove($permission);
        }
        $auth->invalidateCache();
    }
}
