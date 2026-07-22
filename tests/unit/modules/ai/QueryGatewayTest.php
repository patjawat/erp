<?php

declare(strict_types=1);

namespace tests\unit\modules\ai;

use app\modules\ai\datasets\DatasetDefinition;
use app\modules\ai\Module;
use app\modules\ai\security\DataScopeResolver;
use app\modules\ai\security\PermissionChecker;
use app\modules\ai\services\AuditLogger;
use app\modules\ai\services\DatasetRegistry;
use app\modules\ai\services\QueryGateway;
use Codeception\Test\Unit;
use yii\db\Connection;
use yii\web\BadRequestHttpException;

class QueryGatewayTest extends Unit
{
    private Connection $db;

    protected function _before(): void
    {
        $this->db = new Connection(['dsn' => 'sqlite::memory:']);
        $this->db->open();
        $this->db->createCommand()->createTable('ai_test_dataset', [
            'id' => 'INTEGER PRIMARY KEY',
            'name' => 'TEXT',
            'department_id' => 'INTEGER',
            'amount' => 'INTEGER',
        ])->execute();
        $this->db->createCommand()->batchInsert('ai_test_dataset', ['id', 'name', 'department_id', 'amount'], [
            [1, 'Alpha', 10, 5],
            [2, 'Beta', 20, 15],
        ])->execute();
    }

    public function testRunsWhitelistedDatasetQuery(): void
    {
        $gateway = $this->gateway();

        $result = $gateway->run([
            'dataset' => 'test_dataset',
            'fields' => ['name', 'amount'],
            'filters' => [
                ['field' => 'department_id', 'operator' => '=', 'value' => 20],
            ],
            'sort' => [
                ['field' => 'amount', 'direction' => 'desc'],
            ],
            'limit' => 10,
        ]);

        $this->assertSame(1, $result->rowCount());
        $this->assertSame('Beta', $result->rows[0]['name']);
        $this->assertSame(15, (int) $result->rows[0]['amount']);
    }

    public function testRejectsUnknownField(): void
    {
        $this->expectException(BadRequestHttpException::class);

        $this->gateway()->run([
            'dataset' => 'test_dataset',
            'fields' => ['name', 'raw_sql'],
        ]);
    }

    private function gateway(): QueryGateway
    {
        $dataset = DatasetDefinition::fromArray('test_dataset', [
            'name' => 'Test Dataset',
            'view_name' => 'ai_test_dataset',
            'permission_name' => 'ai.test.summary',
            'fields' => [
                ['field_name' => 'id', 'label' => 'ID', 'data_type' => 'integer', 'is_filterable' => true, 'is_sortable' => true],
                ['field_name' => 'name', 'label' => 'Name', 'data_type' => 'string', 'is_filterable' => true, 'is_sortable' => true, 'allowed_operators' => ['=', 'like']],
                ['field_name' => 'department_id', 'label' => 'Department', 'data_type' => 'integer', 'is_filterable' => true, 'is_sortable' => true],
                ['field_name' => 'amount', 'label' => 'Amount', 'data_type' => 'integer', 'is_filterable' => true, 'is_sortable' => true, 'allowed_operators' => ['=', '>', '>=', '<', '<=']],
            ],
        ]);

        return new QueryGateway(
            $this->datasetRegistry($dataset),
            $this->permissionChecker(),
            $this->scopeResolver(),
            $this->auditLogger(),
            $this->module()
        );
    }

    private function datasetRegistry(DatasetDefinition $dataset): DatasetRegistry
    {
        return new class($dataset) extends DatasetRegistry {
            public function __construct(private DatasetDefinition $dataset)
            {
            }

            public function get(string $code): DatasetDefinition
            {
                return $this->dataset;
            }
        };
    }

    private function permissionChecker(): PermissionChecker
    {
        return new class extends PermissionChecker {
            public function requirePermission(string $permission): void
            {
            }

            public function can(string $permission): bool
            {
                return true;
            }
        };
    }

    private function scopeResolver(): DataScopeResolver
    {
        return new class extends DataScopeResolver {
            public function resolve(DatasetDefinition $dataset): array
            {
                return [];
            }
        };
    }

    private function auditLogger(): AuditLogger
    {
        return new class extends AuditLogger {
            public function log(array $data): void
            {
            }
        };
    }

    private function module(): Module
    {
        $module = new class('ai-test', null, ['absoluteMaxRows' => 1000]) extends Module {
            public Connection $testDb;

            public function getDb(): Connection
            {
                return $this->testDb;
            }

            public function getReadDb(): Connection
            {
                return $this->testDb;
            }
        };
        $module->testDb = $this->db;

        return $module;
    }
}
