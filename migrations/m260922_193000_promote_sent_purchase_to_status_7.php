<?php

use yii\db\Migration;

/**
 * ใบสั่งซื้อที่เคย "ส่งการเงิน" ไว้ก่อนมีฟีเจอร์เดินสถานะ จะมีสำเนาในกล่องรอรับ
 * (finance_inbox source_system='purchase') แต่สถานะยังค้างที่ 6 (วัสดุเข้าคลัง)
 * migration นี้เดินให้ขึ้นเป็น 7 (ส่งบัญชี) เพื่อให้สถานะสอดคล้องกับความจริง
 */
class m260922_193000_promote_sent_purchase_to_status_7 extends Migration
{
    public function safeUp()
    {
        $affected = $this->db->createCommand(
            "UPDATE {{%orders}}
             SET status = 7
             WHERE name = 'order'
               AND status = 6
               AND id IN (
                   SELECT source_id FROM {{%finance_inbox}}
                   WHERE source_system = 'purchase'
               )"
        )->execute();
        echo "    > เดินสถานะใบที่ส่งการเงินแล้วเป็น 'ส่งบัญชี' (6 -> 7): {$affected} รายการ\n";
    }

    public function safeDown()
    {
        echo "m260922_193000_promote_sent_purchase_to_status_7 ไม่รองรับการย้อนกลับ\n";
        return false;
    }
}
