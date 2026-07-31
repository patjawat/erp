<?php

use yii\db\Migration;

/**
 * ให้ผู้มีสิทธิ planApprove (ผู้อนุมัติแผน) เข้าถึงระบบแผนงาน /plan ได้ทั้งหมด
 * (เดิม planApprove เปิดเฉพาะ /plan/approve/*) เพื่อให้อนุมัติผ่านระบบแผนงานได้เต็มรูปแบบ
 */
class m260731_000001_grant_plan_access_to_plan_approve extends Migration
{
    public function safeUp()
    {
        $exists = (new \yii\db\Query())->from('auth_item_child')
            ->where(['parent' => 'planApprove', 'child' => '/plan/*'])->exists();
        $childExists = (new \yii\db\Query())->from('auth_item')->where(['name' => '/plan/*'])->exists();
        if (!$exists && $childExists) {
            $this->insert('auth_item_child', ['parent' => 'planApprove', 'child' => '/plan/*']);
        }
    }

    public function safeDown()
    {
        $this->delete('auth_item_child', ['parent' => 'planApprove', 'child' => '/plan/*']);
    }
}
