<?php

declare(strict_types=1);

namespace app\modules\ai\tools;

use app\modules\ai\services\QueryGateway;

class QueryDatasetTool implements AiToolInterface
{
    public function __construct(private ?QueryGateway $queryGateway = null)
    {
        $this->queryGateway = $queryGateway ?: new QueryGateway();
    }

    public function getName(): string
    {
        return 'query_dataset';
    }

    public function getDescription(): string
    {
        return 'Query a registered AI dataset through a read-only AI view. Never accepts table names or raw SQL.';
    }

    public function getJsonSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'dataset' => [
                    'type' => 'string',
                    'description' => 'Registered dataset code, for example leave_overview.',
                ],
                'fields' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Optional list of registered fields to return.',
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
                'limit' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 1000,
                ],
            ],
            'required' => ['dataset'],
        ];
    }

    public function execute(array $arguments, ?string $conversationId = null, ?string $provider = null): array
    {
        $result = $this->queryGateway->run($arguments, $conversationId, $provider, $this->getName());
        return [
            'success' => true,
            'tool' => $this->getName(),
            'data' => $result->toArray(),
        ];
    }
}
