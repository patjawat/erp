<?php

declare(strict_types=1);

use yii\db\Migration;
use yii\db\Query;

class m260722_000002_seed_ai_default_datasets extends Migration
{
    public function safeUp(): void
    {
        $definitions = require dirname(__DIR__) . '/datasets/default.php';
        $now = date('Y-m-d H:i:s');

        foreach ($definitions as $code => $definition) {
            $datasetId = (new Query())
                ->select('id')
                ->from('{{%ai_datasets}}')
                ->where(['code' => $code])
                ->scalar($this->db);

            $data = [
                'code' => $code,
                'name' => $definition['name'],
                'description' => $definition['description'] ?? null,
                'view_name' => $definition['view_name'],
                'permission_name' => $definition['permission_name'],
                'max_rows' => $definition['max_rows'] ?? 100,
                'is_exportable' => (int) ($definition['is_exportable'] ?? true),
                'is_active' => 1,
                'metadata_json' => $this->encodeJsonValue($definition['metadata'] ?? []),
                'updated_at' => $now,
            ];

            if ($datasetId === false) {
                $datasetId = $this->uuid();
                $this->insert('{{%ai_datasets}}', array_merge($data, [
                    'id' => $datasetId,
                    'created_at' => $now,
                ]));
            } else {
                $this->update('{{%ai_datasets}}', $data, ['id' => $datasetId]);
                $this->delete('{{%ai_dataset_fields}}', ['dataset_id' => $datasetId]);
            }

            $sortOrder = 10;
            foreach (($definition['fields'] ?? []) as $field) {
                $this->insert('{{%ai_dataset_fields}}', [
                    'id' => $this->uuid(),
                    'dataset_id' => $datasetId,
                    'field_name' => $field['field_name'],
                    'label' => $field['label'] ?? $field['field_name'],
                    'data_type' => $field['data_type'] ?? 'string',
                    'is_filterable' => (int) ($field['is_filterable'] ?? false),
                    'is_sortable' => (int) ($field['is_sortable'] ?? false),
                    'is_selectable' => (int) ($field['is_selectable'] ?? true),
                    'allowed_operators' => isset($field['allowed_operators']) ? $this->encodeJsonValue($field['allowed_operators']) : null,
                    'sort_order' => $sortOrder,
                    'created_at' => $now,
                ]);
                $sortOrder += 10;
            }
        }
    }

    public function safeDown(): void
    {
        $definitions = require dirname(__DIR__) . '/datasets/default.php';
        $this->delete('{{%ai_datasets}}', ['code' => array_keys($definitions)]);
    }

    private function encodeJsonValue(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
    }

    private function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
