<?php

use yii\db\Migration;

/** Approval workflow and immutable audit trail for the creditor register. */
class m260815_144000_create_finance_payable_review_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%finance_payable}}', 'submitted_at', $this->dateTime()->null()->after('note'));
        $this->addColumn('{{%finance_payable}}', 'submitted_by', $this->integer()->null()->after('submitted_at'));
        $this->addColumn('{{%finance_payable}}', 'approved_at', $this->dateTime()->null()->after('submitted_by'));
        $this->addColumn('{{%finance_payable}}', 'approved_by', $this->integer()->null()->after('approved_at'));

        $this->createTable('{{%finance_payable_review}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(64)->notNull(),
            'finance_payable_id' => $this->integer()->notNull(),
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
        $this->createIndex('uq-finance_payable_review-ref', '{{%finance_payable_review}}', 'ref', true);
        $this->createIndex('idx-finance_payable_review-payable-created', '{{%finance_payable_review}}', ['finance_payable_id', 'created_at']);
        $this->addForeignKey(
            'fk-finance_payable_review-payable',
            '{{%finance_payable_review}}',
            'finance_payable_id',
            '{{%finance_payable}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-finance_payable_review-payable', '{{%finance_payable_review}}');
        $this->dropTable('{{%finance_payable_review}}');
        $this->dropColumn('{{%finance_payable}}', 'approved_by');
        $this->dropColumn('{{%finance_payable}}', 'approved_at');
        $this->dropColumn('{{%finance_payable}}', 'submitted_by');
        $this->dropColumn('{{%finance_payable}}', 'submitted_at');
    }
}
