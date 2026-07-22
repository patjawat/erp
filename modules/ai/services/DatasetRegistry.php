<?php

declare(strict_types=1);

namespace app\modules\ai\services;

use app\modules\ai\datasets\DatasetDefinition;
use app\modules\ai\models\AiDataset;
use InvalidArgumentException;
use Throwable;

class DatasetRegistry
{
    /**
     * @var array<string, DatasetDefinition>|null
     */
    private ?array $cache = null;

    public function get(string $code): DatasetDefinition
    {
        $datasets = $this->all();
        if (!isset($datasets[$code])) {
            throw new InvalidArgumentException("AI dataset '{$code}' is not registered.");
        }

        return $datasets[$code];
    }

    /**
     * @return array<string, DatasetDefinition>
     */
    public function all(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $datasets = $this->loadFromDatabase();
        if ($datasets === []) {
            $datasets = $this->loadFromFile();
        }

        $this->cache = $datasets;
        return $datasets;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function asPromptCatalog(): array
    {
        $catalog = [];
        foreach ($this->all() as $dataset) {
            $fields = [];
            foreach ($dataset->fields as $field) {
                if ((bool) ($field['is_selectable'] ?? true)) {
                    $fields[] = [
                        'name' => $field['field_name'],
                        'label' => $field['label'] ?? $field['field_name'],
                        'type' => $field['data_type'] ?? 'string',
                        'filterable' => (bool) ($field['is_filterable'] ?? false),
                    ];
                }
            }

            $catalog[] = [
                'code' => $dataset->code,
                'name' => $dataset->name,
                'description' => $dataset->description,
                'fields' => $fields,
                'max_rows' => $dataset->maxRows,
                'exportable' => $dataset->isExportable,
            ];
        }

        return $catalog;
    }

    /**
     * @return array<string, DatasetDefinition>
     */
    private function loadFromDatabase(): array
    {
        try {
            if (AiDataset::getTableSchema() === null) {
                return [];
            }

            $models = AiDataset::find()
                ->with('fields')
                ->where(['is_active' => 1])
                ->orderBy(['code' => SORT_ASC])
                ->all();
        } catch (Throwable) {
            return [];
        }

        $datasets = [];
        foreach ($models as $model) {
            $datasets[$model->code] = DatasetDefinition::fromModel($model);
        }

        return $datasets;
    }

    /**
     * @return array<string, DatasetDefinition>
     */
    private function loadFromFile(): array
    {
        $config = require __DIR__ . '/../datasets/default.php';
        $datasets = [];
        foreach ($config as $code => $definition) {
            $datasets[$code] = DatasetDefinition::fromArray((string) $code, $definition);
        }

        return $datasets;
    }
}
