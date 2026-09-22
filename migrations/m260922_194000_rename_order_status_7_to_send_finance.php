<?php

use yii\db\Migration;

/**
 * เปลี่ยนป้ายสถานะใบสั่งซื้อ code 7 จาก "ส่งบัญชี" → "ส่งการเงิน"
 * ให้ตรงกับเจ้าของงาน (พัสดุส่งงานให้การเงิน ไม่ใช่บัญชี)
 * taxonomy order_status เป็นข้อมูลป้อนมือ จึงอัปเดตผ่าน migration ให้ตรงกันทุกฐาน
 */
class m260922_194000_rename_order_status_7_to_send_finance extends Migration
{
    public function safeUp()
    {
        $this->update('{{%categorise}}', ['title' => 'ส่งการเงิน'], [
            'name' => 'order_status',
            'code' => 7,
        ]);
    }

    public function safeDown()
    {
        $this->update('{{%categorise}}', ['title' => 'ส่งบัญชี'], [
            'name' => 'order_status',
            'code' => 7,
        ]);
    }
}
