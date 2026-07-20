<?php

use yii\db\Migration;
use yii\db\Query;
use yii\helpers\Json;

/**
 * Repair existing `employee_type` and `employee_position` rows so `data_json`
 * is stored as a real JSON object, not a quoted JSON string, and backfill
 * `employees.employee_type_id` from the latest `employee_detail` position row.
 */
class m260529_090000_fix_employee_type_legacy_codes_json extends Migration
{
    public function safeUp()
    {
        if ($this->db->getTableSchema('{{%employee_type}}', true) === null) {
            return;
        }

        $rows = [
            [1, 'ข้าราชการ', 1, ['PT1']],
            [2, 'พนักงานราชการ', 2, ['PT2']],
            [3, 'พนักงานกระทรวง (พกส.)', 3, ['PT3']],
            [4, 'ลูกจ้างชั่วคราวรายเดือน', 4, ['PT4']],
            [5, 'ลูกจ้างชั่วคราวรายวัน', 5, ['PT5']],
            [6, 'ลูกจ้างประจำ', 6, ['PT6']],
        ];

        foreach ($rows as [$id, $title, $sort, $legacyCodes]) {
            $this->upsert(
                '{{%employee_type}}',
                [
                    'id' => $id,
                    'title' => $title,
                    'sort' => $sort,
                    'active' => 1,
                    'data_json' => ['legacy_codes' => $legacyCodes],
                ],
                [
                    'title' => $title,
                    'sort' => $sort,
                    'active' => 1,
                    'data_json' => ['legacy_codes' => $legacyCodes],
                ]
            );
        }

        $this->repairEmployeePositions();
        $this->backfillEmployeesFromLatestPositionDetail();
    }

    public function safeDown()
    {
        if ($this->db->getTableSchema('{{%employee_type}}', true) !== null) {
            $rows = (new Query())
                ->from('{{%employee_type}}')
                ->select(['id', 'data_json'])
                ->where(['id' => [1, 2, 3, 4, 5, 6]])
                ->orderBy(['id' => SORT_ASC])
                ->all();

            foreach ($rows as $row) {
                $rowId = (int) ($row['id'] ?? 0);
                if ($rowId <= 0) {
                    continue;
                }

                $encoded = $this->encodeJsonValue($row['data_json'] ?? null);
                if ($encoded !== null) {
                    $this->update('{{%employee_type}}', ['data_json' => $encoded], ['id' => $rowId]);
                }
            }
        }

        if ($this->db->getTableSchema('{{%employee_position}}', true) !== null) {
            $rows = (new Query())
                ->from('{{%employee_position}}')
                ->select(['id', 'data_json'])
                ->orderBy(['id' => SORT_ASC])
                ->all();

            foreach ($rows as $row) {
                $rowId = (int) ($row['id'] ?? 0);
                if ($rowId <= 0) {
                    continue;
                }

                $encoded = $this->encodeJsonValue($row['data_json'] ?? null);
                if ($encoded !== null) {
                    $this->update('{{%employee_position}}', ['data_json' => $encoded], ['id' => $rowId]);
                }
            }
        }

        return true;
    }

    private function repairEmployeePositions(): void
    {
        $positionSchema = $this->db->getTableSchema('{{%employee_position}}', true);
        if ($positionSchema === null) {
            return;
        }

        $rows = (new Query())
            ->from('{{%employee_position}}')
            ->select(['id', 'data_json'])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        foreach ($rows as $row) {
            $rowId = (int) ($row['id'] ?? 0);
            if ($rowId <= 0) {
                continue;
            }

            $original = $row['data_json'] ?? null;
            $normalized = $this->normalizeJsonValue($original);
            if ($normalized !== $original) {
                $this->update('{{%employee_position}}', ['data_json' => $normalized], ['id' => $rowId]);
            }
        }
    }

    private function backfillEmployeesFromLatestPositionDetail(): void
    {
        $employeesSchema = $this->db->getTableSchema('{{%employees}}', true);
        $detailSchema = $this->db->getTableSchema('{{%employee_detail}}', true);
        $employeeTypeSchema = $this->db->getTableSchema('{{%employee_type}}', true);

        if ($employeesSchema === null || $detailSchema === null || $employeeTypeSchema === null) {
            return;
        }

        if (
            !isset($employeesSchema->columns['employee_type_id'], $detailSchema->columns['id'], $detailSchema->columns['emp_id'], $detailSchema->columns['name'], $detailSchema->columns['data_json'], $employeeTypeSchema->columns['id'], $employeeTypeSchema->columns['data_json'])
        ) {
            return;
        }

        $employeesTable = $this->db->quoteTableName($this->db->schema->getRawTableName('{{%employees}}'));
        $employeeDetailTable = $this->db->quoteTableName($this->db->schema->getRawTableName('{{%employee_detail}}'));
        $employeeTypeTable = $this->db->quoteTableName($this->db->schema->getRawTableName('{{%employee_type}}'));

        // Match employees to the latest "position" detail row and map by legacy codes.
        $sql = <<<SQL
UPDATE {$employeesTable} e
LEFT JOIN {$employeeDetailTable} p ON p.id = (
    SELECT p2.id
    FROM {$employeeDetailTable} p2
    WHERE p2.emp_id = e.id
      AND p2.name = 'position'
    ORDER BY JSON_UNQUOTE(JSON_EXTRACT(p2.data_json, '$.date_start')) DESC
    LIMIT 1
)
JOIN {$employeeTypeTable} new_t
    ON JSON_CONTAINS(
        JSON_EXTRACT(new_t.data_json, '$.legacy_codes'),
        JSON_QUOTE(JSON_UNQUOTE(JSON_EXTRACT(p.data_json, '$.position_type')))
    )
SET e.employee_type_id = new_t.id
WHERE e.employee_type_id <> new_t.id
SQL;

        $this->execute($sql);
    }

    private function normalizeJsonValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            return (array) $value;
        }

        if (is_string($value)) {
            $decoded = $value;
            for ($i = 0; $i < 3 && is_string($decoded); $i++) {
                $next = json_decode($decoded, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    break;
                }
                $decoded = $next;
            }

            if (is_array($decoded)) {
                return $decoded;
            }

            if (is_object($decoded)) {
                return (array) $decoded;
            }
        }

        return $value;
    }

    private function encodeJsonValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        if (is_array($value)) {
            return Json::encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }
}
