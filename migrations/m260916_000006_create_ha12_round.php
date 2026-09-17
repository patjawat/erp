<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * HA12-PCT — เฟส 3 : รอบสรุปและประเมินโดย PCT (บท 9, 11)
 *
 *   - ha12_round          : รอบประเมิน (ปีงบ + period เดือน/ไตรมาส/ปี + ขอบเขตหน่วยงาน + สถานะ)
 *   - ha12_assessment     : ผลประเมินต่อกิจกรรม (เกณฑ์ที่เลือกหลายระดับ + เหตุผล + สถานะร่าง/เผยแพร่)
 *   - ha12_summary_row    : แถวสรุป (หน่วยงาน/เรื่อง/ผลปรับปรุง)
 *   - ha12_summary_source : หลักฐานของแถวสรุป (polymorphic review|med|mrec|indicator) + snapshot เฉพาะรุ่น
 *
 * หลักการ: แยก "การทบทวนจริง" ออกจาก "ผลสรุป/ประเมิน" — source เก็บ snapshot ณ รุ่นที่ใช้
 * เพื่อรักษาผลสรุปเดิมเมื่อข้อมูลต้นทางเปลี่ยน (บท 9)
 */
final class m260916_000006_create_ha12_round extends Migration
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

        // 1) รอบประเมิน --------------------------------------------------------
        $this->createTable('{{%ha12_round}}', array_merge([
            'id' => $this->primaryKey(),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'period_type' => $this->string(8)->notNull()->defaultValue('year')->comment('month|quarter|year'),
            'period_no' => $this->tinyInteger()->null()->comment('month 1-12 (เดือนปฏิทิน) | quarter 1-4 | year=null'),
            'period_start' => $this->date()->notNull()->comment('ขอบเขตช่วงเริ่ม (ค.ศ.)'),
            'period_end' => $this->date()->notNull()->comment('ขอบเขตช่วงสิ้นสุด (ค.ศ.)'),
            'scope_unit_id' => $this->bigInteger()->null()->comment('ขอบเขตหน่วยงาน (tree.id) ; null = ทั้งโรงพยาบาล'),
            'title' => $this->string(255)->null(),
            'status' => $this->string(8)->notNull()->defaultValue('open')->comment('open|closed'),
            'reopen_reason' => $this->text()->null()->comment('เหตุผลการเปิดรอบที่ปิดแล้ว'),
            'note' => $this->text()->null(),
        ], $audit()));
        $this->createIndex('uq-ha12_round-ref', '{{%ha12_round}}', 'ref', true);
        $this->createIndex('idx-ha12_round-fy', '{{%ha12_round}}', ['fiscal_year', 'status']);
        $this->addForeignKey('fk-ha12_round-unit', '{{%ha12_round}}', 'scope_unit_id', '{{%tree}}', 'id', 'SET NULL', 'CASCADE');

        // 2) ผลประเมินต่อกิจกรรม ----------------------------------------------
        $this->createTable('{{%ha12_assessment}}', array_merge([
            'id' => $this->primaryKey(),
            'round_id' => $this->integer()->notNull(),
            'activity_id' => $this->integer()->notNull(),
            'levels' => $this->string(32)->null()->comment('ระดับที่เลือก คั่นด้วย , เช่น "3,4"'),
            'reason' => $this->text()->null()->comment('เหตุผลประกอบการเลือกระดับ'),
            'summary_text' => $this->text()->null()->comment('สรุป/ข้อเสนอแนะ'),
            'status' => $this->string(10)->notNull()->defaultValue('draft')->comment('draft|published'),
            'published_at' => $this->dateTime()->null(),
        ], $audit()));
        $this->createIndex('uq-ha12_assessment-ref', '{{%ha12_assessment}}', 'ref', true);
        $this->createIndex('uq-ha12_assessment-ra', '{{%ha12_assessment}}', ['round_id', 'activity_id'], true);
        $this->addForeignKey('fk-ha12_assessment-round', '{{%ha12_assessment}}', 'round_id', '{{%ha12_round}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-ha12_assessment-activity', '{{%ha12_assessment}}', 'activity_id', '{{%ha12_activity}}', 'id', 'RESTRICT', 'CASCADE');

        // 3) แถวสรุป ----------------------------------------------------------
        $this->createTable('{{%ha12_summary_row}}', array_merge([
            'id' => $this->primaryKey(),
            'assessment_id' => $this->integer()->notNull(),
            'unit_name' => $this->string(255)->null()->comment('หน่วยงาน'),
            'topic' => $this->string(500)->null()->comment('เรื่อง/โรค'),
            'improvement' => $this->text()->null()->comment('ผลการปรับปรุง'),
            'sort' => $this->integer()->notNull()->defaultValue(0),
        ], $audit()));
        $this->createIndex('uq-ha12_summary_row-ref', '{{%ha12_summary_row}}', 'ref', true);
        $this->createIndex('idx-ha12_summary_row-as', '{{%ha12_summary_row}}', ['assessment_id', 'sort']);
        $this->addForeignKey('fk-ha12_summary_row-as', '{{%ha12_summary_row}}', 'assessment_id', '{{%ha12_assessment}}', 'id', 'CASCADE', 'CASCADE');

        // 4) หลักฐานของแถวสรุป (polymorphic + snapshot) -----------------------
        $this->createTable('{{%ha12_summary_source}}', array_merge([
            'id' => $this->primaryKey(),
            'summary_row_id' => $this->integer()->notNull(),
            'source_type' => $this->string(16)->notNull()->comment('review|med|mrec|indicator'),
            'source_id' => $this->integer()->notNull()->comment('id ของ record ต้นทาง'),
            'source_rev' => $this->integer()->null()->comment('revision ณ ตอนเลือก (review)'),
            'label' => $this->string(500)->null()->comment('ป้ายแสดง'),
            'snapshot_json' => $this->text()->null()->comment('สำเนาข้อมูล ณ รุ่นที่ใช้'),
        ], $audit()));
        $this->createIndex('uq-ha12_summary_source-ref', '{{%ha12_summary_source}}', 'ref', true);
        $this->createIndex('idx-ha12_summary_source-row', '{{%ha12_summary_source}}', 'summary_row_id');
        $this->addForeignKey('fk-ha12_summary_source-row', '{{%ha12_summary_source}}', 'summary_row_id', '{{%ha12_summary_row}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%ha12_summary_source}}');
        $this->dropTable('{{%ha12_summary_row}}');
        $this->dropTable('{{%ha12_assessment}}');
        $this->dropTable('{{%ha12_round}}');
    }
}
