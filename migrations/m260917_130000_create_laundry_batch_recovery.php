<?php

use yii\db\Migration;

class m260917_130000_create_laundry_batch_recovery extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;
        $this->createTable('{{%laundry_batch_recovery}}', [
            'id' => $this->primaryKey(),
            'aborted_batch_id' => $this->integer()->notNull()->unique(),
            'outcome' => $this->string(20)->notNull(),
            'measured_kg' => $this->decimal(12, 3)->notNull()->defaultValue(0),
            'evidence' => $this->string(255)->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('PENDING'),
            'created_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'approved_at' => $this->dateTime()->null(),
            'approved_by' => $this->integer()->null(),
        ], $options);
        $this->addForeignKey('fk_laundry_recovery_batch', '{{%laundry_batch_recovery}}', 'aborted_batch_id', '{{%laundry_processing_batch}}', 'id', 'RESTRICT', 'CASCADE');
        $this->createIndex('idx_laundry_recovery_status', '{{%laundry_batch_recovery}}', 'status');
    }

    public function safeDown()
    {
        $this->dropTable('{{%laundry_batch_recovery}}');
    }
}
