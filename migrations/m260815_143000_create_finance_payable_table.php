<?php

use yii\db\Migration;

/** Draft creditor register created only from accepted Finance Inbox records. */
class m260815_143000_create_finance_payable_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%finance_payable}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(64)->notNull(),
            'payable_no' => $this->string(50)->null(),
            'finance_inbox_id' => $this->integer()->notNull(),
            'vendor_id' => $this->integer()->notNull()->comment('categorise.id where name=vendor'),
            'vendor_code_snapshot' => $this->string(100)->null(),
            'vendor_name_snapshot' => $this->string(255)->notNull(),
            'invoice_no' => $this->string(100)->notNull(),
            'invoice_date' => $this->date()->notNull(),
            'billing_date' => $this->date()->notNull(),
            'due_date_basis' => $this->string(30)->notNull()->defaultValue('billing_date'),
            'credit_days' => $this->integer()->notNull()->defaultValue(0),
            'due_date' => $this->date()->notNull(),
            'gross_amount' => $this->decimal(15, 2)->notNull(),
            'vat_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'withholding_tax_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'net_amount' => $this->decimal(15, 2)->notNull(),
            'source_document_no' => $this->string(100)->null(),
            'status' => $this->string(30)->notNull()->defaultValue('draft'),
            'note' => $this->text()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);

        $this->createIndex('uq-finance_payable-ref', '{{%finance_payable}}', 'ref', true);
        $this->createIndex('uq-finance_payable-no', '{{%finance_payable}}', 'payable_no', true);
        $this->createIndex('uq-finance_payable-inbox', '{{%finance_payable}}', 'finance_inbox_id', true);
        $this->createIndex(
            'uq-finance_payable-vendor-invoice',
            '{{%finance_payable}}',
            ['vendor_id', 'invoice_no'],
            true
        );
        $this->createIndex('idx-finance_payable-status-due', '{{%finance_payable}}', ['status', 'due_date']);
        $this->createIndex('idx-finance_payable-vendor', '{{%finance_payable}}', 'vendor_id');
        $this->addForeignKey(
            'fk-finance_payable-inbox',
            '{{%finance_payable}}',
            'finance_inbox_id',
            '{{%finance_inbox}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-finance_payable-inbox', '{{%finance_payable}}');
        $this->dropTable('{{%finance_payable}}');
    }
}
