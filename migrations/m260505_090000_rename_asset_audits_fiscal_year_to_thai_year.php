<?php

use yii\db\Migration;

class m260505_090000_rename_asset_audits_fiscal_year_to_thai_year extends Migration
{
    public function safeUp()
    {
        $this->dropIndex('idx_asset_audits_fiscal_year', '{{%asset_audits}}');
        $this->dropIndex('idx_asset_audits_seq_no', '{{%asset_audits}}');

        $this->renameColumn('{{%asset_audits}}', 'fiscal_year', 'thai_year');

        $this->createIndex('idx_asset_audits_thai_year', '{{%asset_audits}}', 'thai_year');
        $this->createIndex('idx_asset_audits_seq_no', '{{%asset_audits}}', ['thai_year', 'seq_no'], true);
    }

    public function safeDown()
    {
        $this->dropIndex('idx_asset_audits_thai_year', '{{%asset_audits}}');
        $this->dropIndex('idx_asset_audits_seq_no', '{{%asset_audits}}');

        $this->renameColumn('{{%asset_audits}}', 'thai_year', 'fiscal_year');

        $this->createIndex('idx_asset_audits_fiscal_year', '{{%asset_audits}}', 'fiscal_year');
        $this->createIndex('idx_asset_audits_seq_no', '{{%asset_audits}}', ['fiscal_year', 'seq_no'], true);
    }
}
