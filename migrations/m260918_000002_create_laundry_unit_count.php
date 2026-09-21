<?php

use yii\db\Migration;

/**
 * ตรวจนับผ้า — ผลนับผ้าสะอาดคงเหลือที่หน่วยงาน (รายประเภท เป็นชิ้น)
 * ใช้อ้างอิงตอนจ่ายผ้า (จ่ายเพิ่มจากที่เหลือให้ถึงยอดตั้งต้น)
 */
class m260918_000002_create_laundry_unit_count extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        $this->createTable('{{%laundry_unit_count}}', [
            'id' => $this->primaryKey(),
            'count_no' => $this->string(40)->null(),
            'tree_id' => $this->integer()->notNull()->comment('หน่วยงาน (tree.id)'),
            'counted_at' => $this->dateTime()->notNull(),
            'note' => $this->string(255)->null(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->dateTime()->null(),
        ], $options);
        $this->createIndex('idx_laundry_unit_count_unit_date', '{{%laundry_unit_count}}', ['tree_id', 'counted_at']);

        $this->createTable('{{%laundry_unit_count_line}}', [
            'id' => $this->primaryKey(),
            'count_id' => $this->integer()->notNull(),
            'item_id' => $this->integer()->notNull(),
            'qty' => $this->integer()->notNull()->defaultValue(0),
        ], $options);
        $this->createIndex('uq_laundry_unit_count_item', '{{%laundry_unit_count_line}}', ['count_id', 'item_id'], true);
        $this->addForeignKey('fk_laundry_unit_count_line', '{{%laundry_unit_count_line}}', 'count_id', '{{%laundry_unit_count}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_laundry_unit_count_line', '{{%laundry_unit_count_line}}');
        $this->dropTable('{{%laundry_unit_count_line}}');
        $this->dropTable('{{%laundry_unit_count}}');
    }
}
