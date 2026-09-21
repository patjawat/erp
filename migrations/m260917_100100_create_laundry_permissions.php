<?php

use yii\db\Migration;

class m260917_100100_create_laundry_permissions extends Migration
{
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        foreach (['laundry.view' => 'ดูรอบเก็บผ้าและรายงานซักฟอก', 'laundry.manage' => 'บันทึกและยืนยันรอบเก็บผ้า'] as $name => $description) {
            if (!$auth->getPermission($name)) {
                $permission = $auth->createPermission($name);
                $permission->description = $description;
                $auth->add($permission);
            }
        }
        $manage = $auth->getPermission('laundry.manage');
        $view = $auth->getPermission('laundry.view');
        if (!$auth->hasChild($manage, $view)) {
            $auth->addChild($manage, $view);
        }
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        foreach (['laundry.manage', 'laundry.view'] as $name) {
            if ($permission = $auth->getPermission($name)) {
                $auth->remove($permission);
            }
        }
    }
}
