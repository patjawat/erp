<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * โครงสร้างฐานข้อมูล KPI ประจำปีรายบุคคล
 *
 * - kpi_cycle       : ชุด KPI ต่อพนักงาน 1 คน / 1 ปีงบประมาณ (ต.ค.–ก.ย.)
 * - kpi_item        : KPI แต่ละตัวในชุด (seed จาก JD หรือหัวหน้า/HR เพิ่มเอง)
 * - kpi_entry       : ผลงานรายงวด (เดือน/ไตรมาส/ปี) ที่เจ้าของ KPI กรอกเอง
 * - kpi_item_score  : คะแนน KPI แต่ละตัวต่อรอบสรุป (H1/H2/FULL) พร้อม snapshot ค่า ณ วันสรุป
 */
final class m260728_000001_create_kpi_tables extends Migration
{
    private string $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';

    public function safeUp(): void
    {
        // 1) ชุด KPI ต่อพนักงาน 1 คน / 1 ปีงบประมาณ
        $this->createTable('{{%kpi_cycle}}', [
            'id' => $this->primaryKey(),
            'emp_id' => $this->integer()->notNull(),
            'fiscal_year' => $this->smallInteger()->notNull()->comment('ปีงบประมาณ พ.ศ. เช่น 2569 = ต.ค.2025–ก.ย.2026'),
            'jd_employee_id' => $this->integer()->comment('อ้างอิงกลับ JD revision ที่ seed KPI มา'),
            'status' => $this->string(20)->notNull()->defaultValue('draft')->comment('draft / pending / active / closed'),
            'approved_by' => $this->integer()->comment('หัวหน้า/HR ที่อนุมัติชุด (user_id)'),
            'approved_at' => $this->dateTime(),
            'note' => $this->text(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $this->tableOptions);
        $this->createIndex('ux_kpi_cycle_emp_year', '{{%kpi_cycle}}', ['emp_id', 'fiscal_year'], true);
        $this->createIndex('ix_kpi_cycle_status', '{{%kpi_cycle}}', ['status', 'fiscal_year']);
        $this->addForeignKey('fk_kpi_cycle_emp', '{{%kpi_cycle}}', 'emp_id', '{{%employees}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_kpi_cycle_jd', '{{%kpi_cycle}}', 'jd_employee_id', '{{%jd_employee}}', 'id', 'SET NULL', 'CASCADE');

        // 2) KPI แต่ละตัวในชุด
        $this->createTable('{{%kpi_item}}', [
            'id' => $this->primaryKey(),
            'cycle_id' => $this->integer()->notNull(),
            'source_type' => $this->string(20)->notNull()->defaultValue('manual')->comment('jd = ตั้งต้นจาก JD / manual = หัวหน้า/HR เพิ่มเอง'),
            'source_jd_section_id' => $this->integer()->comment('อ้างอิงแถว KPI ใน jd_employee_section'),
            'indicator' => $this->string(500)->notNull()->comment('ชื่อตัวชี้วัด'),
            'target_text' => $this->string(500)->comment('เป้าหมาย (ข้อความ เช่น ≥90%)'),
            'target_value' => $this->decimal(14, 2)->comment('เป้าเชิงตัวเลข (ถ้ามี)'),
            'unit' => $this->string(50)->comment('หน่วย เช่น %, ครั้ง, ราย'),
            'value_type' => $this->string(20)->notNull()->defaultValue('numeric')->comment('numeric / qualitative'),
            'frequency' => $this->string(20)->notNull()->defaultValue('monthly')->comment('monthly / quarterly / yearly'),
            'weight' => $this->decimal(6, 2)->notNull()->defaultValue(0)->comment('น้ำหนัก % (รวมทั้งชุด = 100)'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'status' => $this->string(20)->notNull()->defaultValue('active')->comment('active / removed (ลดกลางปี = removed ไม่ลบจริง)'),
            'confirmed_by' => $this->integer()->comment('หัวหน้า/HR ยืนยันความเหมาะสมรายตัว'),
            'confirmed_at' => $this->dateTime(),
            'removed_by' => $this->integer(),
            'removed_at' => $this->dateTime(),
            'removed_reason' => $this->string(500),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $this->tableOptions);
        $this->createIndex('ix_kpi_item_cycle', '{{%kpi_item}}', ['cycle_id', 'status', 'sort_order']);
        $this->addForeignKey('fk_kpi_item_cycle', '{{%kpi_item}}', 'cycle_id', '{{%kpi_cycle}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_kpi_item_jd_section', '{{%kpi_item}}', 'source_jd_section_id', '{{%jd_employee_section}}', 'id', 'SET NULL', 'CASCADE');

        // 3) ผลงานรายงวด (เจ้าของ KPI กรอกเอง)
        $this->createTable('{{%kpi_entry}}', [
            'id' => $this->primaryKey(),
            'kpi_item_id' => $this->integer()->notNull(),
            'period_type' => $this->string(10)->notNull()->comment('month / quarter / year'),
            'period_index' => $this->tinyInteger()->notNull()->comment('month 1–12 (ต.ค.=1) / quarter 1–4 / year 1'),
            'value_num' => $this->decimal(14, 2)->comment('ผลงานเชิงตัวเลข'),
            'value_text' => $this->text()->comment('ผลงานเชิงคุณภาพ/หมายเหตุ'),
            'recorded_by' => $this->integer()->comment('เจ้าของ KPI (user_id)'),
            'recorded_at' => $this->dateTime(),
            'confirm_status' => $this->string(20)->notNull()->defaultValue('pending')->comment('pending / confirmed / revise'),
            'confirmed_by' => $this->integer()->comment('หัวหน้ายืนยัน (user_id)'),
            'confirmed_at' => $this->dateTime(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ], $this->tableOptions);
        $this->createIndex('ux_kpi_entry_period', '{{%kpi_entry}}', ['kpi_item_id', 'period_type', 'period_index'], true);
        $this->addForeignKey('fk_kpi_entry_item', '{{%kpi_entry}}', 'kpi_item_id', '{{%kpi_item}}', 'id', 'CASCADE', 'CASCADE');

        // 4) คะแนน KPI แต่ละตัวต่อรอบสรุป + snapshot ค่า ณ วันสรุป
        $this->createTable('{{%kpi_item_score}}', [
            'id' => $this->primaryKey(),
            'kpi_item_id' => $this->integer()->notNull(),
            'round' => $this->string(10)->notNull()->comment('H1 (ต.ค.–มี.ค.) / H2 (เม.ย.–ก.ย.) / FULL (ทั้งปี)'),
            'indicator_snapshot' => $this->string(500)->comment('ชื่อ KPI ณ วันสรุป'),
            'target_snapshot' => $this->string(500)->comment('เป้า ณ วันสรุป'),
            'weight_snapshot' => $this->decimal(6, 2)->comment('น้ำหนัก ณ วันสรุป'),
            'result_snapshot' => $this->text()->comment('ผลงานสะสม ณ วันสรุป'),
            'self_result_text' => $this->text()->comment('เจ้าของสรุปผลตนเอง'),
            'achievement_pct' => $this->decimal(6, 2)->comment('% บรรลุเป้า'),
            'score' => $this->decimal(8, 2)->comment('คะแนนถ่วงน้ำหนัก = achievement × weight ÷ 100'),
            'status' => $this->string(20)->notNull()->defaultValue('draft')->comment('draft / confirmed'),
            'note' => $this->text(),
            'confirmed_by' => $this->integer()->comment('หัวหน้ายืนยันคะแนน (ขั้นสุดท้าย, user_id)'),
            'confirmed_at' => $this->dateTime(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ], $this->tableOptions);
        $this->createIndex('ux_kpi_item_score_round', '{{%kpi_item_score}}', ['kpi_item_id', 'round'], true);
        $this->addForeignKey('fk_kpi_item_score_item', '{{%kpi_item_score}}', 'kpi_item_id', '{{%kpi_item}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%kpi_item_score}}');
        $this->dropTable('{{%kpi_entry}}');
        $this->dropTable('{{%kpi_item}}');
        $this->dropTable('{{%kpi_cycle}}');
    }
}
