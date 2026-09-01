<?php

use yii\db\Migration;

/** Encrypted employee bank-account envelopes; never stores plaintext account numbers. */
class m260816_140100_create_payroll_bank_account extends Migration
{
    public function safeUp()
    {
        $schema = $this->db->getTableSchema('{{%payroll_bank_account}}', true);
        if ($schema !== null) {
            if (!isset($schema->columns['ref'])) {
                $this->addColumn('{{%payroll_bank_account}}', 'ref', $this->string(255)->null()->after('id'));
            }
            return;
        }
        $this->createTable('{{%payroll_bank_account}}', [
            'id' => $this->primaryKey(), 'ref' => $this->string(255)->null(),
            'employee_id' => $this->integer()->notNull(),
            'bank_code' => $this->string(20)->notNull(), 'account_last4' => $this->string(4)->notNull(),
            'account_ciphertext' => $this->binary()->notNull(), 'account_nonce' => $this->binary()->notNull(),
            'key_version' => $this->smallInteger()->notNull()->defaultValue(1),
            'status' => $this->string(20)->notNull()->defaultValue('pending'),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'verified_at' => $this->dateTime()->null(), 'verified_by' => $this->integer()->null(),
            'created_at' => $this->dateTime()->null(), 'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(), 'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('idx-payroll-bank-employee-active', '{{%payroll_bank_account}}', ['employee_id', 'is_active']);
        $this->addForeignKey('fk-payroll-bank-employee', '{{%payroll_bank_account}}', 'employee_id', '{{%employees}}', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
        if ($this->legacyCoreApplied()) {
            $schema = $this->db->getTableSchema('{{%payroll_bank_account}}', true);
            if ($schema !== null && isset($schema->columns['ref'])) $this->dropColumn('{{%payroll_bank_account}}', 'ref');
            return;
        }
        $this->dropForeignKey('fk-payroll-bank-employee', '{{%payroll_bank_account}}');
        $this->dropTable('{{%payroll_bank_account}}');
    }

    private function legacyCoreApplied(): bool
    {
        return (new \yii\db\Query())->from('{{%migration}}')->where(['version' => 'm260816_140000_create_payroll_core'])->exists($this->db);
    }
}
