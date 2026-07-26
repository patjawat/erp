<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260726_000002_add_housing_inventory_and_photos extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%housing_asset_assignment}}', 'category', $this->string(100)->after('item_name'));
        $this->addColumn('{{%housing_asset_assignment}}', 'unit_name', $this->string(50)->notNull()->defaultValue('ชิ้น')->after('quantity'));
        $this->addColumn('{{%housing_asset_assignment}}', 'unit_price', $this->decimal(12, 2)->notNull()->defaultValue(0)->after('unit_name'));
        $this->addColumn('{{%housing_asset_assignment}}', 'monthly_rent', $this->decimal(12, 2)->notNull()->defaultValue(0)->after('unit_price'));
        $this->addColumn('{{%housing_asset_assignment}}', 'is_active', $this->boolean()->notNull()->defaultValue(true)->after('returned_at'));
        $this->createIndex('ix_housing_asset_condition', '{{%housing_asset_assignment}}', ['condition_status', 'is_active']);

        $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        $this->createTable('{{%housing_location_photo}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'unit_id' => $this->integer()->notNull(),
            'room_id' => $this->integer(),
            'upload_id' => $this->integer()->notNull(),
            'caption' => $this->string(255),
            'is_primary' => $this->boolean()->notNull()->defaultValue(false),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->createIndex('ix_housing_location_photo_location', '{{%housing_location_photo}}', ['unit_id', 'room_id', 'is_primary']);
        $this->createIndex('ux_housing_location_photo_upload', '{{%housing_location_photo}}', 'upload_id', true);
        $this->addForeignKey('fk_housing_location_photo_unit', '{{%housing_location_photo}}', 'unit_id', '{{%housing_unit}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_housing_location_photo_room', '{{%housing_location_photo}}', 'room_id', '{{%housing_room}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%housing_location_photo}}');
        $this->dropIndex('ix_housing_asset_condition', '{{%housing_asset_assignment}}');
        $this->dropColumn('{{%housing_asset_assignment}}', 'is_active');
        $this->dropColumn('{{%housing_asset_assignment}}', 'monthly_rent');
        $this->dropColumn('{{%housing_asset_assignment}}', 'unit_price');
        $this->dropColumn('{{%housing_asset_assignment}}', 'unit_name');
        $this->dropColumn('{{%housing_asset_assignment}}', 'category');
    }
}
