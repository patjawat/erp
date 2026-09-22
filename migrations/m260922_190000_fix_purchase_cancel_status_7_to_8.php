<?php

use yii\db\Migration;

/**
 * จัดระเบียบสถานะใบสั่งซื้อ (orders name='order') ให้ตรง taxonomy order_status:
 *   7 = ส่งบัญชี/ส่งการเงิน, 8 = ยกเลิก
 *
 * โค้ดเดิมบันทึกการ "ยกเลิก" ไว้ที่ status = 7 (ทับความหมาย "ส่งบัญชี")
 * migration นี้ย้ายใบที่ยกเลิกเดิม (status 7) ไปเป็น status 8
 *
 * กันพลาด: ย้ายเฉพาะใบที่ "ยังไม่เคยส่งการเงิน" (ไม่มีสำเนาในกล่องรอรับ finance_inbox)
 * เพื่อไม่ให้ใบที่ส่งบัญชีจริง ๆ (มี finance_inbox) ถูกเปลี่ยนเป็นยกเลิก
 */
class m260922_190000_fix_purchase_cancel_status_7_to_8 extends Migration
{
    public function safeUp()
    {
        $affected = $this->db->createCommand(
            "UPDATE {{%orders}}
             SET status = 8
             WHERE name = 'order'
               AND status = 7
               AND id NOT IN (
                   SELECT source_id FROM {{%finance_inbox}}
                   WHERE source_system = 'purchase'
               )"
        )->execute();
        echo "    > ย้ายใบสั่งซื้อที่ยกเลิก (status 7 -> 8): {$affected} รายการ\n";
    }

    public function safeDown()
    {
        echo "m260922_190000_fix_purchase_cancel_status_7_to_8 ไม่รองรับการย้อนกลับ (ข้อมูลสถานะถูกจัดระเบียบแล้ว)\n";
        return false;
    }
}
