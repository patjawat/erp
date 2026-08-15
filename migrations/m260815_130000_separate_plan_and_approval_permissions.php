<?php

use yii\db\Migration;

/** แยกผู้จัดการแผนออกจากผู้อนุมัติแผนตามหลักแบ่งแยกหน้าที่ */
class m260815_130000_separate_plan_and_approval_permissions extends Migration
{
    public function safeUp()
    {
        $this->delete('{{%auth_item_child}}', ['parent' => 'plan', 'child' => 'planApprove']);
        $this->delete('{{%auth_item_child}}', ['parent' => 'planApprove', 'child' => '/plan/*']);

        $this->ensureChild('plan', '/plan/*');
        $this->ensureChild('planApprove', '/plan/approve/*');
        $this->ensureChild('director', 'planApprove');
        $this->ensureChild('admin', 'planApprove');
    }

    public function safeDown()
    {
        $this->ensureChild('plan', 'planApprove');
        $this->ensureChild('planApprove', '/plan/*');
    }

    private function ensureChild(string $parent, string $child): void
    {
        $parentExists = (new \yii\db\Query())->from('{{%auth_item}}')->where(['name' => $parent])->exists();
        $childExists = (new \yii\db\Query())->from('{{%auth_item}}')->where(['name' => $child])->exists();
        $exists = (new \yii\db\Query())->from('{{%auth_item_child}}')->where(['parent' => $parent, 'child' => $child])->exists();
        if ($parentExists && $childExists && !$exists) {
            $this->insert('{{%auth_item_child}}', ['parent' => $parent, 'child' => $child]);
        }
    }
}
