<?php

declare(strict_types=1);

namespace app\modules\ai\datasets;

use app\modules\ai\models\AiDataset;

final class DatasetDefinition
{
    /**
     * @param array<string, array<string, mixed>> $fields
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $viewName,
        public readonly string $permissionName,
        public readonly array $fields,
        public readonly ?string $description = null,
        public readonly int $maxRows = 100,
        public readonly bool $isExportable = true,
        public readonly array $metadata = []
    ) {
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function fromArray(string $code, array $config): self
    {
        $fields = [];
        foreach (($config['fields'] ?? []) as $field) {
            $fieldName = (string) ($field['field_name'] ?? $field['name'] ?? '');
            if ($fieldName === '') {
                continue;
            }

            $fields[$fieldName] = array_merge($field, ['field_name' => $fieldName]);
        }

        return new self(
            $code,
            (string) ($config['name'] ?? $code),
            (string) ($config['view_name'] ?? ''),
            (string) ($config['permission_name'] ?? ''),
            $fields,
            $config['description'] ?? null,
            (int) ($config['max_rows'] ?? 100),
            (bool) ($config['is_exportable'] ?? true),
            (array) ($config['metadata'] ?? [])
        );
    }

    public static function fromModel(AiDataset $dataset): self
    {
        $fields = [];
        foreach ($dataset->fields as $field) {
            $fields[$field->field_name] = [
                'field_name' => $field->field_name,
                'label' => $field->label,
                'data_type' => $field->data_type,
                'is_filterable' => (bool) $field->is_filterable,
                'is_sortable' => (bool) $field->is_sortable,
                'is_selectable' => (bool) $field->is_selectable,
                'allowed_operators' => $field->getAllowedOperatorList(),
                'sort_order' => (int) $field->sort_order,
            ];
        }

        return new self(
            $dataset->code,
            $dataset->name,
            $dataset->view_name,
            $dataset->permission_name,
            $fields,
            $dataset->description,
            (int) $dataset->max_rows,
            (bool) $dataset->is_exportable,
            $dataset->getMetadata()
        );
    }

    /**
     * @return array<int, string>
     */
    public function selectableFields(): array
    {
        return array_values(array_keys(array_filter(
            $this->fields,
            static fn (array $field): bool => (bool) ($field['is_selectable'] ?? true)
        )));
    }

    public function hasField(string $field): bool
    {
        return isset($this->fields[$field]);
    }

    public function isFilterable(string $field): bool
    {
        return $this->hasField($field) && (bool) ($this->fields[$field]['is_filterable'] ?? false);
    }

    public function isSortable(string $field): bool
    {
        return $this->hasField($field) && (bool) ($this->fields[$field]['is_sortable'] ?? false);
    }

    /**
     * @return array<int, string>
     */
    public function allowedOperators(string $field): array
    {
        if (!$this->hasField($field)) {
            return [];
        }

        $operators = $this->fields[$field]['allowed_operators'] ?? ['=', '!='];
        if (is_string($operators)) {
            $operators = json_decode($operators, true) ?: explode(',', $operators);
        }

        return array_values(array_filter(array_map(static fn ($operator): string => trim((string) $operator), (array) $operators)));
    }

    public function labelFor(string $field): string
    {
        return (string) ($this->fields[$field]['label'] ?? $field);
    }
}
