<?php

use yii\db\Migration;

class m260917_110000_create_laundry_processing extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;
        $this->createTable('{{%laundry_machine}}', [
            'id' => $this->primaryKey(),
            'asset_id' => $this->integer()->notNull()->unique(),
            'machine_type' => $this->string(10)->notNull(),
            'capacity_kg' => $this->decimal(10, 3)->notNull(),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->null(),
        ], $options);
        $this->addForeignKey('fk_laundry_machine_asset', '{{%laundry_machine}}', 'asset_id', '{{%asset}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%laundry_processing_batch}}', [
            'id' => $this->primaryKey(),
            'batch_no' => $this->string(40)->notNull()->unique(),
            'stage' => $this->string(10)->notNull(),
            'linen_class' => $this->string(20)->notNull(),
            'asset_id' => $this->integer()->notNull(),
            'status' => $this->string(20)->notNull(),
            'program' => $this->string(100)->null(),
            'input_kg' => $this->decimal(12, 3)->notNull(),
            'output_kg' => $this->decimal(12, 3)->null(),
            'started_at' => $this->dateTime()->notNull(),
            'ended_at' => $this->dateTime()->null(),
            'operator_id' => $this->integer()->null(),
            'note' => $this->text()->null(),
            'created_at' => $this->dateTime()->notNull(),
        ], $options);
        $this->createIndex('idx_laundry_batch_asset_status', '{{%laundry_processing_batch}}', ['asset_id', 'status']);
        $this->createIndex('idx_laundry_batch_started', '{{%laundry_processing_batch}}', 'started_at');
        $this->addForeignKey('fk_laundry_batch_asset', '{{%laundry_processing_batch}}', 'asset_id', '{{%asset}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%laundry_batch_input}}', [
            'id' => $this->primaryKey(),
            'batch_id' => $this->integer()->notNull(),
            'source_type' => $this->string(20)->notNull(),
            'source_id' => $this->integer()->notNull(),
            'allocated_kg' => $this->decimal(12, 3)->notNull(),
        ], $options);
        $this->createIndex('uq_laundry_batch_input_source', '{{%laundry_batch_input}}', ['batch_id', 'source_type', 'source_id'], true);
        $this->createIndex('idx_laundry_input_source', '{{%laundry_batch_input}}', ['source_type', 'source_id']);
        $this->addForeignKey('fk_laundry_input_batch', '{{%laundry_batch_input}}', 'batch_id', '{{%laundry_processing_batch}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%laundry_batch_input}}');
        $this->dropTable('{{%laundry_processing_batch}}');
        $this->dropTable('{{%laundry_machine}}');
    }
}
