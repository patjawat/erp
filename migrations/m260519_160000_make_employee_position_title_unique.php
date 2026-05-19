<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * รวมตำแหน่งพนักงาน (ใหม่) ที่ชื่อซ้ำกัน และบังคับให้ชื่อไม่ซ้ำกันทั่วระบบ
 * โดยเลือก record หลักจากการใช้งานจริงก่อน เพื่อให้กลุ่มที่เหลือมีโอกาสถูกต้องมากที่สุด
 */
class m260519_160000_make_employee_position_title_unique extends Migration
{
    private array $employeePositionUsageCounts = [];

    public function safeUp()
    {
        if ($this->db->getTableSchema('{{%employee_position}}', true) === null) {
            return;
        }

        $this->dedupeEmployeePositions();

        $this->dropIndexIfExists('ux-employee_position-group-title', '{{%employee_position}}');
        $this->dropIndexIfExists('ux-employee_position-title', '{{%employee_position}}');
        $this->ensureIndex('ux-employee_position-title', '{{%employee_position}}', 'title', true);
    }

    public function safeDown()
    {
        return false;
    }

    private function dedupeEmployeePositions(): void
    {
        $hasEmployeesTable = $this->db->getTableSchema('{{%employees}}', true) !== null;
        $this->employeePositionUsageCounts = $hasEmployeesTable ? $this->loadEmployeePositionUsageCounts() : [];
        $rows = (new Query())
            ->from('{{%employee_position}}')
            ->select(['id', 'employee_type_id', 'employee_position_group_id', 'legacy_code', 'title', 'sort', 'active'])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        $grouped = [];
        foreach ($rows as $row) {
            $titleKey = $this->normalizeTitleKey($row['title'] ?? '');
            if ($titleKey === '') {
                continue;
            }

            $grouped[$titleKey][] = $row;
        }

        foreach ($grouped as $items) {
            if (count($items) < 2) {
                continue;
            }

            usort($items, [$this, 'compareEmployeePositionRows']);
            $canonical = array_shift($items);
            if (!$canonical) {
                continue;
            }

            $canonicalId = (int) ($canonical['id'] ?? 0);
            if ($canonicalId <= 0) {
                continue;
            }

            $normalizedTitle = $this->normalizeTitle($canonical['title'] ?? '');
            if ($normalizedTitle !== '' && $normalizedTitle !== (string) ($canonical['title'] ?? '')) {
                $this->update('{{%employee_position}}', ['title' => $normalizedTitle], ['id' => $canonicalId]);
                $canonical['title'] = $normalizedTitle;
            }

            foreach ($items as $duplicate) {
                $duplicateId = (int) ($duplicate['id'] ?? 0);
                if ($duplicateId <= 0) {
                    continue;
                }

                $update = [
                    'employee_position_id' => $canonicalId,
                    'employee_position_group_id' => $this->pickNullableInt(
                        $canonical['employee_position_group_id'] ?? null,
                        $duplicate['employee_position_group_id'] ?? null
                    ),
                    'employee_type_id' => $this->pickNullableInt(
                        $canonical['employee_type_id'] ?? null,
                        $duplicate['employee_type_id'] ?? null
                    ),
                ];

                if ($hasEmployeesTable) {
                    $this->update('{{%employees}}', $update, ['employee_position_id' => $duplicateId]);
                }
                $this->delete('{{%employee_position}}', ['id' => $duplicateId]);
            }
        }
    }

    private function compareEmployeePositionRows(array $left, array $right): int
    {
        $scoreDiff = $this->employeePositionScore($right) <=> $this->employeePositionScore($left);
        if ($scoreDiff !== 0) {
            return $scoreDiff;
        }

        $sortDiff = (int) ($left['sort'] ?? 0) <=> (int) ($right['sort'] ?? 0);
        if ($sortDiff !== 0) {
            return $sortDiff;
        }

        return (int) ($left['id'] ?? 0) <=> (int) ($right['id'] ?? 0);
    }

    private function employeePositionScore(array $row): int
    {
        $score = 0;
        $usageCount = (int) ($this->employeePositionUsageCounts[(string) ($row['id'] ?? 0)] ?? 0);

        if ($usageCount > 0) {
            // ใช้งานจริงมากกว่า ให้โอกาสเป็นข้อมูลหลักมากกว่า
            $score += $usageCount * 1000;
        }

        if ((int) ($row['active'] ?? 0) === 1) {
            $score += 8;
        }

        if ($this->normalizeLegacyCode($row['legacy_code'] ?? null) !== null) {
            $score += 4;
        }

        if ($this->pickNullableInt($row['employee_position_group_id'] ?? null, null) !== null) {
            $score += 2;
        }

        if ($this->pickNullableInt($row['employee_type_id'] ?? null, null) !== null) {
            $score += 1;
        }

        return $score;
    }

    private function loadEmployeePositionUsageCounts(): array
    {
        $counts = (new Query())
            ->from('{{%employees}}')
            ->select([
                'employee_position_id',
                'usage_count' => new \yii\db\Expression('COUNT(*)'),
            ])
            ->where(['is not', 'employee_position_id', null])
            ->groupBy(['employee_position_id'])
            ->all();

        $map = [];
        foreach ($counts as $row) {
            $positionId = (int) ($row['employee_position_id'] ?? 0);
            if ($positionId <= 0) {
                continue;
            }

            $map[(string) $positionId] = (int) ($row['usage_count'] ?? 0);
        }

        return $map;
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

    private function normalizeLegacyCode($value): ?string
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

    private function pickNullableInt($primary, $fallback): ?int
    {
        $primary = $this->normalizeNullableInt($primary);
        if ($primary !== null) {
            return $primary;
        }

        return $this->normalizeNullableInt($fallback);
    }

    private function normalizeNullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = (int) $value;
        return $value > 0 ? $value : null;
    }

    private function dropIndexIfExists(string $indexName, string $tableName): void
    {
        if ($this->tableExists($tableName) && $this->indexExists($tableName, $indexName)) {
            $this->dropIndex($indexName, $tableName);
        }
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
}
