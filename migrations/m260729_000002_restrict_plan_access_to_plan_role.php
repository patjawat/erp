<?php

use yii\db\Migration;

/**
 * จำกัดสิทธิ์เข้าถึงระบบ /plan (ภาพรวมแผน) ให้เฉพาะผู้มีสิทธิ
 * เดิม auth_item_child ผูก '/plan/*' ไว้กับ role 'user' ทำให้ผู้ใช้ทุกคนเข้าได้
 * ถอนออก คงเหลือเฉพาะ role 'plan' (+ admin ผ่าน '/*')
 *
 * หัวหน้าหน่วยงานจัดทำแผนของหน่วยงานผ่าน /me/plan (อยู่ใต้ role 'user') แทน
 */
class m260729_000002_restrict_plan_access_to_plan_role extends Migration
{
    public function safeUp()
    {
        $this->delete('auth_item_child', ['parent' => 'user', 'child' => '/plan/*']);
    }

    public function safeDown()
    {
        // คืนสิทธิ์เดิม (เผื่อต้องย้อนกลับ)
        $exists = (new \yii\db\Query())
            ->from('auth_item_child')
            ->where(['parent' => 'user', 'child' => '/plan/*'])
            ->exists();

        if (!$exists) {
            $this->insert('auth_item_child', ['parent' => 'user', 'child' => '/plan/*']);
        }
    }
}
