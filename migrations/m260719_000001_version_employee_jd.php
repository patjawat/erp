<?php

use yii\db\Migration;

/** Adds effective-dated employee JD revisions and structured block snapshots. */
class m260719_000001_version_employee_jd extends Migration
{
    public function safeUp()
    {
        $this->dropIndex('uq-jd_employee-emp_id', '{{%jd_employee}}');

        $this->addColumn('{{%jd_employee}}', 'revision_no', $this->integer()->notNull()->defaultValue(1)->after('template_id'));
        $this->addColumn('{{%jd_employee}}', 'status', $this->string(20)->notNull()->defaultValue('draft')->after('revision_no'));
        $this->addColumn('{{%jd_employee}}', 'effective_from', $this->date()->null()->after('status'));
        $this->addColumn('{{%jd_employee}}', 'effective_to', $this->date()->null()->after('effective_from'));
        $this->addColumn('{{%jd_employee}}', 'supersedes_id', $this->integer()->null()->after('effective_to'));
        $this->addColumn('{{%jd_employee}}', 'position_code', $this->string(64)->null()->after('supersedes_id'));
        $this->addColumn('{{%jd_employee}}', 'position_title', $this->string(255)->null()->after('position_code'));
        $this->addColumn('{{%jd_employee}}', 'department_code', $this->string(64)->null()->after('position_title'));
        $this->addColumn('{{%jd_employee}}', 'department_title', $this->string(255)->null()->after('department_code'));
        $this->addColumn('{{%jd_employee}}', 'approved_by', $this->integer()->null()->after('department_title'));
        $this->addColumn('{{%jd_employee}}', 'approved_at', $this->dateTime()->null()->after('approved_by'));
        $this->addColumn('{{%jd_employee}}', 'created_by', $this->integer()->null()->after('updated_at'));
        $this->addColumn('{{%jd_employee}}', 'updated_by', $this->integer()->null()->after('created_by'));

        $this->createIndex('idx-jd_employee-current', '{{%jd_employee}}', ['emp_id', 'status', 'effective_from']);
        $this->createIndex('idx-jd_employee-supersedes', '{{%jd_employee}}', 'supersedes_id');
        $this->addForeignKey('fk-jd_employee-supersedes', '{{%jd_employee}}', 'supersedes_id', '{{%jd_employee}}', 'id', 'SET NULL', 'CASCADE');

        $this->addColumn('{{%jd_employee_section}}', 'section_code', $this->string(40)->null()->after('jd_employee_id'));
        $this->addColumn('{{%jd_employee_section}}', 'block_type', $this->string(30)->notNull()->defaultValue('prose')->after('title'));
        $this->addColumn('{{%jd_employee_section}}', 'data_json', $this->text()->null()->after('content'));
        $this->createIndex('idx-jd_employee_section-code', '{{%jd_employee_section}}', ['jd_employee_id', 'section_code']);

        $this->update('{{%jd_employee}}', [
            'status' => 'active',
            'effective_from' => new \yii\db\Expression('DATE(created_at)'),
        ]);
    }

    public function safeDown()
    {
        $this->dropIndex('idx-jd_employee_section-code', '{{%jd_employee_section}}');
        foreach (['data_json', 'block_type', 'section_code'] as $column) {
            $this->dropColumn('{{%jd_employee_section}}', $column);
        }

        $this->dropForeignKey('fk-jd_employee-supersedes', '{{%jd_employee}}');
        $this->dropIndex('idx-jd_employee-supersedes', '{{%jd_employee}}');
        $this->dropIndex('idx-jd_employee-current', '{{%jd_employee}}');
        foreach (['updated_by', 'created_by', 'approved_at', 'approved_by', 'department_title', 'department_code', 'position_title', 'position_code', 'supersedes_id', 'effective_to', 'effective_from', 'status', 'revision_no'] as $column) {
            $this->dropColumn('{{%jd_employee}}', $column);
        }
        $this->createIndex('uq-jd_employee-emp_id', '{{%jd_employee}}', 'emp_id', true);
    }
}
