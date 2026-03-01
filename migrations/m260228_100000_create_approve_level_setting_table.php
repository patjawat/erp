<?php

use yii\db\Migration;

/**
 * ตารางตั้งค่าระดับการอนุมัติของแต่ละระบบ (leave, purchase, development, ...)
 * โครงสร้างองค์กรจาก /hr/organization/diagram (tree)
 */
class m260228_100000_create_approve_level_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%approve_level_setting}}', [
            'id' => $this->primaryKey(),
            'system' => $this->string(64)->notNull()->comment('ระบบ เช่น leave, purchase, development'),
            'level' => $this->integer()->notNull()->comment('ลำดับระดับ 1, 2, 3, 4 ...'),
            'label' => $this->string(255)->notNull()->comment('ชื่อระดับ เช่น หัวหน้าเห็นชอบ'),
            'approver_type' => $this->string(32)->notNull()->comment('org_leader1|org_leader2|role|director|fixed'),
            'approver_value' => $this->string(255)->comment('role=ชื่อบทบาท, fixed=emp_id'),
            'sort_order' => $this->integer()->defaultValue(0),
            'active' => $this->tinyInteger(1)->defaultValue(1),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ]);

        $this->createIndex('idx-approve_level_setting-system', '{{%approve_level_setting}}', 'system');
        $this->createIndex('idx-approve_level_setting-system-level', '{{%approve_level_setting}}', ['system', 'level'], true);

        // ตัวอย่างระดับการลา
        $now = date('Y-m-d H:i:s');
        $this->batchInsert('{{%approve_level_setting}}', ['system', 'level', 'label', 'approver_type', 'approver_value', 'sort_order', 'active', 'created_at', 'updated_at'], [
            ['leave', 1, 'หัวหน้าเห็นชอบ', 'org_leader1', null, 0, 1, $now, $now],
            ['leave', 2, 'หัวหน้ากลุ่มงานเห็นชอบ', 'org_leader2', null, 0, 1, $now, $now],
            ['leave', 3, 'เจ้าหน้าที่ตรวจสอบ', 'role', 'leave', 0, 1, $now, $now],
            ['leave', 4, 'ผอ.อนุมัติ', 'director', null, 0, 1, $now, $now],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%approve_level_setting}}');
    }
}
