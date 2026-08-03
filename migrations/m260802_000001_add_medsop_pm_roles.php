<?php

use yii\db\Migration;

/**
 * เพิ่ม role สำหรับสิทธิ์เข้าถึงเมนู คลัง SOP/WI (medsop) และ แผนงาน/โครงการ (pm)
 * เดิมเมนูเปิดให้ผู้ใช้ที่ล็อกอินทุกคน — เพิ่ม role เพื่อให้ admin กำหนดสิทธิ์รายบุคคลได้
 * ผูกเป็น child ของ admin เพื่อไม่ให้ผู้ดูแลระบบหลุดสิทธิ์
 */
class m260802_000001_add_medsop_pm_roles extends Migration
{
    private array $roles = [
        'medsop' => 'สิทธิ์ใช้งานคลัง SOP/WI',
        'pm'     => 'สิทธิ์ใช้งานแผนงาน/โครงการ',
    ];

    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $admin = $auth->getRole('admin');

        foreach ($this->roles as $name => $desc) {
            $role = $auth->getRole($name);
            if ($role === null) {
                $role = $auth->createRole($name);
                $role->description = $desc;
                $auth->add($role);
            }
            if ($admin !== null && !$auth->hasChild($admin, $role)) {
                $auth->addChild($admin, $role);
            }
        }

        $auth->invalidateCache();
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        foreach (array_keys($this->roles) as $name) {
            if ($role = $auth->getRole($name)) {
                $auth->remove($role);
            }
        }
        $auth->invalidateCache();
    }
}
