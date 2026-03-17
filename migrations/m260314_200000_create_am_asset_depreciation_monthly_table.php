<?php

use yii\db\Migration;

/**
 * Monthly depreciation records per asset (Straight Line, government standard).
 * One record per asset per month; used for monthly processing and PDF report.
 */
class m260314_200000_create_am_asset_depreciation_monthly_table extends Migration
{
    public function safeUp()
    {
        $tableName = '{{%am_asset_depreciation_monthly}}';
        if ($this->db->getSchema()->getTableSchema($tableName, true) === null) {
            $this->createTable($tableName, [
                'id' => $this->primaryKey(),
                'asset_id' => $this->integer()->notNull()->comment('FK asset.id'),
                'fiscal_year' => $this->integer()->notNull()->comment('ปี (ค.ศ. e.g. 2024)'),
                'month' => $this->tinyInteger()->notNull()->comment('เดือน 1-12'),
                'days_used' => $this->smallInteger()->notNull()->defaultValue(30)->comment('จำนวนวันใช้ในเดือน'),
                'beginning_value' => $this->decimal(14, 2)->notNull()->defaultValue(0)->comment('มูลค่าต้นเดือน'),
                'depreciation_amount' => $this->decimal(14, 2)->notNull()->defaultValue(0)->comment('ค่าเสื่อมประจำเดือน'),
                'accumulated_depreciation' => $this->decimal(14, 2)->notNull()->defaultValue(0)->comment('ค่าเสื่อมสะสม'),
                'remaining_value' => $this->decimal(14, 2)->notNull()->defaultValue(0)->comment('มูลค่าปลายเดือน'),
                'processed_at' => $this->dateTime()->null()->comment('วันเวลาที่ประมวลผล'),
            ]);
        }

        try {
            $this->createIndex('idx_am_dep_monthly_asset_id', $tableName, 'asset_id');
        } catch (\Throwable $e) {
        }
        try {
            $this->createIndex('idx_am_dep_monthly_fiscal_month', $tableName, ['fiscal_year', 'month']);
        } catch (\Throwable $e) {
        }
        try {
            $this->createIndex('uq_am_dep_monthly_asset_year_month', $tableName, ['asset_id', 'fiscal_year', 'month'], true);
        } catch (\Throwable $e) {
        }
        try {
            $this->addForeignKey(
                'fk_am_dep_monthly_asset',
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
        $this->dropForeignKey('fk_am_dep_monthly_asset', '{{%am_asset_depreciation_monthly}}');
        $this->dropTable('{{%am_asset_depreciation_monthly}}');
    }
}
