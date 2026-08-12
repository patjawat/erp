<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * การมอบหมายผู้ประเมินสมรรถนะ — HR กำหนดว่าใครประเมินใคร ในปีงบประมาณหนึ่ง
 *
 * ไม่คำนวณจากผังองค์กรอัตโนมัติ เพราะโครงสร้างจริงมีข้อยกเว้นเยอะ
 * (หัวหน้าคนเดียวคุมหลายหน่วย · หัวหน้าที่ต้องประเมินตัวเอง · หน่วยที่ไม่มีหน่วยแม่)
 * ระบบแค่ "แนะนำ" ผู้ประเมินตามผังให้ แล้ว HR เป็นผู้ตัดสินใจและกดยืนยัน
 *
 * รายชื่อที่ต้องประเมินจะไปแสดงบนหน้า /me ของผู้ประเมิน ก็ต่อเมื่อมีแถวในตารางนี้แล้ว
 */
final class m260809_000004_create_hr_competency_assignment extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%hr_competency_assignment}}', [
            'id' => $this->primaryKey(),
            'emp_id' => $this->integer()->notNull()->comment('ผู้ถูกประเมิน'),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ พ.ศ. (ใช้ทั้ง 2 รอบของปีนั้น)'),
            'evaluator_id' => $this->integer()->null()->comment('ผู้ประเมินที่ HR กำหนด'),
            'source' => $this->string(20)->notNull()->defaultValue('manual')->comment('manual = HR เลือกเอง / suggested = รับค่าที่ระบบแนะนำตามผังองค์กร'),
            'status' => $this->string(20)->notNull()->defaultValue('draft')->comment('draft = ยังกำหนดไม่ครบ / ready = พร้อมให้ประเมิน'),
            'note' => $this->string(255)->null(),
            'assigned_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);

        $this->createIndex('uq-hr_competency_assignment-emp_year', '{{%hr_competency_assignment}}', ['emp_id', 'fiscal_year'], true);
        $this->createIndex('idx-hr_competency_assignment-evaluator', '{{%hr_competency_assignment}}', ['evaluator_id', 'fiscal_year', 'status']);
        $this->addForeignKey('fk-hr_competency_assignment-emp', '{{%hr_competency_assignment}}', 'emp_id', '{{%employees}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-hr_competency_assignment-evaluator', '{{%hr_competency_assignment}}', 'evaluator_id', '{{%employees}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%hr_competency_assignment}}');
    }
}
