<?php

declare(strict_types=1);

namespace app\modules\ai\tools;

use app\modules\ai\services\AiExportService;

class ExportExcelTool implements AiToolInterface
{
    public function __construct(private ?AiExportService $exportService = null)
    {
        $this->exportService = $exportService ?: new AiExportService();
    }

    public function getName(): string
    {
        return 'export_excel';
    }

    public function getDescription(): string
    {
        return 'Create an Excel file from a registered AI dataset. Yii2 performs permission, scope, query, and file generation.';
    }

    public function getJsonSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'dataset' => ['type' => 'string'],
                'fields' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'filters' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'field' => ['type' => 'string'],
                            'operator' => ['type' => 'string', 'enum' => ['=', '!=', '>', '>=', '<', '<=', 'like', 'in', 'between', 'is_null', 'is_not_null']],
                            'value' => [
                                'description' => 'Scalar, array, or two-value array for between.',
                            ],
                        ],
                        'required' => ['field', 'operator'],
                    ],
                ],
                'sort' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'field' => ['type' => 'string'],
                            'direction' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                        ],
                        'required' => ['field'],
                    ],
                ],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 1000],
                'file_name' => ['type' => 'string'],
            ],
            'required' => ['dataset'],
        ];
    }

    public function execute(array $arguments, ?string $conversationId = null, ?string $provider = null): array
    {
        $result = $this->exportService->exportExcel($arguments, $conversationId, $provider, $this->getName());
        return [
            'success' => true,
            'tool' => $this->getName(),
            'data' => $result->toArray(),
        ];
    }
}
