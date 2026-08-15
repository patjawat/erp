<?php

use yii\db\Migration;

/**
 * Finance Inbox stores immutable snapshots from source systems.
 * It does not alter source records and does not create accounting entries.
 */
class m260815_140000_create_finance_inbox_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%finance_inbox}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(64)->notNull()->comment('Token for record files and audit references'),
            'source_system' => $this->string(50)->notNull(),
            'source_type' => $this->string(50)->notNull(),
            'source_id' => $this->string(100)->notNull(),
            'source_version' => $this->integer()->notNull()->defaultValue(1),
            'source_document_no' => $this->string(100)->null(),
            'vendor_id' => $this->integer()->null(),
            'vendor_name_snapshot' => $this->string(255)->null(),
            'document_date' => $this->date()->null(),
            'amount' => $this->decimal(15, 2)->null(),
            'status' => $this->string(30)->notNull()->defaultValue('pending_review'),
            'payload_json' => $this->json()->notNull(),
            'validation_json' => $this->json()->null(),
            'received_at' => $this->dateTime()->notNull(),
            'reviewed_at' => $this->dateTime()->null(),
            'reviewed_by' => $this->integer()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);

        $this->createIndex('uq-finance_inbox-ref', '{{%finance_inbox}}', 'ref', true);
        $this->createIndex(
            'uq-finance_inbox-source-version',
            '{{%finance_inbox}}',
            ['source_system', 'source_type', 'source_id', 'source_version'],
            true
        );
        $this->createIndex('idx-finance_inbox-status-received', '{{%finance_inbox}}', ['status', 'received_at']);
        $this->createIndex('idx-finance_inbox-vendor', '{{%finance_inbox}}', 'vendor_id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%finance_inbox}}');
    }
}
