<?php

use yii\db\Migration;

/**
 * เงินสดย่อย / เงินทดรองจ่าย (imprest) — แยกจากเงินยืมรายสัญญา
 * (ทะเบียนคุม 4.4 ตาม docs/finance/control-registry-plan.md)
 *
 *   finance_petty_cash      : กองเงินสดย่อย/วงเงินทดรอง (imprest fund) รายจุด/ผู้รับผิดชอบ
 *   finance_petty_cash_txn  : การเคลื่อนไหว (ตั้งวงเงิน/จ่าย/เบิกชดเชย/ส่งคืน)
 */
class m260922_210000_create_finance_petty_cash extends Migration
{
    public function safeUp()
    {
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%finance_petty_cash}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(32)->null()->comment('รหัสกอง'),
            'name' => $this->string(255)->notNull()->comment('ชื่อกองเงินสดย่อย'),
            'custodian_name' => $this->string(255)->null()->comment('ผู้รับผิดชอบ/ผู้ถือเงิน'),
            'unit' => $this->string(255)->null()->comment('จุด/หน่วยงาน'),
            'float_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('วงเงิน (imprest float)'),
            'fiscal_year' => $this->integer()->null()->comment('ปีงบประมาณ พ.ศ.'),
            'is_active' => $this->tinyInteger()->notNull()->defaultValue(1),
            'note' => $this->text()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $tableOptions);

        $this->createTable('{{%finance_petty_cash_txn}}', [
            'id' => $this->primaryKey(),
            'petty_cash_id' => $this->integer()->notNull(),
            'txn_type' => $this->string(16)->notNull()->comment('establish|disburse|replenish|return'),
            'doc_date' => $this->date()->notNull(),
            'doc_no' => $this->string(64)->null(),
            'description' => $this->string(500)->null(),
            'payee' => $this->string(255)->null()->comment('จ่ายให้'),
            'amount' => $this->decimal(15, 2)->notNull(),
            'note' => $this->text()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $tableOptions);

        $this->createIndex('idx-pcash_txn-fund', '{{%finance_petty_cash_txn}}', 'petty_cash_id');
        $this->createIndex('idx-pcash_txn-date', '{{%finance_petty_cash_txn}}', 'doc_date');
        $this->addForeignKey(
            'fk-pcash_txn-fund',
            '{{%finance_petty_cash_txn}}',
            'petty_cash_id',
            '{{%finance_petty_cash}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-pcash_txn-fund', '{{%finance_petty_cash_txn}}');
        $this->dropTable('{{%finance_petty_cash_txn}}');
        $this->dropTable('{{%finance_petty_cash}}');
    }
}
