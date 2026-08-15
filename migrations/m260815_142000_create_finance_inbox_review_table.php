<?php

use yii\db\Migration;

/** Immutable audit trail for every Finance Inbox review decision. */
class m260815_142000_create_finance_inbox_review_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%finance_inbox_review}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(64)->notNull(),
            'finance_inbox_id' => $this->integer()->notNull(),
            'decision' => $this->string(30)->notNull(),
            'from_status' => $this->string(30)->notNull(),
            'to_status' => $this->string(30)->notNull(),
            'note' => $this->text()->null(),
            'metadata_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);

        $this->createIndex('uq-finance_inbox_review-ref', '{{%finance_inbox_review}}', 'ref', true);
        $this->createIndex(
            'idx-finance_inbox_review-inbox-created',
            '{{%finance_inbox_review}}',
            ['finance_inbox_id', 'created_at']
        );
        $this->addForeignKey(
            'fk-finance_inbox_review-inbox',
            '{{%finance_inbox_review}}',
            'finance_inbox_id',
            '{{%finance_inbox}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-finance_inbox_review-inbox', '{{%finance_inbox_review}}');
        $this->dropTable('{{%finance_inbox_review}}');
    }
}
