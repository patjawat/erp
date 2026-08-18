<?php

use yii\db\Migration;

/**
 * สิทธิ์เปิด-ปิดค่าแผนคาดการณ์วัสดุ
 *
 * การปิดค่าคือการตรึงตัวเลขที่จะส่ง สสจ. และกำหนดอัตราเผื่อกลางให้ทุกหน่วยงานใช้
 * จึงต้องแยกสิทธิ์ออกจากการเข้าดู/คำนวณ/บันทึกร่าง ซึ่งยังใช้สิทธิ์เข้าโมดูลคลังตามเดิม
 *
 * เขียนแบบ idempotent เพื่อให้รันซ้ำที่เครื่องที่ทำไปแล้วได้โดยไม่พัง
 */
class m260818_140000_add_material_plan_lock_permission extends Migration
{
    private const PERMISSION = 'materialPlanLock';
    private const ROLES = ['admin'];

    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $permission = $auth->getPermission(self::PERMISSION);
        if ($permission === null) {
            $permission = $auth->createPermission(self::PERMISSION);
            $permission->description = 'เปิด-ปิดค่าแผนคาดการณ์วัสดุประจำปี';
            $auth->add($permission);
        }

        foreach (self::ROLES as $roleName) {
            $role = $auth->getRole($roleName);
            if ($role !== null && !$auth->hasChild($role, $permission)) {
                $auth->addChild($role, $permission);
            }
        }
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission(self::PERMISSION);
        if ($permission !== null) {
            $auth->remove($permission);
        }
    }
}
