<?php

use yii\db\Migration;

final class m260803_000002_add_pm_strategy_rbac extends Migration
{
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $view = $auth->getPermission('pmStrategyView') ?: $auth->createPermission('pmStrategyView');
        $view->description = 'ดูทะเบียนแผนยุทธศาสตร์ที่ประกาศใช้';
        if (!$auth->getPermission('pmStrategyView')) $auth->add($view);
        $manage = $auth->getPermission('pmStrategyManage') ?: $auth->createPermission('pmStrategyManage');
        $manage->description = 'จัดการและประกาศใช้ทะเบียนแผนยุทธศาสตร์';
        if (!$auth->getPermission('pmStrategyManage')) $auth->add($manage);
        if (!$auth->hasChild($manage, $view)) $auth->addChild($manage, $view);

        $planner = $auth->getRole('pm_planner') ?: $auth->createRole('pm_planner');
        $planner->description = 'งานแผนงาน: จัดการทะเบียนยุทธศาสตร์';
        if (!$auth->getRole('pm_planner')) $auth->add($planner);
        if (!$auth->hasChild($planner, $manage)) $auth->addChild($planner, $manage);
        foreach (['pm', 'admin'] as $roleName) {
            if (($role = $auth->getRole($roleName)) && !$auth->hasChild($role, $view)) $auth->addChild($role, $view);
        }
        if (($admin = $auth->getRole('admin')) && !$auth->hasChild($admin, $planner)) $auth->addChild($admin, $planner);
        $auth->invalidateCache();
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        foreach (['pm_planner', 'pmStrategyManage', 'pmStrategyView'] as $name) {
            if (($item = $auth->getRole($name)) ?: $auth->getPermission($name)) $auth->remove($item);
        }
        $auth->invalidateCache();
    }
}
