<?php

use yii\db\Migration;

class m260802_000001_create_probation_appraisal_tables extends Migration
{
    private function audit(): array
    {
        return [
            'ref' => $this->string(64)->notNull()->unique(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ];
    }

    public function safeUp()
    {
        $this->createTable('{{%probation_template}}', array_merge([
            'id' => $this->primaryKey(),
            'position_group_id' => $this->integer()->notNull(),
            'name' => $this->string(200)->notNull(),
            'revision_no' => $this->integer()->notNull()->defaultValue(1),
            'status' => $this->string(20)->notNull()->defaultValue('draft'),
            'description' => $this->text()->null(),
            'effective_date' => $this->date()->null(),
        ], $this->audit()));
        $this->createIndex('uq-probation_template-group-revision', '{{%probation_template}}', ['position_group_id', 'revision_no'], true);
        $this->createIndex('idx-probation_template-status', '{{%probation_template}}', 'status');
        $this->addForeignKey('fk-probation_template-position_group', '{{%probation_template}}', 'position_group_id', '{{%employee_position_group}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%probation_template_item}}', array_merge([
            'id' => $this->primaryKey(),
            'template_id' => $this->integer()->notNull(),
            'category' => $this->string(150)->notNull(),
            'question' => $this->text()->notNull(),
            'max_score' => $this->decimal(8, 2)->notNull()->defaultValue(5),
            'sequence' => $this->integer()->notNull()->defaultValue(1),
            'active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
        ], $this->audit()));
        $this->createIndex('idx-probation_template_item-template', '{{%probation_template_item}}', ['template_id', 'sequence']);
        $this->addForeignKey('fk-probation_template_item-template', '{{%probation_template_item}}', 'template_id', '{{%probation_template}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%probation_case}}', array_merge([
            'id' => $this->primaryKey(),
            'employee_id' => $this->integer()->notNull(),
            'template_id' => $this->integer()->notNull(),
            'supervisor_employee_id' => $this->integer()->notNull(),
            'group_head_employee_id' => $this->integer()->notNull(),
            'director_employee_id' => $this->integer()->notNull(),
            'final_recommender_employee_id' => $this->integer()->notNull(),
            'start_date' => $this->date()->notNull(),
            'status' => $this->string(40)->notNull()->defaultValue('assigned'),
            'cancel_reason' => $this->text()->null(),
            'completed_at' => $this->dateTime()->null(),
        ], $this->audit()));
        $this->createIndex('uq-probation_case-employee-start', '{{%probation_case}}', ['employee_id', 'start_date'], true);
        $this->createIndex('idx-probation_case-status', '{{%probation_case}}', ['status', 'start_date']);
        foreach (['employee_id', 'supervisor_employee_id', 'group_head_employee_id', 'director_employee_id', 'final_recommender_employee_id'] as $column) {
            $this->addForeignKey('fk-probation_case-' . str_replace('_', '-', $column), '{{%probation_case}}', $column, '{{%employees}}', 'id', 'RESTRICT', 'CASCADE');
        }
        $this->addForeignKey('fk-probation_case-template', '{{%probation_case}}', 'template_id', '{{%probation_template}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%probation_round}}', array_merge([
            'id' => $this->primaryKey(),
            'case_id' => $this->integer()->notNull(),
            'month_no' => $this->tinyInteger()->notNull(),
            'due_date' => $this->date()->notNull(),
            'status' => $this->string(40)->notNull()->defaultValue('waiting_self'),
            'opened_at' => $this->dateTime()->null(),
            'completed_at' => $this->dateTime()->null(),
        ], $this->audit()));
        $this->createIndex('uq-probation_round-case-month', '{{%probation_round}}', ['case_id', 'month_no'], true);
        $this->createIndex('idx-probation_round-status-due', '{{%probation_round}}', ['status', 'due_date']);
        $this->addForeignKey('fk-probation_round-case', '{{%probation_round}}', 'case_id', '{{%probation_case}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%probation_evaluation}}', array_merge([
            'id' => $this->primaryKey(),
            'round_id' => $this->integer()->notNull(),
            'evaluator_employee_id' => $this->integer()->notNull(),
            'role' => $this->string(20)->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('pending'),
            'total_score' => $this->decimal(10, 2)->null(),
            'max_score' => $this->decimal(10, 2)->null(),
            'percent_score' => $this->decimal(6, 2)->null(),
            'submitted_at' => $this->dateTime()->null(),
            'reopened_at' => $this->dateTime()->null(),
            'reopen_reason' => $this->text()->null(),
        ], $this->audit()));
        $this->createIndex('uq-probation_evaluation-round-role', '{{%probation_evaluation}}', ['round_id', 'role'], true);
        $this->createIndex('idx-probation_evaluation-evaluator-status', '{{%probation_evaluation}}', ['evaluator_employee_id', 'status']);
        $this->addForeignKey('fk-probation_evaluation-round', '{{%probation_evaluation}}', 'round_id', '{{%probation_round}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-probation_evaluation-evaluator', '{{%probation_evaluation}}', 'evaluator_employee_id', '{{%employees}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%probation_evaluation_score}}', array_merge([
            'id' => $this->primaryKey(),
            'evaluation_id' => $this->integer()->notNull(),
            'template_item_id' => $this->integer()->notNull(),
            'score' => $this->decimal(8, 2)->notNull(),
        ], $this->audit()));
        $this->createIndex('uq-probation_score-evaluation-item', '{{%probation_evaluation_score}}', ['evaluation_id', 'template_item_id'], true);
        $this->addForeignKey('fk-probation_score-evaluation', '{{%probation_evaluation_score}}', 'evaluation_id', '{{%probation_evaluation}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-probation_score-item', '{{%probation_evaluation_score}}', 'template_item_id', '{{%probation_template_item}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%probation_decision}}', array_merge([
            'id' => $this->primaryKey(),
            'case_id' => $this->integer()->notNull()->unique(),
            'average_percent' => $this->decimal(6, 2)->notNull(),
            'threshold_percent' => $this->decimal(6, 2)->notNull()->defaultValue(60),
            'result' => $this->string(20)->notNull(),
            'recommendation' => $this->string(20)->notNull(),
            'summary_comment' => $this->text()->notNull(),
            'decided_by_employee_id' => $this->integer()->notNull(),
            'decided_at' => $this->dateTime()->notNull(),
        ], $this->audit()));
        $this->addForeignKey('fk-probation_decision-case', '{{%probation_decision}}', 'case_id', '{{%probation_case}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-probation_decision-employee', '{{%probation_decision}}', 'decided_by_employee_id', '{{%employees}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%probation_acknowledgement}}', array_merge([
            'id' => $this->primaryKey(),
            'case_id' => $this->integer()->notNull()->unique(),
            'director_employee_id' => $this->integer()->notNull(),
            'acknowledged_at' => $this->dateTime()->notNull(),
        ], $this->audit()));
        $this->addForeignKey('fk-probation_ack-case', '{{%probation_acknowledgement}}', 'case_id', '{{%probation_case}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-probation_ack-director', '{{%probation_acknowledgement}}', 'director_employee_id', '{{%employees}}', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%probation_acknowledgement}}');
        $this->dropTable('{{%probation_decision}}');
        $this->dropTable('{{%probation_evaluation_score}}');
        $this->dropTable('{{%probation_evaluation}}');
        $this->dropTable('{{%probation_round}}');
        $this->dropTable('{{%probation_case}}');
        $this->dropTable('{{%probation_template_item}}');
        $this->dropTable('{{%probation_template}}');
    }
}
