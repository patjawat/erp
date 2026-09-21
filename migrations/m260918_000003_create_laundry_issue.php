<?php

use yii\db\Migration;

/**
 * ส่งผ้า/เบิกจ่าย — จ่ายผ้าสะอาดจากคลังหลักให้หน่วยงาน (เติมให้ถึงยอดตั้งต้น)
 * อ้างอิงผลตรวจนับ (count_id) เพื่อรู้ส่วนขาด หรือกรอกเองได้ (ยืดหยุ่น)
 */
class m260918_000003_create_laundry_issue extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        $this->createTable('{{%laundry_issue}}', [
            'id' => $this->primaryKey(),
            'issue_no' => $this->string(40)->null(),
            'tree_id' => $this->integer()->notNull()->comment('หน่วยงานผู้รับ (tree.id)'),
            'count_id' => $this->integer()->null()->comment('อ้างอิงผลตรวจนับ (laundry_unit_count)'),
            'issued_at' => $this->dateTime()->notNull(),
            'note' => $this->string(255)->null(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->dateTime()->null(),
        ], $options);
        $this->createIndex('idx_laundry_issue_unit_date', '{{%laundry_issue}}', ['tree_id', 'issued_at']);

        $this->createTable('{{%laundry_issue_line}}', [
            'id' => $this->primaryKey(),
            'issue_id' => $this->integer()->notNull(),
            'item_id' => $this->integer()->notNull(),
            'need_qty' => $this->integer()->notNull()->defaultValue(0)->comment('จำนวนเบิก (ส่วนขาดตอนจ่าย)'),
            'issue_qty' => $this->integer()->notNull()->defaultValue(0)->comment('จำนวนจ่ายจริง'),
        ], $options);
        $this->createIndex('uq_laundry_issue_line_item', '{{%laundry_issue_line}}', ['issue_id', 'item_id'], true);
        $this->addForeignKey('fk_laundry_issue_line', '{{%laundry_issue_line}}', 'issue_id', '{{%laundry_issue}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_laundry_issue_line', '{{%laundry_issue_line}}');
        $this->dropTable('{{%laundry_issue_line}}');
        $this->dropTable('{{%laundry_issue}}');
    }
}
