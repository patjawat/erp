<?php

use yii\db\Migration;

/** Annual posting configuration and balanced journal drafts generated from approved payables. */
class m260916_160000_create_accounting_journal_draft extends Migration
{
    public function safeUp()
    {
        $audit = ['ref' => $this->string(64)->notNull(), 'created_at' => $this->dateTime()->null(), 'updated_at' => $this->dateTime()->null(), 'created_by' => $this->integer()->null(), 'updated_by' => $this->integer()->null()];
        $this->createTable('{{%accounting_fiscal_config}}', array_merge([
            'id' => $this->primaryKey(), 'fiscal_year' => $this->smallInteger()->notNull(),
            'chart_version_id' => $this->integer()->notNull(), 'payable_account_id' => $this->integer()->notNull(),
            'input_vat_account_id' => $this->integer()->null(), 'note' => $this->text()->null(),
        ], $audit));
        $this->createIndex('uq-accounting_fiscal_config-year', '{{%accounting_fiscal_config}}', 'fiscal_year', true);
        $this->createIndex('uq-accounting_fiscal_config-ref', '{{%accounting_fiscal_config}}', 'ref', true);

        $this->createTable('{{%accounting_journal_draft}}', array_merge([
            'id' => $this->primaryKey(), 'source_type' => $this->string(30)->notNull(), 'source_id' => $this->integer()->notNull(),
            'fiscal_year' => $this->smallInteger()->notNull(), 'document_date' => $this->date()->notNull(),
            'document_no' => $this->string(100)->notNull(), 'description' => $this->string(500)->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('draft'),
            'total_debit' => $this->decimal(15, 2)->notNull(), 'total_credit' => $this->decimal(15, 2)->notNull(),
        ], $audit));
        $this->createIndex('uq-accounting_journal_draft-source', '{{%accounting_journal_draft}}', ['source_type', 'source_id'], true);
        $this->createIndex('uq-accounting_journal_draft-ref', '{{%accounting_journal_draft}}', 'ref', true);
        $this->createIndex('idx-accounting_journal_draft-year-status', '{{%accounting_journal_draft}}', ['fiscal_year', 'status']);

        $this->createTable('{{%accounting_journal_line}}', array_merge([
            'id' => $this->primaryKey(), 'journal_id' => $this->integer()->notNull(), 'sequence' => $this->smallInteger()->notNull(),
            'chart_version_id' => $this->integer()->notNull(), 'chart_account_id' => $this->integer()->notNull(),
            'account_code_snapshot' => $this->string(30)->notNull(), 'account_name_snapshot' => $this->string(500)->notNull(),
            'description' => $this->string(500)->null(), 'debit_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'credit_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
        ], $audit));
        $this->createIndex('uq-accounting_journal_line-journal-seq', '{{%accounting_journal_line}}', ['journal_id', 'sequence'], true);
        $this->createIndex('uq-accounting_journal_line-ref', '{{%accounting_journal_line}}', 'ref', true);
        $this->addForeignKey('fk-accounting-journal-line-journal', '{{%accounting_journal_line}}', 'journal_id', '{{%accounting_journal_draft}}', 'id', 'CASCADE', 'CASCADE');
        foreach ([['config-version', 'accounting_fiscal_config', 'chart_version_id', 'accounting_chart_version'], ['config-payable', 'accounting_fiscal_config', 'payable_account_id', 'accounting_chart_account'], ['config-vat', 'accounting_fiscal_config', 'input_vat_account_id', 'accounting_chart_account'], ['line-version', 'accounting_journal_line', 'chart_version_id', 'accounting_chart_version'], ['line-account', 'accounting_journal_line', 'chart_account_id', 'accounting_chart_account']] as [$name, $table, $column, $target]) {
            $this->addForeignKey('fk-accounting-' . $name, '{{%' . $table . '}}', $column, '{{%' . $target . '}}', 'id', 'RESTRICT', 'CASCADE');
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%accounting_journal_line}}');
        $this->dropTable('{{%accounting_journal_draft}}');
        $this->dropTable('{{%accounting_fiscal_config}}');
    }
}
