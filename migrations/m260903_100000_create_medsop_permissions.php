<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * สิทธิ์ระบบคลังเอกสาร SOP/WI
 *
 * เดิมโค้ดตรวจ permission `medsop.admin` ที่ไม่เคยถูกสร้างไว้ใน auth_item
 * และ role `medsop` ในหน้าจัดการผู้ใช้ก็เป็น role เปล่าไม่มีลูก
 * ผู้ที่ได้รับสิทธิ์จึงเข้าดูได้อย่างเดียว สร้างหรือแก้ไขเอกสารไม่ได้เลย
 *
 * migration นี้สร้าง permission ชุด medsop.* ให้ครบ แล้วผูกเข้ากับ role
 * โดยแยกสองระดับตามที่ตกลงไว้
 *   role `medsop`      → ผู้จัดทำเอกสารของหน่วยงานตัวเอง (medsop.author)
 *   role `medsopAdmin` → ผู้ดูแลระบบทั้งองค์กร (medsop.admin)
 */
final class m260903_100000_create_medsop_permissions extends Migration
{
    /** permission => คำอธิบาย */
    private const PERMISSIONS = [
        'medsop.admin' => 'ผู้ดูแลระบบ SOP/WI: จัดการเอกสารทุกหน่วยงาน เผยแพร่ และตั้งค่าระบบ',
        'medsop.author' => 'ผู้จัดทำเอกสาร SOP/WI ของหน่วยงานตนเอง (ส่งอนุมัติได้ แต่เผยแพร่เองไม่ได้)',
        'medsop.review' => 'ตรวจสอบเอกสาร SOP/WI ทุกสถานะ (ดูอย่างเดียว)',
        'medsop.viewAll' => 'ดูเอกสาร SOP/WI ได้ทุกหน่วยงาน',
        'medsop.viewPublished' => 'ดูเอกสาร SOP/WI ที่เผยแพร่แล้วตามสิทธิ์ผู้รับ',
    ];

    /** role ที่ต้องมีอยู่ => คำอธิบาย (สร้างให้เมื่อยังไม่มี) */
    private const ROLES = [
        'medsop' => 'ผู้จัดทำเอกสาร SOP/WI ของหน่วยงาน',
        'medsopAdmin' => 'ผู้ดูแลระบบคลังเอกสาร SOP/WI',
    ];

    /** role => permission ที่ผูกให้ */
    private const ROLE_MAP = [
        'medsop' => ['medsop.author'],
        'medsopAdmin' => ['medsop.admin', 'medsop.viewAll'],
        'admin' => ['medsop.admin', 'medsop.viewAll'],
        'director' => ['medsop.review', 'medsop.viewAll'],
        'hr' => ['medsop.review', 'medsop.viewAll'],
    ];

    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;

        foreach (self::PERMISSIONS as $name => $description) {
            if ($auth->getPermission($name) === null) {
                $permission = $auth->createPermission($name);
                $permission->description = $description;
                $auth->add($permission);
            }
        }

        foreach (self::ROLES as $name => $description) {
            if ($auth->getRole($name) === null) {
                $role = $auth->createRole($name);
                $role->description = $description;
                $auth->add($role);
            }
        }

        foreach (self::ROLE_MAP as $roleName => $permissionNames) {
            $role = $auth->getRole($roleName);
            if ($role === null) {
                continue; // role นี้ไม่มีในระบบนี้ ข้ามไปโดยไม่ทำให้ migration ล้ม
            }
            foreach ($permissionNames as $permissionName) {
                $permission = $auth->getPermission($permissionName);
                if ($permission !== null && !$auth->hasChild($role, $permission)) {
                    $auth->addChild($role, $permission);
                }
            }
        }

        $auth->invalidateCache();
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;
        foreach (array_keys(self::PERMISSIONS) as $name) {
            $permission = $auth->getPermission($name);
            if ($permission !== null) {
                $auth->remove($permission);
            }
        }
        $medsopAdmin = $auth->getRole('medsopAdmin');
        if ($medsopAdmin !== null) {
            $auth->remove($medsopAdmin);
        }
        $auth->invalidateCache();
    }
}
