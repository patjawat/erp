<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * สิทธิ์ระบบติดตามมาตรฐานโรงพยาบาล (QMS) — 3 ระดับ
 *
 *   qms.admin      ผู้ดูแลมาตรฐาน / QMR: จัดการทะเบียนมาตรฐาน+ข้อกำหนด เปิดรอบปี มอบหมาย คัดลอกปี
 *   qms.contribute ผู้รับผิดชอบ: วางหลักฐาน + อัปเดตสถานะเฉพาะข้อที่ได้รับมอบหมาย
 *   qms.view       ผู้ดู (ผู้บริหาร/ผู้เยี่ยมสำรวจ): อ่านอย่างเดียว
 *
 * role  qms      = ผู้รับผิดชอบ (qms.contribute + qms.view)
 * role  qmsAdmin = ผู้ดูแลมาตรฐาน (qms.admin + qms.view)
 *
 * หมายเหตุ: navbar.php ตอนนี้เปิดเมนู "งานคุณภาพ" ด้วย !isGuest ชั่วคราว
 * เมื่อผูกสิทธิ์แล้วจะเปลี่ยน gate เป็น can('qms.view')
 */
final class m260905_100100_create_qms_permissions extends Migration
{
    /** permission => คำอธิบาย */
    private const PERMISSIONS = [
        'qms.admin' => 'ผู้ดูแลมาตรฐาน (QMR): จัดการทะเบียนมาตรฐาน ข้อกำหนด รอบปี และมอบหมายผู้รับผิดชอบ',
        'qms.contribute' => 'ผู้รับผิดชอบ: วางหลักฐานและอัปเดตสถานะเฉพาะข้อที่ได้รับมอบหมาย',
        'qms.view' => 'ดูความพร้อมและแฟ้มหลักฐานตามมาตรฐาน (อ่านอย่างเดียว)',
    ];

    /** role ที่ต้องมี => คำอธิบาย (สร้างให้เมื่อยังไม่มี) */
    private const ROLES = [
        'qms' => 'ผู้รับผิดชอบงานคุณภาพ/มาตรฐาน ของหน่วยงาน',
        'qmsAdmin' => 'ผู้ดูแลระบบติดตามมาตรฐานโรงพยาบาล',
    ];

    /** role => permission ที่ผูกให้ */
    private const ROLE_MAP = [
        'qms' => ['qms.contribute', 'qms.view'],
        'qmsAdmin' => ['qms.admin', 'qms.view'],
        'admin' => ['qms.admin', 'qms.view'],
        'director' => ['qms.view'],
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
        foreach (['qms', 'qmsAdmin'] as $roleName) {
            $role = $auth->getRole($roleName);
            if ($role !== null) {
                $auth->remove($role);
            }
        }
        $auth->invalidateCache();
    }
}
