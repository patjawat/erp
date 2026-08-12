<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * งานหลักประกัน — เฟส 1 (ทะเบียนหลักประกัน + เกณฑ์ตามวงเงินที่แก้เองได้)
 *
 *   purchase_bond         หลักประกัน 1 ใบ
 *   purchase_bond_policy  เกณฑ์ว่าวงเงินเท่าไรต้องวางกี่เปอร์เซ็นต์
 *
 * เจตนาของโครงสร้าง
 *
 * 1) หลักประกันเป็นตารางแยก ไม่ใช่คอลัมน์ในสัญญา
 *    งานหนึ่งงานมีหลักประกันได้หลายใบจริง — หลักประกันสัญญาคืนเมื่อตรวจรับ
 *    ส่วนหลักประกันผลงานอยู่ต่อจนพ้นระยะรับประกัน และงาน e-bidding ยังมี
 *    หลักประกันซองก่อนหน้านั้นอีกใบ โปรแกรมต้นแบบเก็บเป็น object เดียวฝังใน
 *    เรคคอร์ดสัญญา (c.bond) ใบที่สองจึงต้องไปสร้างเป็น "รายการอิสระ" ที่ไม่มี
 *    ทางโยงกลับมาที่สัญญาได้อีก
 *
 * 2) source_type + source_id แทนการมี contract_id/order_id แยกคอลัมน์
 *    หลักประกันผูกได้ทั้งกับสัญญา กับใบสั่งซื้อ และลอยไม่ผูกอะไรเลย (หลักประกันซอง
 *    เกิดก่อนมีสัญญา) การเปลี่ยนว่าผูกกับอะไรจึงเป็นการแก้สองคอลัมน์นี้
 *    ไม่ใช่การคัดลอกข้อมูลไปอีกที่ — ต้นแบบคัดลอกค่าหลักประกันจากใบสั่งซื้อไปสัญญา
 *    ตอนสร้างสัญญา ทำให้หลักประกันใบเดียวถูกนับสองรอบและยอดรวมในทะเบียนบวกซ้ำ
 *    ไม่ใส่ FK เพราะปลายทางมีสองตารางและ orders ใช้ soft delete
 *
 * 3) แยก "ประเภท" (bond_type) ออกจาก "รูปแบบ" (bond_form)
 *    ประเภท = หลักประกันสัญญา/ซอง/ผลงาน ซึ่งเป็นตัวบอกว่าคืนเมื่อไรตามกฎหมาย
 *    รูปแบบ = เงินสด/หนังสือค้ำประกันธนาคาร/พันธบัตร/เช็ค ซึ่งเป็นตัวบอกว่าคืนอย่างไร
 *    ต้นแบบมีช่องเดียวชื่อ "ประเภท" แต่ในฟอร์มสัญญาใส่ค่ารูปแบบลงไป ส่วนฟอร์มอิสระ
 *    ใส่ค่าประเภท คอลัมน์เดียวกันในทะเบียนจึงมีข้อมูลคนละความหมายปนกัน
 *
 * 4) เก็บ base_amount และ rate ไม่ใช่เก็บแต่ผลลัพธ์
 *    หลักประกันที่วางไว้เมื่อสองปีก่อนต้องอธิบายได้ว่าคิดจากวงเงินเท่าไรที่อัตราเท่าไร
 *    ถ้าเก็บแต่ยอด เมื่อเกณฑ์ในตาราง policy เปลี่ยน ของเก่าจะอธิบายตัวเองไม่ได้
 *
 * 5) การคืนมีคอลัมน์ของตัวเอง (return_date/return_doc_no/return_note)
 *    ต้นแบบมีปุ่ม "ขอคืนหลักประกัน" ที่เปิดไฟล์ Word อย่างเดียว ไม่บันทึกอะไรลงฐาน
 *    และไม่เปลี่ยนสถานะ ทะเบียนจึงไม่มีทางรู้ว่าใบไหนคืนไปแล้วเมื่อไรด้วยหนังสือฉบับใด
 *
 * 6) เกณฑ์วงเงินอยู่ในตาราง ไม่ได้เขียนไว้ในโค้ด
 *    ด้วยเหตุผลเดียวกับ purchase_wht_rate — ตัวเลข 50,000/100,000/5% มาจาก
 *    ระเบียบและหนังสือเวียนที่แก้ไขกันได้ และข้อความที่ขึ้นบนหน้าจอต้องถูกสร้างจาก
 *    แถวในตารางนี้เสมอ ไม่ใช่ข้อความคงที่ (ต้นแบบเขียนป้ายว่า "≥ 100,000 ต้องวาง"
 *    แต่โค้ดยกเว้นให้เมื่อ ≤ 100,000 — ที่ 100,000 พอดีป้ายกับผลลัพธ์ขัดกันเอง)
 */
final class m260812_000005_create_purchase_bond extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        // ── ทะเบียนหลักประกัน ────────────────────────────────────────────────
        $this->createTable('{{%purchase_bond}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null()->comment('คีย์สำหรับแนบไฟล์ผ่าน filemanager'),
            'doc_no' => $this->string(50)->null()->comment('เลขที่ในระบบ'),
            'thai_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'title' => $this->string(255)->notNull()->comment('รายการ/โครงการที่หลักประกันนี้ค้ำอยู่'),

            // ── ผูกกับเอกสารต้นทาง ──
            'source_type' => $this->string(20)->notNull()->defaultValue('none')
                ->comment('contract=สัญญา, order=ใบสั่งซื้อ, none=ไม่ผูก (เช่น หลักประกันซองที่ยังไม่มีสัญญา)'),
            'source_id' => $this->integer()->null()->comment('id ของเอกสารต้นทางตาม source_type'),

            // ── คู่สัญญา ──
            'vendor_id' => $this->string(255)->null()->comment('-> categorise name=vendor code'),
            'vendor_name' => $this->string(255)->null()->comment('ชื่อผู้วางหลักประกัน snapshot'),

            // ── ตัวหลักประกัน ──
            'bond_type' => $this->string(20)->notNull()->defaultValue('contract')
                ->comment('contract=หลักประกันสัญญา, bid=หลักประกันซอง, performance=หลักประกันผลงาน,'
                    . ' advance=หลักประกันเงินล่วงหน้า, other=อื่น ๆ'),
            'bond_form' => $this->string(20)->notNull()->defaultValue('bank_guarantee')
                ->comment('cash=เงินสด, bank_guarantee=หนังสือค้ำประกันธนาคาร, gov_bond=พันธบัตรรัฐบาล,'
                    . ' cheque=เช็คที่ธนาคารรับรอง, other=อื่น ๆ'),
            'doc_ref' => $this->string(100)->null()->comment('เลขที่หนังสือค้ำประกัน/หลักฐานการวาง'),
            'issuer' => $this->string(255)->null()->comment('ธนาคารหรือผู้ออกหลักฐาน'),

            'base_amount' => $this->decimal(15, 2)->null()->comment('วงเงินที่ใช้เป็นฐานคิดหลักประกัน'),
            'rate' => $this->decimal(5, 2)->null()->comment('อัตรา (%) ที่ใช้กับฐานข้างต้น'),
            'amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('วงเงินหลักประกันที่วางจริง'),

            'place_date' => $this->date()->null()->comment('วันที่วางหลักประกัน'),
            'expiry_date' => $this->date()->null()->comment('วันสิ้นอายุของหลักประกัน'),

            'status' => $this->string(20)->notNull()->defaultValue('pending')
                ->comment('pending=ยังไม่วาง, active=วางแล้ว, returned=คืนแล้ว,'
                    . ' seized=ยึดเป็นรายได้แผ่นดิน, exempt=ได้รับยกเว้น'),
            'exempt_reason' => $this->string(255)->null()->comment('เหตุผลที่ยกเว้น — บังคับกรอกเมื่อ status=exempt'),

            // ── การคืน ──
            'return_date' => $this->date()->null()->comment('วันที่คืนหลักประกันจริง'),
            'return_doc_no' => $this->string(100)->null()->comment('เลขที่หนังสือคืนหลักประกัน'),
            'return_note' => $this->text()->null()->comment('บันทึกการคืน/การยึด'),

            'note' => $this->text()->null()->comment('หมายเหตุภายใน'),

            'department_id' => $this->integer()->null()->comment('หน่วยงานเจ้าของงาน (tree.id)'),
            'emp_id' => $this->integer()->null()->comment('ผู้บันทึก (employees.id)'),

            'data_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_at' => $this->dateTime()->null(),
            'deleted_by' => $this->integer()->null(),
        ], $options);

        $this->createIndex('idx-purchase_bond-year_status', '{{%purchase_bond}}', ['thai_year', 'status']);
        // ใช้ทุกครั้งที่เปิดหน้าสัญญาเพื่อดึงหลักประกันของสัญญาฉบับนั้น
        $this->createIndex('idx-purchase_bond-source', '{{%purchase_bond}}', ['source_type', 'source_id']);
        // หน้าทะเบียนกรองใบที่หมดอายุ/ใกล้หมดอายุทุกครั้งที่เปิด
        $this->createIndex('idx-purchase_bond-expiry', '{{%purchase_bond}}', 'expiry_date');
        $this->createIndex('idx-purchase_bond-doc_no', '{{%purchase_bond}}', 'doc_no');
        $this->createIndex('idx-purchase_bond-deleted', '{{%purchase_bond}}', 'deleted_at');

        // ── เกณฑ์หลักประกันตามวงเงิน ─────────────────────────────────────────
        // ช่วงวงเงินนับรวมปลายทั้งสองข้าง (min_amount ≤ วงเงิน ≤ max_amount)
        // max_amount = NULL หมายถึงไม่จำกัดปลายบน
        $this->createTable('{{%purchase_bond_policy}}', [
            'id' => $this->primaryKey(),
            'proc_kind' => $this->string(20)->notNull()->defaultValue('any')
                ->comment('ตรงกับ purchase_contract.contract_type หรือ any=ใช้กับทุกประเภท'),
            'title' => $this->string(255)->notNull()->comment('คำอธิบายที่แสดงให้ผู้ใช้เห็นเมื่อเข้าเกณฑ์นี้'),
            'min_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('วงเงินตั้งแต่ (รวมค่านี้)'),
            'max_amount' => $this->decimal(15, 2)->null()->comment('วงเงินถึง (รวมค่านี้) — ว่าง = ไม่จำกัด'),
            'required' => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('1 = ต้องวางหลักประกัน'),
            'rate' => $this->decimal(5, 2)->notNull()->defaultValue(0)->comment('อัตราหลักประกัน (%) ของวงเงิน'),
            'law_ref' => $this->string(255)->null()->comment('ระเบียบ/หนังสือเวียนที่อ้างอิง'),
            'note' => $this->text()->null(),
            'active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
            // แถวที่เจาะจงประเภทต้องมาก่อนแถว any ที่ครอบช่วงเดียวกัน ตัวจับคู่หยิบแถวแรกที่เข้าเกณฑ์
            'sort_order' => $this->integer()->notNull()->defaultValue(0)->comment('ลำดับการจับคู่ — เลขน้อยถูกพิจารณาก่อน'),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);

        $this->createIndex('idx-purchase_bond_policy-match', '{{%purchase_bond_policy}}', ['active', 'sort_order']);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%purchase_bond_policy}}');
        $this->dropTable('{{%purchase_bond}}');
    }
}
