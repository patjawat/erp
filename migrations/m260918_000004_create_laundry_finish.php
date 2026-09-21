<?php

use yii\db\Migration;

/**
 * นับ-รีด-QC — นับผ้าสะอาดหลังอบ (รายประเภท เป็นชิ้น) → บวกเข้าคลังหลัก (CLEAN inflow)
 * อ้างอิงรอบอบได้ (dry_batch_id) หรือนับรวมประจำวัน
 */
class m260918_000004_create_laundry_finish extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        $this->createTable('{{%laundry_finish}}', [
            'id' => $this->primaryKey(),
            'finish_no' => $this->string(40)->null(),
            'dry_batch_id' => $this->integer()->null()->comment('อ้างอิงรอบอบ (laundry_processing_batch)'),
            'counted_at' => $this->dateTime()->notNull(),
            'note' => $this->string(255)->null(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->dateTime()->null(),
        ], $options);
        $this->createIndex('idx_laundry_finish_date', '{{%laundry_finish}}', ['counted_at']);

        $this->createTable('{{%laundry_finish_line}}', [
            'id' => $this->primaryKey(),
            'finish_id' => $this->integer()->notNull(),
            'item_id' => $this->integer()->notNull(),
            'qty' => $this->integer()->notNull()->defaultValue(0),
        ], $options);
        $this->createIndex('uq_laundry_finish_line_item', '{{%laundry_finish_line}}', ['finish_id', 'item_id'], true);
        $this->addForeignKey('fk_laundry_finish_line', '{{%laundry_finish_line}}', 'finish_id', '{{%laundry_finish}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_laundry_finish_line', '{{%laundry_finish_line}}');
        $this->dropTable('{{%laundry_finish_line}}');
        $this->dropTable('{{%laundry_finish}}');
    }
}
