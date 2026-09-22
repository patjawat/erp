<?php

use yii\db\Migration;

/**
 * งบพิสูจน์ยอดเงินฝากธนาคาร (Bank Reconciliation) — ทะเบียนคุม 2.3 ส่วนที่ขาด
 *
 *   finance_bank_reconcile       : หัวงบพิสูจน์ยอด รายบัญชี/เดือน
 *   finance_bank_reconcile_item  : รายการกระทบยอด (เช็คค้างจ่าย/เงินฝากระหว่างทาง/ค่าธรรมเนียม/ดอกเบี้ย)
 */
class m260923_110000_create_finance_bank_reconcile extends Migration
{
    public function safeUp()
    {
        $t = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%finance_bank_reconcile}}', [
            'id' => $this->primaryKey(),
            'cash_account_id' => $this->integer()->notNull(),
            'fiscal_year' => $this->integer()->notNull(),
            'period_month' => $this->integer()->null()->comment('เดือน 1-12'),
            'statement_date' => $this->date()->null()->comment('วันที่ตาม statement'),
            'statement_balance' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ยอดตาม statement ธนาคาร'),
            'book_balance' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ยอดตามบัญชี รพ.'),
            'status' => $this->string(16)->notNull()->defaultValue('draft')->comment('draft|done'),
            'note' => $this->text()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $t);

        $this->createTable('{{%finance_bank_reconcile_item}}', [
            'id' => $this->primaryKey(),
            'reconcile_id' => $this->integer()->notNull(),
            'side' => $this->string(8)->notNull()->comment('bank|book'),
            'direction' => $this->string(8)->notNull()->comment('add|sub'),
            'item_type' => $this->string(32)->null(),
            'description' => $this->string(500)->null(),
            'amount' => $this->decimal(15, 2)->notNull(),
            'ref' => $this->string(64)->null(),
        ], $t);

        $this->createIndex('idx-bankrec-acct', '{{%finance_bank_reconcile}}', ['cash_account_id', 'fiscal_year']);
        $this->createIndex('idx-bankrec_item-rec', '{{%finance_bank_reconcile_item}}', 'reconcile_id');
        $this->addForeignKey('fk-bankrec_item-rec', '{{%finance_bank_reconcile_item}}', 'reconcile_id', '{{%finance_bank_reconcile}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-bankrec_item-rec', '{{%finance_bank_reconcile_item}}');
        $this->dropTable('{{%finance_bank_reconcile_item}}');
        $this->dropTable('{{%finance_bank_reconcile}}');
    }
}
