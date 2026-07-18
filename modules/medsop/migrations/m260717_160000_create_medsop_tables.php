<?php

use yii\db\Migration;

class m260717_160000_create_medsop_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%medsop_document}}', [
            'id' => $this->bigPrimaryKey(),
            'document_no' => $this->string(50)->notNull(),
            'title' => $this->string(255)->notNull(),
            'document_type' => $this->string(10)->notNull(),
            'organization_id' => $this->integer()->notNull(),
            'objective' => $this->text()->notNull(),
            'scope' => $this->text(),
            'status' => $this->string(20)->notNull()->defaultValue('DRAFT'),
            'current_revision' => $this->integer()->notNull()->defaultValue(1),
            'created_emp_id' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'published_by' => $this->integer(),
            'published_at' => $this->dateTime(),
            'deleted_by' => $this->integer(),
            'deleted_at' => $this->dateTime(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->createIndex('ux_medsop_document_no', '{{%medsop_document}}', 'document_no', true);
        $this->createIndex('ix_medsop_document_org_status', '{{%medsop_document}}', ['organization_id', 'status']);
        $this->createIndex('ix_medsop_document_created_emp', '{{%medsop_document}}', 'created_emp_id');

        $this->createTable('{{%medsop_document_step}}', [
            'id' => $this->bigPrimaryKey(),
            'document_id' => $this->bigInteger()->notNull(),
            'step_order' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'caution' => $this->text(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->createIndex('ux_medsop_step_order', '{{%medsop_document_step}}', ['document_id', 'step_order'], true);
        $this->addForeignKey('fk_medsop_step_document', '{{%medsop_document_step}}', 'document_id', '{{%medsop_document}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%medsop_document_revision}}', [
            'id' => $this->bigPrimaryKey(),
            'document_id' => $this->bigInteger()->notNull(),
            'revision_no' => $this->integer()->notNull(),
            'snapshot_json' => $this->text()->notNull(),
            'file_ref' => $this->string(100)->notNull(),
            'approval_status' => $this->string(20)->notNull()->defaultValue('DRAFT'),
            'created_emp_id' => $this->integer(),
            'created_by' => $this->integer(),
            'created_at' => $this->dateTime()->notNull(),
            'approved_at' => $this->dateTime(),
        ], $tableOptions);
        $this->createIndex('ux_medsop_revision_no', '{{%medsop_document_revision}}', ['document_id', 'revision_no'], true);
        $this->createIndex('ux_medsop_revision_file_ref', '{{%medsop_document_revision}}', 'file_ref', true);
        $this->addForeignKey('fk_medsop_revision_document', '{{%medsop_document_revision}}', 'document_id', '{{%medsop_document}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%medsop_organization_setting}}', [
            'organization_id' => $this->integer()->notNull(),
            'active' => $this->boolean()->notNull()->defaultValue(true),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addPrimaryKey('pk_medsop_organization_setting', '{{%medsop_organization_setting}}', 'organization_id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%medsop_organization_setting}}');
        $this->dropForeignKey('fk_medsop_revision_document', '{{%medsop_document_revision}}');
        $this->dropTable('{{%medsop_document_revision}}');
        $this->dropForeignKey('fk_medsop_step_document', '{{%medsop_document_step}}');
        $this->dropTable('{{%medsop_document_step}}');
        $this->dropTable('{{%medsop_document}}');
    }
}
