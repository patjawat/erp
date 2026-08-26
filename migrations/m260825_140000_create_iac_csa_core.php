<?php

use yii\db\Migration;

/** Phase 3: CSA analysis source-of-truth, steps, risks, controls and improvement plans. */
class m260825_140000_create_iac_csa_core extends Migration
{
    public function safeUp()
    {
        $audit = ['created_at' => $this->dateTime()->null(), 'updated_at' => $this->dateTime()->null(), 'created_by' => $this->integer()->null(), 'updated_by' => $this->integer()->null()];
        $this->createTable('{{%iac_csa}}', array_merge([
            'id' => $this->primaryKey(), 'ref' => $this->string(64)->notNull(),
            'hospital_id' => $this->integer()->notNull(), 'fiscal_year_id' => $this->integer()->notNull(),
            'org_unit_id' => $this->integer()->notNull(), 'service_profile_id' => $this->integer()->notNull(),
            'process_id' => $this->integer()->notNull(), 'process_version_id' => $this->integer()->notNull(),
            'fiscal_year' => $this->integer()->notNull(), 'revision_no' => $this->integer()->notNull()->defaultValue(1),
            'process_name_snapshot' => $this->string(500)->notNull(), 'objective_snapshot' => $this->text()->null(),
            'status' => $this->string(30)->notNull()->defaultValue('draft'),
            'author_confirmed_at' => $this->dateTime()->null(), 'author_confirmed_by' => $this->integer()->null(),
            'head_approved_at' => $this->dateTime()->null(), 'head_approved_by' => $this->integer()->null(),
            'return_note' => $this->text()->null(),
        ], $audit));
        $this->createIndex('ux-iac-csa-ref', '{{%iac_csa}}', 'ref', true);
        $this->createIndex('ux-iac-csa-process-year', '{{%iac_csa}}', ['hospital_id', 'process_id', 'fiscal_year'], true);
        $this->createIndex('idx-iac-csa-scope', '{{%iac_csa}}', ['hospital_id', 'fiscal_year_id', 'org_unit_id', 'status']);
        $this->addForeignKey('fk-iac-csa-hospital', '{{%iac_csa}}', 'hospital_id', '{{%iac_hospital}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-iac-csa-fiscal', '{{%iac_csa}}', 'fiscal_year_id', '{{%iac_fiscal_year}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-iac-csa-profile', '{{%iac_csa}}', 'service_profile_id', '{{%service_profile}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk-iac-csa-process', '{{%iac_csa}}', 'process_id', '{{%iac_service_process}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk-iac-csa-version', '{{%iac_csa}}', 'process_version_id', '{{%iac_service_process_version}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%iac_csa_step}}', array_merge([
            'id' => $this->primaryKey(), 'ref' => $this->string(64)->notNull(), 'csa_id' => $this->integer()->notNull(),
            'sequence' => $this->integer()->notNull(), 'name' => $this->string(500)->notNull(), 'detail' => $this->text()->null(),
            'responsible' => $this->string(500)->null(), 'duration' => $this->string(255)->null(),
            'control_point' => $this->text()->null(), 'has_risk' => $this->boolean()->notNull()->defaultValue(false),
        ], $audit));
        $this->createIndex('ux-iac-csa-step-ref', '{{%iac_csa_step}}', 'ref', true);
        $this->createIndex('idx-iac-csa-step-order', '{{%iac_csa_step}}', ['csa_id', 'sequence']);
        $this->addForeignKey('fk-iac-csa-step-csa', '{{%iac_csa_step}}', 'csa_id', '{{%iac_csa}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%iac_csa_risk}}', array_merge([
            'id' => $this->primaryKey(), 'ref' => $this->string(64)->notNull(), 'csa_id' => $this->integer()->notNull(),
            'step_id' => $this->integer()->notNull(), 'sequence' => $this->integer()->notNull(),
            'name' => $this->string(500)->notNull(), 'cause' => $this->text()->null(), 'impact' => $this->text()->null(),
            'likelihood_score' => $this->tinyInteger()->null(), 'impact_score' => $this->tinyInteger()->null(),
            'residual_risk' => $this->text()->null(), 'adequacy' => $this->string(30)->notNull()->defaultValue('not_assessed'),
        ], $audit));
        $this->createIndex('ux-iac-csa-risk-ref', '{{%iac_csa_risk}}', 'ref', true);
        $this->createIndex('idx-iac-csa-risk-order', '{{%iac_csa_risk}}', ['step_id', 'sequence']);
        $this->addForeignKey('fk-iac-csa-risk-csa', '{{%iac_csa_risk}}', 'csa_id', '{{%iac_csa}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-iac-csa-risk-step', '{{%iac_csa_risk}}', 'step_id', '{{%iac_csa_step}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%iac_risk_control}}', array_merge([
            'id' => $this->primaryKey(), 'ref' => $this->string(64)->notNull(), 'risk_id' => $this->integer()->notNull(),
            'sequence' => $this->integer()->notNull(), 'description' => $this->text()->notNull(),
            'control_type' => $this->string(30)->null(), 'responsible' => $this->string(500)->null(),
        ], $audit));
        $this->createIndex('idx-iac-control-risk', '{{%iac_risk_control}}', ['risk_id', 'sequence']);
        $this->addForeignKey('fk-iac-control-risk', '{{%iac_risk_control}}', 'risk_id', '{{%iac_csa_risk}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%iac_control_assessment}}', array_merge([
            'id' => $this->primaryKey(), 'ref' => $this->string(64)->notNull(), 'risk_id' => $this->integer()->notNull(),
            'adequacy' => $this->string(30)->notNull(), 'reason' => $this->text()->null(),
            'assessed_at' => $this->dateTime()->null(), 'assessed_by' => $this->integer()->null(),
        ], $audit));
        $this->createIndex('ux-iac-assessment-risk', '{{%iac_control_assessment}}', 'risk_id', true);
        $this->addForeignKey('fk-iac-assessment-risk', '{{%iac_control_assessment}}', 'risk_id', '{{%iac_csa_risk}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%iac_improvement_plan}}', array_merge([
            'id' => $this->primaryKey(), 'ref' => $this->string(64)->notNull(), 'risk_id' => $this->integer()->notNull(),
            'action' => $this->text()->notNull(), 'responsible' => $this->string(500)->notNull(),
            'due_date' => $this->date()->notNull(), 'status' => $this->string(30)->notNull()->defaultValue('planned'),
        ], $audit));
        $this->createIndex('idx-iac-plan-risk', '{{%iac_improvement_plan}}', ['risk_id', 'status', 'due_date']);
        $this->addForeignKey('fk-iac-plan-risk', '{{%iac_improvement_plan}}', 'risk_id', '{{%iac_csa_risk}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%iac_improvement_plan}}'); $this->dropTable('{{%iac_control_assessment}}');
        $this->dropTable('{{%iac_risk_control}}'); $this->dropTable('{{%iac_csa_risk}}');
        $this->dropTable('{{%iac_csa_step}}'); $this->dropTable('{{%iac_csa}}');
    }
}
