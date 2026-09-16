<?php

use yii\db\Migration;

/** Versioned chart of accounts for accounting; imports remain drafts until activated. */
class m260916_120000_create_accounting_chart_tables extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%accounting_chart_version}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(64)->notNull(),
            'fiscal_year' => $this->smallInteger()->notNull(),
            'version_code' => $this->string(30)->notNull(),
            'title' => $this->string(255)->notNull(),
            'scope' => $this->string(30)->notNull()->defaultValue('standard'),
            'status' => $this->string(20)->notNull()->defaultValue('draft'),
            'source_file_name' => $this->string(255)->null(),
            'source_file_hash' => $this->string(64)->null(),
            'account_count' => $this->integer()->notNull()->defaultValue(0),
            'note' => $this->text()->null(),
            'activated_at' => $this->dateTime()->null(),
            'activated_by' => $this->integer()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('uq-accounting_chart_version-ref', '{{%accounting_chart_version}}', 'ref', true);
        $this->createIndex('uq-accounting_chart_version-year-code', '{{%accounting_chart_version}}', ['fiscal_year', 'version_code'], true);
        $this->createIndex('idx-accounting_chart_version-year-status', '{{%accounting_chart_version}}', ['fiscal_year', 'status']);
        $this->createIndex('idx-accounting_chart_version-source-hash', '{{%accounting_chart_version}}', 'source_file_hash');

        $this->createTable('{{%accounting_chart_account}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(64)->notNull(),
            'version_id' => $this->integer()->notNull(),
            'code' => $this->string(30)->notNull(),
            'name' => $this->string(500)->notNull(),
            'category' => $this->string(1)->notNull(),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('uq-accounting_chart_account-ref', '{{%accounting_chart_account}}', 'ref', true);
        $this->createIndex('uq-accounting_chart_account-version-code', '{{%accounting_chart_account}}', ['version_id', 'code'], true);
        $this->createIndex('idx-accounting_chart_account-code', '{{%accounting_chart_account}}', 'code');
        $this->createIndex('idx-accounting_chart_account-category', '{{%accounting_chart_account}}', ['version_id', 'category']);
        $this->addForeignKey(
            'fk-accounting_chart_account-version',
            '{{%accounting_chart_account}}',
            'version_id',
            '{{%accounting_chart_version}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-accounting_chart_account-version', '{{%accounting_chart_account}}');
        $this->dropTable('{{%accounting_chart_account}}');
        $this->dropTable('{{%accounting_chart_version}}');
    }
}
