<?php

use yii\db\Migration;

/**
 * Yearly depreciation records per asset (Straight Line).
 * Formula: annual_depreciation = (purchase_price - residual_value) / useful_life
 */
class m260318_100000_create_am_asset_depreciations_table extends Migration
{
    public function safeUp()
    {
        $tableName = '{{%am_asset_depreciations}}';
        if ($this->db->getSchema()->getTableSchema($tableName, true) === null) {
            $this->createTable($tableName, [
                'id' => $this->primaryKey(),
                'asset_id' => $this->integer()->notNull()->comment('FK asset.id'),
                'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (ค.ศ. e.g. 2024)'),
                'opening_value' => $this->decimal(14, 2)->notNull()->defaultValue(0)->comment('มูลค่าต้นปี'),
                'depreciation_amount' => $this->decimal(14, 2)->notNull()->defaultValue(0)->comment('ค่าเสื่อมประจำปี'),
                'accumulated_depreciation' => $this->decimal(14, 2)->notNull()->defaultValue(0)->comment('ค่าเสื่อมสะสม'),
                'closing_value' => $this->decimal(14, 2)->notNull()->defaultValue(0)->comment('มูลค่าปลายปี'),
                'is_locked' => $this->boolean()->notNull()->defaultValue(false)->comment('ล็อกเมื่อปิดปี'),
                'created_at' => $this->dateTime()->null(),
                'updated_at' => $this->dateTime()->null(),
            ]);
        }

        try {
            $this->createIndex('idx_am_asset_depreciations_asset_id', $tableName, 'asset_id');
        } catch (\Throwable $e) {
            // index may already exist
        }
        try {
            $this->createIndex('idx_am_asset_depreciations_fiscal_year', $tableName, 'fiscal_year');
        } catch (\Throwable $e) {
        }
        try {
            $this->createIndex('uq_am_asset_depreciations_asset_year', $tableName, ['asset_id', 'fiscal_year'], true);
        } catch (\Throwable $e) {
        }
        try {
            $this->addForeignKey(
                'fk_am_asset_depreciations_asset',
                $tableName,
                'asset_id',
                '{{%asset}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
        } catch (\Throwable $e) {
        }
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_am_asset_depreciations_asset', '{{%am_asset_depreciations}}');
        $this->dropTable('{{%am_asset_depreciations}}');
    }
}
