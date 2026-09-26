<?php

use yii\db\Migration;

/**
 * ลดขั้นตอนเจ้าหนี้: การเงิน "รับเอกสาร" ครั้งเดียว = เข้าทะเบียนเจ้าหนี้ทันที (ไม่มีร่าง/ส่งตรวจ/อนุมัติ)
 * - เลขใบแจ้งหนี้ไม่บังคับตอนรับ (ใส่ภายหลังได้)
 * - รายการเดิมที่ค้างเป็นร่าง/รอตรวจ/ส่งกลับแก้ไข → เข้าทะเบียน (approved) พร้อมเลขทะเบียน
 */
class m260926_120000_simplify_payable_receive extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%finance_payable}}', 'invoice_no', $this->string(100)->null());
        $this->execute("
            UPDATE {{%finance_payable}}
            SET status = 'approved',
                approved_at = COALESCE(approved_at, NOW()),
                payable_no = CONCAT('AP-', YEAR(COALESCE(created_at, NOW())), '-', LPAD(id, 6, '0'))
            WHERE status IN ('draft', 'pending_approval', 'needs_revision')
        ");
        // เอกสารในกล่องที่ตั้งเจ้าหนี้ไปแล้ว ถือว่ารับแล้ว
        $this->execute("
            UPDATE {{%finance_inbox}} i
            JOIN {{%finance_payable}} p ON p.finance_inbox_id = i.id
            SET i.status = 'accepted', i.reviewed_at = COALESCE(i.reviewed_at, NOW())
            WHERE i.status = 'pending_review'
        ");
    }

    public function safeDown()
    {
        // สถานะที่เปลี่ยนไปแล้วย้อนไม่ได้ (ไม่รู้สถานะเดิม) — คืนเฉพาะโครงคอลัมน์
        $this->execute("UPDATE {{%finance_payable}} SET invoice_no = CONCAT('NOINV-', id) WHERE invoice_no IS NULL");
        $this->alterColumn('{{%finance_payable}}', 'invoice_no', $this->string(100)->notNull());
    }
}
