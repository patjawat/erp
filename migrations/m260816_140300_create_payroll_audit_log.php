<?php

use yii\db\Migration;

/** Append-only evidence for future payroll state transitions. */
class m260816_140300_create_payroll_audit_log extends Migration
{
    public function safeUp()
    {
        $schema = $this->db->getTableSchema('{{%payroll_audit_log}}', true);
        if ($schema !== null) {
            foreach (['ref' => $this->string(255)->null()->after('id'), 'updated_at' => $this->dateTime()->null(), 'updated_by' => $this->integer()->null()] as $name => $type) {
                if (!isset($schema->columns[$name])) $this->addColumn('{{%payroll_audit_log}}', $name, $type);
            }
            return;
        }
        $this->createTable('{{%payroll_audit_log}}', [
            'id' => $this->bigPrimaryKey(), 'ref' => $this->string(255)->null(),
            'entity_type' => $this->string(50)->notNull(), 'entity_id' => $this->integer()->notNull(),
            'action' => $this->string(50)->notNull(), 'reason' => $this->text()->null(),
            'before_json' => $this->json()->null(), 'after_json' => $this->json()->null(),
            'ip_address' => $this->string(45)->null(),
            'created_at' => $this->dateTime()->notNull(), 'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(), 'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('idx-payroll-audit-entity', '{{%payroll_audit_log}}', ['entity_type', 'entity_id', 'created_at']);
    }

    public function safeDown()
    {
        if ($this->legacyCoreApplied()) {
            $schema = $this->db->getTableSchema('{{%payroll_audit_log}}', true);
            foreach (['updated_by', 'updated_at', 'ref'] as $name) {
                if ($schema !== null && isset($schema->columns[$name])) $this->dropColumn('{{%payroll_audit_log}}', $name);
            }
            return;
        }
        $this->dropTable('{{%payroll_audit_log}}');
    }

    private function legacyCoreApplied(): bool
    {
        return (new \yii\db\Query())->from('{{%migration}}')->where(['version' => 'm260816_140000_create_payroll_core'])->exists($this->db);
    }
}
