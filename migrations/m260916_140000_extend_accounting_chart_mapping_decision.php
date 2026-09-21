<?php

use yii\db\Migration;

/** Allows an explicit hospital-only decision and records who made it. */
class m260916_140000_extend_accounting_chart_mapping_decision extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%accounting_chart_mapping}}', 'standard_account_id', $this->integer()->null());
        $this->createIndex('uq-accounting_chart_mapping-hospital-account', '{{%accounting_chart_mapping}}', ['hospital_version_id', 'hospital_account_id'], true);
        $this->addColumn('{{%accounting_chart_mapping}}', 'decided_at', $this->dateTime()->null()->after('note'));
        $this->addColumn('{{%accounting_chart_mapping}}', 'decided_by', $this->integer()->null()->after('decided_at'));
        $this->createIndex('idx-accounting_chart_mapping-decided-by', '{{%accounting_chart_mapping}}', 'decided_by');
    }

    public function safeDown()
    {
        $this->delete('{{%accounting_chart_mapping}}', ['standard_account_id' => null]);
        $this->dropIndex('uq-accounting_chart_mapping-hospital-account', '{{%accounting_chart_mapping}}');
        $this->dropIndex('idx-accounting_chart_mapping-decided-by', '{{%accounting_chart_mapping}}');
        $this->dropColumn('{{%accounting_chart_mapping}}', 'decided_by');
        $this->dropColumn('{{%accounting_chart_mapping}}', 'decided_at');
        $this->alterColumn('{{%accounting_chart_mapping}}', 'standard_account_id', $this->integer()->notNull());
    }
}
