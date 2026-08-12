<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * 9-Box Talent Grid — จัดวางบุคลากรตามแกนผลงาน (performance) และศักยภาพ (potential)
 * เก็บผลการจัดวางรายปีงบประมาณ 1 คน / 1 ปี เพื่อเทียบพัฒนาการข้ามปีได้
 * ชื่อบุคลากรอ้างจากทะเบียนบุคลากร (employees) ไม่เก็บซ้ำในตารางนี้
 */
final class m260808_000005_create_hr_talent_grid extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%hr_talent_grid}}', [
            'id' => $this->primaryKey(),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ พ.ศ.'),
            'emp_id' => $this->integer()->notNull(),
            'performance' => $this->tinyInteger()->notNull()->defaultValue(2)->comment('1=ต่ำ 2=ปานกลาง 3=สูง'),
            'potential' => $this->tinyInteger()->notNull()->defaultValue(2)->comment('1=ต่ำ 2=ปานกลาง 3=สูง'),
            'box_no' => $this->tinyInteger()->notNull()->defaultValue(5)->comment('1-9 คำนวณจาก (potential-1)*3+performance'),
            'note' => $this->text()->null(),
            'assessed_at' => $this->date()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);

        $this->createIndex('idx-hr_talent_grid-fiscal_year', '{{%hr_talent_grid}}', 'fiscal_year');
        $this->createIndex('idx-hr_talent_grid-box_no', '{{%hr_talent_grid}}', 'box_no');
        $this->createIndex('uq-hr_talent_grid-year_emp', '{{%hr_talent_grid}}', ['fiscal_year', 'emp_id'], true);
        $this->addForeignKey('fk-hr_talent_grid-emp', '{{%hr_talent_grid}}', 'emp_id', '{{%employees}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-hr_talent_grid-emp', '{{%hr_talent_grid}}');
        $this->dropTable('{{%hr_talent_grid}}');
    }
}
