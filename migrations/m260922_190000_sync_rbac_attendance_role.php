<?php

use yii\db\Migration;
use app\commands\UpdateRoleController;

/**
 * Re-sync RBAC (role→route) อีกครั้ง หลังเพิ่ม role 'attendance' (เจ้าหน้าที่ลงเวลา)
 *
 * เพิ่มความสามารถ "มอบงานลงเวลาให้คนนอก HR" โดยไม่ต้องให้สิทธิ์ HR เต็ม —
 * โมดูล attendance ตรวจสิทธิ์จัดการด้วย admin||hr||attendance แล้ว. migration นี้
 * รับประกันว่าทุก รพ. ได้ role 'attendance' + grant /attendance/* แม้เครื่องที่
 * รัน m260922_180000 ไปก่อนหน้าที่จะเพิ่ม role นี้ในโค้ด
 *
 * เรียก RouteList()/ChildList() ชุดเดียวกับ `yii update-role` — idempotent
 * (INSERT ... ON DUPLICATE KEY UPDATE, เพิ่มอย่างเดียวไม่ลบ) รันซ้ำปลอดภัย
 * ดูแนวทางเพิ่มสิทธิ์รอบถัดไปที่ m260922_180000_sync_rbac_role_routes
 */
class m260922_190000_sync_rbac_attendance_role extends Migration
{
    public function safeUp()
    {
        $sqlItem = 'INSERT INTO auth_item (type, name, description) VALUES (:type, :name, :description)'
            . ' ON DUPLICATE KEY UPDATE name = VALUES(name), type = VALUES(type), description = VALUES(description)';
        $sqlChild = 'INSERT INTO auth_item_child (parent, child) VALUES (:parent, :child)'
            . ' ON DUPLICATE KEY UPDATE child = VALUES(child), parent = VALUES(parent)';

        foreach (UpdateRoleController::RouteList() as $item) {
            $this->db->createCommand($sqlItem, [
                ':name' => $item['name'],
                ':type' => $item['type'],
                ':description' => $item['description'],
            ])->execute();
        }
        echo "    > sync auth_item เสร็จ\n";

        $skipped = 0;
        foreach (UpdateRoleController::ChildList() as $child) {
            try {
                $this->db->createCommand($sqlChild, [
                    ':parent' => $child['parent'],
                    ':child' => $child['child'],
                ])->execute();
            } catch (\yii\db\Exception $e) {
                $skipped++;
                echo "      ! ข้าม {$child['parent']} → {$child['child']} ({$e->getName()})\n";
            }
        }
        echo "    > sync auth_item_child เสร็จ" . ($skipped ? " (ข้าม {$skipped} คู่)" : '') . "\n";

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
        echo "m260922_190000_sync_rbac_attendance_role ไม่รองรับการ revert (additive sync)\n";
        return true;
    }
}
