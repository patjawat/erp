<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ใบประเมินสมรรถนะรายบุคคล และคะแนนรายข้อพฤติกรรม
 *
 *   hr_competency_evaluation  1 ใบ ต่อ 1 การมอบหมาย (คน + รอบ) — สถานะและคะแนนสรุป
 *     hr_competency_score       คะแนนรายข้อพฤติกรรมบ่งชี้ 1–5
 *
 * เก็บคะแนนที่ระดับ "ข้อ" เสมอ แม้ผู้ประเมินจะให้คะแนนทีละระดับ
 * (ให้ทั้งระดับ = เขียนค่าเดียวกันลงทุกข้อในระดับนั้น แล้วทำเครื่องหมายไว้ที่ scored_by)
 * เพื่อให้สูตรคิดคะแนนเหมือนแบบฟอร์มกระดาษเป๊ะ และเปลี่ยนวิธีกรอกภายหลังได้โดยไม่ต้องย้ายข้อมูล
 */
final class m260809_000007_create_hr_competency_evaluation extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%hr_competency_evaluation}}', [
            'id' => $this->primaryKey(),
            'assignment_id' => $this->integer()->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('draft')
                ->comment('draft = กำลังให้คะแนน / completed = ให้ครบแล้ว / submitted = ส่งผลแล้ว ล็อกแก้ไข'),
            'score_percent' => $this->decimal(6, 2)->null()->comment('คะแนนสมรรถนะรวม เต็ม 100'),
            'comment' => $this->text()->null()->comment('ข้อเสนอแนะของผู้ประเมิน'),
            'completed_at' => $this->dateTime()->null(),
            'submitted_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);

        $this->createIndex('uq-hr_competency_evaluation-assignment', '{{%hr_competency_evaluation}}', 'assignment_id', true);
        $this->createIndex('idx-hr_competency_evaluation-status', '{{%hr_competency_evaluation}}', 'status');
        $this->addForeignKey('fk-hr_competency_evaluation-assignment', '{{%hr_competency_evaluation}}',
            'assignment_id', '{{%hr_competency_assignment}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%hr_competency_score}}', [
            'id' => $this->primaryKey(),
            'evaluation_id' => $this->integer()->notNull(),
            'indicator_id' => $this->integer()->notNull(),
            'score' => $this->tinyInteger()->notNull()->comment('คะแนน 1–5 ตามชุดมาตรวัดของข้อนั้น'),
            'scored_by' => $this->string(10)->notNull()->defaultValue('level')
                ->comment('level = ให้คะแนนทั้งระดับ / item = ผู้ประเมินแยกให้รายข้อ'),
            'updated_at' => $this->dateTime()->null(),
        ], $options);

        $this->createIndex('uq-hr_competency_score-eval_indicator', '{{%hr_competency_score}}',
            ['evaluation_id', 'indicator_id'], true);
        $this->createIndex('idx-hr_competency_score-indicator', '{{%hr_competency_score}}', 'indicator_id');
        $this->addForeignKey('fk-hr_competency_score-evaluation', '{{%hr_competency_score}}',
            'evaluation_id', '{{%hr_competency_evaluation}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-hr_competency_score-indicator', '{{%hr_competency_score}}',
            'indicator_id', '{{%hr_competency_indicator}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%hr_competency_score}}');
        $this->dropTable('{{%hr_competency_evaluation}}');
    }
}
