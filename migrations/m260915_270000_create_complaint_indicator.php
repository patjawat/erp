<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ระบบรับเรื่องร้องเรียน — เฟส 2 : ตารางข้อมูลพื้นฐานตัวชี้วัด (แทน Indicator_Visit sheet)
 *
 * เก็บจำนวน visit รายปีงบ ใช้เป็นตัวหารของ KPI CC01 (ข้อร้องเรียนระดับ 3+ ต่อ 10,000 visit)
 * กรอกมือที่หน้าตั้งค่า (ทีมศูนย์ฯ)
 */
final class m260915_270000_create_complaint_indicator extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%complaint_indicator}}', [
            'id' => $this->primaryKey(),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'visit_count' => $this->integer()->notNull()->defaultValue(0)->comment('จำนวน visit ทั้งปี'),
            'note' => $this->string(255)->null(),
            'ref' => $this->string(64)->notNull(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('uq-complaint_indicator-year', '{{%complaint_indicator}}', 'fiscal_year', true);
        $this->createIndex('uq-complaint_indicator-ref', '{{%complaint_indicator}}', 'ref', true);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%complaint_indicator}}');
    }
}
