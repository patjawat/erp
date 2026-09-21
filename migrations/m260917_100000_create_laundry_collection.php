<?php

use yii\db\Migration;

/** Collection rounds: one trip, many departments, weighed after return to laundry. */
class m260917_100000_create_laundry_collection extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        $this->createTable('{{%laundry_collection_round}}', [
            'id' => $this->primaryKey(),
            'round_no' => $this->string(40)->notNull()->unique(),
            'collection_date' => $this->date()->notNull(),
            'departed_at' => $this->dateTime()->null(),
            'returned_at' => $this->dateTime()->null(),
            'collector_id' => $this->integer()->null(),
            'status' => $this->string(20)->notNull()->defaultValue('OPEN'),
            'note' => $this->text()->null(),
            'confirmed_at' => $this->dateTime()->null(),
            'confirmed_by' => $this->integer()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->null(),
        ], $options);
        $this->createIndex('idx_laundry_round_date_status', '{{%laundry_collection_round}}', ['collection_date', 'status']);

        $this->createTable('{{%laundry_collection_stop}}', [
            'id' => $this->primaryKey(),
            'round_id' => $this->integer()->notNull(),
            'department_id' => $this->integer()->notNull(),
            'collected_at' => $this->dateTime()->notNull(),
            'soiled_bag_count' => $this->integer()->notNull()->defaultValue(0),
            'infectious_bag_count' => $this->integer()->notNull()->defaultValue(0),
            'note' => $this->text()->null(),
        ], $options);
        $this->createIndex('idx_laundry_stop_round_department', '{{%laundry_collection_stop}}', ['round_id', 'department_id']);
        $this->createIndex('idx_laundry_stop_collected_department', '{{%laundry_collection_stop}}', ['collected_at', 'department_id']);
        $this->addForeignKey('fk_laundry_stop_round', '{{%laundry_collection_stop}}', 'round_id', '{{%laundry_collection_round}}', 'id', 'CASCADE', 'CASCADE');
        // Department IDs refer to the existing organization tree; validation also checks this in the service.

        $this->createTable('{{%laundry_collection_weight}}', [
            'id' => $this->primaryKey(),
            'stop_id' => $this->integer()->notNull(),
            'linen_class' => $this->string(20)->notNull(),
            'gross_kg' => $this->decimal(12, 3)->notNull(),
            'tare_kg' => $this->decimal(12, 3)->notNull(),
            'net_kg' => $this->decimal(12, 3)->notNull(),
            'weighed_at' => $this->dateTime()->notNull(),
            'weighed_by' => $this->integer()->null(),
            'scale_ref' => $this->string(100)->null(),
        ], $options);
        $this->createIndex('uq_laundry_weight_stop_class', '{{%laundry_collection_weight}}', ['stop_id', 'linen_class'], true);
        $this->addForeignKey('fk_laundry_weight_stop', '{{%laundry_collection_weight}}', 'stop_id', '{{%laundry_collection_stop}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%laundry_collection_weight}}');
        $this->dropTable('{{%laundry_collection_stop}}');
        $this->dropTable('{{%laundry_collection_round}}');
    }
}
