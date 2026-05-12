<?php

use yii\db\Migration;

/**
 * Creates table `asset_audit_items` for audit item rows.
 */
class m260429_090001_create_am_asset_audit_items_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%asset_audit_items}}', [
            'id' => $this->primaryKey(),
            'audit_id' => $this->integer()->notNull()->comment('FK asset_audits.id'),
            'asset_id' => $this->integer()->null()->comment('FK asset.id'),
            'asset_code' => $this->string(255)->notNull()->comment('รหัสครุภัณฑ์'),
            'asset_name' => $this->string(255)->notNull()->comment('ชื่อครุภัณฑ์'),
            'asset_condition' => $this->string(20)->null()->comment('สภาพครุภัณฑ์'),
            'note' => $this->text()->null()->comment('หมายเหตุ'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0)->comment('ลำดับ'),
            'created_at' => $this->dateTime()->null()->comment('วันเวลาสร้าง'),
            'updated_at' => $this->dateTime()->null()->comment('วันเวลาแก้ไข'),
        ]);

        $this->createIndex('idx_asset_audit_items_audit_id', '{{%asset_audit_items}}', 'audit_id');
        $this->createIndex('idx_asset_audit_items_asset_id', '{{%asset_audit_items}}', 'asset_id');
        $this->createIndex('idx_asset_audit_items_code', '{{%asset_audit_items}}', 'asset_code');

        $this->addForeignKey(
            'fk_asset_audit_items_audit',
            '{{%asset_audit_items}}',
            'audit_id',
            '{{%asset_audits}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_asset_audit_items_asset',
            '{{%asset_audit_items}}',
            'asset_id',
            '{{%asset}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_asset_audit_items_asset', '{{%asset_audit_items}}');
        $this->dropForeignKey('fk_asset_audit_items_audit', '{{%asset_audit_items}}');
        $this->dropTable('{{%asset_audit_items}}');
    }
}
