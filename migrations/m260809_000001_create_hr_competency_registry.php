<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ทะเบียนสมรรถนะ (Competency) และชุดที่ประกาศใช้รายปีงบประมาณ
 *
 * แยก "สมรรถนะแม่" ออกจาก "ชุดรายปี" เพราะแต่ละปีอาจใช้สมรรถนะเหมือนหรือต่างกันได้
 *   hr_competency            ตัวตนที่คงที่ข้ามปี ใช้เทียบพัฒนาการรายบุคคลข้ามปี
 *   hr_competency_year       ตัวจริงที่ใช้ประเมินในปีนั้น (คำจำกัดความ/ลำดับ/สถานะ ของปีนั้น)
 *     hr_competency_level        ระดับที่ 1..N ของสมรรถนะปีนั้น
 *       hr_competency_indicator    ข้อพฤติกรรมบ่งชี้ (1.1, 1.2, …) ที่ผู้ประเมินให้คะแนน
 *
 * แก้ข้อความของปี 2570 จะไม่กระทบใบประเมินปี 2569 เพราะคนละแถวกัน
 *
 * ชุดมาตรวัด (hr_competency_scale) แยกออกมา เพราะบางข้อไม่ได้ใช้มาตรฐาน 5 ระดับ
 * เช่น ข้อวัดสุขภาพในสมรรถนะ "สู่สุขภาวะดี" ใช้ BMI / รอบเอว / ผลเดินวิ่ง คนละมาตร
 */
final class m260809_000001_create_hr_competency_registry extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%hr_competency_scale}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(40)->notNull()->comment('รหัสอ้างอิงในโค้ด/seed'),
            'name' => $this->string(150)->notNull(),
            'is_default' => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('1 = ชุดมาตรฐานที่ใช้เมื่อข้อนั้นไม่ระบุมาตรวัด'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
        ], $options);
        $this->createIndex('uq-hr_competency_scale-code', '{{%hr_competency_scale}}', 'code', true);

        $this->createTable('{{%hr_competency_scale_option}}', [
            'id' => $this->primaryKey(),
            'scale_id' => $this->integer()->notNull(),
            'score' => $this->tinyInteger()->notNull()->comment('คะแนนที่ได้เมื่อเลือกตัวเลือกนี้ (1–5)'),
            'label' => $this->string(255)->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
        ], $options);
        $this->createIndex('uq-hr_competency_scale_option-scale_score', '{{%hr_competency_scale_option}}', ['scale_id', 'score'], true);
        $this->addForeignKey('fk-hr_competency_scale_option-scale', '{{%hr_competency_scale_option}}', 'scale_id', '{{%hr_competency_scale}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%hr_competency}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(40)->notNull(),
            'name' => $this->string(255)->notNull()->comment('ชื่อกลางของสมรรถนะ ใช้อ้างข้ามปี'),
            'type' => $this->string(20)->notNull()->defaultValue('core')->comment('core / functional'),
            'is_active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);
        $this->createIndex('uq-hr_competency-code', '{{%hr_competency}}', 'code', true);
        $this->createIndex('idx-hr_competency-type', '{{%hr_competency}}', ['type', 'is_active']);

        $this->createTable('{{%hr_competency_year}}', [
            'id' => $this->primaryKey(),
            'competency_id' => $this->integer()->notNull(),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ พ.ศ.'),
            'name' => $this->string(255)->notNull()->comment('ชื่อที่ใช้ในปีนั้น (แก้เฉพาะปีได้)'),
            'definition' => $this->text()->null()->comment('คำจำกัดความของสมรรถนะในปีนั้น'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0)->comment('ลำดับ Core 1..N'),
            'status' => $this->string(20)->notNull()->defaultValue('draft')->comment('draft / active / retired'),
            'note' => $this->text()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);
        $this->createIndex('uq-hr_competency_year-comp_year', '{{%hr_competency_year}}', ['competency_id', 'fiscal_year'], true);
        $this->createIndex('idx-hr_competency_year-fiscal_year', '{{%hr_competency_year}}', ['fiscal_year', 'status']);
        $this->addForeignKey('fk-hr_competency_year-competency', '{{%hr_competency_year}}', 'competency_id', '{{%hr_competency}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%hr_competency_level}}', [
            'id' => $this->primaryKey(),
            'competency_year_id' => $this->integer()->notNull(),
            'level_no' => $this->tinyInteger()->notNull(),
            'description' => $this->text()->null()->comment('คำอธิบายระดับ เช่น "แสดงสมรรถนะระดับที่ 1 และ …"'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
        ], $options);
        $this->createIndex('uq-hr_competency_level-year_level', '{{%hr_competency_level}}', ['competency_year_id', 'level_no'], true);
        $this->addForeignKey('fk-hr_competency_level-year', '{{%hr_competency_level}}', 'competency_year_id', '{{%hr_competency_year}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%hr_competency_indicator}}', [
            'id' => $this->primaryKey(),
            'level_id' => $this->integer()->notNull(),
            'indicator_no' => $this->string(20)->null()->comment('เลขข้อตามเอกสาร เช่น 1.1'),
            'text' => $this->text()->notNull()->comment('พฤติกรรมที่แสดงออก'),
            'scale_id' => $this->integer()->null()->comment('NULL = ใช้ชุดมาตรวัดมาตรฐาน'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
        ], $options);
        $this->createIndex('idx-hr_competency_indicator-level', '{{%hr_competency_indicator}}', ['level_id', 'sort_order']);
        $this->addForeignKey('fk-hr_competency_indicator-level', '{{%hr_competency_indicator}}', 'level_id', '{{%hr_competency_level}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-hr_competency_indicator-scale', '{{%hr_competency_indicator}}', 'scale_id', '{{%hr_competency_scale}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%hr_competency_indicator}}');
        $this->dropTable('{{%hr_competency_level}}');
        $this->dropTable('{{%hr_competency_year}}');
        $this->dropTable('{{%hr_competency}}');
        $this->dropTable('{{%hr_competency_scale_option}}');
        $this->dropTable('{{%hr_competency_scale}}');
    }
}
