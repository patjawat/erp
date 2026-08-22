<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * งานพัสดุ — ระบบสร้างเอกสารที่แก้ไขบนจอได้ก่อนพิมพ์
 *
 *   purchase_doc_template  แม่แบบเอกสาร (ข้อความตั้งต้น + merge tag)
 *   purchase_doc           เอกสารที่สร้างจากแม่แบบแล้ว 1 ฉบับ
 *
 * เจตนาของโครงสร้าง
 *
 * 1) แม่แบบอยู่ในตาราง ไม่ใช่ไฟล์ .docx และไม่ใช่ข้อความในโค้ด
 *    ระบบเดิม (/ms-word/purchase_1..12) เก็บแม่แบบเป็นไฟล์ .docx ใน web/msword
 *    แล้วแทนค่าด้วย TemplateProcessor::setValue() ซึ่งแก้ได้แต่ "ค่า" ไม่ใช่ "ข้อความ"
 *    เวลามีหนังสือเวียนใหม่ (เช่น ว 804 ที่อ้างในเอกสารชุดนี้) งานพัสดุต้องส่งไฟล์ให้
 *    โปรแกรมเมอร์แก้แล้ว deploy ใหม่ เก็บ body_html ในตารางทำให้แก้เองได้จากหน้าเว็บ
 *    ระบบเดิมยังอยู่ครบไม่ถูกแตะ ตารางชุดนี้เป็นทางที่สองที่เดินขนานกันไป
 *
 * 2) purchase_doc.body_html เป็นสำเนาแยกจากแม่แบบ ไม่ใช่การอ้างอิงกลับ
 *    เอกสารที่ออกไปแล้วต้องพิมพ์ซ้ำได้เหมือนฉบับที่เซ็นไปแล้วเสมอ ถ้าเก็บแต่
 *    template_id แล้วไป render ใหม่ตอนพิมพ์ การแก้แม่แบบวันนี้จะย้อนไปเปลี่ยน
 *    ข้อความในเอกสารที่ผู้อำนวยการลงนามไปเมื่อปีที่แล้ว ซึ่งใช้เป็นหลักฐานไม่ได้
 *    ตอนสร้างเอกสารจึง merge ค่าลงแล้วหยุดไว้ที่ body_html ของแถวนั้น
 *
 * 3) เก็บ template_code ซ้ำไว้ใน purchase_doc
 *    ไม่ใส่ FK เพราะแม่แบบอาจถูกปิดใช้หรือลบทิ้งหลังจากเอกสารออกไปแล้ว
 *    แต่ยังต้องรู้ว่าเอกสารฉบับนี้เกิดจากแม่แบบตัวไหนเพื่อการตรวจสอบย้อนหลัง
 *
 * 4) ref_type + ref_id แบบเดียวกับ purchase_bond.source_type
 *    เอกสารหนึ่งฉบับผูกกับใบขอซื้อ กับสัญญา กับหลักประกัน หรือไม่ผูกอะไรเลยก็ได้
 *    ใช้คู่คอลัมน์เดียวกับที่งานหลักประกันใช้อยู่เพื่อไม่ให้มีสองแบบแผนในโมดูลเดียว
 *    ไม่ใส่ FK เพราะปลายทางมีหลายตารางและ orders ใช้ soft delete
 *
 * 5) emblem/font_size/orientation ถูก snapshot ลง purchase_doc ด้วย
 *    ผู้ใช้ปรับสามค่านี้ได้บนหน้าแก้ไข ถ้าไม่เก็บไว้ที่ตัวเอกสาร การพิมพ์ซ้ำจะย้อน
 *    กลับไปใช้ค่าของแม่แบบ ทำให้กระดาษที่พิมพ์รอบสองหน้าตาไม่เหมือนรอบแรก
 */
final class m260812_000007_create_purchase_doc extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        // ── แม่แบบเอกสาร ─────────────────────────────────────────────────────
        $this->createTable('{{%purchase_doc_template}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(50)->notNull()->comment('รหัสอ้างอิงในโค้ด/seed — ห้ามซ้ำ'),
            'name' => $this->string(255)->notNull()->comment('ชื่อที่ผู้ใช้เห็นในรายการเลือกเอกสาร'),
            'category' => $this->string(30)->notNull()->defaultValue('buy')
                ->comment('buy=จัดซื้อจัดจ้าง, contract=สัญญา, bond=หลักประกัน, tor=TOR, general=ทั่วไป'),

            // เอกสารนี้ดึงค่าจากเรื่องประเภทไหน — ตัวเลือกชุดเดียวกับ purchase_bond.source_type
            'ref_type' => $this->string(20)->notNull()->defaultValue('order')
                ->comment('order=ใบขอซื้อ, contract=สัญญา, bond=หลักประกัน, none=ไม่ผูกเรื่อง'),

            'body_html' => $this->getDb()->getSchema()->createColumnSchemaBuilder('mediumtext')->null()
                ->comment('เนื้อเอกสารเป็น HTML พร้อม merge tag {{...}}'),

            'orientation' => $this->string(10)->notNull()->defaultValue('portrait')
                ->comment('portrait=ตั้ง, landscape=นอน'),
            'emblem' => $this->string(10)->notNull()->defaultValue('1.5')
                ->comment('ขนาดตราครุฑเป็นเซนติเมตร — none=ไม่แสดง, 1.5, 3.0'),
            'font_size' => $this->integer()->notNull()->defaultValue(14)
                ->comment('ขนาดฟอนต์เริ่มต้นเป็น pt'),
            'margin_json' => $this->json()->null()->comment('ขอบกระดาษหน่วยมิลลิเมตร {top,right,bottom,left}'),

            'law_ref' => $this->string(255)->null()->comment('ระเบียบ/หนังสือเวียนที่เอกสารนี้อ้างถึง'),
            'note' => $this->text()->null()->comment('คำอธิบายวิธีใช้แม่แบบนี้'),

            'active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
            'sort_order' => $this->integer()->notNull()->defaultValue(0)
                ->comment('ลำดับที่แสดงในรายการเลือกเอกสาร — เลขน้อยขึ้นก่อน'),

            'data_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);

        $this->createIndex('idx-purchase_doc_template-code', '{{%purchase_doc_template}}', 'code', true);
        // หน้าเลือกเอกสารดึงเฉพาะแม่แบบที่เปิดใช้ เรียงตาม sort_order ทุกครั้งที่เปิด
        $this->createIndex('idx-purchase_doc_template-pick', '{{%purchase_doc_template}}', ['active', 'sort_order']);

        // ── เอกสารที่สร้างแล้ว ───────────────────────────────────────────────
        $this->createTable('{{%purchase_doc}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null()->comment('คีย์สำหรับแนบไฟล์ผ่าน filemanager'),
            'doc_no' => $this->string(50)->null()->comment('เลขที่หนังสือที่พิมพ์บนเอกสาร'),
            'thai_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'doc_date' => $this->date()->null()->comment('วันที่ของหนังสือ'),

            'template_id' => $this->integer()->null()->comment('แม่แบบต้นทาง — ว่างได้ถ้าแม่แบบถูกลบไปแล้ว'),
            'template_code' => $this->string(50)->null()->comment('สำเนารหัสแม่แบบไว้ตรวจย้อนหลัง'),
            'title' => $this->string(255)->notNull()->comment('ชื่อเอกสารที่แสดงในทะเบียน'),

            'ref_type' => $this->string(20)->notNull()->defaultValue('none')
                ->comment('order=ใบขอซื้อ, contract=สัญญา, bond=หลักประกัน, none=ไม่ผูกเรื่อง'),
            'ref_id' => $this->integer()->null()->comment('id ของเรื่องต้นทางตาม ref_type'),

            'body_html' => $this->getDb()->getSchema()->createColumnSchemaBuilder('mediumtext')->null()
                ->comment('เนื้อเอกสารหลัง merge และหลังผู้ใช้แก้บนจอ — เป็นฉบับจริงที่ใช้พิมพ์'),

            'orientation' => $this->string(10)->notNull()->defaultValue('portrait'),
            'emblem' => $this->string(10)->notNull()->defaultValue('1.5'),
            'font_size' => $this->integer()->notNull()->defaultValue(14),
            'margin_json' => $this->json()->null(),

            'status' => $this->string(20)->notNull()->defaultValue('draft')
                ->comment('draft=ร่าง แก้ได้, final=ออกเลขแล้ว ไม่ควรแก้'),
            'printed_at' => $this->dateTime()->null()->comment('พิมพ์ครั้งล่าสุดเมื่อไร'),
            'print_count' => $this->integer()->notNull()->defaultValue(0)->comment('พิมพ์ไปแล้วกี่ครั้ง'),

            'note' => $this->text()->null()->comment('หมายเหตุภายใน ไม่ขึ้นบนกระดาษ'),

            'department_id' => $this->integer()->null()->comment('หน่วยงานเจ้าของงาน (tree.id)'),
            'emp_id' => $this->integer()->null()->comment('ผู้จัดทำ (employees.id)'),

            'data_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_at' => $this->dateTime()->null(),
            'deleted_by' => $this->integer()->null(),
        ], $options);

        $this->createIndex('idx-purchase_doc-year_status', '{{%purchase_doc}}', ['thai_year', 'status']);
        // ใช้ทุกครั้งที่เปิดหน้าใบขอซื้อ/สัญญา เพื่อดึงเอกสารที่ออกจากเรื่องนั้น
        $this->createIndex('idx-purchase_doc-ref', '{{%purchase_doc}}', ['ref_type', 'ref_id']);
        $this->createIndex('idx-purchase_doc-template', '{{%purchase_doc}}', 'template_id');
        $this->createIndex('idx-purchase_doc-doc_no', '{{%purchase_doc}}', 'doc_no');
        $this->createIndex('idx-purchase_doc-deleted', '{{%purchase_doc}}', 'deleted_at');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%purchase_doc}}');
        $this->dropTable('{{%purchase_doc_template}}');
    }
}
