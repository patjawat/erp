<?php

declare(strict_types=1);

namespace app\modules\ai\security;

use app\modules\ai\datasets\DatasetDefinition;
use yii\web\ForbiddenHttpException;
use Yii;

class DataScopeResolver
{
    public function __construct(private ?PermissionChecker $permissionChecker = null)
    {
        $this->permissionChecker = $permissionChecker ?: new PermissionChecker();
    }

    /**
     * @return array<int, array{field: string, operator: string, value: mixed}>
     */
    public function resolve(DatasetDefinition $dataset): array
    {
        $rules = $dataset->metadata['scope_rules'] ?? [];
        if ($rules === []) {
            return [];
        }

        foreach ($rules as $rule) {
            $permissions = (array) ($rule['permissions'] ?? []);
            if ($permissions !== [] && !$this->permissionChecker->canAny($permissions)) {
                continue;
            }

            $filters = [];
            foreach (($rule['filters'] ?? []) as $filter) {
                $filters[] = [
                    'field' => (string) ($filter['field'] ?? ''),
                    'operator' => strtolower((string) ($filter['operator'] ?? '=')),
                    'value' => $this->resolveValue((string) ($filter['value'] ?? '')),
                ];
            }

            $this->assertScopeValues($filters, (string) ($rule['name'] ?? 'scope'));
            return $filters;
        }

        throw new ForbiddenHttpException('No data scope rule matched for this AI dataset.');
    }

    private function resolveValue(string $key): mixed
    {
        return match ($key) {
            'current_user_id' => $this->permissionChecker->currentUserId(),
            'current_employee_id' => $this->identityValue(['employee_id', 'emp_id', 'employeeId']),
            'current_department_id' => $this->identityValue(['department_id', 'departmentId', 'department']),
            'managed_department_ids' => $this->identityList(['managed_department_ids', 'managedDepartmentIds', 'department_ids'], 'current_department_id'),
            'managed_warehouse_ids' => $this->identityList(['managed_warehouse_ids', 'managedWarehouseIds', 'warehouse_ids'], 'current_warehouse_id'),
            'current_warehouse_id' => $this->identityValue(['warehouse_id', 'warehouseId']),
            default => $key,
        };
    }

    /**
     * @param array<int, array{field: string, operator: string, value: mixed}> $filters
     */
    private function assertScopeValues(array $filters, string $scopeName): void
    {
        foreach ($filters as $filter) {
            $value = $filter['value'];
            if ($value === null || $value === '' || (is_array($value) && $value === [])) {
                throw new ForbiddenHttpException("Data scope '{$scopeName}' cannot be resolved for the current user.");
            }
        }
    }

    /**
     * @param array<int, string> $keys
     */
    private function identityValue(array $keys): mixed
    {
        if (!isset(Yii::$app->user) || Yii::$app->user->isGuest || Yii::$app->user->identity === null) {
            return null;
        }

        $identity = Yii::$app->user->identity;
        foreach ($keys as $key) {
            $getter = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($identity, $getter)) {
                return $identity->{$getter}();
            }

            if (isset($identity->{$key})) {
                return $identity->{$key};
            }
        }

        return null;
    }

    /**
     * @param array<int, string> $keys
     * @return array<int, mixed>
     */
    private function identityList(array $keys, string $fallbackKey): array
    {
        $value = $this->identityValue($keys);
        if ($value === null || $value === '') {
            $value = $this->resolveValue($fallbackKey);
        }

        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return array_values(array_filter($value, static fn ($item): bool => $item !== null && $item !== ''));
        }

        return [$value];
    }
}
