<?php

use yii\db\Migration;

class m260429_110001_create_asset_disposal_items_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%asset_disposal_items}}', [
            'id' => $this->primaryKey(),
            'disposal_id' => $this->integer()->notNull()->comment('ใบขอจำหน่าย'),
            'asset_id' => $this->integer()->null()->comment('ครุภัณฑ์'),
            'asset_code' => $this->string(255)->notNull()->comment('รหัส'),
            'asset_name' => $this->string(255)->notNull()->comment('ชื่อ'),
            'asset_condition' => $this->string(20)->null()->comment('สภาพ'),
            'reason' => $this->text()->null()->comment('เหตุผล'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
        ]);

        $this->createIndex('idx_asset_disposal_items_disposal_id', '{{%asset_disposal_items}}', 'disposal_id');
        $this->createIndex('idx_asset_disposal_items_asset_id', '{{%asset_disposal_items}}', 'asset_id');
        $this->createIndex('idx_asset_disposal_items_sort_order', '{{%asset_disposal_items}}', 'sort_order');

        $this->addForeignKey(
            'fk_asset_disposal_items_disposal_id',
            '{{%asset_disposal_items}}',
            'disposal_id',
            '{{%asset_disposals}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_asset_disposal_items_disposal_id', '{{%asset_disposal_items}}');
        $this->dropIndex('idx_asset_disposal_items_sort_order', '{{%asset_disposal_items}}');
        $this->dropIndex('idx_asset_disposal_items_asset_id', '{{%asset_disposal_items}}');
        $this->dropIndex('idx_asset_disposal_items_disposal_id', '{{%asset_disposal_items}}');
        $this->dropTable('{{%asset_disposal_items}}');
    }
}
