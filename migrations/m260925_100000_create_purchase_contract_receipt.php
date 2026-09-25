<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ตรวจรับรายงวด (บริหารสัญญา) — เฟส 0 โครงสร้างข้อมูล
 * สเปก: docs/purchase/contract-installment-receipt-spec.md
 *
 * ปัญหา: ใบสั่งซื้อออกเต็มวงเงิน (เช่น e-bidding ฟอกไต 16 ล.) แต่ตรวจรับ/ตั้งหนี้เป็นรายเดือน
 * ระบบเดิมตรวจรับและส่งการเงินได้ครั้งเดียวต่อใบ
 *
 *   purchase_contract            + billing_mode / period_unit / closed_at / closed_note
 *   purchase_contract_receipt    การตรวจรับที่เกิดขึ้นจริง 1 แถว = 1 งวด = 1 รายการส่งการเงิน
 *   purchase_contract_receipt_item  รายการในงวด (ปริมาณจริง × ราคาต่อหน่วย)
 *
 * เจตนาของโครงสร้าง
 * 1) แยก "กำหนดการ" (purchase_contract_milestone) ออกจาก "การตรวจรับจริง" (receipt)
 *    สัญญาราคาต่อหน่วยไม่มียอดงวดล่วงหน้า — ยอดเกิดจากปริมาณจริงของเดือนนั้น
 * 2) ไม่เพิ่มคอลัมน์ใน orders — ใบที่ตรวจรับรายงวด = มีสัญญาผูกและ billing_mode <> 'lump'
 * 3) ยอดคงเหลือของสัญญาคำนวณจาก receipt ไม่เก็บซ้ำ
 * 4) thai_year ของงวดยึดวันตรวจรับของงวด (สัญญาข้ามปีงบได้)
 * 5) ไม่ใส่ FK ไป orders (soft delete + ข้อมูลจริงเดินอยู่) แบบเดียวกับ purchase_contract.order_id
 */
final class m260925_100000_create_purchase_contract_receipt extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        // ── หัวสัญญา: รูปแบบการตรวจรับ/เบิกจ่าย + ปิดสัญญา ───────────────────
        $this->addColumn('{{%purchase_contract}}', 'billing_mode', $this->string(20)->notNull()->defaultValue('lump')
            ->comment('lump=ตรวจรับครั้งเดียว, fixed=งวดยอดตายตัว, unit_price=ราคาต่อหน่วยตามปริมาณจริงไม่เกินวงเงิน')
            ->after('contract_type'));
        $this->addColumn('{{%purchase_contract}}', 'period_unit', $this->string(10)->null()
            ->comment('month=งวดรายเดือน, custom=กำหนดเอง')
            ->after('billing_mode'));
        $this->addColumn('{{%purchase_contract}}', 'closed_at', $this->dateTime()->null()
            ->comment('ปิดสัญญา (ตรวจรับงวดสุดท้าย/สิ้นสุดสัญญา)')
            ->after('status'));
        $this->addColumn('{{%purchase_contract}}', 'closed_note', $this->string(500)->null()
            ->comment('เหตุผลปิดสัญญา เช่น ใช้ไม่ครบวงเงิน คืนเงินเหลือจ่าย')
            ->after('closed_at'));

        // ── การตรวจรับรายงวด ──────────────────────────────────────────────────
        $this->createTable('{{%purchase_contract_receipt}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(64)->null()->comment('token ไฟล์แนบ (สแกนใบตรวจรับ/ใบแจ้งหนี้)'),
            'contract_id' => $this->integer()->notNull(),
            'order_id' => $this->integer()->null()->comment('สำเนาจาก contract.order_id เพื่อ query จากฝั่งใบสั่งซื้อ'),
            'milestone_id' => $this->integer()->null()->comment('งวดตามสัญญาที่ตรงกัน (สัญญา fixed)'),
            'seq' => $this->integer()->notNull()->comment('งวดที่'),
            'period_start' => $this->date()->null()->comment('ผลงานตั้งแต่วันที่'),
            'period_end' => $this->date()->null()->comment('ผลงานถึงวันที่'),
            'thai_year' => $this->integer()->null()->comment('ปีงบของงวด (จาก receive_date)'),
            'invoice_no' => $this->string(100)->null()->comment('เลขที่ใบแจ้งหนี้/ใบส่งมอบงานของผู้รับจ้าง'),
            'invoice_date' => $this->date()->null(),
            'delivered_date' => $this->date()->null()->comment('วันผู้รับจ้างส่งมอบงาน'),
            'receive_date' => $this->date()->null()->comment('วันคณะกรรมการตรวจรับ'),
            'vat_type' => $this->string(10)->null()->comment('IN / EX / NONE — ค่าเริ่มต้นตามใบสั่งซื้อ'),
            'amount_before_vat' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'vat_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ยอดหลัง VAT = ยอดเรียกเก็บ/ตั้งหนี้'),
            'fine_days' => $this->integer()->notNull()->defaultValue(0),
            'fine_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ค่าปรับของงวด (ฐาน = ยอดงวด)'),
            'wht_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ภาษีหัก ณ ที่จ่ายของงวด'),
            'status' => $this->string(20)->notNull()->defaultValue('draft')
                ->comment('draft=ร่าง, received=ตรวจรับแล้ว, sent_finance=ส่งการเงินแล้ว, cancelled=ยกเลิก'),
            'sent_finance_at' => $this->dateTime()->null(),
            'note' => $this->string(500)->null(),
            'data_json' => $this->json()->null()->comment('snapshot กรรมการที่ลงนามงวดนี้ ฯลฯ'),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_at' => $this->dateTime()->null(),
            'deleted_by' => $this->integer()->null(),
        ], $options);

        // งวดที่ซ้ำกันในสัญญาเดียวไม่ได้ — ยกเลิกงวดใช้ status ไม่ใช่ลบ จึงไม่ชน soft delete
        $this->createIndex('uq-purchase_contract_receipt-seq', '{{%purchase_contract_receipt}}', ['contract_id', 'seq'], true);
        $this->createIndex('idx-purchase_contract_receipt-order', '{{%purchase_contract_receipt}}', 'order_id');
        // แดชบอร์ด/กราฟตรวจรับรายเดือน
        $this->createIndex('idx-purchase_contract_receipt-year_date', '{{%purchase_contract_receipt}}', ['thai_year', 'receive_date']);
        $this->createIndex('idx-purchase_contract_receipt-status', '{{%purchase_contract_receipt}}', 'status');
        $this->addForeignKey(
            'fk-purchase_contract_receipt-contract',
            '{{%purchase_contract_receipt}}',
            'contract_id',
            '{{%purchase_contract}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        // ── รายการในงวด ──────────────────────────────────────────────────────
        $this->createTable('{{%purchase_contract_receipt_item}}', [
            'id' => $this->primaryKey(),
            'receipt_id' => $this->integer()->notNull(),
            'order_item_id' => $this->integer()->null()->comment('บรรทัดในใบสั่งซื้อ (orders name=order_item)'),
            'asset_item' => $this->string(255)->null()->comment('รหัสพัสดุ snapshot'),
            'item_name' => $this->string(255)->null()->comment('ชื่อรายการ snapshot'),
            'unit_name' => $this->string(50)->null(),
            'qty' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ปริมาณที่ตรวจรับงวดนี้ เช่น 312 ครั้ง'),
            'unit_price' => $this->decimal(15, 4)->notNull()->defaultValue(0)->comment('ราคาต่อหน่วยตามสัญญา snapshot'),
            'amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('qty × unit_price'),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
        ], $options);

        $this->createIndex('idx-purchase_contract_receipt_item-receipt', '{{%purchase_contract_receipt_item}}', 'receipt_id');
        $this->createIndex('idx-purchase_contract_receipt_item-order_item', '{{%purchase_contract_receipt_item}}', 'order_item_id');
        $this->addForeignKey(
            'fk-purchase_contract_receipt_item-receipt',
            '{{%purchase_contract_receipt_item}}',
            'receipt_id',
            '{{%purchase_contract_receipt}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-purchase_contract_receipt_item-receipt', '{{%purchase_contract_receipt_item}}');
        $this->dropTable('{{%purchase_contract_receipt_item}}');
        $this->dropForeignKey('fk-purchase_contract_receipt-contract', '{{%purchase_contract_receipt}}');
        $this->dropTable('{{%purchase_contract_receipt}}');
        $this->dropColumn('{{%purchase_contract}}', 'closed_note');
        $this->dropColumn('{{%purchase_contract}}', 'closed_at');
        $this->dropColumn('{{%purchase_contract}}', 'period_unit');
        $this->dropColumn('{{%purchase_contract}}', 'billing_mode');
    }
}
