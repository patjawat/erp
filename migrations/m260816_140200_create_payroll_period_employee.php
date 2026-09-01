<?php

use yii\db\Migration;

/** Source snapshots and later calculation results for each employee in a period. */
class m260816_140200_create_payroll_period_employee extends Migration
{
    public function safeUp()
    {
        $schema = $this->db->getTableSchema('{{%payroll_period_employee}}', true);
        if ($schema !== null) {
            if (!isset($schema->columns['ref'])) {
                $this->addColumn('{{%payroll_period_employee}}', 'ref', $this->string(255)->null()->after('id'));
            }
            return;
        }
        $this->createTable('{{%payroll_period_employee}}', [
            'id' => $this->primaryKey(), 'ref' => $this->string(255)->null(),
            'payroll_period_id' => $this->integer()->notNull(), 'employee_id' => $this->integer()->notNull(),
            'employee_snapshot' => $this->json()->notNull(), 'calculation_snapshot' => $this->json()->null(),
            'payroll_case' => $this->string(30)->notNull()->defaultValue('regular'),
            'status' => $this->string(30)->notNull()->defaultValue('needs_review'),
            'gross_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'deduction_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'net_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'reason' => $this->text()->null(), 'reference_no' => $this->string(255)->null(),
            'created_at' => $this->dateTime()->null(), 'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(), 'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('uq-payroll-period-employee', '{{%payroll_period_employee}}', ['payroll_period_id', 'employee_id'], true);
        $this->addForeignKey('fk-payroll-pe-period', '{{%payroll_period_employee}}', 'payroll_period_id', '{{%payroll_period}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-payroll-pe-employee', '{{%payroll_period_employee}}', 'employee_id', '{{%employees}}', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
        if ($this->legacyCoreApplied()) {
            $schema = $this->db->getTableSchema('{{%payroll_period_employee}}', true);
            if ($schema !== null && isset($schema->columns['ref'])) $this->dropColumn('{{%payroll_period_employee}}', 'ref');
            return;
        }
        $this->dropForeignKey('fk-payroll-pe-employee', '{{%payroll_period_employee}}');
        $this->dropForeignKey('fk-payroll-pe-period', '{{%payroll_period_employee}}');
        $this->dropTable('{{%payroll_period_employee}}');
    }

    private function legacyCoreApplied(): bool
    {
        return (new \yii\db\Query())->from('{{%migration}}')->where(['version' => 'm260816_140000_create_payroll_core'])->exists($this->db);
    }
}
