<?php

use yii\db\Migration;

/**
 * รวมสิทธิ์อนุมัติแผน (planApprove) เข้ากับ role 'plan' (แผนงานและโครงการ)
 * -> ผู้ดูแลแผน = ทุกคนที่มีสิทธิ์แผนงานโครงการ สามารถอนุมัติ/ไม่อนุมัติแผนได้
 */
class m260731_000002_grant_approve_to_plan_role extends Migration
{
    public function safeUp()
    {
        $exists = (new \yii\db\Query())->from('auth_item_child')
            ->where(['parent' => 'plan', 'child' => 'planApprove'])->exists();
        $ok = (new \yii\db\Query())->from('auth_item')->where(['name' => 'planApprove'])->exists()
            && (new \yii\db\Query())->from('auth_item')->where(['name' => 'plan'])->exists();
        if (!$exists && $ok) {
            $this->insert('auth_item_child', ['parent' => 'plan', 'child' => 'planApprove']);
        }
    }

    public function safeDown()
    {
        $this->delete('auth_item_child', ['parent' => 'plan', 'child' => 'planApprove']);
    }
}
