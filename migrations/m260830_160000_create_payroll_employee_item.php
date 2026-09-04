<?php

use yii\db\Migration;

/** Effective-dated recurring earning and deduction assignments per employee. */
class m260830_160000_create_payroll_employee_item extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%payroll_employee_item}}', [
            'id' => $this->primaryKey(), 'ref' => $this->string(255)->null(),
            'employee_id' => $this->integer()->notNull(), 'item_type_id' => $this->integer()->notNull(),
            'amount' => $this->decimal(15, 2)->notNull(),
            'effective_from' => $this->date()->notNull(), 'effective_to' => $this->date()->null(),
            'reference_no' => $this->string(255)->null(), 'reason' => $this->text()->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('active'),
            'created_at' => $this->dateTime()->null(), 'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(), 'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('idx-payroll-employee-item-effective', '{{%payroll_employee_item}}', ['employee_id', 'item_type_id', 'status', 'effective_from', 'effective_to']);
        $this->addForeignKey('fk-payroll-employee-item-employee', '{{%payroll_employee_item}}', 'employee_id', '{{%employees}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk-payroll-employee-item-type', '{{%payroll_employee_item}}', 'item_type_id', '{{%payroll_item_type}}', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-payroll-employee-item-type', '{{%payroll_employee_item}}');
        $this->dropForeignKey('fk-payroll-employee-item-employee', '{{%payroll_employee_item}}');
        $this->dropTable('{{%payroll_employee_item}}');
    }
}
