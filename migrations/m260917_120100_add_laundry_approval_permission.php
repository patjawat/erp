<?php

use yii\db\Migration;

class m260917_120100_add_laundry_approval_permission extends Migration
{
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        if (!$auth->getPermission('laundry.approve')) {
            $permission = $auth->createPermission('laundry.approve');
            $permission->description = 'อนุมัติตัดผ้าและปรับยอดจากการสอบประจำปี';
            $auth->add($permission);
        }
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        if ($permission = $auth->getPermission('laundry.approve')) {
            $auth->remove($permission);
        }
    }
}
