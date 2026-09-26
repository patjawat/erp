<?php

use yii\db\Migration;

/**
 * ขั้น "รับวางบิล" ของทะเบียนเจ้าหนี้
 * ตั้งเจ้าหนี้ → (ผู้ขายมาวางบิล) การเงินบันทึกรับวางบิล → ดึงบิลที่วางแล้วไปจ่าย
 * บันทึกรับวางบิลจะยึดวันวางบิลจริงเป็น billing_date แล้วคำนวณวันครบกำหนดใหม่
 *
 * รายการเดิมทั้งหมดถือว่าวางบิลแล้ว ณ billing_date (ไม่ให้หายจากหน้าจ่ายชำระ)
 */
class m260926_100000_add_payable_billing extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%finance_payable}}', 'billed_at', $this->dateTime()->null()->comment('เวลาที่บันทึกรับวางบิล (null = ยังไม่วางบิล)'));
        $this->addColumn('{{%finance_payable}}', 'billed_by', $this->integer()->null()->comment('ผู้บันทึกรับวางบิล'));
        $this->addColumn('{{%finance_payable}}', 'billing_ref', $this->string(60)->null()->comment('เลขที่ใบวางบิลของผู้ขาย'));
        $this->createIndex('idx-finance_payable-billed', '{{%finance_payable}}', 'billed_at');

        $this->execute("UPDATE {{%finance_payable}} SET billed_at = CONCAT(billing_date, ' 00:00:00') WHERE billing_date IS NOT NULL");
    }

    public function safeDown()
    {
        $this->dropIndex('idx-finance_payable-billed', '{{%finance_payable}}');
        $this->dropColumn('{{%finance_payable}}', 'billing_ref');
        $this->dropColumn('{{%finance_payable}}', 'billed_by');
        $this->dropColumn('{{%finance_payable}}', 'billed_at');
    }
}
