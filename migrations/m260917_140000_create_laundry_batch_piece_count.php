<?php

use yii\db\Migration;

class m260917_140000_create_laundry_batch_piece_count extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;
        $this->createTable('{{%laundry_batch_piece_count}}', [
            'id' => $this->primaryKey(),
            'dry_batch_id' => $this->integer()->notNull(),
            'item_id' => $this->integer()->notNull(),
            'qty' => $this->integer()->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('PENDING'),
            'evidence' => $this->string(255)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'approved_at' => $this->dateTime()->null(),
            'approved_by' => $this->integer()->null(),
        ], $options);
        $this->createIndex('uq_laundry_batch_piece_count', '{{%laundry_batch_piece_count}}', ['dry_batch_id', 'item_id'], true);
        $this->addForeignKey('fk_laundry_batch_piece_count_batch', '{{%laundry_batch_piece_count}}', 'dry_batch_id', '{{%laundry_processing_batch}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_laundry_batch_piece_count_item', '{{%laundry_batch_piece_count}}', 'item_id', '{{%laundry_item}}', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%laundry_batch_piece_count}}');
    }
}
