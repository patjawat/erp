<?php

use yii\db\Migration;

final class m260726_000009_normalize_housing_request_numbers extends Migration
{
    public function safeUp(): void
    {
        $rows = $this->db->createCommand(
            "SELECT id, request_no, COALESCE(requested_at, created_at, NOW()) AS request_date
             FROM {{%housing_request}}
             ORDER BY request_date, id"
        )->queryAll();

        $legacyRows = array_filter($rows, static fn(array $row): bool => !str_starts_with((string)$row['request_no'], 'HOM-'));
        foreach ($legacyRows as $row) {
            $this->update('{{%housing_request}}', ['request_no' => 'TMP-HOM-' . $row['id']], ['id' => $row['id']]);
        }

        $sequences = [];
        foreach ($rows as $row) {
            if (str_starts_with((string)$row['request_no'], 'HOM-')) {
                if (preg_match('/^HOM-(\d{2})-(\d{4})$/', (string)$row['request_no'], $matches)) {
                    $sequences[$matches[1]] = max($sequences[$matches[1]] ?? 0, (int)$matches[2]);
                }
                continue;
            }
            $year = substr((string)((int)date('Y', strtotime((string)$row['request_date'])) + 543), -2);
            $sequence = ($sequences[$year] ?? 0) + 1;
            $sequences[$year] = $sequence;
            $this->update('{{%housing_request}}', [
                'request_no' => 'HOM-' . $year . '-' . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT),
            ], ['id' => $row['id']]);
        }
    }

    public function safeDown(): void
    {
        echo "m260726_000009_normalize_housing_request_numbers cannot restore legacy random request numbers.\n";
    }
}
