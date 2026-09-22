<?php

use yii\db\Migration;

/**
 * รอบจ่ายเจ้าหนี้รายบริษัท (payment batch) — 1 การจ่าย = 1 เช็ค + 1 หนังสือนำส่ง
 * ผูกกับบิลที่จ่ายผ่าน finance_payable_settlement.payment_id
 */
class m260922_120000_create_finance_payable_payment extends Migration
{
    public function safeUp()
    {
        $opts = $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        $this->createTable('{{%finance_payable_payment}}', [
            'id' => $this->primaryKey(),
            'vendor_id' => $this->integer()->null(),
            'vendor_name_snapshot' => $this->string(255)->notNull(),
            'pay_date' => $this->date()->notNull(),
            'pay_method' => $this->string(20)->notNull()->defaultValue('cheque')->comment('cheque/transfer/cash'),
            'bank_name' => $this->string(100)->null(),
            'bank_branch' => $this->string(100)->null(),
            'cheque_no' => $this->string(50)->null(),
            'doc_no' => $this->string(60)->null()->comment('เลขที่หนังสือนำส่ง'),
            'subject' => $this->string(255)->null()->comment('เรื่อง (ชำระเงินค่า...)'),
            'gross_total' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'wht_total' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'net_total' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'note' => $this->string(255)->null(),
            'created_at' => $this->integer()->null(),
            'created_by' => $this->integer()->null(),
        ], $opts);

        // ผูก settlement -> payment batch
        $this->addColumn('{{%finance_payable_settlement}}', 'payment_id', $this->integer()->null()->after('cash_voucher_id'));
        $this->createIndex('idx-fps-payment', '{{%finance_payable_settlement}}', 'payment_id');
        $this->addForeignKey('fk-fps-payment', '{{%finance_payable_settlement}}', 'payment_id', '{{%finance_payable_payment}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-fps-payment', '{{%finance_payable_settlement}}');
        $this->dropColumn('{{%finance_payable_settlement}}', 'payment_id');
        $this->dropTable('{{%finance_payable_payment}}');
    }
}
