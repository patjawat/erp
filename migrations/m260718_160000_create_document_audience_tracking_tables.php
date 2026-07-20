<?php

use yii\db\Migration;

class m260718_160000_create_document_audience_tracking_tables extends Migration
{
    public function safeUp()
    {
        $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%medsop_document_audience}}', [
            'id' => $this->bigPrimaryKey(),
            'document_id' => $this->bigInteger()->notNull(),
            'audience_type' => $this->string(20)->notNull(),
            'audience_id' => $this->integer()->notNull(),
            'audience_version_id' => $this->integer()->notNull()->defaultValue(0),
            'include_children' => $this->boolean()->notNull()->defaultValue(false),
            'required' => $this->boolean()->notNull()->defaultValue(true),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->dateTime()->notNull(),
        ], $options);
        $this->createIndex('ux_medsop_audience_target', '{{%medsop_document_audience}}', [
            'document_id', 'audience_type', 'audience_id', 'audience_version_id',
        ], true);
        $this->createIndex('ix_medsop_audience_document', '{{%medsop_document_audience}}', 'document_id');
        $this->addForeignKey(
            'fk_medsop_audience_document',
            '{{%medsop_document_audience}}',
            'document_id',
            '{{%medsop_document}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createTable('{{%medsop_document_assignment}}', [
            'id' => $this->bigPrimaryKey(),
            'document_id' => $this->bigInteger()->notNull(),
            'revision_no' => $this->integer()->notNull(),
            'employee_id' => $this->integer()->notNull(),
            'required' => $this->boolean()->notNull()->defaultValue(true),
            'source_json' => $this->text()->notNull(),
            'due_date' => $this->date()->null(),
            'status' => $this->string(20)->notNull()->defaultValue('UNREAD'),
            'assigned_at' => $this->dateTime()->notNull(),
            'assigned_by' => $this->integer()->null(),
            'first_opened_at' => $this->dateTime()->null(),
            'last_opened_at' => $this->dateTime()->null(),
            'open_count' => $this->integer()->notNull()->defaultValue(0),
            'acknowledged_at' => $this->dateTime()->null(),
            'acknowledged_by' => $this->integer()->null(),
            'acknowledged_ip' => $this->string(45)->null(),
            'acknowledged_user_agent' => $this->string(500)->null(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $options);
        $this->createIndex('ux_medsop_assignment_recipient', '{{%medsop_document_assignment}}', [
            'document_id', 'revision_no', 'employee_id',
        ], true);
        $this->createIndex('ix_medsop_assignment_employee_status', '{{%medsop_document_assignment}}', [
            'employee_id', 'status', 'due_date',
        ]);
        $this->createIndex('ix_medsop_assignment_document_revision', '{{%medsop_document_assignment}}', [
            'document_id', 'revision_no',
        ]);
        $this->addForeignKey(
            'fk_medsop_assignment_document',
            '{{%medsop_document_assignment}}',
            'document_id',
            '{{%medsop_document}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createTable('{{%medsop_document_read_log}}', [
            'id' => $this->bigPrimaryKey(),
            'assignment_id' => $this->bigInteger()->notNull(),
            'document_id' => $this->bigInteger()->notNull(),
            'revision_no' => $this->integer()->notNull(),
            'employee_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->null(),
            'event_type' => $this->string(30)->notNull(),
            'event_at' => $this->dateTime()->notNull(),
            'ip_address' => $this->string(45)->null(),
            'user_agent' => $this->string(500)->null(),
            'metadata_json' => $this->text()->null(),
        ], $options);
        $this->createIndex('ix_medsop_read_log_assignment_event', '{{%medsop_document_read_log}}', [
            'assignment_id', 'event_type', 'event_at',
        ]);
        $this->createIndex('ix_medsop_read_log_document_revision', '{{%medsop_document_read_log}}', [
            'document_id', 'revision_no', 'event_type',
        ]);
        $this->addForeignKey(
            'fk_medsop_read_log_assignment',
            '{{%medsop_document_read_log}}',
            'assignment_id',
            '{{%medsop_document_assignment}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_medsop_read_log_document',
            '{{%medsop_document_read_log}}',
            'document_id',
            '{{%medsop_document}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%medsop_document_read_log}}');
        $this->dropTable('{{%medsop_document_assignment}}');
        $this->dropTable('{{%medsop_document_audience}}');
    }
}
