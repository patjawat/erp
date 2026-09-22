<?php

use yii\db\Migration;

/**
 * เงินงบประมาณ + การนำส่งคลัง (ทะเบียนคุมหมวด 1)
 *   1.1 เงินประจำงวด           → finance_budget_allotment
 *   1.2 รับ-จ่ายเงินงบประมาณ    → finance_budget_txn (txn_type receive|disburse)
 *   1.3 รับและนำส่งเงิน (นส.02) → finance_treasury_remit
 *   1.4 เบิกเกินส่งคืนคลัง       → finance_budget_return
 *   1.5 ค่าใช้จ่ายงบกลาง        → finance_budget_txn ที่ budget_category = 'central'
 */
class m260923_120000_create_finance_budget extends Migration
{
    public function safeUp()
    {
        $t = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%finance_budget_allotment}}', [
            'id' => $this->primaryKey(),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบ พ.ศ.'),
            'period_no' => $this->integer()->null()->comment('งวดที่'),
            'budget_category' => $this->string(16)->notNull()->comment('personnel|operation|investment|subsidy|other|central'),
            'allotment_no' => $this->string(64)->null()->comment('เลขที่หนังสือจัดสรร'),
            'allotment_date' => $this->date()->null(),
            'amount' => $this->decimal(15, 2)->notNull()->comment('ยอดจัดสรร'),
            'note' => $this->string(500)->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $t);

        $this->createTable('{{%finance_budget_txn}}', [
            'id' => $this->primaryKey(),
            'fiscal_year' => $this->integer()->notNull(),
            'allotment_id' => $this->integer()->null(),
            'txn_type' => $this->string(16)->notNull()->comment('receive|disburse'),
            'budget_category' => $this->string(16)->notNull(),
            'doc_date' => $this->date()->notNull(),
            'doc_no' => $this->string(64)->null(),
            'description' => $this->string(500)->null(),
            'payee' => $this->string(255)->null(),
            'amount' => $this->decimal(15, 2)->notNull(),
            'note' => $this->string(500)->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $t);

        $this->createTable('{{%finance_treasury_remit}}', [
            'id' => $this->primaryKey(),
            'fiscal_year' => $this->integer()->notNull(),
            'revenue_type' => $this->string(255)->null()->comment('ประเภทรายได้แผ่นดิน'),
            'collect_date' => $this->date()->null()->comment('วันที่จัดเก็บ'),
            'collected_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'remit_date' => $this->date()->null()->comment('วันที่นำส่งคลัง'),
            'remit_no' => $this->string(64)->null()->comment('เลขที่ นส.02'),
            'note' => $this->string(500)->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $t);

        $this->createTable('{{%finance_budget_return}}', [
            'id' => $this->primaryKey(),
            'fiscal_year' => $this->integer()->notNull(),
            'budget_category' => $this->string(16)->null(),
            'source_ref' => $this->string(255)->null()->comment('อ้างการเบิกเดิม'),
            'return_date' => $this->date()->null(),
            'return_no' => $this->string(64)->null(),
            'amount' => $this->decimal(15, 2)->notNull(),
            'note' => $this->string(500)->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $t);

        $this->createIndex('idx-budget_allot-fy', '{{%finance_budget_allotment}}', ['fiscal_year', 'budget_category']);
        $this->createIndex('idx-budget_txn-fy', '{{%finance_budget_txn}}', ['fiscal_year', 'budget_category']);
        $this->createIndex('idx-treasury-fy', '{{%finance_treasury_remit}}', 'fiscal_year');
        $this->createIndex('idx-budget_return-fy', '{{%finance_budget_return}}', 'fiscal_year');
        $this->addForeignKey('fk-budget_txn-allot', '{{%finance_budget_txn}}', 'allotment_id', '{{%finance_budget_allotment}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-budget_txn-allot', '{{%finance_budget_txn}}');
        $this->dropTable('{{%finance_budget_return}}');
        $this->dropTable('{{%finance_treasury_remit}}');
        $this->dropTable('{{%finance_budget_txn}}');
        $this->dropTable('{{%finance_budget_allotment}}');
    }
}
