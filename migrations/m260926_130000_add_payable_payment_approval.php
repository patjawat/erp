<?php

use yii\db\Migration;

/**
 * อนุมัติก่อนจ่ายเงินเจ้าหนี้ — บันทึกรอบจ่ายเป็น "รออนุมัติ" ก่อน (ยังไม่ตัดหนี้/ไม่ออกใบสำคัญ/เช็ค)
 * ผู้อนุมัติ (คนละคนกับผู้บันทึก) กดอนุมัติ → ระบบตัดหนี้ + ใบสำคัญจ่าย + เช็ค
 * รอบจ่ายเดิมทั้งหมดถือว่า "จ่ายแล้ว"
 */
class m260926_130000_add_payable_payment_approval extends Migration
{
    public function safeUp()
    {
        $t = '{{%finance_payable_payment}}';
        $this->addColumn($t, 'status', $this->string(20)->notNull()->defaultValue('paid')->comment('pending=รออนุมัติ, paid=อนุมัติ/จ่ายแล้ว, rejected=ไม่อนุมัติ'));
        $this->addColumn($t, 'request_json', $this->text()->null()->comment('บิล/ยอด/หมวด และข้อมูลการจ่ายที่ขอ (ใช้ตอนอนุมัติ)'));
        $this->addColumn($t, 'approved_at', $this->integer()->null());
        $this->addColumn($t, 'approved_by', $this->integer()->null());
        $this->addColumn($t, 'rejected_at', $this->integer()->null());
        $this->addColumn($t, 'rejected_by', $this->integer()->null());
        $this->addColumn($t, 'reject_reason', $this->string(255)->null());
        $this->createIndex('idx-finance_payable_payment-status', $t, 'status');
    }

    public function safeDown()
    {
        $t = '{{%finance_payable_payment}}';
        $this->dropIndex('idx-finance_payable_payment-status', $t);
        foreach (['reject_reason', 'rejected_by', 'rejected_at', 'approved_by', 'approved_at', 'request_json', 'status'] as $c) {
            $this->dropColumn($t, $c);
        }
    }
}
