<?php

use yii\db\Migration;

/**
 * Creates the profession-neutral Training Roadmap engine.
 */
class m260725_000001_create_training_roadmap_tables extends Migration
{
    public function safeUp()
    {
        $audit = function () {
            return [
                'ref' => $this->string(64)->notNull(),
                'created_at' => $this->dateTime()->null(),
                'updated_at' => $this->dateTime()->null(),
                'created_by' => $this->integer()->null(),
                'updated_by' => $this->integer()->null(),
            ];
        };

        $this->createTable('{{%training_roadmap}}', array_merge([
            'id' => $this->primaryKey(),
            'code' => $this->string(64)->notNull(),
            'title' => $this->string(255)->notNull(),
            'roadmap_type' => $this->string(40)->notNull()->defaultValue('professional'),
            'version_no' => $this->integer()->notNull()->defaultValue(1),
            'duration_value' => $this->integer()->notNull()->defaultValue(90),
            'duration_unit' => $this->string(20)->notNull()->defaultValue('day'),
            'description' => $this->text()->null(),
            'target_json' => $this->text()->null(),
            'status' => $this->string(20)->notNull()->defaultValue('draft'),
            'effective_from' => $this->date()->null(),
            'effective_to' => $this->date()->null(),
            'supersedes_id' => $this->integer()->null(),
            'approved_by' => $this->integer()->null(),
            'approved_at' => $this->dateTime()->null(),
        ], $audit()));
        $this->createIndex('uq-training_roadmap-code-version', '{{%training_roadmap}}', ['code', 'version_no'], true);
        $this->createIndex('idx-training_roadmap-status', '{{%training_roadmap}}', ['status', 'effective_from']);
        $this->addForeignKey('fk-training_roadmap-supersedes', '{{%training_roadmap}}', 'supersedes_id', '{{%training_roadmap}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%training_roadmap_phase}}', array_merge([
            'id' => $this->primaryKey(),
            'roadmap_id' => $this->integer()->notNull(),
            'sequence' => $this->integer()->notNull()->defaultValue(1),
            'title' => $this->string(255)->notNull(),
            'period_label' => $this->string(100)->null(),
            'start_offset' => $this->integer()->notNull()->defaultValue(0),
            'end_offset' => $this->integer()->null(),
            'offset_unit' => $this->string(20)->notNull()->defaultValue('day'),
            'description' => $this->text()->null(),
            'color_role' => $this->string(20)->notNull()->defaultValue('primary'),
        ], $audit()));
        $this->createIndex('idx-training_phase-roadmap', '{{%training_roadmap_phase}}', ['roadmap_id', 'sequence']);
        $this->addForeignKey('fk-training_phase-roadmap', '{{%training_roadmap_phase}}', 'roadmap_id', '{{%training_roadmap}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%training_roadmap_activity}}', array_merge([
            'id' => $this->primaryKey(),
            'phase_id' => $this->integer()->notNull(),
            'sequence' => $this->integer()->notNull()->defaultValue(1),
            'title' => $this->string(255)->notNull(),
            'activity_type' => $this->string(40)->notNull()->defaultValue('practice'),
            'competency_code' => $this->string(100)->null(),
            'competency_level' => $this->integer()->null(),
            'development_method' => $this->string(100)->null(),
            'requirement_type' => $this->string(30)->notNull()->defaultValue('pass_fail'),
            'target_value' => $this->decimal(10, 2)->null(),
            'is_required' => $this->boolean()->notNull()->defaultValue(true),
            'evidence_required' => $this->boolean()->notNull()->defaultValue(false),
            'description' => $this->text()->null(),
            'checklist_json' => $this->text()->null(),
        ], $audit()));
        $this->createIndex('idx-training_activity-phase', '{{%training_roadmap_activity}}', ['phase_id', 'sequence']);
        $this->addForeignKey('fk-training_activity-phase', '{{%training_roadmap_activity}}', 'phase_id', '{{%training_roadmap_phase}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%training_roadmap_milestone}}', array_merge([
            'id' => $this->primaryKey(),
            'roadmap_id' => $this->integer()->notNull(),
            'phase_id' => $this->integer()->null(),
            'sequence' => $this->integer()->notNull()->defaultValue(1),
            'title' => $this->string(255)->notNull(),
            'due_offset' => $this->integer()->notNull()->defaultValue(0),
            'offset_unit' => $this->string(20)->notNull()->defaultValue('day'),
            'criteria_text' => $this->text()->null(),
            'minimum_score' => $this->decimal(5, 2)->null(),
            'requires_signoff' => $this->boolean()->notNull()->defaultValue(true),
        ], $audit()));
        $this->createIndex('idx-training_milestone-roadmap', '{{%training_roadmap_milestone}}', ['roadmap_id', 'sequence']);
        $this->addForeignKey('fk-training_milestone-roadmap', '{{%training_roadmap_milestone}}', 'roadmap_id', '{{%training_roadmap}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-training_milestone-phase', '{{%training_roadmap_milestone}}', 'phase_id', '{{%training_roadmap_phase}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%employee_training_plan}}', array_merge([
            'id' => $this->primaryKey(),
            'emp_id' => $this->integer()->notNull(),
            'roadmap_id' => $this->integer()->notNull(),
            'roadmap_snapshot_json' => $this->text()->null(),
            'start_date' => $this->date()->notNull(),
            'target_end_date' => $this->date()->null(),
            'actual_end_date' => $this->date()->null(),
            'mentor_emp_id' => $this->integer()->null(),
            'assessor_emp_id' => $this->integer()->null(),
            'status' => $this->string(30)->notNull()->defaultValue('assigned'),
            'progress_percent' => $this->decimal(5, 2)->notNull()->defaultValue(0),
            'assigned_by' => $this->integer()->null(),
            'assigned_at' => $this->dateTime()->null(),
            'completed_at' => $this->dateTime()->null(),
            'note' => $this->text()->null(),
        ], $audit()));
        $this->createIndex('idx-employee_training_plan-emp', '{{%employee_training_plan}}', ['emp_id', 'status']);
        $this->createIndex('idx-employee_training_plan-roadmap', '{{%employee_training_plan}}', 'roadmap_id');
        $this->addForeignKey('fk-employee_training_plan-roadmap', '{{%employee_training_plan}}', 'roadmap_id', '{{%training_roadmap}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%employee_training_result}}', array_merge([
            'id' => $this->primaryKey(),
            'plan_id' => $this->integer()->notNull(),
            'activity_id' => $this->integer()->notNull(),
            'status' => $this->string(30)->notNull()->defaultValue('pending'),
            'result_value' => $this->decimal(10, 2)->null(),
            'competency_level' => $this->integer()->null(),
            'result_text' => $this->text()->null(),
            'evidence_json' => $this->text()->null(),
            'assessed_by' => $this->integer()->null(),
            'assessed_at' => $this->dateTime()->null(),
        ], $audit()));
        $this->createIndex('uq-employee_training_result-plan-activity', '{{%employee_training_result}}', ['plan_id', 'activity_id'], true);
        $this->addForeignKey('fk-employee_training_result-plan', '{{%employee_training_result}}', 'plan_id', '{{%employee_training_plan}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-employee_training_result-activity', '{{%employee_training_result}}', 'activity_id', '{{%training_roadmap_activity}}', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%employee_training_result}}');
        $this->dropTable('{{%employee_training_plan}}');
        $this->dropTable('{{%training_roadmap_milestone}}');
        $this->dropTable('{{%training_roadmap_activity}}');
        $this->dropTable('{{%training_roadmap_phase}}');
        $this->dropTable('{{%training_roadmap}}');
    }
}
