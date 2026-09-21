<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * HA12-PCT — เฟส 2a : ความคลาดเคลื่อนทางยา (กิจกรรม 7)
 *
 * โครงเฉพาะตามบท 7-8 ของคู่มือ:
 *   - ha12_med_report : รายงานร่วมเภสัช+พยาบาล 1 ช่วงข้อมูล ต่อหน่วยงาน (เจ้าของ=tree.id)
 *   - ha12_med_group  : 5 หัวข้อหลัก (สั่งยา/คัดลอก/จัดยา/จ่ายยา/บริหารยา) + ตัวหาร/หน่วย/ฐานอัตรา
 *   - ha12_med_count  : แถวย่อยความเสี่ยง — จำนวนตามระดับ No Harm/E-I + ทีมผู้บันทึก + ผล/แก้ไข
 *
 * กฎ (บท 8) บังคับที่ชั้น model/service:
 *   - จำนวนครั้ง = ผลรวม No Harm + E..I ; จำนวนเต็มไม่ติดลบ
 *   - ตัวหารหัวข้อ 1-4 = ใบสั่งยา ; หัวข้อ 5 = วันนอน/รายผู้ป่วย
 *   - อัตรา = จำนวนครั้ง ÷ ตัวหาร × ฐาน (100/1000/10000) — คำนวณตอนแสดง ไม่เก็บ
 *   - ช่วงข้อมูลไม่ซ้อนกันในหน่วยงานเดียวกัน ; review_date ≥ period_end
 */
final class m260916_000003_create_ha12_medication extends Migration
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

        // 1) รายงานยา (1 ช่วงข้อมูล ต่อหน่วยงาน) -------------------------------
        $this->createTable('{{%ha12_med_report}}', array_merge([
            'id' => $this->primaryKey(),
            'owner_unit_id' => $this->bigInteger()->null()->comment('หน่วยงานเจ้าของ (tree.id)'),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'period_start' => $this->date()->notNull()->comment('เริ่มช่วงข้อมูล'),
            'period_end' => $this->date()->notNull()->comment('สิ้นสุดช่วงข้อมูล'),
            'review_date' => $this->date()->null()->comment('วันที่ทบทวน (≥ period_end)'),
            'note' => $this->text()->null()->comment('บันทึกรวม'),
            'deleted' => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('ลบแบบกู้คืนได้'),
        ], $audit()));
        $this->createIndex('uq-ha12_med_report-ref', '{{%ha12_med_report}}', 'ref', true);
        $this->createIndex('idx-ha12_med_report-scope', '{{%ha12_med_report}}', ['owner_unit_id', 'fiscal_year', 'deleted']);
        $this->createIndex('idx-ha12_med_report-period', '{{%ha12_med_report}}', ['period_start', 'period_end']);
        $this->addForeignKey('fk-ha12_med_report-unit', '{{%ha12_med_report}}', 'owner_unit_id', '{{%tree}}', 'id', 'SET NULL', 'CASCADE');

        // 2) หัวข้อหลัก 1-5 ต่อรายงาน -----------------------------------------
        $this->createTable('{{%ha12_med_group}}', array_merge([
            'id' => $this->primaryKey(),
            'report_id' => $this->integer()->notNull(),
            'group_no' => $this->tinyInteger(1)->notNull()->comment('1 สั่งยา|2 คัดลอก|3 จัดยา|4 จ่ายยา|5 บริหารยา'),
            'divisor' => $this->integer()->null()->comment('ตัวหาร (>0)'),
            'divisor_unit' => $this->string(16)->null()->comment('prescription|patient_day|patient'),
            'rate_base' => $this->integer()->null()->comment('ฐานอัตรา 100|1000|10000'),
            'note' => $this->text()->null(),
        ], $audit()));
        $this->createIndex('uq-ha12_med_group-ref', '{{%ha12_med_group}}', 'ref', true);
        $this->createIndex('uq-ha12_med_group-rg', '{{%ha12_med_group}}', ['report_id', 'group_no'], true);
        $this->addForeignKey('fk-ha12_med_group-report', '{{%ha12_med_group}}', 'report_id', '{{%ha12_med_report}}', 'id', 'CASCADE', 'CASCADE');

        // 3) แถวย่อยความเสี่ยง + จำนวนตามระดับความรุนแรง ----------------------
        $this->createTable('{{%ha12_med_count}}', array_merge([
            'id' => $this->primaryKey(),
            'group_id' => $this->integer()->notNull(),
            'risk_code' => $this->string(32)->null()->comment('รหัสความเสี่ยง (ถ้ามี)'),
            'risk_name' => $this->string(255)->notNull()->comment('ชื่อความเสี่ยงย่อย'),
            'is_other' => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('เป็นรายการ “อื่น ๆ” ที่เพิ่มเอง'),
            'team' => $this->string(16)->null()->comment('pharmacy|nursing|joint'),
            // จำนวนตามระดับความรุนแรง (null = ยังไม่รายงาน, 0 = ตรวจแล้วไม่พบ)
            'c_no_harm' => $this->integer()->null()->comment('No Harm'),
            'c_e' => $this->integer()->null()->comment('ระดับ E'),
            'c_f' => $this->integer()->null()->comment('ระดับ F'),
            'c_g' => $this->integer()->null()->comment('ระดับ G'),
            'c_h' => $this->integer()->null()->comment('ระดับ H'),
            'c_i' => $this->integer()->null()->comment('ระดับ I'),
            'total_count' => $this->integer()->null()->comment('จำนวนครั้ง = ผลรวม No Harm + E..I (คำนวณตอนบันทึก)'),
            'review_result' => $this->text()->null()->comment('ผลการทบทวน'),
            'fix' => $this->text()->null()->comment('การแก้ไข/ป้องกัน'),
            'sort' => $this->integer()->notNull()->defaultValue(0),
        ], $audit()));
        $this->createIndex('uq-ha12_med_count-ref', '{{%ha12_med_count}}', 'ref', true);
        $this->createIndex('idx-ha12_med_count-group', '{{%ha12_med_count}}', ['group_id', 'sort']);
        $this->addForeignKey('fk-ha12_med_count-group', '{{%ha12_med_count}}', 'group_id', '{{%ha12_med_group}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%ha12_med_count}}');
        $this->dropTable('{{%ha12_med_group}}');
        $this->dropTable('{{%ha12_med_report}}');
    }
}
