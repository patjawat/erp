<?php

use yii\db\Migration;
use yii\db\Query;
use yii\helpers\Json;

/**
 * สร้าง master table ใหม่สำหรับประเภทพนักงาน/กลุ่มตำแหน่ง/ตำแหน่ง
 * และเพิ่มคอลัมน์ map ข้อมูลใหม่ในตาราง employees
 */
class m260518_160000_create_employee_master_tables extends Migration
{
    /**
     * map จาก position_type เดิม ไป employee_type ใหม่
     * employee_type ใหม่รวม PT4-PT7 เดิมไว้ในกลุ่มลูกจ้างชั่วคราว
     */
    private $legacyTypeMap = [
        'PT1' => 1,
        'PT2' => 2,
        'PT3' => 3,
        'PT4' => 4,
        'PT5' => 4,
        'PT6' => 4,
        'PT7' => 4,
        '1' => 1,
        '2' => 2,
        '3' => 3,
        '4' => 4,
        '5' => 4,
        '6' => 4,
        '7' => 4,
    ];

    public function safeUp()
    {
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->ensureTable('{{%employee_type}}', [
            'id' => $this->primaryKey()->comment('รหัสประเภทพนักงาน (ใหม่)'),
            'title' => $this->string(255)->notNull()->comment('ประเภทพนักงาน (ใหม่)'),
            'sort' => $this->integer()->notNull()->defaultValue(0)->comment('ลำดับแสดงผล'),
            'active' => $this->boolean()->notNull()->defaultValue(true)->comment('สถานะใช้งาน'),
            'data_json' => $this->json()->null()->comment('ข้อมูลเพิ่มเติม/รหัสเดิม'),
        ], $tableOptions);

        $this->ensureTable('{{%employee_position_group}}', [
            'id' => $this->primaryKey()->comment('รหัสกลุ่มตำแหน่งพนักงาน (ใหม่)'),
            'employee_type_id' => $this->integer()->notNull()->comment('ประเภทพนักงาน (ใหม่)'),
            'legacy_code' => $this->string(30)->null()->comment('รหัสเดิมจาก categorise'),
            'title' => $this->string(255)->notNull()->comment('กลุ่มตำแหน่งพนักงาน (ใหม่)'),
            'sort' => $this->integer()->notNull()->defaultValue(0)->comment('ลำดับแสดงผล'),
            'active' => $this->boolean()->notNull()->defaultValue(true)->comment('สถานะใช้งาน'),
            'data_json' => $this->json()->null()->comment('ข้อมูลเพิ่มเติม'),
        ], $tableOptions);

        $this->ensureTable('{{%employee_position}}', [
            'id' => $this->primaryKey()->comment('รหัสตำแหน่งพนักงาน (ใหม่)'),
            'employee_type_id' => $this->integer()->notNull()->comment('ประเภทพนักงาน (ใหม่)'),
            'employee_position_group_id' => $this->integer()->notNull()->comment('กลุ่มตำแหน่งพนักงาน (ใหม่)'),
            'legacy_code' => $this->string(50)->null()->comment('รหัสเดิมจาก categorise'),
            'title' => $this->string(255)->notNull()->comment('ตำแหน่งพนักงาน (ใหม่)'),
            'sort' => $this->integer()->notNull()->defaultValue(0)->comment('ลำดับแสดงผล'),
            'active' => $this->boolean()->notNull()->defaultValue(true)->comment('สถานะใช้งาน'),
            'data_json' => $this->json()->null()->comment('ข้อมูลเพิ่มเติม'),
        ], $tableOptions);

        $this->ensureIndex('ux-employee_position_group-legacy_code', '{{%employee_position_group}}', 'legacy_code', true);
        $this->ensureIndex('ux-employee_position-legacy_code', '{{%employee_position}}', 'legacy_code', true);
        $this->ensureIndex('idx-employee_position_group-employee_type_id', '{{%employee_position_group}}', 'employee_type_id');
        $this->ensureIndex('idx-employee_position-employee_type_id', '{{%employee_position}}', 'employee_type_id');
        $this->ensureIndex('idx-employee_position-employee_position_group_id', '{{%employee_position}}', 'employee_position_group_id');

        $schema = $this->db->getTableSchema('{{%employees}}', true);
        $this->ensureColumn(
            '{{%employees}}',
            'employee_type_id',
            $this->integer()->null()->comment('ประเภทพนักงาน (ใหม่)')->after('position_type')
        );
        $this->ensureColumn(
            '{{%employees}}',
            'employee_position_group_id',
            $this->integer()->null()->comment('กลุ่มตำแหน่งพนักงาน (ใหม่)')->after('employee_type_id')
        );
        $this->ensureColumn(
            '{{%employees}}',
            'employee_position_id',
            $this->integer()->null()->comment('ตำแหน่งพนักงาน (ใหม่)')->after('employee_position_group_id')
        );

        $this->ensureIndex('idx-employees-employee_type_id', '{{%employees}}', 'employee_type_id');
        $this->ensureIndex('idx-employees-employee_position_group_id', '{{%employees}}', 'employee_position_group_id');
        $this->ensureIndex('idx-employees-employee_position_id', '{{%employees}}', 'employee_position_id');

        $this->ensureForeignKey(
            'fk-employee_position_group-employee_type_id',
            '{{%employee_position_group}}',
            'employee_type_id',
            '{{%employee_type}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->ensureForeignKey(
            'fk-employee_position-employee_type_id',
            '{{%employee_position}}',
            'employee_type_id',
            '{{%employee_type}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->ensureForeignKey(
            'fk-employee_position-employee_position_group_id',
            '{{%employee_position}}',
            'employee_position_group_id',
            '{{%employee_position_group}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->ensureForeignKey(
            'fk-employees-employee_type_id',
            '{{%employees}}',
            'employee_type_id',
            '{{%employee_type}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->ensureForeignKey(
            'fk-employees-employee_position_group_id',
            '{{%employees}}',
            'employee_position_group_id',
            '{{%employee_position_group}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->ensureForeignKey(
            'fk-employees-employee_position_id',
            '{{%employees}}',
            'employee_position_id',
            '{{%employee_position}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->seedEmployeeTypes();
        [$groupMap, $groupMeta] = $this->seedEmployeePositionGroups();
        [$positionMap, $positionMeta] = $this->seedEmployeePositions($groupMap, $groupMeta);
        $this->backfillEmployees($groupMap, $groupMeta, $positionMap, $positionMeta);
    }

    public function safeDown()
    {
        $schema = $this->db->getTableSchema('{{%employees}}', true);
        if ($schema && isset($schema->columns['employee_position_id'])) {
            $this->dropForeignKey('fk-employees-employee_position_id', '{{%employees}}');
            $this->dropForeignKey('fk-employees-employee_position_group_id', '{{%employees}}');
            $this->dropForeignKey('fk-employees-employee_type_id', '{{%employees}}');
            $this->dropIndex('idx-employees-employee_position_id', '{{%employees}}');
            $this->dropIndex('idx-employees-employee_position_group_id', '{{%employees}}');
            $this->dropIndex('idx-employees-employee_type_id', '{{%employees}}');
            $this->dropColumn('{{%employees}}', 'employee_position_id');
            $this->dropColumn('{{%employees}}', 'employee_position_group_id');
            $this->dropColumn('{{%employees}}', 'employee_type_id');
        }

        $this->dropForeignKey('fk-employee_position-employee_position_group_id', '{{%employee_position}}');
        $this->dropForeignKey('fk-employee_position-employee_type_id', '{{%employee_position}}');
        $this->dropForeignKey('fk-employee_position_group-employee_type_id', '{{%employee_position_group}}');

        $this->dropIndex('ux-employee_position-legacy_code', '{{%employee_position}}');
        $this->dropIndex('ux-employee_position_group-legacy_code', '{{%employee_position_group}}');
        $this->dropIndex('idx-employee_position-employee_position_group_id', '{{%employee_position}}');
        $this->dropIndex('idx-employee_position-employee_type_id', '{{%employee_position}}');
        $this->dropIndex('idx-employee_position_group-employee_type_id', '{{%employee_position_group}}');

        $this->dropTable('{{%employee_position}}');
        $this->dropTable('{{%employee_position_group}}');
        $this->dropTable('{{%employee_type}}');
    }

    private function seedEmployeeTypes(): void
    {
        $rows = [
            [1, 'ข้าราชการ', 1, ['legacy_codes' => ['PT1']]],
            [2, 'พนักงานราชการ', 2, ['legacy_codes' => ['PT2']]],
            [3, 'พนักงานกระทรวง (พกส.)', 3, ['legacy_codes' => ['PT3']]],
            [4, 'ลูกจ้างชั่วคราว', 4, ['legacy_codes' => ['PT4', 'PT5', 'PT6', 'PT7']]],
        ];

        foreach ($rows as [$id, $title, $sort, $data]) {
            $this->upsert('{{%employee_type}}', [
                'id' => $id,
                'title' => $title,
                'sort' => $sort,
                'active' => 1,
                'data_json' => Json::encode($data, JSON_UNESCAPED_UNICODE),
            ], [
                'title' => $title,
                'sort' => $sort,
                'active' => 1,
                'data_json' => Json::encode($data, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    /**
     * @return array{0: array<string,int>, 1: array<int,array<string,mixed>>}
     */
    private function seedEmployeePositionGroups(): array
    {
        $rows = (new Query())
            ->from('{{%categorise}}')
            ->select(['code', 'category_id', 'title', 'data_json', 'sort', 'active'])
            ->where(['name' => 'position_group'])
            ->andWhere(['<>', 'code', '-'])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        $groupMap = [];
        $groupMeta = [];
        $groupId = $this->getNextPrimaryId('{{%employee_position_group}}');
        $seenLegacyCodes = [];

        foreach ((new Query())
            ->from('{{%employee_position_group}}')
            ->select(['id', 'employee_type_id', 'legacy_code', 'title'])
            ->orderBy(['id' => SORT_ASC])
            ->all() as $existingRow) {
            $legacyCode = $this->normalizeLegacyCode($existingRow['legacy_code'] ?? null);
            if ($legacyCode === null) {
                continue;
            }

            $groupMap[$legacyCode] = (int) $existingRow['id'];
            $groupMeta[(int) $existingRow['id']] = [
                'employee_type_id' => (int) ($existingRow['employee_type_id'] ?? 0),
                'legacy_code' => $legacyCode,
                'title' => $existingRow['title'] ?? '',
            ];
        }

        foreach ($rows as $row) {
            $legacyCode = $this->normalizeLegacyCode($row['code'] ?? null);
            if ($legacyCode === null || isset($seenLegacyCodes[$legacyCode])) {
                continue;
            }

            $typeId = $this->legacyTypeMap[$row['category_id'] ?? ''] ?? null;
            if ($typeId === null) {
                continue;
            }

            $payload = [
                'employee_type_id' => $typeId,
                'legacy_code' => $legacyCode,
                'title' => $row['title'] ?? '',
                'sort' => (int) ($row['sort'] ?? 0),
                'active' => (int) ($row['active'] ?? 1),
                'data_json' => $this->normalizeJsonValue($row['data_json'] ?? null),
            ];

            if (isset($groupMap[$legacyCode])) {
                $groupIdExisting = $groupMap[$legacyCode];
                $this->update('{{%employee_position_group}}', $payload, ['id' => $groupIdExisting]);
                $groupMeta[$groupIdExisting] = [
                    'employee_type_id' => $typeId,
                    'legacy_code' => $legacyCode,
                    'title' => $row['title'] ?? '',
                ];
                $seenLegacyCodes[$legacyCode] = true;
                continue;
            }

            $this->insert('{{%employee_position_group}}', array_merge(['id' => $groupId], $payload));

            $seenLegacyCodes[$legacyCode] = true;
            $groupMap[$legacyCode] = $groupId;
            $groupMeta[$groupId] = [
                'employee_type_id' => $typeId,
                'legacy_code' => $legacyCode,
                'title' => $row['title'] ?? '',
            ];
            ++$groupId;
        }

        return [$groupMap, $groupMeta];
    }

    /**
     * @param array<string,int> $groupMap
     * @param array<int,array<string,mixed>> $groupMeta
     * @return array{0: array<string,int>, 1: array<int,array<string,mixed>>}
     */
    private function seedEmployeePositions(array $groupMap, array $groupMeta): array
    {
        $rows = (new Query())
            ->from('{{%categorise}}')
            ->select(['code', 'category_id', 'title', 'data_json', 'sort', 'active'])
            ->where(['name' => 'position_name'])
            ->andWhere(['<>', 'code', '-'])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        $positionMap = [];
        $positionMeta = [];
        $positionId = $this->getNextPrimaryId('{{%employee_position}}');
        $seenLegacyCodes = [];

        foreach ((new Query())
            ->from('{{%employee_position}}')
            ->select(['id', 'employee_type_id', 'employee_position_group_id', 'legacy_code', 'title'])
            ->orderBy(['id' => SORT_ASC])
            ->all() as $existingRow) {
            $legacyCode = $this->normalizeLegacyCode($existingRow['legacy_code'] ?? null);
            if ($legacyCode === null) {
                continue;
            }

            $positionMap[$legacyCode] = (int) $existingRow['id'];
            $positionMeta[(int) $existingRow['id']] = [
                'employee_type_id' => (int) ($existingRow['employee_type_id'] ?? 0),
                'employee_position_group_id' => (int) ($existingRow['employee_position_group_id'] ?? 0),
                'legacy_code' => $legacyCode,
                'title' => $existingRow['title'] ?? '',
            ];
        }

        foreach ($rows as $row) {
            $legacyCode = $this->normalizeLegacyCode($row['code'] ?? null);
            if ($legacyCode === null || isset($seenLegacyCodes[$legacyCode])) {
                continue;
            }

            $groupCode = $this->normalizeLegacyCode($row['category_id'] ?? null);
            $groupId = $groupCode !== null ? ($groupMap[$groupCode] ?? null) : null;
            if ($groupId === null) {
                continue;
            }

            $typeId = $groupMeta[$groupId]['employee_type_id'] ?? null;
            if ($typeId === null) {
                continue;
            }

            $payload = [
                'employee_type_id' => $typeId,
                'employee_position_group_id' => $groupId,
                'legacy_code' => $legacyCode,
                'title' => $row['title'] ?? '',
                'sort' => (int) ($row['sort'] ?? 0),
                'active' => (int) ($row['active'] ?? 1),
                'data_json' => $this->normalizeJsonValue($row['data_json'] ?? null),
            ];

            if (isset($positionMap[$legacyCode])) {
                $positionIdExisting = $positionMap[$legacyCode];
                $this->update('{{%employee_position}}', $payload, ['id' => $positionIdExisting]);
                $positionMeta[$positionIdExisting] = [
                    'employee_type_id' => $typeId,
                    'employee_position_group_id' => $groupId,
                    'legacy_code' => $legacyCode,
                    'title' => $row['title'] ?? '',
                ];
                $seenLegacyCodes[$legacyCode] = true;
                continue;
            }

            $this->insert('{{%employee_position}}', array_merge(['id' => $positionId], $payload));

            $seenLegacyCodes[$legacyCode] = true;
            $positionMap[$legacyCode] = $positionId;
            $positionMeta[$positionId] = [
                'employee_type_id' => $typeId,
                'employee_position_group_id' => $groupId,
                'legacy_code' => $legacyCode,
                'title' => $row['title'] ?? '',
            ];
            ++$positionId;
        }

        return [$positionMap, $positionMeta];
    }

    /**
     * @param array<string,int> $groupMap
     * @param array<int,array<string,mixed>> $groupMeta
     * @param array<string,int> $positionMap
     * @param array<int,array<string,mixed>> $positionMeta
     */
    private function backfillEmployees(array $groupMap, array $groupMeta, array $positionMap, array $positionMeta): void
    {
        $rows = (new Query())
            ->from('{{%employees}}')
            ->select(['id', 'position_type', 'position_group', 'position_name'])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        foreach ($rows as $row) {
            $update = [];

            $positionCode = $this->normalizeLegacyCode($row['position_name'] ?? null);
            if ($positionCode !== null && isset($positionMap[$positionCode])) {
                $positionId = $positionMap[$positionCode];
                $position = $positionMeta[$positionId] ?? null;
                $update['employee_position_id'] = $positionId;

                if ($position && isset($position['employee_position_group_id'])) {
                    $update['employee_position_group_id'] = $position['employee_position_group_id'];
                }
                if ($position && isset($position['employee_type_id'])) {
                    $update['employee_type_id'] = $position['employee_type_id'];
                }
            } else {
                $groupCode = $this->normalizeLegacyCode($row['position_group'] ?? null);
                if ($groupCode !== null && isset($groupMap[$groupCode])) {
                    $groupId = $groupMap[$groupCode];
                    $group = $groupMeta[$groupId] ?? null;
                    $update['employee_position_group_id'] = $groupId;

                    if ($group && isset($group['employee_type_id'])) {
                        $update['employee_type_id'] = $group['employee_type_id'];
                    }
                }
            }

            if (empty($update['employee_type_id']) && !empty($row['position_type'])) {
                $typeCode = $this->normalizeLegacyCode($row['position_type']);
                $typeId = $typeCode !== null ? ($this->legacyTypeMap[$typeCode] ?? null) : null;
                if ($typeId !== null) {
                    $update['employee_type_id'] = $typeId;
                }
            }

            if (!empty($update)) {
                $this->update('{{%employees}}', $update, ['id' => $row['id']]);
            }
        }
    }

    private function normalizeJsonValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return Json::encode($value, JSON_UNESCAPED_UNICODE);
        }

        return $value;
    }

    private function normalizeLegacyCode($value)
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '' || $value === '-') {
            return null;
        }

        return $value;
    }

    private function ensureTable(string $tableName, array $columns, ?string $tableOptions): void
    {
        if ($this->db->getTableSchema($tableName, true) === null) {
            $this->createTable($tableName, $columns, $tableOptions);
        }
    }

    private function ensureColumn(string $tableName, string $columnName, $definition): void
    {
        $schema = $this->db->getTableSchema($tableName, true);
        if ($schema && !isset($schema->columns[$columnName])) {
            $this->addColumn($tableName, $columnName, $definition);
        }
    }

    private function ensureIndex(string $indexName, string $tableName, $columns, bool $unique = false): void
    {
        if (!$this->indexExists($tableName, $indexName)) {
            $this->createIndex($indexName, $tableName, $columns, $unique);
        }
    }

    private function ensureForeignKey(
        string $fkName,
        string $tableName,
        $columns,
        string $refTable,
        $refColumns,
        ?string $delete = null,
        ?string $update = null
    ): void {
        if (!$this->foreignKeyExists($tableName, $fkName)) {
            $this->addForeignKey($fkName, $tableName, $columns, $refTable, $refColumns, $delete, $update);
        }
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $indexes = $this->db->getSchema()->getTableIndexes($tableName, true);
        foreach ($indexes as $key => $index) {
            if (is_string($key) && $key === $indexName) {
                return true;
            }

            $name = null;
            if (is_array($index)) {
                $name = $index['name'] ?? null;
            } elseif (is_object($index)) {
                $name = $index->name ?? null;
            }

            if ($name === $indexName) {
                return true;
            }
        }

        return false;
    }

    private function foreignKeyExists(string $tableName, string $fkName): bool
    {
        $foreignKeys = $this->db->getSchema()->getTableForeignKeys($tableName, true);
        foreach ($foreignKeys as $key => $foreignKey) {
            if (is_string($key) && $key === $fkName) {
                return true;
            }

            $name = null;
            if (is_array($foreignKey)) {
                $name = $foreignKey['name'] ?? null;
            } elseif (is_object($foreignKey)) {
                $name = $foreignKey->name ?? null;
            }

            if ($name === $fkName) {
                return true;
            }
        }

        return false;
    }

    private function getNextPrimaryId(string $tableName): int
    {
        $maxId = (new Query())
            ->from($tableName)
            ->max('id');

        return ((int) $maxId) + 1;
    }
}
