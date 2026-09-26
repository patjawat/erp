<?php

use yii\db\Migration;

/**
 * ยกเลิกรอบจ่ายเจ้าหนี้ — คืนยอดคงค้าง (ลบการตัดหนี้) + ลบใบสำคัญจ่าย + ยกเลิกเช็ค
 * แถวรอบจ่ายเก็บไว้เป็นหลักฐาน พร้อม snapshot บิลที่เคยจ่าย
 */
class m260926_110000_add_payable_payment_cancel extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%finance_payable_payment}}', 'cancelled_at', $this->integer()->null()->comment('เวลายกเลิกรอบจ่าย (null = ปกติ)'));
        $this->addColumn('{{%finance_payable_payment}}', 'cancelled_by', $this->integer()->null()->comment('ผู้ยกเลิก'));
        $this->addColumn('{{%finance_payable_payment}}', 'cancel_reason', $this->string(255)->null()->comment('เหตุผลที่ยกเลิก'));
        $this->addColumn('{{%finance_payable_payment}}', 'cancel_snapshot', $this->text()->null()->comment('JSON บิล/ยอดที่เคยตัดก่อนยกเลิก'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%finance_payable_payment}}', 'cancel_snapshot');
        $this->dropColumn('{{%finance_payable_payment}}', 'cancel_reason');
        $this->dropColumn('{{%finance_payable_payment}}', 'cancelled_by');
        $this->dropColumn('{{%finance_payable_payment}}', 'cancelled_at');
    }
}
