<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260725_000001_create_housing_registry_tables extends Migration
{
    public function safeUp(): void
    {
        $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%housing_building}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'code' => $this->string(50)->notNull()->unique(),
            'name' => $this->string(255)->notNull(),
            'building_type' => $this->string(30)->notNull(),
            'address' => $this->text(),
            'description' => $this->text(),
            'status' => $this->string(30)->notNull()->defaultValue('active'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);

        $this->createTable('{{%housing_floor}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'building_id' => $this->integer()->notNull(),
            'floor_no' => $this->integer()->notNull(),
            'name' => $this->string(100)->notNull(),
            'description' => $this->text(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->createIndex('ux_housing_floor_building_no', '{{%housing_floor}}', ['building_id', 'floor_no'], true);
        $this->addForeignKey('fk_housing_floor_building', '{{%housing_floor}}', 'building_id', '{{%housing_building}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%housing_unit}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'building_id' => $this->integer()->notNull(),
            'floor_id' => $this->integer(),
            'code' => $this->string(50)->notNull()->unique(),
            'name' => $this->string(150)->notNull(),
            'occupancy_mode' => $this->string(30)->notNull(),
            'capacity' => $this->integer(),
            'monthly_base_fee' => $this->decimal(12, 2),
            'description' => $this->text(),
            'status' => $this->string(30)->notNull()->defaultValue('vacant'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->createIndex('ix_housing_unit_building', '{{%housing_unit}}', 'building_id');
        $this->createIndex('ix_housing_unit_floor', '{{%housing_unit}}', 'floor_id');
        $this->createIndex('ix_housing_unit_status', '{{%housing_unit}}', 'status');
        $this->addForeignKey('fk_housing_unit_building', '{{%housing_unit}}', 'building_id', '{{%housing_building}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_housing_unit_floor', '{{%housing_unit}}', 'floor_id', '{{%housing_floor}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%housing_room}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'unit_id' => $this->integer()->notNull(),
            'code' => $this->string(50)->notNull()->unique(),
            'name' => $this->string(150)->notNull(),
            'capacity' => $this->integer(),
            'description' => $this->text(),
            'status' => $this->string(30)->notNull()->defaultValue('vacant'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->createIndex('ix_housing_room_unit', '{{%housing_room}}', 'unit_id');
        $this->createIndex('ix_housing_room_status', '{{%housing_room}}', 'status');
        $this->addForeignKey('fk_housing_room_unit', '{{%housing_room}}', 'unit_id', '{{%housing_unit}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%housing_meter}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'building_id' => $this->integer(),
            'unit_id' => $this->integer(),
            'room_id' => $this->integer(),
            'meter_type' => $this->string(20)->notNull(),
            'meter_no' => $this->string(100),
            'name' => $this->string(150)->notNull(),
            'installed_at' => $this->date(),
            'retired_at' => $this->date(),
            'status' => $this->string(30)->notNull()->defaultValue('active'),
            'description' => $this->text(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->createIndex('ix_housing_meter_location', '{{%housing_meter}}', ['building_id', 'unit_id', 'room_id']);
        $this->addForeignKey('fk_housing_meter_building', '{{%housing_meter}}', 'building_id', '{{%housing_building}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_housing_meter_unit', '{{%housing_meter}}', 'unit_id', '{{%housing_unit}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_housing_meter_room', '{{%housing_meter}}', 'room_id', '{{%housing_room}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%housing_asset_assignment}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'unit_id' => $this->integer(),
            'room_id' => $this->integer(),
            'asset_id' => $this->integer(),
            'item_name' => $this->string(255)->notNull(),
            'quantity' => $this->decimal(10, 2)->notNull()->defaultValue(1),
            'condition_status' => $this->string(30)->notNull()->defaultValue('normal'),
            'assigned_at' => $this->date(),
            'returned_at' => $this->date(),
            'description' => $this->text(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->createIndex('ix_housing_asset_location', '{{%housing_asset_assignment}}', ['unit_id', 'room_id']);
        $this->addForeignKey('fk_housing_asset_unit', '{{%housing_asset_assignment}}', 'unit_id', '{{%housing_unit}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_housing_asset_room', '{{%housing_asset_assignment}}', 'room_id', '{{%housing_room}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%housing_asset_assignment}}');
        $this->dropTable('{{%housing_meter}}');
        $this->dropTable('{{%housing_room}}');
        $this->dropTable('{{%housing_unit}}');
        $this->dropTable('{{%housing_floor}}');
        $this->dropTable('{{%housing_building}}');
    }
}
