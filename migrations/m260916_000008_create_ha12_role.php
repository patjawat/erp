<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * สิทธิ์ HA12-PCT — role สำหรับทีมคุณภาพ/PCT
 *
 *   role ha12  = ผู้ดูแล/ทีมคุณภาพ (PCT) — สร้าง/ประเมิน/ปิดรอบ + นำเข้าข้อมูล
 *
 * โค้ดในโมดูลตรวจสิทธิ์ผู้ดูแลด้วย can('admin') || can('ha12')
 * (Ha12ReviewService::isManager) ; ผู้ใช้ทั่วไปเข้าบันทึกทบทวนของหน่วยตนเองได้อยู่แล้ว
 * ผ่าน allowActions 'ha12/*' โดย controller คุมขอบเขตหน่วยงานภายใน
 *
 * permission ha12.manage เผยไว้เผื่อผูกสิทธิ์ละเอียดในอนาคต (ปัจจุบันยังไม่ใช้ในโค้ด)
 */
final class m260916_000008_create_ha12_role extends Migration
{
    private const ROLES = [
        'ha12' => 'ทีมคุณภาพ/PCT: สร้าง-ประเมิน-ปิดรอบ HA12 และนำเข้าข้อมูล',
    ];

    private const PERMISSIONS = [
        'ha12.manage' => 'จัดการรอบสรุป/ประเมิน HA12 และนำเข้าข้อมูล',
    ];

    private const ROLE_MAP = [
        'ha12' => ['ha12.manage'],
        'admin' => ['ha12.manage'],
    ];

    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;

        foreach (self::PERMISSIONS as $name => $description) {
            if ($auth->getPermission($name) === null) {
                $p = $auth->createPermission($name);
                $p->description = $description;
                $auth->add($p);
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
                continue; // ไม่มี role นี้ในระบบนี้ ข้ามไปโดยไม่ทำให้ migration ล้ม
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
            $p = $auth->getPermission($name);
            if ($p !== null) {
                $auth->remove($p);
            }
        }
        foreach (array_keys(self::ROLES) as $name) {
            $role = $auth->getRole($name);
            if ($role !== null) {
                $auth->remove($role);
            }
        }
        $auth->invalidateCache();
    }
}
