<?php

use yii\db\Migration;

/** Links a payable draft to its primary debit account without creating a journal entry. */
class m260916_150000_link_finance_payable_to_chart_account extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%finance_payable}}', 'accounting_chart_version_id', $this->integer()->null()->after('finance_inbox_id'));
        $this->addColumn('{{%finance_payable}}', 'accounting_chart_account_id', $this->integer()->null()->after('accounting_chart_version_id'));
        $this->addColumn('{{%finance_payable}}', 'account_code_snapshot', $this->string(30)->null()->after('accounting_chart_account_id'));
        $this->addColumn('{{%finance_payable}}', 'account_name_snapshot', $this->string(500)->null()->after('account_code_snapshot'));
        $this->createIndex('idx-finance_payable-chart-version', '{{%finance_payable}}', 'accounting_chart_version_id');
        $this->createIndex('idx-finance_payable-chart-account', '{{%finance_payable}}', 'accounting_chart_account_id');
        $this->addForeignKey('fk-finance_payable-chart-version', '{{%finance_payable}}', 'accounting_chart_version_id', '{{%accounting_chart_version}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk-finance_payable-chart-account', '{{%finance_payable}}', 'accounting_chart_account_id', '{{%accounting_chart_account}}', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-finance_payable-chart-account', '{{%finance_payable}}');
        $this->dropForeignKey('fk-finance_payable-chart-version', '{{%finance_payable}}');
        $this->dropIndex('idx-finance_payable-chart-account', '{{%finance_payable}}');
        $this->dropIndex('idx-finance_payable-chart-version', '{{%finance_payable}}');
        $this->dropColumn('{{%finance_payable}}', 'account_name_snapshot');
        $this->dropColumn('{{%finance_payable}}', 'account_code_snapshot');
        $this->dropColumn('{{%finance_payable}}', 'accounting_chart_account_id');
        $this->dropColumn('{{%finance_payable}}', 'accounting_chart_version_id');
    }
}
