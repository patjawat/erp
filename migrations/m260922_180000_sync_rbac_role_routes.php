<?php

use yii\db\Migration;
use app\commands\UpdateRoleController;

/**
 * Sync บทบาท → route (RBAC) ให้ตรงกับ commands/UpdateRoleController.php
 *
 * ที่มา: เมนูใน navbar โผล่จาก can('<role>') แต่การเข้าหน้าจริงตรวจด้วย
 * AccessControl (เทียบ route จริงกับ route-children ของ role) — เมื่อ role
 * ผูก route ไม่ครบ/ผิด จึง "เห็นเมนู แต่กดแล้ว 403" (hr, leave, pm, ฯลฯ)
 *
 * เดิมข้อมูลชุดนี้ seed ด้วยคำสั่ง console `yii update-role` ที่ต้องรันมือ
 * ทำให้ฐานข้อมูลค้างเก่าเมื่อไม่มีใครรันหลังแก้สิทธิ์ในโค้ด. migration นี้
 * ผูกการ sync เข้ากับ `yii migrate` (ที่ deploy เรียกผ่าน migrate.sh อยู่แล้ว)
 * โดยเรียก RouteList()/ChildList() ชุดเดียวกับคำสั่ง — ไม่ให้ข้อมูล 2 ทางแตกกัน
 *
 * เป็น idempotent (INSERT ... ON DUPLICATE KEY UPDATE) และ "เพิ่มอย่างเดียว
 * ไม่ลบ" จึงรันซ้ำได้ปลอดภัย ไม่ลบสิทธิ์ที่ทีมอื่น/หน้าจัดการสิทธิ์เพิ่มไว้
 *
 * ครั้งต่อไปที่แก้สิทธิ์ role→route: แก้ arrays ใน UpdateRoleController แล้ว
 * copy migration นี้เป็นไฟล์ใหม่ (เปลี่ยนแค่ชื่อคลาส/วันที่) เพื่อ sync บน deploy
 */
class m260922_180000_sync_rbac_role_routes extends Migration
{
    public function safeUp()
    {
        $sqlItem = 'INSERT INTO auth_item (type, name, description) VALUES (:type, :name, :description)'
            . ' ON DUPLICATE KEY UPDATE name = VALUES(name), type = VALUES(type), description = VALUES(description)';
        $sqlChild = 'INSERT INTO auth_item_child (parent, child) VALUES (:parent, :child)'
            . ' ON DUPLICATE KEY UPDATE child = VALUES(child), parent = VALUES(parent)';

        // 1) route/permission items (ต้องมีก่อน ไม่งั้น FK ของ auth_item_child จะ error)
        foreach (UpdateRoleController::RouteList() as $item) {
            $this->db->createCommand($sqlItem, [
                ':name' => $item['name'],
                ':type' => $item['type'],
                ':description' => $item['description'],
            ])->execute();
        }
        echo "    > sync auth_item (route/permission) เสร็จ\n";

        // 2) ผูก child (role → route / role → role)
        $skipped = 0;
        foreach (UpdateRoleController::ChildList() as $child) {
            try {
                $this->db->createCommand($sqlChild, [
                    ':parent' => $child['parent'],
                    ':child' => $child['child'],
                ])->execute();
            } catch (\yii\db\Exception $e) {
                // ข้ามคู่ที่ item ยังไม่มีในระบบ (เช่น role ที่สร้างโดย migration อื่น
                // ที่ยังไม่รัน) แทนที่จะทำ migrate ทั้งชุดล้ม
                $skipped++;
                echo "      ! ข้าม {$child['parent']} → {$child['child']} ({$e->getName()})\n";
            }
        }
        echo "    > sync auth_item_child (grant) เสร็จ" . ($skipped ? " (ข้าม {$skipped} คู่)" : '') . "\n";

        // ล้าง cache RBAC (mdm/admin ใช้ TagDependency tag 'mdm.admin')
        if (($cache = Yii::$app->get('cache', false)) !== null) {
            \yii\caching\TagDependency::invalidate($cache, 'mdm.admin');
        }
        $auth = Yii::$app->get('authManager', false);
        if ($auth !== null && method_exists($auth, 'invalidateCache')) {
            $auth->invalidateCache();
        }
        echo "    > ล้าง cache RBAC เสร็จ\n";
    }

    public function safeDown()
    {
        // เป็นการ sync แบบเพิ่มอย่างเดียว — ไม่ย้อนกลับ (การลบ grant อาจล็อกผู้ใช้ออกจากระบบ)
        echo "m260922_180000_sync_rbac_role_routes ไม่รองรับการ revert (additive sync)\n";
        return true;
    }
}
