<?php

declare(strict_types=1);

namespace tests\unit\modules\ai;

use app\modules\ai\datasets\DatasetDefinition;
use app\modules\ai\security\DataScopeResolver;
use app\modules\ai\security\PermissionChecker;
use Codeception\Test\Unit;
use yii\web\ForbiddenHttpException;

class DataScopeResolverTest extends Unit
{
    public function testReturnsFirstMatchingScopeRule(): void
    {
        $dataset = DatasetDefinition::fromArray('document_overview', [
            'view_name' => 'ai_document_overview',
            'permission_name' => 'ai.document.summary',
            'fields' => [],
            'metadata' => [
                'scope_rules' => [
                    [
                        'name' => 'all',
                        'permissions' => ['ai.scope.document.all'],
                        'filters' => [],
                    ],
                    [
                        'name' => 'department',
                        'permissions' => ['ai.scope.document.department'],
                        'filters' => [
                            ['field' => 'owner_department_id', 'operator' => '=', 'value' => '10'],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new DataScopeResolver($this->permissionChecker(['ai.scope.document.department']));

        $this->assertSame([
            ['field' => 'owner_department_id', 'operator' => '=', 'value' => '10'],
        ], $resolver->resolve($dataset));
    }

    public function testThrowsWhenNoScopeRuleMatches(): void
    {
        $dataset = DatasetDefinition::fromArray('health_overview', [
            'view_name' => 'ai_health_overview',
            'permission_name' => 'ai.health.summary',
            'fields' => [],
            'metadata' => [
                'scope_rules' => [
                    ['name' => 'health', 'permissions' => ['ai.scope.health.all'], 'filters' => []],
                ],
            ],
        ]);

        $this->expectException(ForbiddenHttpException::class);
        (new DataScopeResolver($this->permissionChecker([])))->resolve($dataset);
    }

    /**
     * @param array<int, string> $allowedPermissions
     */
    private function permissionChecker(array $allowedPermissions): PermissionChecker
    {
        return new class($allowedPermissions) extends PermissionChecker {
            /**
             * @param array<int, string> $allowedPermissions
             */
            public function __construct(private array $allowedPermissions)
            {
            }

            public function can(string $permission): bool
            {
                return in_array($permission, $this->allowedPermissions, true);
            }

            public function canAny(array $permissions): bool
            {
                foreach ($permissions as $permission) {
                    if ($this->can($permission)) {
                        return true;
                    }
                }

                return false;
            }
        };
    }
}
