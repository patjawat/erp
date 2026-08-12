<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * งานเขียน TOR / ข้อกำหนดคุณลักษณะ — เฟส 1
 *
 *   purchase_tor            หัวเอกสาร TOR 1 ใบ
 *   purchase_tor_price        ใบสืบราคา — 1 แถว = ผู้เสนอราคา 1 ราย (ระเบียบกำหนด "ไม่น้อยกว่า 3" จึงไม่ล็อก 3 แถว)
 *   purchase_tor_template   คลังแม่แบบคุณลักษณะ ใช้เติมฟอร์มตอนสร้าง TOR ใหม่
 *
 * หลักการเก็บข้อมูล: แยก "เนื้อความที่คนอ่าน" ออกจาก "ตัวเลขที่เครื่องคำนวณ"
 *   - เนื้อความ (purpose/spec/standard/warranty/delivery_term/payment_term/vendor_qualification)
 *     เก็บเป็น HTML เพราะผู้ใช้จัดรูปแบบเองได้เหมือน Word (ตัวหนา/ข้อ 1.2.3./ตาราง)
 *     ต้องผ่าน HtmlPurifier ทุกครั้งก่อนบันทึก — ดู Tor::beforeSave()
 *   - ตัวเลข (qty/delivery_days/mid_price/price) เก็บเป็นชนิดตัวเลขจริง ไม่ปนหน่วยนับ
 *     เพราะเฟสถัดไปใบขอซื้อจะดึงค่าเหล่านี้ไปคำนวณต่อ ถ้าเก็บเป็นข้อความจะดึงไปใช้ไม่ได้
 *
 * ค่าอ้างอิงใช้ทะเบียนกลางที่มีอยู่แล้ว ไม่สร้างชุดใหม่:
 *   asset_type_id     -> categorise name='asset_type' (57 รายการ)
 *   purchase_method   -> categorise name='purchase'   (19 วิธี)
 *   unit_name         -> categorise name='unit' เก็บเป็น title ตามแบบที่โมดูล sm ใช้อยู่ (unit ไม่มี code)
 *   vendor_id         -> categorise name='vendor' เก็บเป็น code เหมือน orders.vendor_id
 */
final class m260812_000001_create_purchase_tor extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        // ── หัวเอกสาร TOR ────────────────────────────────────────────────────
        $this->createTable('{{%purchase_tor}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'doc_no' => $this->string(50)->null()->comment('เลขที่เอกสาร TOR'),
            'thai_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'title' => $this->string(255)->notNull()->comment('ชื่อโครงการ/รายการพัสดุ'),

            // ── ข้อมูลทั่วไป ──
            'asset_type_id' => $this->string(50)->null()->comment('ประเภทพัสดุ -> categorise name=asset_type code'),
            'purchase_method' => $this->string(50)->null()->comment('วิธีจัดซื้อจัดจ้าง -> categorise name=purchase code'),
            'budget' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('วงเงินงบประมาณ'),
            'tor_date' => $this->date()->null()->comment('วันที่จัดทำ TOR'),
            'egp_no' => $this->string(50)->null()->comment('เลขที่โครงการ e-GP'),
            'status' => $this->string(20)->notNull()->defaultValue('draft')
                ->comment('draft=ร่าง, submitted=รออนุมัติ, approved=อนุมัติแล้ว, cancelled=ยกเลิก'),
            'purpose' => $this->text()->null()->comment('วัตถุประสงค์และความจำเป็น (HTML)'),

            // ── คุณลักษณะ ──
            'qty' => $this->decimal(15, 2)->null()->comment('จำนวน — ตัวเลขล้วน ไม่รวมหน่วยนับ'),
            'unit_name' => $this->string(50)->null()->comment('หน่วยนับ -> categorise name=unit (เก็บ title)'),
            'spec' => $this->getDb()->getSchema()->createColumnSchemaBuilder('mediumtext')->null()
                ->comment('คุณลักษณะเฉพาะ (HTML) — ห้ามระบุยี่ห้อ/แหล่งกำเนิด ตาม พ.ร.บ. 2560 ม.7'),
            'standard' => $this->text()->null()->comment('มาตรฐาน/การรับรองคุณภาพ (HTML)'),
            'warranty' => $this->text()->null()->comment('เงื่อนไขการรับประกัน (HTML)'),

            // ── เงื่อนไข ──
            'delivery_days' => $this->integer()->null()->comment('ระยะเวลาส่งมอบ (วันทำการ)'),
            'delivery_place' => $this->string(255)->null()->comment('สถานที่ส่งมอบ'),
            'delivery_term' => $this->text()->null()->comment('เงื่อนไขการส่งมอบ (HTML)'),
            'payment_term' => $this->text()->null()->comment('เงื่อนไขการชำระเงิน (HTML)'),
            'vendor_qualification' => $this->text()->null()->comment('คุณสมบัติผู้เสนอราคา (HTML)'),

            // ── ราคากลาง ──
            'mid_price' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ราคากลาง'),
            'mid_method' => $this->string(100)->null()->comment('วิธีกำหนดราคากลาง'),
            'mid_note' => $this->text()->null()->comment('หมายเหตุราคากลาง'),

            // ── ผู้จัดทำ ──
            'department_id' => $this->integer()->null()->comment('หน่วยงานเจ้าของ TOR (tree.id)'),
            'emp_id' => $this->integer()->null()->comment('ผู้จัดทำ (employees.id)'),

            'data_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_at' => $this->dateTime()->null(),
            'deleted_by' => $this->integer()->null(),
        ], $options);

        $this->createIndex('idx-purchase_tor-year_status', '{{%purchase_tor}}', ['thai_year', 'status']);
        $this->createIndex('idx-purchase_tor-doc_no', '{{%purchase_tor}}', 'doc_no');
        $this->createIndex('idx-purchase_tor-deleted', '{{%purchase_tor}}', 'deleted_at');

        // ── ใบสืบราคา (N แถว) ────────────────────────────────────────────────
        // ผู้เสนอราคาเลือกจากทะเบียนผู้แทนจำหน่ายได้ หรือพิมพ์ชื่อเองเมื่อยังไม่มีในทะเบียน
        // จึงเก็บทั้ง vendor_id และ vendor_name — เวลาแสดงผลให้ยึด vendor_name ที่ snapshot ไว้
        // เพื่อให้เอกสารเก่าคงชื่อเดิมแม้ทะเบียนผู้ขายถูกแก้ภายหลัง
        $this->createTable('{{%purchase_tor_price}}', [
            'id' => $this->primaryKey(),
            'tor_id' => $this->integer()->notNull(),
            'seq' => $this->integer()->notNull()->defaultValue(0)->comment('ลำดับแถวที่แสดง'),
            'vendor_id' => $this->string(255)->null()->comment('-> categorise name=vendor code (null ได้ถ้าพิมพ์ชื่อเอง)'),
            'vendor_name' => $this->string(255)->null()->comment('ชื่อผู้เสนอราคา/แหล่งอ้างอิง'),
            'detail' => $this->string(500)->null()->comment('รายละเอียดที่เสนอ'),
            'price' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
        ], $options);

        $this->createIndex('idx-purchase_tor_price-tor', '{{%purchase_tor_price}}', ['tor_id', 'seq']);
        $this->addForeignKey(
            'fk-purchase_tor_price-tor',
            '{{%purchase_tor_price}}',
            'tor_id',
            '{{%purchase_tor}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // ── คลังแม่แบบคุณลักษณะ ──────────────────────────────────────────────
        // ref_price = ราคาที่เคยพบในตลาด ใช้ "ดูประกอบ" ตอนเลือกแม่แบบเท่านั้น
        // ห้ามนำไปเติมตารางสืบราคาอัตโนมัติ — ราคาสืบต้องมาจากการสืบราคาจริงทุกแถว
        $this->createTable('{{%purchase_tor_template}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(50)->null()->comment('รหัสอ้างอิงแม่แบบ'),
            'category' => $this->string(50)->null()->comment('หมวดแม่แบบ เช่น computer/printer/network'),
            'title' => $this->string(255)->notNull()->comment('ชื่อรายการครุภัณฑ์/พัสดุ'),
            'unit_name' => $this->string(50)->null()->comment('หน่วยนับที่ใช้บ่อย'),
            'delivery_days' => $this->integer()->null()->comment('ระยะเวลาส่งมอบที่ใช้บ่อย (วัน)'),
            'warranty' => $this->string(255)->null()->comment('เงื่อนไขรับประกันที่ใช้บ่อย'),
            'standard' => $this->string(255)->null()->comment('มาตรฐาน/การรับรองที่ใช้บ่อย'),
            'spec' => $this->getDb()->getSchema()->createColumnSchemaBuilder('mediumtext')->null()
                ->comment('คุณลักษณะเฉพาะตั้งต้น (HTML)'),
            'ref_price' => $this->decimal(15, 2)->null()->comment('ราคาอ้างอิงตลาด — แสดงประกอบเท่านั้น ไม่เติมลงใบสืบราคา'),
            'active' => $this->tinyInteger(1)->notNull()->defaultValue(1)
                ->comment('0 = ซ่อนจากหน้าเลือกแม่แบบ (ใช้ปิดหมวดที่หน่วยงานไม่ใช้ โดยไม่ต้องลบ)'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'data_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);

        $this->createIndex('idx-purchase_tor_template-cat', '{{%purchase_tor_template}}', ['active', 'category', 'sort_order']);

        // ── เตรียมช่องเชื่อมใบขอซื้อ (ยังไม่เปิดใช้ในเฟสนี้) ──────────────────
        // เฟสถัดไป: ใบขอซื้อจะดึงคุณลักษณะ/ราคากลางจาก TOR มาใช้ แล้วบันทึก tor_id ไว้ตรงนี้
        // ใส่คอลัมน์ไว้ตั้งแต่ตอนนี้เพื่อให้ไม่ต้องแก้ตาราง orders ซ้ำอีกรอบ
        $this->addColumn('{{%orders}}', 'tor_id', $this->integer()->null()->after('plan_order_id'));
        $this->createIndex('idx-orders-tor', '{{%orders}}', 'tor_id');
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx-orders-tor', '{{%orders}}');
        $this->dropColumn('{{%orders}}', 'tor_id');

        $this->dropTable('{{%purchase_tor_template}}');
        $this->dropForeignKey('fk-purchase_tor_price-tor', '{{%purchase_tor_price}}');
        $this->dropTable('{{%purchase_tor_price}}');
        $this->dropTable('{{%purchase_tor}}');
    }
}
