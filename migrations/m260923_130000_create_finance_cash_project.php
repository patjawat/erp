<?php

use yii\db\Migration;

/**
 * โครงการเงินบำรุง/เงินนอกงบประมาณ (ทะเบียนคุม 2.4 เงินนอกงบฯ จำแนกตามโครงการ)
 *
 *   finance_cash_project : ทะเบียนโครงการ (เงินบริจาค/อุดหนุน/เงินบำรุงเฉพาะโครงการ)
 *   + เพิ่ม project_id บน finance_cash_txn เพื่อ tag รายการเข้าโครงการ
 */
class m260923_130000_create_finance_cash_project extends Migration
{
    public function safeUp()
    {
        $t = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%finance_cash_project}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(32)->null(),
            'name' => $this->string(255)->notNull(),
            'fiscal_year' => $this->integer()->null()->comment('ปีงบ พ.ศ.'),
            'fund_source' => $this->string(32)->null()->comment('donation|subsidy|own|other'),
            'budget_amount' => $this->decimal(15, 2)->null()->comment('วงเงินโครงการ (ถ้ามี)'),
            'is_active' => $this->tinyInteger()->notNull()->defaultValue(1),
            'note' => $this->string(500)->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $t);

        $this->addColumn('{{%finance_cash_txn}}', 'project_id', $this->integer()->null()->after('money_account_id'));
        $this->createIndex('idx-cash_txn-project', '{{%finance_cash_txn}}', 'project_id');
        $this->addForeignKey('fk-cash_txn-project', '{{%finance_cash_txn}}', 'project_id', '{{%finance_cash_project}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-cash_txn-project', '{{%finance_cash_txn}}');
        $this->dropIndex('idx-cash_txn-project', '{{%finance_cash_txn}}');
        $this->dropColumn('{{%finance_cash_txn}}', 'project_id');
        $this->dropTable('{{%finance_cash_project}}');
    }
}
