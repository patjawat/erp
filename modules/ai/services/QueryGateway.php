<?php

declare(strict_types=1);

namespace app\modules\ai\services;

use app\modules\ai\datasets\DatasetDefinition;
use app\modules\ai\Module;
use app\modules\ai\security\DataScopeResolver;
use app\modules\ai\security\PermissionChecker;
use Throwable;
use Yii;
use yii\db\Query;
use yii\web\BadRequestHttpException;

class QueryGateway
{
    private const OPERATOR_MAP = [
        '=' => '=',
        '!=' => '<>',
        '<>' => '<>',
        '>' => '>',
        '>=' => '>=',
        '<' => '<',
        '<=' => '<=',
        'like' => 'like',
        'in' => 'in',
        'between' => 'between',
        'is_null' => 'is null',
        'is_not_null' => 'is not null',
    ];

    public function __construct(
        private ?DatasetRegistry $datasetRegistry = null,
        private ?PermissionChecker $permissionChecker = null,
        private ?DataScopeResolver $scopeResolver = null,
        private ?AuditLogger $auditLogger = null,
        private ?Module $module = null
    ) {
        $this->datasetRegistry = $datasetRegistry ?: new DatasetRegistry();
        $this->permissionChecker = $permissionChecker ?: new PermissionChecker();
        $this->scopeResolver = $scopeResolver ?: new DataScopeResolver($this->permissionChecker);
        $this->auditLogger = $auditLogger ?: new AuditLogger();
        $this->module = $module ?: Yii::$app->getModule('ai');
    }

    /**
     * @param array<string, mixed> $request
     */
    public function run(array $request, ?string $conversationId = null, ?string $provider = null, ?string $toolName = null): QueryResult
    {
        $startedAt = microtime(true);
        $datasetCode = (string) ($request['dataset'] ?? $request['dataset_code'] ?? '');

        try {
            if ($datasetCode === '') {
                throw new BadRequestHttpException('Dataset is required.');
            }

            $dataset = $this->datasetRegistry->get($datasetCode);
            $this->permissionChecker->requirePermission($dataset->permissionName);
            $this->assertAiView($dataset->viewName);

            $db = $this->module->getReadDb();
            $schema = $db->getSchema()->getTableSchema($dataset->viewName, true);
            if ($schema === null) {
                throw new BadRequestHttpException("AI view '{$dataset->viewName}' does not exist.");
            }

            $viewColumns = array_fill_keys(array_keys($schema->columns), true);
            $fields = $this->normalizeFields($dataset, $request['fields'] ?? [], $viewColumns);
            $labels = [];
            foreach ($fields as $field) {
                $labels[$field] = $dataset->labelFor($field);
            }

            $query = (new Query())
                ->select($fields)
                ->from($dataset->viewName);

            foreach ((array) ($request['filters'] ?? []) as $filter) {
                $this->applyFilter($query, $dataset, (array) $filter, $viewColumns, true);
            }

            foreach ($this->scopeResolver->resolve($dataset) as $filter) {
                $this->applyFilter($query, $dataset, $filter, $viewColumns, false);
            }

            $this->applySort($query, $dataset, $request['sort'] ?? [], $viewColumns);
            $limit = $this->normalizeLimit($dataset, $request['limit'] ?? null);
            $rows = $query->limit($limit)->all($db);
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

            $result = new QueryResult($dataset, $rows, $fields, $labels, $durationMs);
            $this->auditLogger->log([
                'conversation_id' => $conversationId,
                'provider' => $provider,
                'dataset_code' => $dataset->code,
                'tool_name' => $toolName,
                'action' => 'query_dataset',
                'status' => 'success',
                'row_count' => $result->rowCount(),
                'duration_ms' => $durationMs,
                'request' => $this->redactRequest($request),
                'response' => ['row_count' => $result->rowCount()],
            ]);

            return $result;
        } catch (Throwable $exception) {
            $this->auditLogger->log([
                'conversation_id' => $conversationId,
                'provider' => $provider,
                'dataset_code' => $datasetCode ?: null,
                'tool_name' => $toolName,
                'action' => 'query_dataset',
                'status' => 'error',
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'request' => $this->redactRequest($request),
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * @param array<string, bool> $viewColumns
     * @return array<int, string>
     */
    private function normalizeFields(DatasetDefinition $dataset, mixed $fields, array $viewColumns): array
    {
        $fields = is_array($fields) && $fields !== [] ? $fields : $dataset->selectableFields();
        $normalized = [];

        foreach ($fields as $field) {
            $field = (string) $field;
            $this->assertSafeFieldName($field);
            if (!$dataset->hasField($field) || !($dataset->fields[$field]['is_selectable'] ?? true)) {
                throw new BadRequestHttpException("Field '{$field}' is not selectable for dataset '{$dataset->code}'.");
            }

            if (!isset($viewColumns[$field])) {
                throw new BadRequestHttpException("Field '{$field}' is not available in AI view '{$dataset->viewName}'.");
            }

            $normalized[] = $field;
        }

        $normalized = array_values(array_unique($normalized));
        if ($normalized === []) {
            throw new BadRequestHttpException("Dataset '{$dataset->code}' has no selectable fields.");
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $filter
     * @param array<string, bool> $viewColumns
     */
    private function applyFilter(Query $query, DatasetDefinition $dataset, array $filter, array $viewColumns, bool $fromUser): void
    {
        $field = (string) ($filter['field'] ?? '');
        $operator = strtolower((string) ($filter['operator'] ?? '='));
        $value = $filter['value'] ?? null;

        $this->assertSafeFieldName($field);
        if (!$dataset->hasField($field) || !isset($viewColumns[$field])) {
            throw new BadRequestHttpException("Filter field '{$field}' is not available for dataset '{$dataset->code}'.");
        }

        if ($fromUser && !$dataset->isFilterable($field)) {
            throw new BadRequestHttpException("Field '{$field}' is not filterable for dataset '{$dataset->code}'.");
        }

        if (!isset(self::OPERATOR_MAP[$operator])) {
            throw new BadRequestHttpException("Operator '{$operator}' is not supported.");
        }

        if ($fromUser && !in_array($operator, $dataset->allowedOperators($field), true)) {
            throw new BadRequestHttpException("Operator '{$operator}' is not allowed for field '{$field}'.");
        }

        $mappedOperator = self::OPERATOR_MAP[$operator];
        if ($mappedOperator === 'like') {
            $query->andWhere(['like', $field, (string) $value]);
            return;
        }

        if ($mappedOperator === 'in') {
            $query->andWhere(['in', $field, $this->normalizeListValue($value)]);
            return;
        }

        if ($mappedOperator === 'between') {
            $range = $this->normalizeListValue($value);
            if (count($range) !== 2) {
                throw new BadRequestHttpException("Operator 'between' requires exactly two values.");
            }
            $query->andWhere(['between', $field, $range[0], $range[1]]);
            return;
        }

        if ($mappedOperator === 'is null') {
            $query->andWhere([$field => null]);
            return;
        }

        if ($mappedOperator === 'is not null') {
            $query->andWhere(['not', [$field => null]]);
            return;
        }

        $query->andWhere([$mappedOperator, $field, $value]);
    }

    /**
     * @param array<string, bool> $viewColumns
     */
    private function applySort(Query $query, DatasetDefinition $dataset, mixed $sort, array $viewColumns): void
    {
        if (!is_array($sort) || $sort === []) {
            return;
        }

        $orderBy = [];
        foreach ($sort as $key => $item) {
            if (is_array($item)) {
                $field = (string) ($item['field'] ?? '');
                $direction = strtolower((string) ($item['direction'] ?? 'asc'));
            } else {
                $field = is_string($key) ? $key : (string) $item;
                $direction = is_string($item) && in_array(strtolower($item), ['asc', 'desc'], true) ? strtolower($item) : 'asc';
            }

            $this->assertSafeFieldName($field);
            if (!$dataset->isSortable($field) || !isset($viewColumns[$field])) {
                throw new BadRequestHttpException("Field '{$field}' is not sortable for dataset '{$dataset->code}'.");
            }

            $orderBy[$field] = $direction === 'desc' ? SORT_DESC : SORT_ASC;
        }

        if ($orderBy !== []) {
            $query->orderBy($orderBy);
        }
    }

    private function normalizeLimit(DatasetDefinition $dataset, mixed $limit): int
    {
        $limit = $limit === null ? $dataset->maxRows : (int) $limit;
        $limit = max(1, $limit);
        $limit = min($limit, $dataset->maxRows);

        return min($limit, $this->module->absoluteMaxRows);
    }

    /**
     * @return array<int, mixed>
     */
    private function normalizeListValue(mixed $value): array
    {
        if (is_array($value)) {
            return array_values($value);
        }

        if (is_string($value)) {
            return array_values(array_filter(array_map('trim', explode(',', $value)), static fn (string $item): bool => $item !== ''));
        }

        return [$value];
    }

    private function assertAiView(string $viewName): void
    {
        if (!preg_match('/^ai_[a-z0-9_]+$/', $viewName)) {
            throw new BadRequestHttpException("AI view '{$viewName}' is not allowed.");
        }
    }

    private function assertSafeFieldName(string $field): void
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $field)) {
            throw new BadRequestHttpException("Field '{$field}' is not allowed.");
        }
    }

    /**
     * @param array<string, mixed> $request
     * @return array<string, mixed>
     */
    private function redactRequest(array $request): array
    {
        unset($request['api_key'], $request['token'], $request['password']);
        return $request;
    }
}
