<?php

declare(strict_types=1);

use yii\db\Migration;

/** Strategic master data for the PM module. */
final class m260803_000001_create_pm_strategy_master_tables extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $audit = [
            'ref' => $this->string(64)->notNull()->unique(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ];

        $this->createTable('{{%pm_strategy_plan}}', array_merge([
            'id' => $this->primaryKey(),
            'code' => $this->string(50)->notNull(),
            'name' => $this->string(255)->notNull(),
            'version' => $this->integer()->notNull()->defaultValue(1),
            'start_year' => $this->integer()->notNull(),
            'end_year' => $this->integer()->notNull(),
            'vision' => $this->text()->null(),
            'status' => $this->string(20)->notNull()->defaultValue('draft'),
            'published_at' => $this->dateTime()->null(),
            'published_by' => $this->integer()->null(),
            'source_note' => $this->text()->null(),
        ], $audit), $options);
        $this->createIndex('uq-pm_strategy_plan-code-version', '{{%pm_strategy_plan}}', ['code', 'version'], true);
        $this->createIndex('idx-pm_strategy_plan-status-years', '{{%pm_strategy_plan}}', ['status', 'start_year', 'end_year']);

        $this->createTable('{{%pm_strategy_mission}}', array_merge([
            'id' => $this->primaryKey(), 'plan_id' => $this->integer()->notNull(),
            'code' => $this->string(50)->notNull(), 'name' => $this->text()->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0), 'is_active' => $this->boolean()->notNull()->defaultValue(true),
        ], $audit), $options);
        $this->childKeys('mission', 'plan_id', 'plan');

        $this->createTable('{{%pm_strategy_issue}}', array_merge([
            'id' => $this->primaryKey(), 'mission_id' => $this->integer()->notNull(),
            'code' => $this->string(50)->notNull(), 'name' => $this->text()->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0), 'is_active' => $this->boolean()->notNull()->defaultValue(true),
        ], $audit), $options);
        $this->childKeys('issue', 'mission_id', 'mission');

        $this->createTable('{{%pm_strategy_goal}}', array_merge([
            'id' => $this->primaryKey(), 'issue_id' => $this->integer()->notNull(),
            'code' => $this->string(50)->notNull(), 'name' => $this->text()->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0), 'is_active' => $this->boolean()->notNull()->defaultValue(true),
        ], $audit), $options);
        $this->childKeys('goal', 'issue_id', 'issue');

        $this->createTable('{{%pm_strategy_indicator}}', array_merge([
            'id' => $this->primaryKey(), 'plan_id' => $this->integer()->notNull(),
            'goal_id' => $this->integer()->null(), 'parent_id' => $this->integer()->null(),
            'code' => $this->string(50)->notNull(), 'name' => $this->text()->notNull(),
            'level' => $this->string(30)->notNull()->defaultValue('hospital'),
            'unit' => $this->string(100)->null(), 'calculation' => $this->text()->null(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0), 'is_active' => $this->boolean()->notNull()->defaultValue(true),
        ], $audit), $options);
        $this->childKeys('indicator', 'plan_id', 'plan');
        $this->addForeignKey('fk-pm_strategy_indicator-goal', '{{%pm_strategy_indicator}}', 'goal_id', '{{%pm_strategy_goal}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk-pm_strategy_indicator-parent', '{{%pm_strategy_indicator}}', 'parent_id', '{{%pm_strategy_indicator}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%pm_strategy_indicator_value}}', array_merge([
            'id' => $this->primaryKey(), 'indicator_id' => $this->integer()->notNull(),
            'fiscal_year' => $this->integer()->notNull(), 'baseline_value' => $this->decimal(18, 4)->null(),
            'target_value' => $this->decimal(18, 4)->null(), 'actual_value' => $this->decimal(18, 4)->null(),
            'operator' => $this->string(10)->null(), 'note' => $this->text()->null(),
        ], $audit), $options);
        $this->childKeys('indicator_value', 'indicator_id', 'indicator');
        $this->createIndex('uq-pm_strategy_indicator_value-year', '{{%pm_strategy_indicator_value}}', ['indicator_id', 'fiscal_year'], true);

        $this->createTable('{{%pm_strategy_success_factor}}', array_merge([
            'id' => $this->primaryKey(), 'goal_id' => $this->integer()->notNull(),
            'code' => $this->string(50)->null(), 'name' => $this->text()->notNull(),
            'factor_type' => $this->string(30)->notNull()->defaultValue('success_factor'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0), 'is_active' => $this->boolean()->notNull()->defaultValue(true),
        ], $audit), $options);
        $this->childKeys('success_factor', 'goal_id', 'goal');

        $this->createTable('{{%pm_strategy_measure}}', array_merge([
            'id' => $this->primaryKey(), 'goal_id' => $this->integer()->notNull(),
            'fiscal_year' => $this->integer()->notNull(), 'code' => $this->string(50)->null(),
            'name' => $this->text()->notNull(), 'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
        ], $audit), $options);
        $this->childKeys('measure', 'goal_id', 'goal');

        $this->createTable('{{%pm_strategy_program}}', array_merge([
            'id' => $this->primaryKey(), 'plan_id' => $this->integer()->notNull(),
            'measure_id' => $this->integer()->null(), 'fiscal_year' => $this->integer()->notNull(),
            'code' => $this->string(50)->null(), 'name' => $this->text()->notNull(),
            'owner_org_id' => $this->integer()->null(), 'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
        ], $audit), $options);
        $this->childKeys('program', 'plan_id', 'plan');
        $this->addForeignKey('fk-pm_strategy_program-measure', '{{%pm_strategy_program}}', 'measure_id', '{{%pm_strategy_measure}}', 'id', 'SET NULL', 'CASCADE');
    }

    private function childKeys(string $table, string $column, string $parent): void
    {
        $this->createIndex("idx-pm_strategy_{$table}-{$column}", "{{%pm_strategy_{$table}}}", $column);
        $this->createForeignKey("fk-pm_strategy_{$table}-{$parent}", "{{%pm_strategy_{$table}}}", $column, "{{%pm_strategy_{$parent}}}", 'id', 'CASCADE', 'CASCADE');
    }

    private function createForeignKey(string $name, string $table, string $column, string $refTable, string $refColumn, string $delete, string $update): void
    {
        $this->addForeignKey($name, $table, $column, $refTable, $refColumn, $delete, $update);
    }

    public function safeDown(): void
    {
        foreach (['program', 'measure', 'success_factor', 'indicator_value', 'indicator', 'goal', 'issue', 'mission', 'plan'] as $table) {
            $this->dropTable("{{%pm_strategy_{$table}}}");
        }
    }
}
