<?php

use yii\db\Migration;

/** Hospital chart scope and mappings to the annual standard chart. */
class m260916_130000_create_accounting_chart_mapping extends Migration
{
    public function safeUp()
    {
        $this->dropIndex('uq-accounting_chart_version-year-code', '{{%accounting_chart_version}}');
        $this->createIndex(
            'uq-accounting_chart_version-year-scope-code',
            '{{%accounting_chart_version}}',
            ['fiscal_year', 'scope', 'version_code'],
            true
        );

        $this->createTable('{{%accounting_chart_mapping}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(64)->notNull(),
            'fiscal_year' => $this->smallInteger()->notNull(),
            'standard_version_id' => $this->integer()->notNull(),
            'standard_account_id' => $this->integer()->notNull(),
            'hospital_version_id' => $this->integer()->notNull(),
            'hospital_account_id' => $this->integer()->notNull(),
            'match_type' => $this->string(20)->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('suggested'),
            'note' => $this->text()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('uq-accounting_chart_mapping-ref', '{{%accounting_chart_mapping}}', 'ref', true);
        $this->createIndex(
            'uq-accounting_chart_mapping-version-hospital',
            '{{%accounting_chart_mapping}}',
            ['standard_version_id', 'hospital_version_id', 'hospital_account_id'],
            true
        );
        $this->createIndex('idx-accounting_chart_mapping-year-status', '{{%accounting_chart_mapping}}', ['fiscal_year', 'status']);
        foreach ([
            ['standard-version', 'standard_version_id', '{{%accounting_chart_version}}'],
            ['hospital-version', 'hospital_version_id', '{{%accounting_chart_version}}'],
            ['standard-account', 'standard_account_id', '{{%accounting_chart_account}}'],
            ['hospital-account', 'hospital_account_id', '{{%accounting_chart_account}}'],
        ] as [$name, $column, $table]) {
            $this->addForeignKey('fk-accounting_chart_mapping-' . $name, '{{%accounting_chart_mapping}}', $column, $table, 'id', 'CASCADE', 'CASCADE');
        }
    }

    public function safeDown()
    {
        foreach (['standard-version', 'hospital-version', 'standard-account', 'hospital-account'] as $name) {
            $this->dropForeignKey('fk-accounting_chart_mapping-' . $name, '{{%accounting_chart_mapping}}');
        }
        $this->dropTable('{{%accounting_chart_mapping}}');
        $this->dropIndex('uq-accounting_chart_version-year-scope-code', '{{%accounting_chart_version}}');
        $this->createIndex('uq-accounting_chart_version-year-code', '{{%accounting_chart_version}}', ['fiscal_year', 'version_code'], true);
    }
}
