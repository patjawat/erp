<?php

use yii\db\Migration;

/**
 * Asset lifecycle: transaction history table and asset columns (lifecycle_status, qr_code_path).
 */
class m260317_100000_asset_lifecycle extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%am_asset_transactions}}', [
            'id' => $this->primaryKey(),
            'asset_id' => $this->integer()->notNull()->comment('FK asset.id'),
            'transaction_type' => $this->string(32)->notNull()->comment('RECEIVE|TRANSFER|REPAIR|RETURN|DISPOSE'),
            'from_location' => $this->string(255)->comment('สถานที่เดิม'),
            'to_location' => $this->string(255)->comment('สถานที่ใหม่'),
            'from_department' => $this->integer()->comment('หน่วยงานเดิม'),
            'to_department' => $this->integer()->comment('หน่วยงานใหม่'),
            'remark' => $this->text()->comment('หมายเหตุ'),
            'data_json' => $this->json()->comment('repair_cost, vendor, disposal_method, etc.'),
            'created_by' => $this->integer()->comment('ผู้บันทึก'),
            'created_at' => $this->dateTime()->notNull()->comment('วันเวลา'),
        ]);

        $this->createIndex('idx_am_asset_transactions_asset_id', '{{%am_asset_transactions}}', 'asset_id');
        $this->createIndex('idx_am_asset_transactions_type', '{{%am_asset_transactions}}', 'transaction_type');
        $this->createIndex('idx_am_asset_transactions_created_at', '{{%am_asset_transactions}}', 'created_at');
        $this->addForeignKey(
            'fk_am_asset_transactions_asset',
            '{{%am_asset_transactions}}',
            'asset_id',
            '{{%asset}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        if ($this->db->getSchema()->getTableSchema('{{%asset}}')->getColumn('lifecycle_status') === null) {
            $this->addColumn('{{%asset}}', 'lifecycle_status', $this->string(32)->comment('received|active|repair|disposed'));
        }
        if ($this->db->getSchema()->getTableSchema('{{%asset}}')->getColumn('qr_code_path') === null) {
            $this->addColumn('{{%asset}}', 'qr_code_path', $this->string(500)->comment('Path to saved QR image'));
        }
        $this->createIndex('idx_asset_lifecycle_status', '{{%asset}}', 'lifecycle_status');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_am_asset_transactions_asset', '{{%am_asset_transactions}}');
        $this->dropTable('{{%am_asset_transactions}}');
        if ($this->db->getSchema()->getTableSchema('{{%asset}}')->getColumn('lifecycle_status') !== null) {
            $this->dropIndex('idx_asset_lifecycle_status', '{{%asset}}');
            $this->dropColumn('{{%asset}}', 'lifecycle_status');
        }
        if ($this->db->getSchema()->getTableSchema('{{%asset}}')->getColumn('qr_code_path') !== null) {
            $this->dropColumn('{{%asset}}', 'qr_code_path');
        }
    }
}
