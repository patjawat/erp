<?php

declare(strict_types=1);

use yii\db\Migration;

class m260722_000001_create_ai_tables extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%ai_conversations}}', [
            'id' => $this->char(36)->notNull(),
            'user_id' => $this->integer()->null(),
            'title' => $this->string(255)->notNull(),
            'provider' => $this->string(64)->notNull(),
            'model' => $this->string(128)->null(),
            'status' => $this->string(32)->notNull()->defaultValue('active'),
            'metadata_json' => $this->text()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addPrimaryKey('pk_ai_conversations', '{{%ai_conversations}}', 'id');
        $this->createIndex('idx_ai_conv_user_status', '{{%ai_conversations}}', ['user_id', 'status']);
        $this->createIndex('idx_ai_conv_updated', '{{%ai_conversations}}', 'updated_at');

        $this->createTable('{{%ai_messages}}', [
            'id' => $this->char(36)->notNull(),
            'conversation_id' => $this->char(36)->notNull(),
            'role' => $this->string(32)->notNull(),
            'content' => $this->text()->null(),
            'tool_name' => $this->string(128)->null(),
            'tool_call_id' => $this->string(128)->null(),
            'provider' => $this->string(64)->null(),
            'metadata_json' => $this->text()->null(),
            'token_count' => $this->integer()->null(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addPrimaryKey('pk_ai_messages', '{{%ai_messages}}', 'id');
        $this->createIndex('idx_ai_msg_conversation', '{{%ai_messages}}', ['conversation_id', 'created_at']);
        $this->createIndex('idx_ai_msg_role', '{{%ai_messages}}', 'role');
        $this->addForeignKey(
            'fk_ai_msg_conversation',
            '{{%ai_messages}}',
            'conversation_id',
            '{{%ai_conversations}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createTable('{{%ai_datasets}}', [
            'id' => $this->char(36)->notNull(),
            'code' => $this->string(128)->notNull(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text()->null(),
            'view_name' => $this->string(128)->notNull(),
            'permission_name' => $this->string(128)->notNull(),
            'max_rows' => $this->integer()->notNull()->defaultValue(100),
            'is_exportable' => $this->boolean()->notNull()->defaultValue(true),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'metadata_json' => $this->text()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addPrimaryKey('pk_ai_datasets', '{{%ai_datasets}}', 'id');
        $this->createIndex('ux_ai_dataset_code', '{{%ai_datasets}}', 'code', true);
        $this->createIndex('idx_ai_dataset_active', '{{%ai_datasets}}', 'is_active');

        $this->createTable('{{%ai_dataset_fields}}', [
            'id' => $this->char(36)->notNull(),
            'dataset_id' => $this->char(36)->notNull(),
            'field_name' => $this->string(128)->notNull(),
            'label' => $this->string(255)->notNull(),
            'data_type' => $this->string(32)->notNull()->defaultValue('string'),
            'is_filterable' => $this->boolean()->notNull()->defaultValue(false),
            'is_sortable' => $this->boolean()->notNull()->defaultValue(false),
            'is_selectable' => $this->boolean()->notNull()->defaultValue(true),
            'allowed_operators' => $this->string(255)->null(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addPrimaryKey('pk_ai_dataset_fields', '{{%ai_dataset_fields}}', 'id');
        $this->createIndex('ux_ai_field_dataset_name', '{{%ai_dataset_fields}}', ['dataset_id', 'field_name'], true);
        $this->addForeignKey(
            'fk_ai_field_dataset',
            '{{%ai_dataset_fields}}',
            'dataset_id',
            '{{%ai_datasets}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createTable('{{%ai_audit_logs}}', [
            'id' => $this->char(36)->notNull(),
            'user_id' => $this->integer()->null(),
            'conversation_id' => $this->char(36)->null(),
            'message_id' => $this->char(36)->null(),
            'provider' => $this->string(64)->null(),
            'dataset_code' => $this->string(128)->null(),
            'tool_name' => $this->string(128)->null(),
            'action' => $this->string(64)->notNull(),
            'status' => $this->string(64)->notNull(),
            'row_count' => $this->integer()->notNull()->defaultValue(0),
            'duration_ms' => $this->integer()->notNull()->defaultValue(0),
            'error_message' => $this->text()->null(),
            'request_json' => $this->text()->null(),
            'response_json' => $this->text()->null(),
            'ip_address' => $this->string(45)->null(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addPrimaryKey('pk_ai_audit_logs', '{{%ai_audit_logs}}', 'id');
        $this->createIndex('idx_ai_audit_user_created', '{{%ai_audit_logs}}', ['user_id', 'created_at']);
        $this->createIndex('idx_ai_audit_dataset', '{{%ai_audit_logs}}', 'dataset_code');
        $this->createIndex('idx_ai_audit_tool_status', '{{%ai_audit_logs}}', ['tool_name', 'status']);
        $this->addForeignKey(
            'fk_ai_audit_conversation',
            '{{%ai_audit_logs}}',
            'conversation_id',
            '{{%ai_conversations}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_ai_audit_message',
            '{{%ai_audit_logs}}',
            'message_id',
            '{{%ai_messages}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_ai_audit_message', '{{%ai_audit_logs}}');
        $this->dropForeignKey('fk_ai_audit_conversation', '{{%ai_audit_logs}}');
        $this->dropTable('{{%ai_audit_logs}}');

        $this->dropForeignKey('fk_ai_field_dataset', '{{%ai_dataset_fields}}');
        $this->dropTable('{{%ai_dataset_fields}}');
        $this->dropTable('{{%ai_datasets}}');

        $this->dropForeignKey('fk_ai_msg_conversation', '{{%ai_messages}}');
        $this->dropTable('{{%ai_messages}}');
        $this->dropTable('{{%ai_conversations}}');
    }
}
