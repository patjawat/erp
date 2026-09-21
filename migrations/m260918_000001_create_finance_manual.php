<?php

use yii\db\Migration;

/**
 * คู่มือการเงิน (survival guide) — โครงสร้าง 3 ระดับ
 *  หมวด (category) → เรื่อง (topic) → บรรทัดเอกสาร/ข้อกำหนด (item)
 *
 * อยู่ระดับ migrations/ ชั้นเดียว เพื่อให้ deploy (yii migrate) รันเจอบน production
 */
class m260918_000001_create_finance_manual extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        // ---- หมวดใหญ่ -----------------------------------------------------
        $this->createTable('{{%finance_manual_category}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(40)->notNull()->unique(),
            'title' => $this->string(255)->notNull(),
            'icon' => $this->string(60)->null(),
            'description' => $this->string(500)->null(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);

        // ---- เรื่องย่อย ----------------------------------------------------
        $this->createTable('{{%finance_manual_topic}}', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull(),
            'intro' => $this->text()->null(),
            'note' => $this->text()->null(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);
        $this->createIndex('idx_fin_manual_topic_cat', '{{%finance_manual_topic}}', ['category_id', 'sort_order']);
        $this->addForeignKey('fk_fin_manual_topic_cat', '{{%finance_manual_topic}}', 'category_id', '{{%finance_manual_category}}', 'id', 'CASCADE', 'CASCADE');

        // ---- บรรทัดเอกสาร/ข้อกำหนด ----------------------------------------
        // kind: doc = เอกสารที่ต้องเตรียม, warning = ข้อห้าม/ข้อควรระวัง, note = หมายเหตุ
        $this->createTable('{{%finance_manual_item}}', [
            'id' => $this->primaryKey(),
            'topic_id' => $this->integer()->notNull(),
            'content' => $this->text()->notNull(),
            'kind' => $this->string(20)->notNull()->defaultValue('doc'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
        ], $options);
        $this->createIndex('idx_fin_manual_item_topic', '{{%finance_manual_item}}', ['topic_id', 'sort_order']);
        $this->addForeignKey('fk_fin_manual_item_topic', '{{%finance_manual_item}}', 'topic_id', '{{%finance_manual_topic}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%finance_manual_item}}');
        $this->dropTable('{{%finance_manual_topic}}');
        $this->dropTable('{{%finance_manual_category}}');
    }
}
