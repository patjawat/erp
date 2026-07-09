<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Connection;
use yii\helpers\BaseConsole;
use yii\helpers\Console;

/**
 * Normalize every string column in a tenant DB to one target charset+collation
 * (utf8mb4/utf8mb4_unicode_ci by default), one tenant DB at a time.
 *
 * Covers two distinct problems that both surface as SQL errors at query time:
 *  - utf8mb3 columns (error 1253/3780: invalid or incompatible collation)
 *  - utf8mb4 columns whose collation disagrees with another utf8mb4 column
 *    they're compared/joined against (error 1267: illegal mix of collations)
 *    — common when tables were created/migrated at different times with
 *    different MySQL defaults (utf8mb4_general_ci vs utf8mb4_unicode_ci).
 *
 * Tables linked by a string-typed foreign key (e.g. asset <-> asset_condition,
 * auth_item <-> auth_rule) are converted together as a group: FKs in the group
 * are dropped, every table in the group is converted, then the FKs are
 * recreated. Converting a single table in a group in isolation fails with
 * MySQL error 3780 (incompatible FK charset/collation) because the two sides
 * of the FK end up mismatched mid-migration.
 *
 * Usage:
 *   docker exec dansai php yii charset/status                  # inspect current .env DB
 *   docker exec dansai php yii charset/status --db=lomkao       # inspect another tenant DB
 *   docker exec dansai php yii charset/convert --db=lomkao      # convert it (asks to confirm)
 *   docker exec dansai php yii charset/convert --db=lomkao --yes
 */
class CharsetController extends Controller
{
    public $collation = 'utf8mb4_unicode_ci';

    /** @var string|null Tenant DB name to target; defaults to whatever `db` component is already connected to. */
    public $db;

    /** @var bool Skip the interactive confirmation prompt. */
    public $yes = false;

    public function options($actionID)
    {
        return ['collation', 'db', 'yes'];
    }

    public function optionAliases()
    {
        return ['c' => 'collation', 'd' => 'db', 'y' => 'yes'];
    }

    public function actionStatus()
    {
        $conn = $this->resolveConnection();
        $dbName = $conn->createCommand('SELECT DATABASE()')->queryScalar();
        $tables = $this->getTablesNeedingConversion($conn);

        if (!$tables) {
            $this->stdout("{$dbName}: already fully utf8mb4/{$this->collation}, no mismatches.\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        $groups = $this->buildFkGroups($conn, $tables);
        $this->printPlan($dbName, $groups);
        return ExitCode::OK;
    }

    public function actionConvert()
    {
        $conn = $this->resolveConnection();
        $dbName = $conn->createCommand('SELECT DATABASE()')->queryScalar();
        $tables = $this->getTablesNeedingConversion($conn);

        if (!$tables) {
            $this->stdout("{$dbName}: nothing to do, already fully utf8mb4/{$this->collation}.\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        $groups = $this->buildFkGroups($conn, $tables);
        $this->printPlan($dbName, $groups);

        $this->stdout("\nNo automated backup runs from inside this container (mysqldump isn't installed here).\n", Console::FG_YELLOW);
        $this->stdout("Back up first, e.g.: docker exec dansai_db mysqldump -uroot -pdocker {$dbName} > {$dbName}_backup.sql\n", Console::FG_YELLOW);

        if (!$this->yes && !BaseConsole::confirm("\nBackup taken and ready to convert {$dbName} as shown above?")) {
            $this->stdout("Aborted.\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        foreach ($groups as $i => $group) {
            $this->stdout("\n== Group " . ($i + 1) . '/' . count($groups) . ': ' . implode(', ', $group['tables']) . " ==\n", Console::FG_CYAN);

            foreach ($group['fks'] as $fk) {
                $this->exec($conn, "ALTER TABLE `{$fk['table']}` DROP FOREIGN KEY `{$fk['name']}`");
            }
            foreach ($group['tables'] as $table) {
                $this->exec($conn, "ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE {$this->collation}");
            }
            foreach ($group['fks'] as $fk) {
                $this->exec($conn, "ALTER TABLE `{$fk['table']}` ADD CONSTRAINT `{$fk['name']}` FOREIGN KEY (`{$fk['column']}`) "
                    . "REFERENCES `{$fk['refTable']}` (`{$fk['refColumn']}`) ON UPDATE {$fk['onUpdate']} ON DELETE {$fk['onDelete']}");
            }
        }

        $this->stdout("\nDone. {$dbName} is fully utf8mb4.\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    private function resolveConnection(): Connection
    {
        if ($this->db === null) {
            return Yii::$app->db;
        }

        $base = Yii::$app->db;
        $dsn = preg_replace('/dbname=[^;]+/', 'dbname=' . $this->db, $base->dsn);

        $conn = new Connection([
            'dsn' => $dsn,
            'username' => $base->username,
            'password' => $base->password,
            'charset' => 'utf8mb4',
        ]);
        $conn->open();

        return $conn;
    }

    private function exec(Connection $conn, string $sql): void
    {
        $this->stdout("  {$sql}\n");
        $conn->createCommand($sql)->execute();
    }

    /**
     * Tables with at least one string column that isn't already on the
     * target charset+collation — either still utf8mb3, or utf8mb4 with a
     * different collation than the target (e.g. utf8mb4_general_ci when the
     * target is utf8mb4_unicode_ci).
     */
    private function getTablesNeedingConversion(Connection $conn): array
    {
        return $conn->createCommand(
            "SELECT DISTINCT c.TABLE_NAME FROM information_schema.COLUMNS c
             JOIN information_schema.TABLES t
                  ON t.TABLE_SCHEMA = c.TABLE_SCHEMA AND t.TABLE_NAME = c.TABLE_NAME
             WHERE c.TABLE_SCHEMA = DATABASE() AND c.CHARACTER_SET_NAME IS NOT NULL
               AND t.TABLE_TYPE = 'BASE TABLE'
               AND (c.CHARACTER_SET_NAME <> 'utf8mb4' OR c.COLLATION_NAME <> :collation)
             ORDER BY c.TABLE_NAME",
            [':collation' => $this->collation]
        )->queryColumn();
    }

    /**
     * Group tables that need converting with any table they share a
     * string-typed foreign key with (in either direction), using union-find.
     * The FK partner is pulled in even if it's already utf8mb4, since its FK
     * must be dropped before its neighbour converts and recreated after.
     */
    private function buildFkGroups(Connection $conn, array $seedTables): array
    {
        $parent = array_combine($seedTables, $seedTables);
        $find = function ($x) use (&$parent, &$find) {
            if (!isset($parent[$x])) {
                $parent[$x] = $x;
            }
            while ($parent[$x] !== $x) {
                $parent[$x] = $parent[$parent[$x]];
                $x = $parent[$x];
            }
            return $x;
        };
        $union = function ($a, $b) use (&$parent, $find) {
            $ra = $find($a);
            $rb = $find($b);
            if ($ra !== $rb) {
                $parent[$ra] = $rb;
            }
        };

        $tablePh = [];
        $refPh = [];
        $params = [];
        foreach (array_values($seedTables) as $i => $table) {
            $tablePh[] = ":t{$i}";
            $refPh[] = ":r{$i}";
            $params[":t{$i}"] = $table;
            $params[":r{$i}"] = $table;
        }
        $allFks = $conn->createCommand(
            "SELECT k.TABLE_NAME AS `table`, k.CONSTRAINT_NAME AS name, k.COLUMN_NAME AS `column`,
                    k.REFERENCED_TABLE_NAME AS refTable, k.REFERENCED_COLUMN_NAME AS refColumn,
                    r.UPDATE_RULE AS onUpdate, r.DELETE_RULE AS onDelete
             FROM information_schema.KEY_COLUMN_USAGE k
             JOIN information_schema.REFERENTIAL_CONSTRAINTS r
                  ON r.CONSTRAINT_SCHEMA = k.TABLE_SCHEMA AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME AND r.TABLE_NAME = k.TABLE_NAME
             WHERE k.TABLE_SCHEMA = DATABASE() AND k.REFERENCED_TABLE_NAME IS NOT NULL
               AND (k.TABLE_NAME IN (" . implode(',', $tablePh) . ') OR k.REFERENCED_TABLE_NAME IN (' . implode(',', $refPh) . '))',
            $params
        )->queryAll();

        // Only FKs on string (charset-bearing) columns are charset-sensitive.
        $stringFks = array_values(array_filter($allFks, function ($fk) use ($conn) {
            return $this->isStringColumn($conn, $fk['table'], $fk['column'])
                && $this->isStringColumn($conn, $fk['refTable'], $fk['refColumn']);
        }));

        foreach ($stringFks as $fk) {
            $union($fk['table'], $fk['refTable']);
        }

        $byRoot = [];
        foreach (array_unique(array_merge($seedTables, array_column($stringFks, 'table'), array_column($stringFks, 'refTable'))) as $table) {
            $byRoot[$find($table)][] = $table;
        }

        $groups = [];
        foreach ($byRoot as $root => $tables) {
            sort($tables);
            $fksInGroup = array_values(array_filter($stringFks, function ($fk) use ($tables) {
                return in_array($fk['table'], $tables, true);
            }));
            $groups[] = ['tables' => $tables, 'fks' => $fksInGroup];
        }

        return $groups;
    }

    private $columnCharsetCache = [];

    private function isStringColumn(Connection $conn, string $table, string $column): bool
    {
        $key = "{$table}.{$column}";
        if (!array_key_exists($key, $this->columnCharsetCache)) {
            $this->columnCharsetCache[$key] = (bool) $conn->createCommand(
                'SELECT CHARACTER_SET_NAME FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c',
                [':t' => $table, ':c' => $column]
            )->queryScalar();
        }
        return $this->columnCharsetCache[$key];
    }

    private function printPlan(string $dbName, array $groups): void
    {
        $totalTables = array_sum(array_map(fn ($g) => count($g['tables']), $groups));
        $this->stdout("{$dbName}: {$totalTables} table(s) to convert to utf8mb4/{$this->collation}, in " . count($groups) . " group(s):\n", Console::FG_CYAN);
        foreach ($groups as $i => $group) {
            $this->stdout('  Group ' . ($i + 1) . ': ' . implode(', ', $group['tables']));
            if ($group['fks']) {
                $this->stdout(' (FKs to drop/recreate: ' . implode(', ', array_column($group['fks'], 'name')) . ')');
            }
            $this->stdout("\n");
        }
    }
}
