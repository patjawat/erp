<?php

declare(strict_types=1);

namespace app\modules\ai\services;

use app\modules\ai\datasets\DatasetDefinition;

final class QueryResult
{
    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, string> $fields
     * @param array<string, string> $labels
     */
    public function __construct(
        public readonly DatasetDefinition $dataset,
        public readonly array $rows,
        public readonly array $fields,
        public readonly array $labels,
        public readonly int $durationMs
    ) {
    }

    public function rowCount(): int
    {
        return count($this->rows);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(bool $includeRows = true): array
    {
        return [
            'dataset' => $this->dataset->code,
            'dataset_name' => $this->dataset->name,
            'fields' => $this->fields,
            'labels' => $this->labels,
            'row_count' => $this->rowCount(),
            'duration_ms' => $this->durationMs,
            'rows' => $includeRows ? $this->rows : [],
        ];
    }
}
