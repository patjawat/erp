<?php

use yii\db\Migration;

class m260816_100000_create_executive_dashboard_rbac extends Migration
{
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $permission = $auth->getPermission('executiveDashboardView');
        if ($permission === null) {
            $permission = $auth->createPermission('executiveDashboardView');
            $permission->description = 'ดู Dashboard ผู้บริหาร';
            $auth->add($permission);
        }

        $role = $auth->getRole('executiveViewer');
        if ($role === null) {
            $role = $auth->createRole('executiveViewer');
            $role->description = 'ผู้ดู Dashboard ผู้บริหาร';
            $auth->add($role);
        }

        if (!$auth->hasChild($role, $permission)) {
            $auth->addChild($role, $permission);
        }

        $admin = $auth->getRole('admin');
        if ($admin !== null && !$auth->hasChild($admin, $role)) {
            $auth->addChild($admin, $role);
        }

        $auth->invalidateCache();
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        if ($role = $auth->getRole('executiveViewer')) {
            $auth->remove($role);
        }
        if ($permission = $auth->getPermission('executiveDashboardView')) {
            $auth->remove($permission);
        }

        $auth->invalidateCache();
    }
}
