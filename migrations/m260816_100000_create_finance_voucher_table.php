<?php

use yii\db\Migration;

/** Finance draft created from an accounting-approved payable. */
class m260816_100000_create_finance_voucher_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%finance_voucher}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(64)->notNull(),
            'voucher_no' => $this->string(50)->null(),
            'finance_payable_id' => $this->integer()->notNull(),
            'vendor_id' => $this->integer()->notNull(),
            'vendor_code_snapshot' => $this->string(100)->null(),
            'vendor_name_snapshot' => $this->string(255)->notNull(),
            'payable_no_snapshot' => $this->string(50)->notNull(),
            'invoice_no_snapshot' => $this->string(100)->notNull(),
            'gross_amount' => $this->decimal(15, 2)->notNull(),
            'withholding_tax_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'net_amount' => $this->decimal(15, 2)->notNull(),
            'funding_source' => $this->string(255)->notNull(),
            'requested_payment_date' => $this->date()->notNull(),
            'payment_method' => $this->string(30)->notNull(),
            'status' => $this->string(30)->notNull()->defaultValue('draft'),
            'note' => $this->text()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);

        $this->createIndex('uq-finance_voucher-ref', '{{%finance_voucher}}', 'ref', true);
        $this->createIndex('uq-finance_voucher-no', '{{%finance_voucher}}', 'voucher_no', true);
        $this->createIndex('uq-finance_voucher-payable', '{{%finance_voucher}}', 'finance_payable_id', true);
        $this->createIndex('idx-finance_voucher-status-date', '{{%finance_voucher}}', ['status', 'requested_payment_date']);
        $this->addForeignKey('fk-finance_voucher-payable', '{{%finance_voucher}}', 'finance_payable_id', '{{%finance_payable}}', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-finance_voucher-payable', '{{%finance_voucher}}');
        $this->dropTable('{{%finance_voucher}}');
    }
}
