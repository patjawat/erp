<?php

use yii\db\Migration;

/** Separates salary, compensation and overtime runs and their applicable items. */
class m260901_200000_add_payroll_run_type extends Migration
{
    public function safeUp()
    {
        $period = $this->db->getTableSchema('{{%payroll_period}}', true);
        if (!isset($period->columns['period_type'])) {
            $this->dropIndex('uq-payroll-period-code', '{{%payroll_period}}');
            $this->addColumn('{{%payroll_period}}', 'period_type', $this->string(30)->notNull()->defaultValue('salary')->after('period_code'));
            $this->createIndex('uq-payroll-period-code-type', '{{%payroll_period}}', ['period_code', 'period_type'], true);
        }
        $itemType = $this->db->getTableSchema('{{%payroll_item_type}}', true);
        if (!isset($itemType->columns['payroll_scope'])) {
            $this->addColumn('{{%payroll_item_type}}', 'payroll_scope', $this->string(30)->notNull()->defaultValue('salary')->after('item_group'));
            $this->update('{{%payroll_item_type}}', ['payroll_scope' => 'compensation'], ['item_group' => 'compensation']);
            $this->update('{{%payroll_item_type}}', ['payroll_scope' => 'overtime'], ['item_group' => 'overtime']);
            $this->createIndex('idx-payroll-item-type-scope-status', '{{%payroll_item_type}}', ['payroll_scope', 'status']);
        }
    }

    public function safeDown()
    {
        $itemType = $this->db->getTableSchema('{{%payroll_item_type}}', true);
        if (isset($itemType->columns['payroll_scope'])) {
            $this->dropIndex('idx-payroll-item-type-scope-status', '{{%payroll_item_type}}');
            $this->dropColumn('{{%payroll_item_type}}', 'payroll_scope');
        }
        $period = $this->db->getTableSchema('{{%payroll_period}}', true);
        if (isset($period->columns['period_type'])) {
            $this->dropIndex('uq-payroll-period-code-type', '{{%payroll_period}}');
            $this->dropColumn('{{%payroll_period}}', 'period_type');
            $this->createIndex('uq-payroll-period-code', '{{%payroll_period}}', 'period_code', true);
        }
    }
}
