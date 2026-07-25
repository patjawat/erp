<?php

use yii\db\Migration;

class m260725_000003_create_idp_tables extends Migration
{
    public function safeUp()
    {
        $audit = [
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ];

        $this->createTable('{{%idp_cycle}}', array_merge([
            'id' => $this->primaryKey(),
            'title' => $this->string(150)->notNull(),
            'fiscal_year' => $this->integer()->notNull(),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date()->notNull(),
            'submission_due_date' => $this->date()->null(),
            'review_due_date' => $this->date()->null(),
            'status' => $this->string(20)->notNull()->defaultValue('draft'),
            'description' => $this->text()->null(),
        ], $audit));
        $this->createIndex('idx-idp_cycle-status', '{{%idp_cycle}}', ['status', 'start_date', 'end_date']);

        $this->createTable('{{%idp_plan}}', array_merge([
            'id' => $this->primaryKey(),
            'cycle_id' => $this->integer()->notNull(),
            'emp_id' => $this->integer()->notNull(),
            'supervisor_emp_id' => $this->integer()->null(),
            'status' => $this->string(30)->notNull()->defaultValue('draft'),
            'progress_percent' => $this->decimal(5, 2)->notNull()->defaultValue(0),
            'employee_summary' => $this->text()->null(),
            'supervisor_comment' => $this->text()->null(),
            'submitted_at' => $this->dateTime()->null(),
            'reviewed_at' => $this->dateTime()->null(),
            'completed_at' => $this->dateTime()->null(),
        ], $audit));
        $this->createIndex('uq-idp_plan-cycle-emp', '{{%idp_plan}}', ['cycle_id', 'emp_id'], true);
        $this->createIndex('idx-idp_plan-status', '{{%idp_plan}}', ['status', 'emp_id']);
        $this->addForeignKey('fk-idp_plan-cycle', '{{%idp_plan}}', 'cycle_id', '{{%idp_cycle}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%idp_goal}}', array_merge([
            'id' => $this->primaryKey(),
            'plan_id' => $this->integer()->notNull(),
            'sequence' => $this->integer()->notNull()->defaultValue(1),
            'title' => $this->string(255)->notNull(),
            'source_type' => $this->string(30)->notNull()->defaultValue('employee'),
            'gap_reason' => $this->text()->null(),
            'expected_outcome' => $this->text()->null(),
            'success_measure' => $this->text()->null(),
            'due_date' => $this->date()->null(),
            'weight_percent' => $this->decimal(5, 2)->notNull()->defaultValue(100),
            'progress_percent' => $this->decimal(5, 2)->notNull()->defaultValue(0),
            'status' => $this->string(20)->notNull()->defaultValue('not_started'),
        ], $audit));
        $this->createIndex('idx-idp_goal-plan', '{{%idp_goal}}', ['plan_id', 'sequence']);
        $this->addForeignKey('fk-idp_goal-plan', '{{%idp_goal}}', 'plan_id', '{{%idp_plan}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%idp_activity}}', array_merge([
            'id' => $this->primaryKey(),
            'goal_id' => $this->integer()->notNull(),
            'sequence' => $this->integer()->notNull()->defaultValue(1),
            'title' => $this->string(255)->notNull(),
            'method_type' => $this->string(30)->notNull()->defaultValue('on_the_job'),
            'due_date' => $this->date()->null(),
            'status' => $this->string(20)->notNull()->defaultValue('not_started'),
            'progress_percent' => $this->decimal(5, 2)->notNull()->defaultValue(0),
            'evidence_note' => $this->text()->null(),
            'reflection' => $this->text()->null(),
            'completed_at' => $this->dateTime()->null(),
        ], $audit));
        $this->createIndex('idx-idp_activity-goal', '{{%idp_activity}}', ['goal_id', 'sequence']);
        $this->addForeignKey('fk-idp_activity-goal', '{{%idp_activity}}', 'goal_id', '{{%idp_goal}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%idp_activity}}');
        $this->dropTable('{{%idp_goal}}');
        $this->dropTable('{{%idp_plan}}');
        $this->dropTable('{{%idp_cycle}}');
    }
}
