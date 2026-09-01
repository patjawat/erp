<?php

use yii\db\Migration;

/** Payroll period header and locking state. */
class m260816_140000_create_payroll_period extends Migration
{
    public function safeUp()
    {
        $schema = $this->db->getTableSchema('{{%payroll_period}}', true);
        if ($schema !== null) {
            if (!isset($schema->columns['ref'])) {
                $this->addColumn('{{%payroll_period}}', 'ref', $this->string(255)->null()->after('id'));
            }
            return;
        }
        $this->createTable('{{%payroll_period}}', [
            'id' => $this->primaryKey(), 'ref' => $this->string(255)->null(),
            'period_code' => $this->string(20)->notNull(),
            'date_start' => $this->date()->notNull(), 'date_end' => $this->date()->notNull(),
            'pay_date' => $this->date()->null(), 'status' => $this->string(30)->notNull()->defaultValue('draft'),
            'locked_at' => $this->dateTime()->null(), 'locked_by' => $this->integer()->null(),
            'created_at' => $this->dateTime()->null(), 'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(), 'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('uq-payroll-period-code', '{{%payroll_period}}', 'period_code', true);
    }

    public function safeDown()
    {
        if ($this->legacyCoreApplied()) {
            $schema = $this->db->getTableSchema('{{%payroll_period}}', true);
            if ($schema !== null && isset($schema->columns['ref'])) $this->dropColumn('{{%payroll_period}}', 'ref');
            return;
        }
        $this->dropTable('{{%payroll_period}}');
    }

    private function legacyCoreApplied(): bool
    {
        return (new \yii\db\Query())->from('{{%migration}}')->where(['version' => 'm260816_140000_create_payroll_core'])->exists($this->db);
    }
}
