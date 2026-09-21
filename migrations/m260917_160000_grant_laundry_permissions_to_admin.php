<?php

use yii\db\Migration;

class m260917_160000_grant_laundry_permissions_to_admin extends Migration
{
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $admin = $auth->getRole('admin');
        if ($admin === null) {
            return;
        }
        foreach (['laundry.view', 'laundry.manage', 'laundry.approve'] as $name) {
            $permission = $auth->getPermission($name);
            if ($permission !== null && !$auth->hasChild($admin, $permission)) {
                $auth->addChild($admin, $permission);
            }
        }
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $admin = $auth->getRole('admin');
        if ($admin === null) {
            return;
        }
        foreach (['laundry.view', 'laundry.manage', 'laundry.approve'] as $name) {
            $permission = $auth->getPermission($name);
            if ($permission !== null && $auth->hasChild($admin, $permission)) {
                $auth->removeChild($admin, $permission);
            }
        }
    }
}
