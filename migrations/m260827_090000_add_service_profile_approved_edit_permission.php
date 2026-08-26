<?php

use yii\db\Migration;

/** Adds an explicit permission for editing Service Profiles after submission. */
class m260827_090000_add_service_profile_approved_edit_permission extends Migration
{
    private const PERMISSION = 'serviceProfileEditApproved';

    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission(self::PERMISSION);
        if ($permission === null) {
            $permission = $auth->createPermission(self::PERMISSION);
            $permission->description = 'แก้ไข Service Profile หลังส่งอนุมัติและปิดความคิดเห็นค้าง (ยกเว้นสถานะสิ้นสุดและยกเลิก)';
            $auth->add($permission);
        }

        foreach (['admin', 'serviceProfileAdmin'] as $parentName) {
            $parent = $auth->getRole($parentName) ?: $auth->getPermission($parentName);
            if ($parent !== null && !$auth->hasChild($parent, $permission)) $auth->addChild($parent, $permission);
        }
        $auth->invalidateCache();
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission(self::PERMISSION);
        if ($permission !== null) $auth->remove($permission);
        $auth->invalidateCache();
    }
}
