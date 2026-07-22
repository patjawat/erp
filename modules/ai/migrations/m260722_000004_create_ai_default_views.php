<?php

declare(strict_types=1);

use yii\db\Migration;
use yii\db\TableSchema;

class m260722_000004_create_ai_default_views extends Migration
{
    private const VIEWS = [
        'ai_hr_department_summary',
        'ai_leave_overview',
        'ai_vehicle_booking_schedule',
        'ai_meeting_booking_schedule',
        'ai_stock_balances',
        'ai_training_overview',
        'ai_document_overview',
        'ai_health_overview',
    ];

    public function safeUp(): void
    {
        $this->createHrDepartmentSummaryView();
        $this->createLeaveOverviewView();
        $this->createVehicleBookingScheduleView();
        $this->createMeetingBookingScheduleView();
        $this->createStockBalancesView();
        $this->createTrainingOverviewView();
        $this->createDocumentOverviewView();
        $this->createHealthOverviewView();
    }

    public function safeDown(): void
    {
        foreach (array_reverse(self::VIEWS) as $viewName) {
            $this->dropViewIfExists($viewName);
        }
    }

    private function createHrDepartmentSummaryView(): void
    {
        $viewName = 'ai_hr_department_summary';
        if (!$this->tableExists('employees')) {
            $this->createEmptyView($viewName, [
                'department_id',
                'department_name',
                'employee_count',
                'active_employee_count',
                'vacant_position_count',
            ]);
            return;
        }

        $departmentColumn = $this->pickColumn('employees', [
            'department_id',
            'organization_id',
            'department',
            'dep_id',
            'work_department_id',
        ]);
        $statusColumn = $this->pickColumn('employees', ['status', 'employee_status', 'active']);
        $deletedFilter = $this->notDeletedWhere('employees', 'e');

        $departmentId = $departmentColumn ? $this->column('e', $departmentColumn) : '0';
        $departmentName = "'ทั้งหมด'";
        $join = '';
        if ($departmentColumn && $this->tableExists('organization_diagram')) {
            $orgName = $this->pickColumn('organization_diagram', ['name', 'title']);
            if ($orgName && $this->hasColumn('organization_diagram', 'id')) {
                $join = ' LEFT JOIN ' . $this->table('organization_diagram') . ' od ON od.' . $this->columnName('id') . ' = ' . $departmentId;
                $departmentName = 'COALESCE(od.' . $this->columnName($orgName) . ', CONCAT(\'Department \', ' . $departmentId . '))';
            }
        }

        if ($departmentColumn && $departmentName === "'ทั้งหมด'") {
            $departmentName = 'CONCAT(\'Department \', ' . $departmentId . ')';
        }

        $activeCount = $statusColumn
            ? 'SUM(CASE WHEN e.' . $this->columnName($statusColumn) . ' IS NULL OR e.' . $this->columnName($statusColumn) . ' IN (1, \'1\', \'active\', \'ปกติ\', \'ทำงาน\') THEN 1 ELSE 0 END)'
            : 'COUNT(*)';

        $groupBy = $departmentColumn ? ' GROUP BY department_id, department_name' : '';
        $sql = <<<SQL
SELECT
    {$departmentId} AS department_id,
    {$departmentName} AS department_name,
    COUNT(*) AS employee_count,
    {$activeCount} AS active_employee_count,
    0 AS vacant_position_count
FROM {$this->table('employees')} e
{$join}
{$deletedFilter}
{$groupBy}
SQL;

        $this->createOrReplaceView($viewName, $sql);
    }

    private function createLeaveOverviewView(): void
    {
        $viewName = 'ai_leave_overview';
        if (!$this->tableExists('leave')) {
            $this->createEmptyView($viewName, [
                'leave_id',
                'employee_id',
                'department_id',
                'employee_name',
                'leave_type',
                'start_date',
                'end_date',
                'status',
                'total_days',
            ]);
            return;
        }

        $employeeId = $this->pickColumn('leave', ['emp_id', 'employee_id', 'user_id', 'created_by']);
        $leaveTypeColumn = $this->pickColumn('leave', ['leave_type_id', 'leave_type', 'type_id', 'type_name']);
        $leaveTypeExpression = $leaveTypeColumn ? $this->column('l', $leaveTypeColumn) : "'ไม่ระบุ'";
        $leaveTypeJoin = '';
        if ($leaveTypeColumn && $this->tableExists('leave_types') && $this->hasColumn('leave_types', 'id')) {
            $leaveTypeName = $this->pickColumn('leave_types', ['name', 'title']);
            if ($leaveTypeName) {
                $leaveTypeJoin = ' LEFT JOIN ' . $this->table('leave_types') . ' lt ON lt.' . $this->columnName('id') . ' = ' . $this->column('l', $leaveTypeColumn);
                $leaveTypeExpression = 'COALESCE(lt.' . $this->columnName($leaveTypeName) . ', ' . $this->column('l', $leaveTypeColumn) . ')';
            }
        }

        $employeeJoin = $this->employeeJoin('l', $employeeId);
        $department = $this->departmentExpression('l', 'leave', $employeeJoin !== '');
        $sql = <<<SQL
SELECT
    {$this->columnOrFallback('l', 'leave', ['id'], '0')} AS leave_id,
    {$this->columnOrFallback('l', 'leave', ['emp_id', 'employee_id', 'user_id', 'created_by'], '0')} AS employee_id,
    {$department} AS department_id,
    {$this->employeeNameExpression('l', 'leave', $employeeJoin !== '')} AS employee_name,
    {$leaveTypeExpression} AS leave_type,
    {$this->columnOrFallback('l', 'leave', ['start_date', 'date_start', 'from_date', 'leave_start_date'], 'NULL')} AS start_date,
    {$this->columnOrFallback('l', 'leave', ['end_date', 'date_end', 'to_date', 'leave_end_date'], 'NULL')} AS end_date,
    {$this->columnOrFallback('l', 'leave', ['status', 'leave_status', 'approve_status'], "'ไม่ระบุ'")} AS status,
    {$this->columnOrFallback('l', 'leave', ['total_days', 'days', 'qty', 'leave_days'], '0')} AS total_days
FROM {$this->table('leave')} l
{$employeeJoin}
{$leaveTypeJoin}
{$this->notDeletedWhere('leave', 'l')}
SQL;

        $this->createOrReplaceView($viewName, $sql);
    }

    private function createVehicleBookingScheduleView(): void
    {
        $viewName = 'ai_vehicle_booking_schedule';
        if (!$this->tableExists('vehicle')) {
            $this->createEmptyView($viewName, [
                'booking_id',
                'vehicle_name',
                'requester_name',
                'department_id',
                'start_at',
                'end_at',
                'destination',
                'status',
            ]);
            return;
        }

        $employeeId = $this->pickColumn('vehicle', ['emp_id', 'owner_id', 'employee_id', 'user_id', 'created_by']);
        $employeeJoin = $this->employeeJoin('v', $employeeId);
        $vehicleNameFallback = $this->hasColumn('vehicle', 'id') ? "CONCAT('Vehicle ', v." . $this->columnName('id') . ")" : "'Vehicle'";
        $sql = <<<SQL
SELECT
    {$this->columnOrFallback('v', 'vehicle', ['id'], '0')} AS booking_id,
    {$this->columnOrFallback('v', 'vehicle', ['vehicle_name', 'car_name', 'license_plate', 'asset_name', 'name'], $vehicleNameFallback)} AS vehicle_name,
    {$this->employeeNameExpression('v', 'vehicle', $employeeJoin !== '')} AS requester_name,
    {$this->departmentExpression('v', 'vehicle', $employeeJoin !== '')} AS department_id,
    {$this->dateTimeExpression('v', 'vehicle', ['start_at', 'started_at'], ['start_date', 'date_start', 'date'], ['start_time', 'time_start', 'time'])} AS start_at,
    {$this->dateTimeExpression('v', 'vehicle', ['end_at', 'ended_at'], ['end_date', 'date_end', 'date'], ['end_time', 'time_end'])} AS end_at,
    {$this->columnOrFallback('v', 'vehicle', ['destination', 'location', 'place', 'address'], "''")} AS destination,
    {$this->columnOrFallback('v', 'vehicle', ['status', 'driver_service_status', 'approve_status'], "'ไม่ระบุ'")} AS status
FROM {$this->table('vehicle')} v
{$employeeJoin}
{$this->notDeletedWhere('vehicle', 'v')}
SQL;

        $this->createOrReplaceView($viewName, $sql);
    }

    private function createMeetingBookingScheduleView(): void
    {
        $viewName = 'ai_meeting_booking_schedule';
        if (!$this->tableExists('meeting')) {
            $this->createEmptyView($viewName, [
                'booking_id',
                'room_name',
                'requester_name',
                'department_id',
                'start_at',
                'end_at',
                'topic',
                'status',
            ]);
            return;
        }

        $employeeId = $this->pickColumn('meeting', ['emp_id', 'owner_id', 'employee_id', 'user_id', 'created_by']);
        $employeeJoin = $this->employeeJoin('m', $employeeId);
        [$roomJoin, $roomName] = $this->roomJoinAndName();
        $sql = <<<SQL
SELECT
    {$this->columnOrFallback('m', 'meeting', ['id'], '0')} AS booking_id,
    {$roomName} AS room_name,
    {$this->employeeNameExpression('m', 'meeting', $employeeJoin !== '')} AS requester_name,
    {$this->departmentExpression('m', 'meeting', $employeeJoin !== '')} AS department_id,
    {$this->dateTimeExpression('m', 'meeting', ['start_at', 'started_at'], ['start_date', 'date_start', 'date'], ['start_time', 'time_start', 'time'])} AS start_at,
    {$this->dateTimeExpression('m', 'meeting', ['end_at', 'ended_at'], ['end_date', 'date_end', 'date'], ['end_time', 'time_end'])} AS end_at,
    {$this->columnOrFallback('m', 'meeting', ['topic', 'title', 'subject', 'name'], "''")} AS topic,
    {$this->columnOrFallback('m', 'meeting', ['status', 'approve_status'], "'ไม่ระบุ'")} AS status
FROM {$this->table('meeting')} m
{$employeeJoin}
{$roomJoin}
{$this->notDeletedWhere('meeting', 'm')}
SQL;

        $this->createOrReplaceView($viewName, $sql);
    }

    private function createStockBalancesView(): void
    {
        $viewName = 'ai_stock_balances';
        if (!$this->tableExists('stock_balance')) {
            $this->createEmptyView($viewName, [
                'item_id',
                'item_code',
                'item_name',
                'warehouse_id',
                'warehouse_name',
                'balance_qty',
                'unit_name',
            ]);
            return;
        }

        $itemId = $this->pickColumn('stock_balance', ['item_id', 'stock_item_id']);
        $warehouseId = $this->pickColumn('stock_balance', ['warehouse_id', 'store_id']);
        $itemJoin = $itemId && $this->tableExists('stock_item') && $this->hasColumn('stock_item', 'id')
            ? ' LEFT JOIN ' . $this->table('stock_item') . ' si ON si.' . $this->columnName('id') . ' = ' . $this->column('sb', $itemId)
            : '';
        $warehouseJoin = $warehouseId && $this->tableExists('warehouse') && $this->hasColumn('warehouse', 'id')
            ? ' LEFT JOIN ' . $this->table('warehouse') . ' w ON w.' . $this->columnName('id') . ' = ' . $this->column('sb', $warehouseId)
            : '';

        $sql = <<<SQL
SELECT
    {$this->columnOrFallback('sb', 'stock_balance', ['item_id', 'stock_item_id'], '0')} AS item_id,
            {$this->stockItemField($itemJoin !== '', 'item_code', "''")} AS item_code,
            {$this->stockItemField($itemJoin !== '', 'item_name', "''")} AS item_name,
            {$this->columnOrFallback('sb', 'stock_balance', ['warehouse_id', 'store_id'], '0')} AS warehouse_id,
            {$this->warehouseNameExpression($warehouseJoin !== '')} AS warehouse_name,
            {$this->columnOrFallback('sb', 'stock_balance', ['balance_qty', 'qty', 'quantity', 'amount'], '0')} AS balance_qty,
            {$this->stockItemField($itemJoin !== '', 'unit_name', "''", ['unit_name', 'unit'])} AS unit_name
FROM {$this->table('stock_balance')} sb
{$itemJoin}
{$warehouseJoin}
SQL;

        $this->createOrReplaceView($viewName, $sql);
    }

    private function createTrainingOverviewView(): void
    {
        $viewName = 'ai_training_overview';
        if (!$this->tableExists('development')) {
            $this->createEmptyView($viewName, [
                'training_id',
                'course_name',
                'department_id',
                'start_date',
                'end_date',
                'participant_count',
                'status',
            ]);
            return;
        }

        $participantCount = '0';
        if ($this->tableExists('development_detail') && $this->hasColumn('development_detail', 'development_id')) {
            $participantCount = '(SELECT COUNT(*) FROM ' . $this->table('development_detail') . ' dd WHERE dd.' . $this->columnName('development_id') . ' = d.' . $this->columnName('id') . $this->softDeleteAnd('development_detail', 'dd') . ')';
        }

        $sql = <<<SQL
SELECT
    {$this->columnOrFallback('d', 'development', ['id'], '0')} AS training_id,
    {$this->columnOrFallback('d', 'development', ['course_name', 'name', 'title', 'topic'], "''")} AS course_name,
    {$this->columnOrFallback('d', 'development', ['department_id', 'organization_id', 'dep_id'], '0')} AS department_id,
    {$this->columnOrFallback('d', 'development', ['start_date', 'date_start', 'from_date'], 'NULL')} AS start_date,
    {$this->columnOrFallback('d', 'development', ['end_date', 'date_end', 'to_date'], 'NULL')} AS end_date,
    {$participantCount} AS participant_count,
    {$this->columnOrFallback('d', 'development', ['status', 'approve_status'], "'ไม่ระบุ'")} AS status
FROM {$this->table('development')} d
{$this->notDeletedWhere('development', 'd')}
SQL;

        $this->createOrReplaceView($viewName, $sql);
    }

    private function createDocumentOverviewView(): void
    {
        $viewName = 'ai_document_overview';
        $table = $this->tableExists('documents_detail') ? 'documents_detail' : null;
        if ($table === null) {
            $this->createEmptyView($viewName, [
                'document_id',
                'document_no',
                'subject',
                'document_type',
                'owner_department_id',
                'received_at',
                'status',
            ]);
            return;
        }

        $sql = <<<SQL
SELECT
    {$this->columnOrFallback('doc', $table, ['document_id', 'id'], '0')} AS document_id,
    {$this->columnOrFallback('doc', $table, ['ref', 'document_no', 'doc_no', 'code'], "''")} AS document_no,
    {$this->columnOrFallback('doc', $table, ['name', 'subject', 'title'], "''")} AS subject,
    {$this->columnOrFallback('doc', $table, ['document_type', 'to_type', 'from_type'], "''")} AS document_type,
    {$this->columnOrFallback('doc', $table, ['owner_department_id', 'department_id', 'from_id', 'to_id'], '0')} AS owner_department_id,
    {$this->columnOrFallback('doc', $table, ['received_at', 'receive_date', 'created_at'], 'NULL')} AS received_at,
    {$this->columnOrFallback('doc', $table, ['status', 'doc_read', 'bookmark'], "'ไม่ระบุ'")} AS status
FROM {$this->table($table)} doc
{$this->notDeletedWhere($table, 'doc')}
SQL;

        $this->createOrReplaceView($viewName, $sql);
    }

    private function createHealthOverviewView(): void
    {
        $viewName = 'ai_health_overview';
        if (!$this->tableExists('health_screen')) {
            $this->createEmptyView($viewName, [
                'record_id',
                'employee_id',
                'department_id',
                'screening_date',
                'risk_level',
                'status',
            ]);
            return;
        }

        $employeeId = $this->pickColumn('health_screen', ['emp_id', 'employee_id', 'user_id']);
        $employeeJoin = $this->employeeJoin('hs', $employeeId);
        $sql = <<<SQL
SELECT
    {$this->columnOrFallback('hs', 'health_screen', ['id'], '0')} AS record_id,
    {$this->columnOrFallback('hs', 'health_screen', ['emp_id', 'employee_id', 'user_id'], '0')} AS employee_id,
    {$this->departmentExpression('hs', 'health_screen', $employeeJoin !== '')} AS department_id,
    {$this->columnOrFallback('hs', 'health_screen', ['screening_date', 'screen_date', 'date', 'created_at'], 'NULL')} AS screening_date,
    {$this->columnOrFallback('hs', 'health_screen', ['risk_level', 'risk', 'result_level'], "'ไม่ระบุ'")} AS risk_level,
    {$this->columnOrFallback('hs', 'health_screen', ['status', 'screening_status'], "'ไม่ระบุ'")} AS status
FROM {$this->table('health_screen')} hs
{$employeeJoin}
{$this->notDeletedWhere('health_screen', 'hs')}
SQL;

        $this->createOrReplaceView($viewName, $sql);
    }

    /**
     * @param array<int, string> $columns
     */
    private function createEmptyView(string $viewName, array $columns): void
    {
        $select = [];
        foreach ($columns as $column) {
            $select[] = 'NULL AS ' . $this->columnName($column);
        }

        $this->createOrReplaceView($viewName, 'SELECT ' . implode(', ', $select) . ' WHERE 1 = 0');
    }

    private function createOrReplaceView(string $viewName, string $selectSql): void
    {
        $this->dropViewIfExists($viewName);
        $this->execute('CREATE VIEW ' . $this->table($viewName) . ' AS ' . $selectSql);
    }

    private function dropViewIfExists(string $viewName): void
    {
        $this->execute('DROP VIEW IF EXISTS ' . $this->table($viewName));
    }

    private function tableExists(string $tableName): bool
    {
        return $this->schema($tableName) !== null;
    }

    private function hasColumn(string $tableName, string $columnName): bool
    {
        $schema = $this->schema($tableName);
        return $schema !== null && isset($schema->columns[$columnName]);
    }

    /**
     * @param array<int, string> $candidates
     */
    private function pickColumn(string $tableName, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if ($this->hasColumn($tableName, $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param array<int, string> $candidates
     */
    private function columnOrFallback(string $alias, string $tableName, array $candidates, string $fallback): string
    {
        $column = $this->pickColumn($tableName, $candidates);
        return $column ? $this->column($alias, $column) : $fallback;
    }

    private function column(string $alias, string $columnName): string
    {
        return $alias . '.' . $this->columnName($columnName);
    }

    private function columnName(string $columnName): string
    {
        return $this->db->quoteColumnName($columnName);
    }

    private function table(string $tableName): string
    {
        return $this->db->quoteTableName($tableName);
    }

    private function schema(string $tableName): ?TableSchema
    {
        return $this->db->getSchema()->getTableSchema($tableName, true);
    }

    private function notDeletedWhere(string $tableName, string $alias): string
    {
        if ($this->hasColumn($tableName, 'deleted_at')) {
            return 'WHERE ' . $this->column($alias, 'deleted_at') . ' IS NULL';
        }

        return '';
    }

    private function softDeleteAnd(string $tableName, string $alias): string
    {
        if ($this->hasColumn($tableName, 'deleted_at')) {
            return ' AND ' . $this->column($alias, 'deleted_at') . ' IS NULL';
        }

        return '';
    }

    private function employeeJoin(string $sourceAlias, ?string $employeeIdColumn): string
    {
        if (!$employeeIdColumn || !$this->tableExists('employees') || !$this->hasColumn('employees', 'id')) {
            return '';
        }

        return ' LEFT JOIN ' . $this->table('employees') . ' e ON e.' . $this->columnName('id') . ' = ' . $this->column($sourceAlias, $employeeIdColumn);
    }

    private function employeeNameExpression(string $sourceAlias, string $sourceTable, bool $employeeJoined): string
    {
        if ($employeeJoined) {
            $parts = [];
            foreach (['fname', 'firstname', 'first_name', 'name', 'lname', 'lastname', 'last_name'] as $column) {
                if ($this->hasColumn('employees', $column)) {
                    $parts[] = 'e.' . $this->columnName($column);
                }
            }

            if ($parts !== []) {
                return 'TRIM(CONCAT_WS(\' \', ' . implode(', ', $parts) . '))';
            }
        }

        return $this->columnOrFallback($sourceAlias, $sourceTable, ['employee_name', 'requester_name', 'name'], "''");
    }

    private function departmentExpression(string $sourceAlias, string $sourceTable, bool $employeeJoined): string
    {
        $sourceColumn = $this->pickColumn($sourceTable, ['department_id', 'organization_id', 'dep_id']);
        if ($sourceColumn) {
            return $this->column($sourceAlias, $sourceColumn);
        }

        if ($employeeJoined) {
            $employeeColumn = $this->pickColumn('employees', ['department_id', 'organization_id', 'department', 'dep_id', 'work_department_id']);
            if ($employeeColumn) {
                return 'e.' . $this->columnName($employeeColumn);
            }
        }

        return '0';
    }

    /**
     * @param array<int, string> $dateTimeCandidates
     * @param array<int, string> $dateCandidates
     * @param array<int, string> $timeCandidates
     */
    private function dateTimeExpression(
        string $alias,
        string $tableName,
        array $dateTimeCandidates,
        array $dateCandidates,
        array $timeCandidates
    ): string {
        $dateTimeColumn = $this->pickColumn($tableName, $dateTimeCandidates);
        if ($dateTimeColumn) {
            return $this->column($alias, $dateTimeColumn);
        }

        $dateColumn = $this->pickColumn($tableName, $dateCandidates);
        $timeColumn = $this->pickColumn($tableName, $timeCandidates);
        if ($dateColumn && $timeColumn) {
            return 'CONCAT(' . $this->column($alias, $dateColumn) . ', \' \', ' . $this->column($alias, $timeColumn) . ')';
        }

        if ($dateColumn) {
            return $this->column($alias, $dateColumn);
        }

        return 'NULL';
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function roomJoinAndName(): array
    {
        $roomId = $this->pickColumn('meeting', ['room_id', 'meeting_room_id']);
        if ($roomId && $this->tableExists('categorise') && $this->hasColumn('categorise', 'id')) {
            $nameColumn = $this->pickColumn('categorise', ['name', 'title']);
            if ($nameColumn) {
                return [
                    ' LEFT JOIN ' . $this->table('categorise') . ' r ON r.' . $this->columnName('id') . ' = ' . $this->column('m', $roomId),
                    'COALESCE(r.' . $this->columnName($nameColumn) . ', CONCAT(\'Room \', ' . $this->column('m', $roomId) . '))',
                ];
            }
        }

        return ['', $this->columnOrFallback('m', 'meeting', ['room_name', 'room', 'room_id'], "'ไม่ระบุ'")];
    }

    /**
     * @param array<int, string>|null $candidates
     */
    private function stockItemField(bool $joined, string $defaultColumn, string $fallback, ?array $candidates = null): string
    {
        if (!$joined || !$this->tableExists('stock_item')) {
            return $fallback;
        }

        $column = $this->pickColumn('stock_item', $candidates ?: [$defaultColumn]);
        return $column ? 'si.' . $this->columnName($column) : $fallback;
    }

    private function warehouseNameExpression(bool $joined): string
    {
        if (!$joined || !$this->tableExists('warehouse')) {
            return "''";
        }

        $column = $this->pickColumn('warehouse', ['warehouse_name', 'name']);
        return $column ? 'w.' . $this->columnName($column) : "''";
    }
}
