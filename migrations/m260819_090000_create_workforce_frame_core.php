<?php

use yii\db\Migration;

/**
 * โครงตารางกรอบอัตรากำลัง — อ้างเกณฑ์ สป.สธ. (กองบริหารทรัพยากรบุคคล)
 *
 * แนวคิด: เกณฑ์ไม่ได้ให้ "ตัวเลขกรอบ" สำเร็จรูป แต่ให้
 *   1. สายงานไหนมีกรอบได้ในโรงพยาบาลขนาดไหน  -> workforce_standard_rule.eligible
 *   2. วิธีคำนวณของแต่ละสายงาน                  -> workforce_standard_line.method
 *   3. สูตร/อัตราส่วนของบางสายงาน               -> workforce_standard_line.formula_json
 * ตัวเลขที่เข้าสูตรเป็นของโรงพยาบาลแต่ละแห่ง      -> workforce_profile (แยกรายปี)
 *
 * ตารางเกณฑ์ (standard_*) ต้องมาพร้อม migration ให้ครบทุกระดับ A..F3
 * เพื่อให้โรงพยาบาลอื่นที่ติดตั้งระบบนี้ใช้ได้ทันทีโดยไม่ต้องป้อนเกณฑ์เอง
 */
class m260819_090000_create_workforce_frame_core extends Migration
{
    public function safeUp()
    {
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        // ── ตัวขับเคลื่อนของโรงพยาบาล แยกรายปี (เตียง/ประชากร/รถ เปลี่ยนได้ทุกปี) ──
        $this->createTable('{{%workforce_profile}}', [
            'id' => $this->primaryKey(),
            'thai_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'level_code' => $this->string(10)->null()->comment('ระดับ รพ. A/S/M1/M2/F1/F2/F3'),
            'bed_total' => $this->integer()->null()->comment('เตียงทั้งหมด — ใช้หาช่วงเกณฑ์สายสนับสนุน'),
            'active_bed' => $this->integer()->null()->comment('Active bed — นักโภชนาการ 1:50'),
            'ward_count' => $this->integer()->null()->comment('จำนวนหอผู้ป่วย — พยาบาล/ทำความสะอาด'),
            'catchment_population' => $this->integer()->null()->comment('ประชากรที่รับผิดชอบระดับ CUP'),
            'vehicle_count' => $this->integer()->null()->comment('รถที่ใช้งาน — ขับรถ 70%'),
            'office_area_sqm' => $this->decimal(12, 2)->null()->comment('พื้นที่สำนักงาน ตร.ม. — ทำความสะอาด 800 ตร.ม./คน'),
            'garden_rai' => $this->decimal(10, 2)->null()->comment('พื้นที่สวน ไร่ — เกษตรพื้นฐาน 3 ไร่/คน'),
            'security_post' => $this->integer()->null()->comment('จุดรักษาความปลอดภัย — 1 จุด/คน/เวร 8 ชม.'),
            'laundry_kg_per_day' => $this->decimal(10, 2)->null()->comment('ผ้าสะอาด กก./วัน — ซักฟอก 150 กก./คน/วัน'),
            'data_json' => $this->json()->null()->comment('ตัวขับเคลื่อนเพิ่มเติมที่เกณฑ์รุ่นใหม่อาจต้องใช้'),
            'note' => $this->text()->null(),
            'created_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_at' => $this->dateTime()->null(),
            'updated_by' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('uq-workforce-profile-year', '{{%workforce_profile}}', 'thai_year', true);

        // ── สายงานมาตรฐานตามเกณฑ์ (ไม่ผูกกับตำแหน่งของโรงพยาบาลใด) ──
        $this->createTable('{{%workforce_standard_line}}', [
            'id' => $this->primaryKey(),
            'edition' => $this->string(30)->notNull()->comment('รุ่นของเกณฑ์ เช่น MOPH-2565-2569'),
            'org_type' => $this->string(20)->notNull()->defaultValue('HOSPITAL')->comment('HOSPITAL/PCU/DHO — เผื่อ รพ.สต. และ สสอ.'),
            'seq' => $this->integer()->null()->comment('ลำดับที่ในเอกสารเกณฑ์'),
            'category' => $this->string(20)->notNull()->comment('professional=สายวิชาชีพ, support=สายสนับสนุน, service=บริการพื้นฐาน'),
            'title' => $this->string(255)->notNull()->comment('ชื่อสายงานตามเกณฑ์'),
            'method' => $this->string(20)->notNull()->defaultValue('manual')
                ->comment('fte|service_based|population_based|ratio|manual — วิธีได้มาซึ่งกรอบ'),
            'formula_json' => $this->json()->null()->comment('พารามิเตอร์สูตร เช่น {"driver":"active_bed","per":50}'),
            'note' => $this->text()->null(),
            'sort' => $this->integer()->notNull()->defaultValue(0),
            'active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
        ], $tableOptions);
        $this->createIndex('uq-wf-line-edition-seq-title', '{{%workforce_standard_line}}', ['edition', 'org_type', 'seq', 'title'], true);
        $this->createIndex('idx-wf-line-category', '{{%workforce_standard_line}}', ['edition', 'category']);

        // ── สายงานนี้มีกรอบได้ไหม ในขนาดไหน ──
        $this->createTable('{{%workforce_standard_rule}}', [
            'id' => $this->primaryKey(),
            'line_id' => $this->integer()->notNull(),
            'level_code' => $this->string(10)->notNull()->comment('A/S/M1/M2/F1/F2/F3'),
            'size_band' => $this->string(30)->null()
                ->comment('ช่วงเตียงย่อยของระดับ เช่น 101-200 (null = ทั้งระดับ)'),
            'eligible' => $this->tinyInteger(1)->null()
                ->comment('1=มีกรอบได้ 0=ไม่มีกรอบ NULL=ยังไม่ได้ยืนยันจากเอกสาร'),
            'min_qty' => $this->decimal(8, 2)->null()->comment('กรอบขั้นต่ำถ้าเกณฑ์ระบุเป็นตัวเลข'),
            'max_qty' => $this->decimal(8, 2)->null()->comment('กรอบขั้นสูงถ้าเกณฑ์ระบุเป็นตัวเลข'),
            'note' => $this->text()->null(),
        ], $tableOptions);
        $this->createIndex('uq-wf-rule-line-level-band', '{{%workforce_standard_rule}}', ['line_id', 'level_code', 'size_band'], true);
        $this->addForeignKey('fk-wf-rule-line', '{{%workforce_standard_rule}}', 'line_id', '{{%workforce_standard_line}}', 'id', 'CASCADE', 'CASCADE');

        // ── ช่วงกรอบรวมระดับหน่วยงาน (เกณฑ์กำหนดเป็นช่วง เช่น 24-30 ทั้งหน่วย) ──
        $this->createTable('{{%workforce_unit_quota}}', [
            'id' => $this->primaryKey(),
            'edition' => $this->string(30)->notNull(),
            'level_code' => $this->string(10)->notNull(),
            'size_band' => $this->string(30)->null(),
            'unit_key' => $this->string(100)->notNull()->comment('ชื่อหน่วยตามเกณฑ์ เช่น MEDICAL_EDU_CENTER'),
            'unit_title' => $this->string(255)->null(),
            'min_total' => $this->integer()->null(),
            'max_total' => $this->integer()->null(),
            'note' => $this->text()->null(),
        ], $tableOptions);
        $this->createIndex('uq-wf-quota', '{{%workforce_unit_quota}}', ['edition', 'level_code', 'size_band', 'unit_key'], true);

        // ── จับคู่สายงานมาตรฐานกับตำแหน่งจริงของโรงพยาบาลนี้ ──
        $this->createTable('{{%workforce_position_map}}', [
            'id' => $this->primaryKey(),
            'line_id' => $this->integer()->notNull(),
            'employee_position_id' => $this->integer()->notNull(),
            'matched_by' => $this->string(20)->notNull()->defaultValue('manual')->comment('auto=จับคู่ตามชื่อ, manual=คนเลือก'),
            'created_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
        ], $tableOptions);
        // 1 ตำแหน่งของโรงพยาบาล ผูกได้กับ 1 สายงานมาตรฐานเท่านั้น กันนับซ้ำ
        $this->createIndex('uq-wf-map-position', '{{%workforce_position_map}}', 'employee_position_id', true);
        $this->createIndex('idx-wf-map-line', '{{%workforce_position_map}}', 'line_id');
        $this->addForeignKey('fk-wf-map-line', '{{%workforce_position_map}}', 'line_id', '{{%workforce_standard_line}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-wf-map-position', '{{%workforce_position_map}}', 'employee_position_id', '{{%employee_position}}', 'id', 'CASCADE', 'CASCADE');

        // ── กรอบที่ใช้จริงรายปี ต่อหน่วยงาน ต่อตำแหน่ง ──
        $this->createTable('{{%workforce_frame}}', [
            'id' => $this->primaryKey(),
            'thai_year' => $this->integer()->notNull(),
            'org_unit_id' => $this->bigInteger()->notNull()->comment('ทะเบียนหน่วยงานรายปี (org_unit)'),
            'employee_position_id' => $this->integer()->null()->comment('ตำแหน่งของโรงพยาบาล'),
            'line_id' => $this->integer()->null()->comment('สายงานมาตรฐานที่เทียบได้ (null = ไม่มีในเกณฑ์)'),
            'frame_qty' => $this->decimal(8, 2)->null()->comment('กรอบที่ใช้จริง'),
            'frame_min' => $this->decimal(8, 2)->null(),
            'frame_max' => $this->decimal(8, 2)->null(),
            'source' => $this->string(20)->notNull()->defaultValue('none')
                ->comment('standard_formula|population_based|manual_fte|override|none'),
            'calc_json' => $this->json()->null()->comment('ที่มาของตัวเลข สำหรับกางให้ผู้ใช้ดู'),
            'override_reason' => $this->text()->null()->comment('บังคับกรอกเมื่อ source=override'),
            'status' => $this->string(20)->notNull()->defaultValue('draft')
                ->comment('draft|submitted|approved|closed'),
            'note' => $this->text()->null(),
            'created_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_at' => $this->dateTime()->null(),
            'updated_by' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('uq-wf-frame-row', '{{%workforce_frame}}', ['thai_year', 'org_unit_id', 'employee_position_id'], true);
        $this->createIndex('idx-wf-frame-year-status', '{{%workforce_frame}}', ['thai_year', 'status']);
        $this->addForeignKey('fk-wf-frame-org-unit', '{{%workforce_frame}}', 'org_unit_id', '{{%org_unit}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-wf-frame-position', '{{%workforce_frame}}', 'employee_position_id', '{{%employee_position}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk-wf-frame-line', '{{%workforce_frame}}', 'line_id', '{{%workforce_standard_line}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%workforce_frame}}');
        $this->dropTable('{{%workforce_position_map}}');
        $this->dropTable('{{%workforce_unit_quota}}');
        $this->dropTable('{{%workforce_standard_rule}}');
        $this->dropTable('{{%workforce_standard_line}}');
        $this->dropTable('{{%workforce_profile}}');
    }
}
