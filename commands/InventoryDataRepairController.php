<?php

namespace app\commands;

use Throwable;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Connection;
use yii\helpers\Console;

class InventoryDataRepairController extends Controller
{
    public $apply = false;
    public $db = 'db';

    private const STOCK_DATA_JSON_TABLES = [
        'stock_order',
        'stock_detail',
    ];

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'apply',
            'db',
        ]);
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'a' => 'apply',
        ]);
    }

    public function actionUnwrapStockDataJson()
    {
        $db = $this->resolveDb();
        if ($db === null) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $apply = $this->shouldApply();
        $counts = [];
        $total = 0;

        foreach (self::STOCK_DATA_JSON_TABLES as $table) {
            if (!$this->assertDataJsonColumn($db, $table)) {
                return ExitCode::UNSPECIFIED_ERROR;
            }

            $count = (int) $db->createCommand($this->buildCountSql($db, $table))->queryScalar();
            $counts[$table] = $count;
            $total += $count;
        }

        $this->stdout(($apply ? 'APPLY' : 'DRY RUN') . ": unwrap inventory stock data_json\n", Console::FG_CYAN);
        foreach ($counts as $table => $count) {
            $this->stdout("- {$table}: {$count} row(s)\n");
        }

        if (!$apply) {
            $this->stdout("No changes were written. Re-run with --apply=1 to update data.\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        if ($total === 0) {
            $this->stdout("No wrapped JSON values found.\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        $transaction = $db->beginTransaction();
        try {
            foreach (self::STOCK_DATA_JSON_TABLES as $table) {
                $affected = $db->createCommand($this->buildUpdateSql($db, $table))->execute();
                $this->stdout("- {$table}: updated {$affected} row(s)\n");
            }
            $transaction->commit();
        } catch (Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            $this->stderr("Failed to unwrap data_json: {$e->getMessage()}\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Done.\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    private function shouldApply()
    {
        return filter_var($this->apply, FILTER_VALIDATE_BOOLEAN);
    }

    private function resolveDb()
    {
        if (!Yii::$app->has($this->db)) {
            $this->stderr("DB component not found: {$this->db}\n", Console::FG_RED);
            return null;
        }

        $db = Yii::$app->get($this->db);
        if (!$db instanceof Connection) {
            $this->stderr("Component {$this->db} is not a database connection.\n", Console::FG_RED);
            return null;
        }

        return $db;
    }

    private function assertDataJsonColumn(Connection $db, $table)
    {
        $schema = $db->schema->getTableSchema($table, true);
        if ($schema === null) {
            $this->stderr("Table not found: {$table}\n", Console::FG_RED);
            return false;
        }

        if (!isset($schema->columns['data_json'])) {
            $this->stderr("Column not found: {$table}.data_json\n", Console::FG_RED);
            return false;
        }

        return true;
    }

    private function buildCountSql(Connection $db, $table)
    {
        return 'SELECT COUNT(*) FROM ' . $db->quoteTableName($table)
            . ' WHERE ' . $this->buildWrappedJsonCondition($db);
    }

    private function buildUpdateSql(Connection $db, $table)
    {
        $column = $db->quoteColumnName('data_json');

        return 'UPDATE ' . $db->quoteTableName($table)
            . " SET {$column} = JSON_UNQUOTE({$column})"
            . ' WHERE ' . $this->buildWrappedJsonCondition($db);
    }

    private function buildWrappedJsonCondition(Connection $db)
    {
        $column = $db->quoteColumnName('data_json');

        return "{$column} IS NOT NULL"
            . " AND JSON_TYPE({$column}) = 'STRING'"
            . " AND JSON_VALID(JSON_UNQUOTE({$column})) = 1"
            . " AND ("
            . "LTRIM(JSON_UNQUOTE({$column})) LIKE '{%'"
            . " OR LTRIM(JSON_UNQUOTE({$column})) LIKE '[%'"
            . ")";
    }
}
