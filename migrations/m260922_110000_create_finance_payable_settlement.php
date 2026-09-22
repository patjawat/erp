<?php

use yii\db\Migration;

/**
 * การตัดหนี้/จ่ายชำระเจ้าหนี้ (AP settlement) — ผูกใบสำคัญจ่ายเงินกับหนี้ที่ตั้งไว้
 * เพื่อคำนวณยอดคงค้าง = finance_payable.net_amount - SUM(settlement.amount) และทำ aging
 *
 * รองรับ 1 ใบจ่าย -> หลายบิล (many-to-many) และจ่ายบางส่วน
 * cash_voucher_id เป็น null ได้ (บันทึกจ่ายแบบไม่ผูกใบสำคัญ mophcash โดยตรง)
 */
class m260922_110000_create_finance_payable_settlement extends Migration
{
    public function safeUp()
    {
        $opts = $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        $this->createTable('{{%finance_payable_settlement}}', [
            'id' => $this->primaryKey(),
            'payable_id' => $this->integer()->notNull()->comment('หนี้ที่ตัด -> finance_payable'),
            'cash_voucher_id' => $this->integer()->null()->comment('ใบสำคัญจ่ายเงินบำรุง -> finance_cash_voucher (null = บันทึกเอง)'),
            'amount' => $this->decimal(15, 2)->notNull()->comment('ยอดที่ตัดบิลนี้'),
            'settle_date' => $this->date()->notNull()->comment('วันที่จ่าย/ตัดหนี้'),
            'note' => $this->string(255)->null(),
            'created_at' => $this->integer()->null(),
            'created_by' => $this->integer()->null(),
        ], $opts);

        $this->createIndex('idx-fps-payable', '{{%finance_payable_settlement}}', 'payable_id');
        $this->createIndex('idx-fps-voucher', '{{%finance_payable_settlement}}', 'cash_voucher_id');

        // FK: ลบหนี้ไม่ได้ถ้ามีการตัดหนี้แล้ว (RESTRICT); ลบใบจ่ายแล้วตัดหนี้ที่ผูกไปด้วย (CASCADE)
        $this->addForeignKey('fk-fps-payable', '{{%finance_payable_settlement}}', 'payable_id', '{{%finance_payable}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk-fps-voucher', '{{%finance_payable_settlement}}', 'cash_voucher_id', '{{%finance_cash_voucher}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%finance_payable_settlement}}');
    }
}
