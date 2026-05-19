<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * ทำความสะอาด master ข้อมูลพนักงานให้กลุ่มตำแหน่งเป็น master อิสระ
 * - รวมกลุ่มตำแหน่งที่ชื่อซ้ำกันให้เหลือรายการเดียว
 * - รวมตำแหน่งที่ชื่อซ้ำกันภายใต้กลุ่มเดียวกัน
 * - ย้าย FK ของกลุ่ม/ตำแหน่ง/พนักงานไปยังรายการ canonical
 * - ตัด employee_type_id ออกจาก employee_position_group
 */
class m260519_140000_cleanup_employee_master_duplicates extends Migration
{
    public function safeUp()
    {
        $this->dedupeEmployeePositionGroups();
        $this->dedupeEmployeePositions();

        $this->dropForeignKeyIfExists('fk-employee_position_group-employee_type_id', '{{%employee_position_group}}');
        $this->dropIndexIfExists('ux-employee_position_group-type-title', '{{%employee_position_group}}');
        $this->dropIndexIfExists('idx-employee_position_group-employee_type_id', '{{%employee_position_group}}');
        $this->dropColumnIfExists('{{%employee_position_group}}', 'employee_type_id');

        $this->ensureIndex(
            'ux-employee_position_group-title',
            '{{%employee_position_group}}',
            'title',
            true
        );

        $this->ensureIndex(
            'ux-employee_position-group-title',
            '{{%employee_position}}',
            ['employee_position_group_id', 'title'],
            true
        );
    }

    public function safeDown()
    {
        return false;
    }

    private function dedupeEmployeePositionGroups(): void
    {
        $groupSchema = $this->db->getTableSchema('{{%employee_position_group}}', true);
        $positionSchema = $this->db->getTableSchema('{{%employee_position}}', true);
        $employeeSchema = $this->db->getTableSchema('{{%employees}}', true);

        if ($groupSchema === null || $positionSchema === null || $employeeSchema === null) {
            return;
        }

        $rows = (new Query())
            ->from('{{%employee_position_group}}')
            ->select(['id', 'title', 'sort', 'active'])
            ->orderBy([
                'title' => SORT_ASC,
                'active' => SORT_DESC,
                'sort' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->all();

        $canonicalByTitle = [];

        foreach ($rows as $row) {
            $normalizedTitle = $this->normalizeTitle($row['title'] ?? '');
            $titleKey = $this->normalizeTitleKey($row['title'] ?? '');
            if ($titleKey === '') {
                continue;
            }

            $rowId = (int) ($row['id'] ?? 0);

            if (!isset($canonicalByTitle[$titleKey])) {
                $canonicalByTitle[$titleKey] = [
                    'id' => $rowId,
                    'title' => $normalizedTitle,
                ];

                if ($normalizedTitle !== (string) ($row['title'] ?? '')) {
                    $this->update('{{%employee_position_group}}', ['title' => $normalizedTitle], ['id' => $rowId]);
                }
                continue;
            }

            $canonical = $canonicalByTitle[$titleKey];

            $this->update('{{%employee_position}}', [
                'employee_position_group_id' => $canonical['id'],
            ], ['employee_position_group_id' => $rowId]);

            $this->update('{{%employees}}', [
                'employee_position_group_id' => $canonical['id'],
            ], ['employee_position_group_id' => $rowId]);

            $this->delete('{{%employee_position_group}}', ['id' => $rowId]);
        }
    }

    private function dedupeEmployeePositions(): void
    {
        $positionSchema = $this->db->getTableSchema('{{%employee_position}}', true);
        $employeeSchema = $this->db->getTableSchema('{{%employees}}', true);

        if ($positionSchema === null || $employeeSchema === null) {
            return;
        }

        $rows = (new Query())
            ->from('{{%employee_position}}')
            ->select(['id', 'employee_type_id', 'employee_position_group_id', 'title', 'sort', 'active'])
            ->orderBy([
                'employee_position_group_id' => SORT_ASC,
                'title' => SORT_ASC,
                'active' => SORT_DESC,
                'sort' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->all();

        $canonicalByKey = [];

        foreach ($rows as $row) {
            $key = $this->buildPositionKey($row['employee_position_group_id'] ?? null, $row['title'] ?? null);
            if ($key === null) {
                continue;
            }

            $normalizedTitle = $this->normalizeTitle($row['title'] ?? '');
            $rowId = (int) ($row['id'] ?? 0);
            $groupId = (int) ($row['employee_position_group_id'] ?? 0);
            $typeId = (int) ($row['employee_type_id'] ?? 0);

            if (!isset($canonicalByKey[$key])) {
                $canonicalByKey[$key] = [
                    'id' => $rowId,
                    'employee_type_id' => $typeId,
                    'employee_position_group_id' => $groupId,
                    'title' => $normalizedTitle,
                ];

                if ($normalizedTitle !== (string) ($row['title'] ?? '')) {
                    $this->update('{{%employee_position}}', ['title' => $normalizedTitle], ['id' => $rowId]);
                }
                continue;
            }

            $canonical = $canonicalByKey[$key];

            $this->update('{{%employees}}', [
                'employee_type_id' => $canonical['employee_type_id'],
                'employee_position_group_id' => $canonical['employee_position_group_id'],
                'employee_position_id' => $canonical['id'],
            ], ['employee_position_id' => $rowId]);

            $this->delete('{{%employee_position}}', ['id' => $rowId]);
        }
    }

    private function buildPositionKey($groupId, $title): ?string
    {
        $groupId = (int) $groupId;
        $title = $this->normalizeTitleKey($title);

        if ($groupId <= 0 || $title === '') {
            return null;
        }

        return $groupId . '|' . $title;
    }

    private function normalizeTitle($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/\s+/u', ' ', $value);

        return $value === null ? '' : $value;
    }

    private function normalizeTitleKey($value): string
    {
        $value = $this->normalizeTitle($value);
        if ($value === '') {
            return '';
        }

        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }

    private function ensureIndex(string $indexName, string $tableName, $columns, bool $unique = false): void
    {
        if (!$this->tableExists($tableName)) {
            return;
        }

        if (!$this->indexExists($tableName, $indexName)) {
            $this->createIndex($indexName, $tableName, $columns, $unique);
        }
    }

    private function dropIndexIfExists(string $indexName, string $tableName): void
    {
        if ($this->tableExists($tableName) && $this->indexExists($tableName, $indexName)) {
            $this->dropIndex($indexName, $tableName);
        }
    }

    private function dropForeignKeyIfExists(string $fkName, string $tableName): void
    {
        if ($this->tableExists($tableName) && $this->foreignKeyExists($tableName, $fkName)) {
            $this->dropForeignKey($fkName, $tableName);
        }
    }

    private function dropColumnIfExists(string $tableName, string $columnName): void
    {
        $schema = $this->db->getTableSchema($tableName, true);
        if ($schema && isset($schema->columns[$columnName])) {
            $this->dropColumn($tableName, $columnName);
        }
    }

    private function tableExists(string $tableName): bool
    {
        return $this->db->getTableSchema($tableName, true) !== null;
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
}
