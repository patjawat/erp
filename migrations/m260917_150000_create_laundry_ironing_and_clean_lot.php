<?php

use yii\db\Migration;

class m260917_150000_create_laundry_ironing_and_clean_lot extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;
        $this->createTable('{{%laundry_ironing}}', [
            'id' => $this->primaryKey(),
            'dry_batch_id' => $this->integer()->notNull(),
            'item_id' => $this->integer()->notNull(),
            'qty' => $this->integer()->notNull(),
            'started_at' => $this->dateTime()->notNull(),
            'ended_at' => $this->dateTime()->notNull(),
            'evidence' => $this->string(255)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->notNull(),
        ], $options);
        $this->createIndex('idx_laundry_ironing_batch_item', '{{%laundry_ironing}}', ['dry_batch_id', 'item_id']);
        $this->addForeignKey('fk_laundry_ironing_batch', '{{%laundry_ironing}}', 'dry_batch_id', '{{%laundry_processing_batch}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_laundry_ironing_item', '{{%laundry_ironing}}', 'item_id', '{{%laundry_item}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%laundry_clean_lot}}', [
            'id' => $this->primaryKey(),
            'lot_no' => $this->string(50)->notNull(),
            'item_id' => $this->integer()->notNull(),
            'source_type' => $this->string(20)->notNull(),
            'dry_batch_id' => $this->integer()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->null(),
        ], $options);
        $this->createIndex('uq_laundry_clean_lot_no', '{{%laundry_clean_lot}}', 'lot_no', true);
        $this->createIndex('uq_laundry_clean_lot_production', '{{%laundry_clean_lot}}', ['dry_batch_id', 'item_id'], true);
        $this->addForeignKey('fk_laundry_clean_lot_item', '{{%laundry_clean_lot}}', 'item_id', '{{%laundry_item}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_laundry_clean_lot_batch', '{{%laundry_clean_lot}}', 'dry_batch_id', '{{%laundry_processing_batch}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addColumn('{{%laundry_piece_event}}', 'clean_lot_id', $this->integer()->null());
        $this->createIndex('idx_laundry_piece_event_lot', '{{%laundry_piece_event}}', 'clean_lot_id');
        $this->addForeignKey('fk_laundry_piece_event_clean_lot', '{{%laundry_piece_event}}', 'clean_lot_id', '{{%laundry_clean_lot}}', 'id', 'RESTRICT', 'CASCADE');
        // Existing installations may already have a circulating-piece history.
        // Keep its net CLEAN balance in one traceable legacy lot per item.
        $itemIds = $this->db->createCommand("SELECT DISTINCT item_id FROM {{%laundry_piece_event}} WHERE to_location = 'CLEAN' OR from_location = 'CLEAN'")->queryColumn();
        foreach ($itemIds as $itemId) {
            $this->insert('{{%laundry_clean_lot}}', [
                'lot_no' => 'LL-LEGACY-' . (int) $itemId,
                'item_id' => (int) $itemId,
                'source_type' => 'LEGACY',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $lotId = (int) $this->db->getLastInsertID();
            $this->update('{{%laundry_piece_event}}', ['clean_lot_id' => $lotId],
                ['and', ['item_id' => (int) $itemId], ['or', ['to_location' => 'CLEAN'], ['from_location' => 'CLEAN']]]);
        }
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_laundry_piece_event_clean_lot', '{{%laundry_piece_event}}');
        $this->dropIndex('idx_laundry_piece_event_lot', '{{%laundry_piece_event}}');
        $this->dropColumn('{{%laundry_piece_event}}', 'clean_lot_id');
        $this->dropTable('{{%laundry_clean_lot}}');
        $this->dropTable('{{%laundry_ironing}}');
    }
}
