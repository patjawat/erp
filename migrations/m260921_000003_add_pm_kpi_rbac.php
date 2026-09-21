<?php

use yii\db\Migration;

/**
 * สิทธิ์จัดการทะเบียนตัวชี้วัด (KPI) นอกแผน
 * การ "ดู" dashboard ใช้สิทธิ์ executiveDashboardView เดิม จึงไม่สร้าง view permission ใหม่
 */
final class m260921_000003_add_pm_kpi_rbac extends Migration
{
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $manage = $auth->getPermission('kpiManage') ?: $auth->createPermission('kpiManage');
        $manage->description = 'จัดการทะเบียนตัวชี้วัด (KPI) นอกแผนยุทธศาสตร์';
        if (!$auth->getPermission('kpiManage')) {
            $auth->add($manage);
        }

        // ผูกเข้ากับ role งานแผน + admin เพื่อให้จัดการได้
        foreach (['pm_planner', 'pm', 'admin'] as $roleName) {
            if (($role = $auth->getRole($roleName)) && !$auth->hasChild($role, $manage)) {
                $auth->addChild($role, $manage);
            }
        }
        $auth->invalidateCache();
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        if ($item = $auth->getPermission('kpiManage')) {
            $auth->remove($item);
        }
        $auth->invalidateCache();
    }
}
