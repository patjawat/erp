<?php

use yii\db\Migration;
use yii\db\Query;

class m260429_044945_add_status_condition extends Migration
{
    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb3';

        // =====================================================
        // ลบ column life ถ้ามี
        // =====================================================
        $assetSchema = $this->db->getSchema()->getTableSchema('asset', true);
        if ($assetSchema !== null && $assetSchema->getColumn('life') !== null) {
            $this->dropColumn('asset', 'life');
        }

        // =====================================================
        // 1. ตาราง asset_status
        // =====================================================
        $this->ensureLookupTable(
            'asset_status',
            [
                'id' => $this->string(20)->notNull(),
                'name' => $this->string(100)->notNull(),
                'color_css' => $this->string(50)->comment('ชื่อ CSS Class เช่น success, warning, danger'),
                'color_code' => $this->string(10)->comment('รหัสสี Hex Code เช่น #28a745'),
                'sort_order' => $this->tinyInteger()->unsigned()->defaultValue(0),
                'is_active' => $this->boolean()->notNull()->defaultValue(true),
            ],
            [
                ['id' => 'active', 'name' => 'ใช้งาน', 'color_css' => 'success', 'color_code' => '#28a745', 'sort_order' => 1],
                ['id' => 'borrowed', 'name' => 'ถูกยืม', 'color_css' => 'info', 'color_code' => '#17a2b8', 'sort_order' => 2],
                ['id' => 'repair', 'name' => 'ส่งซ่อม', 'color_css' => 'warning', 'color_code' => '#ffc107', 'sort_order' => 3],
                ['id' => 'wait_dispose', 'name' => 'รอจำหน่าย', 'color_css' => 'secondary', 'color_code' => '#6c757d', 'sort_order' => 4],
                ['id' => 'disposed', 'name' => 'จำหน่ายแล้ว', 'color_css' => 'danger', 'color_code' => '#dc3545', 'sort_order' => 5],
            ],
            'pk_asset_status',
            $tableOptions
        );

        // =====================================================
        // 2. ตาราง asset_condition
        // =====================================================
        $this->ensureLookupTable(
            'asset_condition',
            [
                'id' => $this->string(20)->notNull(),
                'name' => $this->string(50)->notNull(),
                'sort_order' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
                'is_active' => $this->boolean()->notNull()->defaultValue(true),
            ],
            [
                ['id' => 'good', 'name' => 'ดี', 'sort_order' => 1],
                ['id' => 'fair', 'name' => 'พอใช้', 'sort_order' => 2],
                ['id' => 'damaged', 'name' => 'ชำรุด', 'sort_order' => 3],
                ['id' => 'worn', 'name' => 'เสื่อมสภาพ', 'sort_order' => 4],
                ['id' => 'lost', 'name' => 'หาย', 'sort_order' => 5],
            ],
            'pk_asset_condition',
            $tableOptions
        );

        // =====================================================
        // 3. ปรับ column asset_status ให้เป็น string ก่อนอัปเดตข้อมูล
        // =====================================================
        $assetSchema = $this->db->getSchema()->getTableSchema('asset', true);
        if ($assetSchema !== null && $assetSchema->getColumn('asset_status') !== null) {
            $this->alterColumn(
                'asset',
                'asset_status',
                $this->string(20)->defaultValue('active')
            );
        }

        // -----------------------------------------------------
        // * อัปเดตข้อมูลสถานะเดิม ให้เป็นรหัสสถานะใหม่ *
        // -----------------------------------------------------
        $this->update('asset', ['asset_status' => 'active'],       ['asset_status' => '1']);
        $this->update('asset', ['asset_status' => 'disposed'],     ['asset_status' => '2']);
        $this->update('asset', ['asset_status' => 'wait_dispose'], ['asset_status' => '3']);
        $this->update('asset', ['asset_status' => 'borrowed'],     ['asset_status' => '4']);
        $this->update('asset', ['asset_status' => 'repair'],       ['asset_status' => '5']);
        $this->update('asset', ['asset_status' => 'active'],       ['asset_status' => '0']); 
        $this->update('asset', ['asset_status' => 'active'],       ['asset_status' => null]);
        $this->update('asset', ['asset_status' => 'active'],       ['asset_status' => '']); // ดักค่า String ว่าง

        // [เพิ่มใหม่] คลีนอัพข้อมูลขยะที่ไม่ได้อยู่ใน List ให้กลายเป็น active ทั้งหมด
        $validStatuses = ['active', 'borrowed', 'repair', 'wait_dispose', 'disposed'];
        $this->update('asset', ['asset_status' => 'active'], ['NOT IN', 'asset_status', $validStatuses]);

        // =====================================================
        // 4. เพิ่ม asset_condition
        // =====================================================
        $assetSchema = $this->db->getSchema()->getTableSchema('asset', true);
        if ($assetSchema !== null && $assetSchema->getColumn('asset_condition') === null) {
            $this->addColumn(
                'asset',
                'asset_condition',
                "VARCHAR(20) NOT NULL DEFAULT 'good' COMMENT 'FK → asset_condition.id' AFTER asset_status"
            );
        }

        // =====================================================
        // 5. index
        // =====================================================
        $this->ensureIndex('idx_asset_status', 'asset', 'asset_status');
        $this->ensureIndex('idx_asset_condition', 'asset', 'asset_condition');

        // =====================================================
        // 6. FK 
        // =====================================================
        $this->ensureForeignKey(
            'fk_asset_status',
            'asset',
            'asset_status',
            'asset_status',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->ensureForeignKey(
            'fk_asset_condition',
            'asset',
            'asset_condition',
            'asset_condition',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $assetSchema = $this->db->getSchema()->getTableSchema('asset', true);
        if ($assetSchema !== null) {
            $this->dropForeignKeyIfExists('fk_asset_condition', 'asset');
            $this->dropForeignKeyIfExists('fk_asset_status', 'asset');

            if ($assetSchema->getColumn('asset_condition') !== null) {
                $this->dropIndexIfExists('idx_asset_condition', 'asset');
                $this->dropColumn('asset', 'asset_condition');
            }

            $this->dropIndexIfExists('idx_asset_status', 'asset');
        }

        $this->update('asset', ['asset_status' => '1'], ['asset_status' => 'active']);
        $this->update('asset', ['asset_status' => '2'], ['asset_status' => 'disposed']);
        $this->update('asset', ['asset_status' => '3'], ['asset_status' => 'wait_dispose']);
        $this->update('asset', ['asset_status' => '4'], ['asset_status' => 'borrowed']);
        $this->update('asset', ['asset_status' => '5'], ['asset_status' => 'repair']);

        $this->dropTableIfExists('asset_condition');
        $this->dropTableIfExists('asset_status');
    }

    private function ensureLookupTable(string $tableName, array $columns, array $rows, string $primaryKeyName, string $tableOptions): void
    {
        $schema = $this->db->getSchema()->getTableSchema($tableName, true);

        if ($schema === null) {
            $this->createTable($tableName, $columns, $tableOptions);
            $schema = $this->db->getSchema()->getTableSchema($tableName, true);
        }

        if ($schema !== null && empty($schema->primaryKey)) {
            try {
                $this->addPrimaryKey($primaryKeyName, $tableName, 'id');
            } catch (\Throwable $e) {
                // primary key may already exist from a partial migration run
            }
        }

        foreach ($rows as $row) {
            if (!(new Query())->from($tableName)->where(['id' => $row['id']])->exists($this->db)) {
                $this->insert($tableName, $row);
            }
        }
    }

    private function ensureIndex(string $name, string $table, $columns): void
    {
        try {
            $this->createIndex($name, $table, $columns);
        } catch (\Throwable $e) {
            // index may already exist from a partial migration run
        }
    }

    private function ensureForeignKey(
        string $name,
        string $table,
        string $column,
        string $refTable,
        string $refColumn,
        string $delete,
        string $update
    ): void {
        try {
            $this->addForeignKey($name, $table, $column, $refTable, $refColumn, $delete, $update);
        } catch (\Throwable $e) {
            // foreign key may already exist from a partial migration run
        }
    }

    private function dropIndexIfExists(string $name, string $table): void
    {
        try {
            $this->dropIndex($name, $table);
        } catch (\Throwable $e) {
        }
    }

    private function dropForeignKeyIfExists(string $name, string $table): void
    {
        try {
            $this->dropForeignKey($name, $table);
        } catch (\Throwable $e) {
        }
    }

    private function dropTableIfExists(string $tableName): void
    {
        if ($this->db->getSchema()->getTableSchema($tableName, true) !== null) {
            $this->dropTable($tableName);
        }
    }
}
