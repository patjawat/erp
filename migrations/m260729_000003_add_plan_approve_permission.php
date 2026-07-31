<?php

use yii\db\Migration;

/**
 * เพิ่มสิทธิ์อนุมัติแผนของหน่วยงาน (Part D)
 *  - permission `planApprove` (ผู้อนุมัติแผน — ผอ./ผู้ที่กำหนด)
 *  - route `/plan/approve/*` (หน้าอนุมัติในโมดูล plan)
 *  - มอบ planApprove ให้ role `director` และ `admin`
 *  - ผูก route ให้ planApprove เพื่อให้ director (ที่ไม่มี role plan) เข้าถึงหน้าอนุมัติได้
 *
 * หมายเหตุ: ApproveController ยังเช็ค can('planApprove') ในตัว controller อีกชั้น
 * ดังนั้นผู้มี role plan แต่ไม่มี planApprove จะเข้าดูได้แต่กดอนุมัติไม่ได้
 */
class m260729_000003_add_plan_approve_permission extends Migration
{
    private $items = [
        ['name' => 'planApprove', 'type' => 2, 'description' => 'ผู้อนุมัติแผนของหน่วยงาน'],
        ['name' => '/plan/approve/*', 'type' => 2, 'description' => 'หน้าอนุมัติแผนหน่วยงาน'],
    ];

    private $children = [
        ['parent' => 'planApprove', 'child' => '/plan/approve/*'],
        ['parent' => 'director', 'child' => 'planApprove'],
        ['parent' => 'admin', 'child' => 'planApprove'],
    ];

    public function safeUp()
    {
        $now = time();
        foreach ($this->items as $it) {
            $exists = (new \yii\db\Query())->from('auth_item')->where(['name' => $it['name']])->exists();
            if (!$exists) {
                $this->insert('auth_item', [
                    'name' => $it['name'],
                    'type' => $it['type'],
                    'description' => $it['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        foreach ($this->children as $c) {
            $exists = (new \yii\db\Query())->from('auth_item_child')
                ->where(['parent' => $c['parent'], 'child' => $c['child']])->exists();
            // parent ต้องมีอยู่จริง (director/admin/planApprove)
            $parentExists = (new \yii\db\Query())->from('auth_item')->where(['name' => $c['parent']])->exists();
            if (!$exists && $parentExists) {
                $this->insert('auth_item_child', ['parent' => $c['parent'], 'child' => $c['child']]);
            }
        }
    }

    public function safeDown()
    {
        foreach ($this->children as $c) {
            $this->delete('auth_item_child', ['parent' => $c['parent'], 'child' => $c['child']]);
        }
        // ลบ child links ที่อ้างถึง items เหล่านี้ก่อน แล้วค่อยลบ item
        $this->delete('auth_item_child', ['child' => 'planApprove']);
        $this->delete('auth_item_child', ['parent' => 'planApprove']);
        foreach ($this->items as $it) {
            $this->delete('auth_item', ['name' => $it['name']]);
        }
    }
}
