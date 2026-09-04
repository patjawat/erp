<?php

use yii\db\Migration;

/** Classifies payroll items and preserves each source document's personnel order. */
class m260830_170000_add_payroll_item_group_and_document_order extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%payroll_item_type}}', 'item_group', $this->string(30)->notNull()->defaultValue('monthly_pay')->after('direction'));
        $this->addColumn('{{%payroll_employee_item}}', 'document_order', $this->integer()->null()->after('item_type_id'));
        $this->createIndex('idx-payroll-employee-item-document-order', '{{%payroll_employee_item}}', ['item_type_id', 'status', 'document_order']);

        $this->update('{{%payroll_item_type}}', ['item_group' => 'deduction'], ['direction' => 'deduction']);
        $this->update('{{%payroll_item_type}}', ['item_group' => 'employer_contribution'], ['direction' => 'employer_contribution']);
        $this->update('{{%payroll_item_type}}', ['item_group' => 'compensation'], ['code' => ['POSITION_ALLOWANCE', 'RETROACTIVE_PAY']]);
        $this->execute("SET @payroll_item := 0, @payroll_order := 0");
        $this->execute("UPDATE {{%payroll_employee_item}} pei JOIN (SELECT ranked.id, ranked.document_order FROM (SELECT id, IF(@payroll_item = item_type_id, @payroll_order := @payroll_order + 1, @payroll_order := 1) AS document_order, @payroll_item := item_type_id FROM {{%payroll_employee_item}} ORDER BY item_type_id, id) ranked) ordering ON ordering.id = pei.id SET pei.document_order = ordering.document_order");
    }

    public function safeDown()
    {
        $this->dropIndex('idx-payroll-employee-item-document-order', '{{%payroll_employee_item}}');
        $this->dropColumn('{{%payroll_employee_item}}', 'document_order');
        $this->dropColumn('{{%payroll_item_type}}', 'item_group');
    }
}
