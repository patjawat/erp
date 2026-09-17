<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * HA12-PCT — เฟส 2c : ติดตามตัวชี้วัดสำคัญ (กิจกรรม 12) — standalone
 *
 *   - ha12_indicator       : ตัวชี้วัด 1 ตัว/ปีงบ/หน่วยงาน (เป้าหมาย/ความเสี่ยง/ระดับ A-I/แก้ไข)
 *   - ha12_indicator_value : ค่ารายเดือน M01-M12 (M01 = ต.ค. ตามปีงบประมาณ)
 *
 * เฟสนี้กรอกเองในโมดูล HA12 (ยังไม่เชื่อมโมดูล KPI)
 */
final class m260916_000005_create_ha12_indicator extends Migration
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

        $this->createTable('{{%ha12_indicator}}', array_merge([
            'id' => $this->primaryKey(),
            'owner_unit_id' => $this->bigInteger()->null()->comment('หน่วยงานเจ้าของ (tree.id)'),
            'fiscal_year' => $this->integer()->notNull(),
            'name' => $this->string(500)->notNull()->comment('ชื่อตัวชี้วัด'),
            'target' => $this->string(255)->null()->comment('เป้าหมาย'),
            'unit_label' => $this->string(64)->null()->comment('หน่วยของค่า (เช่น %, ครั้ง)'),
            'risk' => $this->text()->null()->comment('ความเสี่ยง'),
            'level' => $this->string(2)->null()->comment('ระดับ A-I'),
            'fix' => $this->text()->null()->comment('การแก้ไข'),
            'note' => $this->text()->null(),
            'deleted' => $this->tinyInteger(1)->notNull()->defaultValue(0),
        ], $audit()));
        $this->createIndex('uq-ha12_indicator-ref', '{{%ha12_indicator}}', 'ref', true);
        $this->createIndex('idx-ha12_indicator-scope', '{{%ha12_indicator}}', ['owner_unit_id', 'fiscal_year', 'deleted']);
        $this->addForeignKey('fk-ha12_indicator-unit', '{{%ha12_indicator}}', 'owner_unit_id', '{{%tree}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%ha12_indicator_value}}', array_merge([
            'id' => $this->primaryKey(),
            'indicator_id' => $this->integer()->notNull(),
            'month_no' => $this->tinyInteger(1)->notNull()->comment('1-12 (M01=ต.ค.)'),
            'value' => $this->decimal(14, 2)->null(),
        ], $audit()));
        $this->createIndex('uq-ha12_indicator_value-ref', '{{%ha12_indicator_value}}', 'ref', true);
        $this->createIndex('uq-ha12_indicator_value-im', '{{%ha12_indicator_value}}', ['indicator_id', 'month_no'], true);
        $this->addForeignKey('fk-ha12_indicator_value-ind', '{{%ha12_indicator_value}}', 'indicator_id', '{{%ha12_indicator}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%ha12_indicator_value}}');
        $this->dropTable('{{%ha12_indicator}}');
    }
}
