<?php

use yii\db\Migration;

class m260917_120000_create_laundry_piece_inventory extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;
        $this->createTable('{{%laundry_item}}', [
            'id' => $this->primaryKey(),
            'item_code' => $this->string(50)->notNull()->unique(),
            'item_name' => $this->string(255)->notNull(),
            'stock_item_code' => $this->string(50)->null()->unique(),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->null(),
        ], $options);

        $this->createTable('{{%laundry_config}}', [
            'id' => $this->primaryKey(),
            'receiving_warehouse_id' => $this->integer()->notNull(),
            'configured_at' => $this->dateTime()->notNull(),
            'configured_by' => $this->integer()->null(),
        ], $options);
        $this->addForeignKey('fk_laundry_config_warehouse', '{{%laundry_config}}', 'receiving_warehouse_id', '{{%warehouses}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%laundry_piece_event}}', [
            'id' => $this->primaryKey(),
            'event_no' => $this->string(40)->notNull()->unique(),
            'event_type' => $this->string(30)->notNull(),
            'item_id' => $this->integer()->notNull(),
            'qty' => $this->integer()->notNull(),
            'from_location' => $this->string(30)->notNull(),
            'from_department_id' => $this->integer()->null(),
            'to_location' => $this->string(30)->notNull(),
            'to_department_id' => $this->integer()->null(),
            'status' => $this->string(20)->notNull(),
            'source_stock_detail_id' => $this->integer()->null()->unique(),
            'audit_count_line_id' => $this->integer()->null()->unique(),
            'processing_batch_id' => $this->integer()->null(),
            'reason' => $this->string(255)->null(),
            'occurred_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->null(),
            'approved_at' => $this->dateTime()->null(),
            'approved_by' => $this->integer()->null(),
        ], $options);
        $this->createIndex('idx_laundry_piece_item_status', '{{%laundry_piece_event}}', ['item_id', 'status']);
        $this->createIndex('idx_laundry_piece_from', '{{%laundry_piece_event}}', ['from_location', 'from_department_id']);
        $this->createIndex('idx_laundry_piece_to', '{{%laundry_piece_event}}', ['to_location', 'to_department_id']);
        $this->createIndex('idx_laundry_piece_batch', '{{%laundry_piece_event}}', 'processing_batch_id');
        $this->addForeignKey('fk_laundry_piece_item', '{{%laundry_piece_event}}', 'item_id', '{{%laundry_item}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%laundry_par}}', [
            'id' => $this->primaryKey(),
            'department_id' => $this->integer()->notNull(),
            'item_id' => $this->integer()->notNull(),
            'target_qty' => $this->integer()->notNull(),
            'min_qty' => $this->integer()->notNull()->defaultValue(0),
            'updated_at' => $this->dateTime()->notNull(),
            'updated_by' => $this->integer()->null(),
        ], $options);
        $this->createIndex('uq_laundry_par_department_item', '{{%laundry_par}}', ['department_id', 'item_id'], true);
        $this->addForeignKey('fk_laundry_par_item', '{{%laundry_par}}', 'item_id', '{{%laundry_item}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%laundry_annual_count}}', [
            'id' => $this->primaryKey(),
            'department_id' => $this->integer()->notNull(),
            'count_year' => $this->integer()->notNull(),
            'cutoff_at' => $this->dateTime()->notNull(),
            'cutoff_event_id' => $this->integer()->notNull()->defaultValue(0),
            'status' => $this->string(20)->notNull()->defaultValue('OPEN'),
            'created_by' => $this->integer()->null(),
            'cancel_reason' => $this->string(255)->null(),
            'cancelled_at' => $this->dateTime()->null(),
            'cancelled_by' => $this->integer()->null(),
            'approved_at' => $this->dateTime()->null(),
            'approved_by' => $this->integer()->null(),
        ], $options);
        $this->createIndex('idx_laundry_count_department_year', '{{%laundry_annual_count}}', ['department_id', 'count_year']);

        $this->createTable('{{%laundry_annual_count_line}}', [
            'id' => $this->primaryKey(),
            'count_id' => $this->integer()->notNull(),
            'item_id' => $this->integer()->notNull(),
            'book_qty' => $this->integer()->notNull(),
            'on_hand_qty' => $this->integer()->null(),
            'verified_in_transit_qty' => $this->integer()->null(),
            'transit_evidence' => $this->string(255)->null(),
            'actual_qty' => $this->integer()->null(),
            'variance_reason' => $this->string(255)->null(),
        ], $options);
        $this->createIndex('uq_laundry_count_item', '{{%laundry_annual_count_line}}', ['count_id', 'item_id'], true);
        $this->addForeignKey('fk_laundry_count_line_count', '{{%laundry_annual_count_line}}', 'count_id', '{{%laundry_annual_count}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_laundry_count_line_item', '{{%laundry_annual_count_line}}', 'item_id', '{{%laundry_item}}', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%laundry_annual_count_line}}');
        $this->dropTable('{{%laundry_annual_count}}');
        $this->dropTable('{{%laundry_par}}');
        $this->dropTable('{{%laundry_piece_event}}');
        $this->dropTable('{{%laundry_config}}');
        $this->dropTable('{{%laundry_item}}');
    }
}
