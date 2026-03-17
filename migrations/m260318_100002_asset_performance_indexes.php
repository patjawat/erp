<?php

use yii\db\Migration;

/**
 * Indexes for 50k+ assets: list, dashboard, filters.
 * Does not drop existing indexes.
 */
class m260318_100002_asset_performance_indexes extends Migration
{
    public function safeUp()
    {
        $table = '{{%asset}}';
        $schema = $this->db->getSchema()->getTableSchema($table);
        if ($schema === null) {
            return;
        }

        $indexes = [
            'idx_asset_deleted_at' => ['deleted_at'],
            'idx_asset_lifecycle_deleted' => ['lifecycle_status', 'deleted_at'],
            'idx_asset_department_deleted' => ['department', 'deleted_at'],
            'idx_asset_receive_date' => ['receive_date'],
            'idx_asset_group_deleted' => ['asset_group_id', 'deleted_at'],
        ];

        foreach ($indexes as $name => $cols) {
            try {
                if ($this->db->getSchema()->getTableSchema($table)->getColumn($cols[0]) !== null) {
                    $this->createIndex($name, $table, $cols);
                }
            } catch (\Throwable $e) {
                // index may already exist
            }
        }
    }

    public function safeDown()
    {
        $table = '{{%asset}}';
        foreach (['idx_asset_deleted_at', 'idx_asset_lifecycle_deleted', 'idx_asset_department_deleted', 'idx_asset_receive_date', 'idx_asset_group_deleted'] as $name) {
            try {
                $this->dropIndex($name, $table);
            } catch (\Throwable $e) {
            }
        }
    }
}
