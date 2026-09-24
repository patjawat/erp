<?php

use yii\db\Migration;

/**
 * งานจัดซื้อผูกแผนรายปี: ใบขอซื้อปีที่เปิด "จัดซื้อผูกแผน" ยังไม่รู้ว่าในแผน/นอกแผนจนกว่าพัสดุลงทะเบียนคุม
 * → ให้ orders.request_type เป็น NULL ได้ (= รอตรวจแผน)
 * ค่าเริ่มต้นยังเป็น 'planned' เหมือนเดิม ฟอร์ม/ข้อมูลปีเก่าไม่เปลี่ยน — ไม่แตะข้อมูลเดิมแม้แต่แถวเดียว
 */
class m260924_120000_purchase_request_type_nullable extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%orders}}', 'request_type', "ENUM('planned','unplanned') NULL DEFAULT 'planned'");
    }

    public function safeDown()
    {
        // ใบที่ยังรอตรวจแผน (NULL) ต้องมีค่าก่อนกลับเป็น NOT NULL — ถือเป็นนอกแผนเพื่อไม่ให้ผ่านอนุมัติอัตโนมัติ
        $this->update('{{%orders}}', ['request_type' => 'unplanned'], ['request_type' => null]);
        $this->alterColumn('{{%orders}}', 'request_type', "ENUM('planned','unplanned') NOT NULL DEFAULT 'planned'");
    }
}
