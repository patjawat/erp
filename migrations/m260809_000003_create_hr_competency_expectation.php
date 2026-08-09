<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ระดับที่คาดหวังของบุคลากรแต่ละคน ต่อสมรรถนะ 1 ตัว ในปีงบประมาณหนึ่ง
 *
 * ผู้ประเมินจะให้คะแนนเฉพาะระดับ 1 ถึงระดับที่คาดหวังนี้ ระดับที่เหลือไม่ถูกนับเข้าตัวหาร
 * ปีงบประมาณอยู่ใน hr_competency_year อยู่แล้ว จึงไม่เก็บซ้ำในตารางนี้
 */
final class m260809_000003_create_hr_competency_expectation extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%hr_competency_expectation}}', [
            'id' => $this->primaryKey(),
            'emp_id' => $this->integer()->notNull(),
            'competency_year_id' => $this->integer()->notNull(),
            'expected_level' => $this->tinyInteger()->notNull()->comment('ประเมินถึงระดับนี้'),
            'source' => $this->string(20)->notNull()->defaultValue('manual')->comment('manual = HR กำหนดเอง / suggested = รับค่าที่ระบบแนะนำ'),
            'note' => $this->string(255)->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);

        $this->createIndex('uq-hr_competency_expectation-emp_year', '{{%hr_competency_expectation}}', ['emp_id', 'competency_year_id'], true);
        $this->createIndex('idx-hr_competency_expectation-year', '{{%hr_competency_expectation}}', 'competency_year_id');
        $this->addForeignKey('fk-hr_competency_expectation-emp', '{{%hr_competency_expectation}}', 'emp_id', '{{%employees}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-hr_competency_expectation-year', '{{%hr_competency_expectation}}', 'competency_year_id', '{{%hr_competency_year}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%hr_competency_expectation}}');
    }
}
