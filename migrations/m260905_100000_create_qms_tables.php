<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * QMS — ระบบติดตามมาตรฐานโรงพยาบาล (เฟส 1)
 *
 * เก็บทะเบียนมาตรฐาน + ข้อกำหนด (แม่แบบใช้ซ้ำทุกปี) แล้วสร้างรอบปีงบ
 * เป็น checklist ให้ผู้รับผิดชอบวางหลักฐาน — ดึงจาก DMS/medsop หรือแนบไฟล์เอง (manual)
 *
 * หน่วยงาน = tree.id (bigint) ตามผังองค์กร, คน = employees.id (int)
 * fiscal_year เก็บเป็น int (พ.ศ.) ตรงๆ ไม่ทำตาราง FiscalYear แยก (ERP โรงพยาบาลเดียว)
 *
 * shared control ข้ามมาตรฐาน: เฟส 1 เก็บ "ความเชื่อมโยง" ที่ qms_requirement_link
 * เพื่อทำ matrix + ตัวเลขข้อใช้ร่วม (ยังไม่ dedup หลักฐานอัตโนมัติ — เฟส 2)
 */
final class m260905_100000_create_qms_tables extends Migration
{
    public function safeUp(): void
    {
        $audit = fn (): array => [
            'ref' => $this->string(64)->notNull(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ];

        // 1) มาตรฐาน ------------------------------------------------------------
        $this->createTable('{{%qms_standard}}', array_merge([
            'id' => $this->primaryKey(),
            'code' => $this->string(32)->notNull()->comment('รหัสมาตรฐาน เช่น HA, ISO9001, PDPA'),
            'name' => $this->string(255)->notNull()->comment('ชื่อเต็ม'),
            'short_name' => $this->string(64)->null()->comment('ชื่อย่อบนการ์ด'),
            'description' => $this->text()->null(),
            'owner_unit_id' => $this->bigInteger()->null()->comment('หน่วยงานเจ้าของมาตรฐาน (tree.id)'),
            'owner_label' => $this->string(255)->null()->comment('ชื่อเจ้าของแบบข้อความ เช่น คณะกรรมการ HA'),
            'icon' => $this->string(64)->null()->comment('คลาสไอคอน/โลโก้'),
            'color' => $this->string(32)->null()->comment('สีธีมการ์ด'),
            'sort' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
        ], $audit()));
        $this->createIndex('uq-qms_standard-code', '{{%qms_standard}}', 'code', true);
        $this->createIndex('uq-qms_standard-ref', '{{%qms_standard}}', 'ref', true);
        $this->createIndex('idx-qms_standard-active', '{{%qms_standard}}', ['is_active', 'sort']);
        $this->addForeignKey('fk-qms_standard-unit', '{{%qms_standard}}', 'owner_unit_id', '{{%tree}}', 'id', 'SET NULL', 'CASCADE');

        // 2) ข้อกำหนด (แม่แบบ, เป็นชั้น) ----------------------------------------
        $this->createTable('{{%qms_requirement}}', array_merge([
            'id' => $this->primaryKey(),
            'standard_id' => $this->integer()->notNull()->comment('อยู่ในมาตรฐานใด'),
            'parent_id' => $this->integer()->null()->comment('ข้อแม่ (null = หมวดบนสุด)'),
            'code' => $this->string(64)->null()->comment('เลขข้อ เช่น IC-2.1'),
            'title' => $this->string(500)->notNull()->comment('ชื่อข้อกำหนด'),
            'detail' => $this->text()->null(),
            'evidence_hint' => $this->string(255)->null()->comment('ประเภทหลักฐานที่คาดหวัง เช่น คำสั่ง/รายงานประชุม'),
            'default_assignee_unit_id' => $this->bigInteger()->null()->comment('ผู้รับผิดชอบตั้งต้น หน่วยงาน (tree.id)'),
            'default_assignee_emp_id' => $this->integer()->null()->comment('ผู้รับผิดชอบตั้งต้น บุคคล (employees.id)'),
            'sort' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
        ], $audit()));
        $this->createIndex('uq-qms_requirement-ref', '{{%qms_requirement}}', 'ref', true);
        $this->createIndex('idx-qms_requirement-standard', '{{%qms_requirement}}', ['standard_id', 'parent_id', 'sort']);
        $this->addForeignKey('fk-qms_requirement-standard', '{{%qms_requirement}}', 'standard_id', '{{%qms_standard}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-qms_requirement-parent', '{{%qms_requirement}}', 'parent_id', '{{%qms_requirement}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk-qms_requirement-unit', '{{%qms_requirement}}', 'default_assignee_unit_id', '{{%tree}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk-qms_requirement-emp', '{{%qms_requirement}}', 'default_assignee_emp_id', '{{%employees}}', 'id', 'SET NULL', 'CASCADE');

        // 3) Mapping ข้ามมาตรฐาน (matrix / shared control) ----------------------
        $this->createTable('{{%qms_requirement_link}}', array_merge([
            'id' => $this->primaryKey(),
            'requirement_id' => $this->integer()->notNull()->comment('ข้อกำหนดเจ้าของ (ที่มีหลักฐานจริง)'),
            'standard_id' => $this->integer()->notNull()->comment('มาตรฐานปลายทางที่ข้อนี้ไปสนอง'),
            'relation' => $this->string(16)->notNull()->defaultValue('direct')->comment('direct | partial'),
            'note' => $this->string(255)->null(),
        ], $audit()));
        $this->createIndex('uq-qms_req_link', '{{%qms_requirement_link}}', ['requirement_id', 'standard_id'], true);
        $this->createIndex('uq-qms_req_link-ref', '{{%qms_requirement_link}}', 'ref', true);
        $this->createIndex('idx-qms_req_link-standard', '{{%qms_requirement_link}}', 'standard_id');
        $this->addForeignKey('fk-qms_req_link-req', '{{%qms_requirement_link}}', 'requirement_id', '{{%qms_requirement}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-qms_req_link-standard', '{{%qms_requirement_link}}', 'standard_id', '{{%qms_standard}}', 'id', 'CASCADE', 'CASCADE');

        // 4) รอบปีงบ × มาตรฐาน --------------------------------------------------
        $this->createTable('{{%qms_cycle}}', array_merge([
            'id' => $this->primaryKey(),
            'standard_id' => $this->integer()->notNull(),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ พ.ศ. เช่น 2569'),
            'status' => $this->string(16)->notNull()->defaultValue('open')->comment('open | closed'),
            'next_review_date' => $this->date()->null()->comment('ทบทวนครั้งถัดไป'),
            'note' => $this->text()->null(),
        ], $audit()));
        $this->createIndex('uq-qms_cycle', '{{%qms_cycle}}', ['standard_id', 'fiscal_year'], true);
        $this->createIndex('uq-qms_cycle-ref', '{{%qms_cycle}}', 'ref', true);
        $this->createIndex('idx-qms_cycle-year', '{{%qms_cycle}}', ['fiscal_year', 'status']);
        $this->addForeignKey('fk-qms_cycle-standard', '{{%qms_cycle}}', 'standard_id', '{{%qms_standard}}', 'id', 'CASCADE', 'CASCADE');

        // 5) checklist item ในรอบ (ตัวที่ copy ข้ามปี) --------------------------
        $this->createTable('{{%qms_cycle_item}}', array_merge([
            'id' => $this->primaryKey(),
            'cycle_id' => $this->integer()->notNull(),
            'requirement_id' => $this->integer()->notNull()->comment('อ้างข้อกำหนดแม่'),
            'title_snapshot' => $this->string(500)->notNull()->comment('ชื่อ ณ ปีนั้น กันแม่แบบแก้ย้อนหลัง'),
            'assignee_unit_id' => $this->bigInteger()->null()->comment('ผู้รับผิดชอบ หน่วยงาน (tree.id)'),
            'assignee_emp_id' => $this->integer()->null()->comment('ผู้รับผิดชอบ บุคคล (employees.id)'),
            'due_date' => $this->date()->null()->comment('กำหนดส่ง'),
            'status' => $this->string(16)->notNull()->defaultValue('none')->comment('none | in_progress | complete | na'),
            'note' => $this->text()->null(),
            'sort' => $this->integer()->notNull()->defaultValue(0),
        ], $audit()));
        $this->createIndex('uq-qms_cycle_item', '{{%qms_cycle_item}}', ['cycle_id', 'requirement_id'], true);
        $this->createIndex('uq-qms_cycle_item-ref', '{{%qms_cycle_item}}', 'ref', true);
        $this->createIndex('idx-qms_cycle_item-status', '{{%qms_cycle_item}}', ['cycle_id', 'status']);
        $this->createIndex('idx-qms_cycle_item-assignee', '{{%qms_cycle_item}}', ['assignee_emp_id', 'status']);
        $this->createIndex('idx-qms_cycle_item-unit', '{{%qms_cycle_item}}', ['assignee_unit_id', 'status']);
        $this->addForeignKey('fk-qms_cycle_item-cycle', '{{%qms_cycle_item}}', 'cycle_id', '{{%qms_cycle}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-qms_cycle_item-req', '{{%qms_cycle_item}}', 'requirement_id', '{{%qms_requirement}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk-qms_cycle_item-unit', '{{%qms_cycle_item}}', 'assignee_unit_id', '{{%tree}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk-qms_cycle_item-emp', '{{%qms_cycle_item}}', 'assignee_emp_id', '{{%employees}}', 'id', 'SET NULL', 'CASCADE');

        // 6) หลักฐานต่อ 1 ข้อ (เลือกที่มา) --------------------------------------
        $this->createTable('{{%qms_evidence}}', array_merge([
            'id' => $this->primaryKey(),
            'cycle_item_id' => $this->integer()->notNull(),
            'source_type' => $this->string(16)->notNull()->comment('dms | medsop | file | link'),
            'source_module' => $this->string(32)->null()->comment('โมดูลต้นทาง เผื่อขยาย'),
            'source_id' => $this->string(64)->null()->comment('id เอกสารต้นทางในโมดูลนั้น'),
            'file_path' => $this->string(500)->null()->comment('เมื่อ source_type = file'),
            'file_name' => $this->string(255)->null(),
            'url' => $this->string(500)->null()->comment('เมื่อ source_type = link'),
            'title' => $this->string(255)->null()->comment('ป้ายกำกับหลักฐาน'),
            'note' => $this->string(255)->null(),
        ], $audit()));
        $this->createIndex('uq-qms_evidence-ref', '{{%qms_evidence}}', 'ref', true);
        $this->createIndex('idx-qms_evidence-item', '{{%qms_evidence}}', 'cycle_item_id');
        $this->createIndex('idx-qms_evidence-source', '{{%qms_evidence}}', ['source_type', 'source_module', 'source_id']);
        $this->addForeignKey('fk-qms_evidence-item', '{{%qms_evidence}}', 'cycle_item_id', '{{%qms_cycle_item}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%qms_evidence}}');
        $this->dropTable('{{%qms_cycle_item}}');
        $this->dropTable('{{%qms_cycle}}');
        $this->dropTable('{{%qms_requirement_link}}');
        $this->dropTable('{{%qms_requirement}}');
        $this->dropTable('{{%qms_standard}}');
    }
}
