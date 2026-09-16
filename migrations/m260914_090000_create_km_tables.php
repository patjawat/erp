<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * KM — คลังกิจกรรม/หลักฐานการดำเนินงาน (เฟส 0 : โครงตาราง)
 *
 * แกนคือ "กิจกรรม" ของหน่วยงาน แนบรูปเป็นชุด (km_activity_photo) แล้วชี้ไปหาหลักฐาน
 * ที่มีอยู่แล้วในระบบผ่านตารางกลาง polymorphic (km_activity_link) — task/kpi/risk/dms/medsop
 * ไม่คัดลอกเอกสาร/งาน/KPI/ความเสี่ยงเข้ามาเก็บซ้ำ
 *
 * คอนเวนชันเดียวกับ QMS:
 *   หน่วยงาน = tree.id (bigint) ตามผังองค์กร, คน = employees.id (int, ผ่าน created_by/updated_by)
 *   fiscal_year เก็บเป็น int (พ.ศ.) ตรงๆ ไม่ทำตาราง FiscalYear แยก
 */
final class m260914_090000_create_km_tables extends Migration
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

        // 1) หมวดหมู่กิจกรรม ----------------------------------------------------
        $this->createTable('{{%km_category}}', array_merge([
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->comment('ชื่อหมวด'),
            'parent_id' => $this->integer()->null()->comment('หมวดแม่ (null = หมวดบนสุด)'),
            'icon' => $this->string(64)->null()->comment('คลาสไอคอน Bootstrap Icons'),
            'color' => $this->string(32)->null()->comment('สีธีมการ์ด'),
            'sort' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
        ], $audit()));
        $this->createIndex('uq-km_category-ref', '{{%km_category}}', 'ref', true);
        $this->createIndex('idx-km_category-tree', '{{%km_category}}', ['parent_id', 'sort']);
        $this->createIndex('idx-km_category-active', '{{%km_category}}', ['is_active', 'sort']);
        $this->addForeignKey('fk-km_category-parent', '{{%km_category}}', 'parent_id', '{{%km_category}}', 'id', 'SET NULL', 'CASCADE');

        // 2) กิจกรรม (แทน Events sheet เดิม) -----------------------------------
        $this->createTable('{{%km_activity}}', array_merge([
            'id' => $this->primaryKey(),
            'title' => $this->string(500)->notNull()->comment('ชื่อกิจกรรม'),
            'category_id' => $this->integer()->null()->comment('หมวดหมู่'),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'activity_date' => $this->date()->null()->comment('วันที่จัดกิจกรรม'),
            'start_time' => $this->time()->null(),
            'end_time' => $this->time()->null(),
            'location' => $this->string(255)->null()->comment('สถานที่'),
            'owner_unit_id' => $this->bigInteger()->null()->comment('หน่วยงานเจ้าภาพ (tree.id)'),
            'summary' => $this->text()->null()->comment('สรุปย่อสำหรับการ์ด'),
            'objective' => $this->text()->null()->comment('วัตถุประสงค์'),
            'detail' => $this->text()->null()->comment('รายละเอียด/ถอดบทเรียน'),
            'cover_photo_id' => $this->integer()->null()->comment('รูปปก (km_activity_photo.id — ไม่ผูก FK กันวนลูป)'),
            'status' => $this->string(16)->notNull()->defaultValue('draft')->comment('draft | published'),
        ], $audit()));
        $this->createIndex('uq-km_activity-ref', '{{%km_activity}}', 'ref', true);
        $this->createIndex('idx-km_activity-year', '{{%km_activity}}', ['fiscal_year', 'status']);
        $this->createIndex('idx-km_activity-cat', '{{%km_activity}}', 'category_id');
        $this->createIndex('idx-km_activity-unit', '{{%km_activity}}', 'owner_unit_id');
        $this->createIndex('idx-km_activity-date', '{{%km_activity}}', 'activity_date');
        $this->addForeignKey('fk-km_activity-cat', '{{%km_activity}}', 'category_id', '{{%km_category}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk-km_activity-unit', '{{%km_activity}}', 'owner_unit_id', '{{%tree}}', 'id', 'SET NULL', 'CASCADE');

        // 3) รูปภาพกิจกรรม (แทน Files sheet เดิม) ------------------------------
        $this->createTable('{{%km_activity_photo}}', array_merge([
            'id' => $this->primaryKey(),
            'activity_id' => $this->integer()->notNull(),
            'file_path' => $this->string(500)->notNull()->comment('path/storage key ของไฟล์จริง'),
            'file_name' => $this->string(255)->null(),
            'mime' => $this->string(100)->null(),
            'size' => $this->integer()->null()->comment('ขนาดไฟล์ (bytes)'),
            'thumbnail_path' => $this->string(500)->null(),
            'caption' => $this->string(500)->null(),
            'sort' => $this->integer()->notNull()->defaultValue(0),
        ], $audit()));
        $this->createIndex('uq-km_activity_photo-ref', '{{%km_activity_photo}}', 'ref', true);
        $this->createIndex('idx-km_activity_photo-act', '{{%km_activity_photo}}', ['activity_id', 'sort']);
        $this->addForeignKey('fk-km_activity_photo-act', '{{%km_activity_photo}}', 'activity_id', '{{%km_activity}}', 'id', 'CASCADE', 'CASCADE');

        // 4) ลิงก์หลักฐาน polymorphic (หัวใจ evidence hub) --------------------
        $this->createTable('{{%km_activity_link}}', array_merge([
            'id' => $this->primaryKey(),
            'activity_id' => $this->integer()->notNull(),
            'item_type' => $this->string(20)->notNull()->comment('task | kpi | risk | dms_doc | medsop'),
            'ref_id' => $this->string(64)->notNull()->comment('id/ref ของรายการปลายทาง (string รองรับทั้ง int และ ref)'),
            'ref_label' => $this->string(500)->null()->comment('ข้อความแสดงผล (cache ณ เวลาผูก)'),
            'note' => $this->string(255)->null(),
        ], $audit()));
        $this->createIndex('uq-km_activity_link-ref', '{{%km_activity_link}}', 'ref', true);
        $this->createIndex('idx-km_activity_link-act', '{{%km_activity_link}}', 'activity_id');
        $this->createIndex('idx-km_activity_link-target', '{{%km_activity_link}}', ['item_type', 'ref_id']);
        $this->addForeignKey('fk-km_activity_link-act', '{{%km_activity_link}}', 'activity_id', '{{%km_activity}}', 'id', 'CASCADE', 'CASCADE');

        // 5) แม่แบบกิจกรรม (กรอกซ้ำเร็ว — แทน ActivityTemplates เดิม) ----------
        $this->createTable('{{%km_template}}', array_merge([
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->comment('ชื่อแม่แบบ'),
            'category_id' => $this->integer()->null(),
            'default_title' => $this->string(500)->null(),
            'default_objective' => $this->text()->null(),
            'default_detail' => $this->text()->null(),
            'sort' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
        ], $audit()));
        $this->createIndex('uq-km_template-ref', '{{%km_template}}', 'ref', true);
        $this->createIndex('idx-km_template-active', '{{%km_template}}', ['is_active', 'sort']);
        $this->addForeignKey('fk-km_template-cat', '{{%km_template}}', 'category_id', '{{%km_category}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%km_template}}');
        $this->dropTable('{{%km_activity_link}}');
        $this->dropTable('{{%km_activity_photo}}');
        $this->dropTable('{{%km_activity}}');
        $this->dropTable('{{%km_category}}');
    }
}
