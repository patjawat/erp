<?php

use yii\db\Migration;

/**
 * Class m260713_120000_create_elearning_tables
 * สร้างตารางสำหรับระบบ E-learning ในระบบจัดการบุคลากร (HR)
 */
class m260713_120000_create_elearning_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        // 1. ตารางหลักสูตร (hr_elearning_course)
        $this->createTable('{{%hr_elearning_course}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull()->comment('ชื่อหลักสูตร'),
            'description' => $this->text()->null()->comment('รายละเอียดหลักสูตร'),
            'target_departments' => $this->text()->null()->comment('แผนกเป้าหมาย (JSON list of department IDs หรือ "all")'),
            'passing_score_percent' => $this->integer()->notNull()->defaultValue(80)->comment('เกณฑ์คะแนนสอบผ่าน (%)'),
            'is_active' => $this->tinyInteger(1)->notNull()->defaultValue(1)->comment('1=เปิดใช้งาน, 0=ปิดใช้งาน'),
            'created_at' => $this->datetime()->notNull(),
            'updated_at' => $this->datetime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $tableOptions);

        $this->createIndex('idx-hr_elearning_course-is_active', '{{%hr_elearning_course}}', 'is_active');

        // 2. ตารางสื่อการเรียนรู้ (hr_elearning_material)
        $this->createTable('{{%hr_elearning_material}}', [
            'id' => $this->primaryKey(),
            'course_id' => $this->integer()->notNull()->comment('รหัสหลักสูตร'),
            'title' => $this->string(255)->notNull()->comment('ชื่อสื่อการสอน'),
            'type' => $this->string(50)->notNull()->comment('ประเภทสื่อ (video_url, pdf_file, slide_link)'),
            'file_path' => $this->string(500)->notNull()->comment('ลิงก์หรือที่อยู่ไฟล์'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0)->comment('ลำดับการแสดงผล'),
            'created_at' => $this->datetime()->notNull(),
            'updated_at' => $this->datetime()->null(),
        ], $tableOptions);

        $this->createIndex('idx-hr_elearning_material-course_id', '{{%hr_elearning_material}}', 'course_id');
        $this->addForeignKey(
            'fk-hr_elearning_material-course',
            '{{%hr_elearning_material}}',
            'course_id',
            '{{%hr_elearning_course}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // 3. ตารางโจทย์ข้อสอบ (hr_elearning_question)
        $this->createTable('{{%hr_elearning_question}}', [
            'id' => $this->primaryKey(),
            'course_id' => $this->integer()->notNull()->comment('รหัสหลักสูตร'),
            'question_text' => $this->text()->notNull()->comment('โจทย์คำถาม'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0)->comment('ลำดับคำถาม'),
        ], $tableOptions);

        $this->createIndex('idx-hr_elearning_question-course_id', '{{%hr_elearning_question}}', 'course_id');
        $this->addForeignKey(
            'fk-hr_elearning_question-course',
            '{{%hr_elearning_question}}',
            'course_id',
            '{{%hr_elearning_course}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // 4. ตารางตัวเลือกและเฉลยคำตอบ (hr_elearning_answer)
        $this->createTable('{{%hr_elearning_answer}}', [
            'id' => $this->primaryKey(),
            'question_id' => $this->integer()->notNull()->comment('รหัสโจทย์ข้อถาม'),
            'answer_text' => $this->text()->notNull()->comment('ข้อความตัวเลือก'),
            'is_correct' => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('1=คำตอบที่ถูกต้อง, 0=คำตอบผิด'),
        ], $tableOptions);

        $this->createIndex('idx-hr_elearning_answer-question_id', '{{%hr_elearning_answer}}', 'question_id');
        $this->addForeignKey(
            'fk-hr_elearning_answer-question',
            '{{%hr_elearning_answer}}',
            'question_id',
            '{{%hr_elearning_question}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // 5. ตารางติดตามความคืบหน้าการเรียน (hr_elearning_progress)
        $this->createTable('{{%hr_elearning_progress}}', [
            'id' => $this->primaryKey(),
            'emp_id' => $this->integer()->notNull()->comment('รหัสพนักงาน'),
            'course_id' => $this->integer()->notNull()->comment('รหัสหลักสูตร'),
            'status' => $this->string(50)->notNull()->defaultValue('not_started')->comment('สถานะการเรียน (not_started, learning, completed)'),
            'started_at' => $this->datetime()->null(),
            'completed_at' => $this->datetime()->null(),
        ], $tableOptions);

        $this->createIndex('idx-hr_elearning_progress-emp_id', '{{%hr_elearning_progress}}', 'emp_id');
        $this->createIndex('idx-hr_elearning_progress-course_id', '{{%hr_elearning_progress}}', 'course_id');
        $this->createIndex('uq-hr_elearning_progress-emp_course', '{{%hr_elearning_progress}}', ['emp_id', 'course_id'], true);

        $this->addForeignKey(
            'fk-hr_elearning_progress-emp',
            '{{%hr_elearning_progress}}',
            'emp_id',
            '{{%employees}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-hr_elearning_progress-course',
            '{{%hr_elearning_progress}}',
            'course_id',
            '{{%hr_elearning_course}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // 6. ตารางบันทึกการทำแบบทดสอบ (hr_elearning_attempt)
        $this->createTable('{{%hr_elearning_attempt}}', [
            'id' => $this->primaryKey(),
            'emp_id' => $this->integer()->notNull()->comment('รหัสพนักงาน'),
            'course_id' => $this->integer()->notNull()->comment('รหัสหลักสูตร'),
            'attempt_number' => $this->integer()->notNull()->comment('จำนวนครั้งที่ทำ'),
            'score' => $this->integer()->notNull()->comment('คะแนนที่ได้'),
            'total_questions' => $this->integer()->notNull()->comment('จำนวนข้อทั้งหมด'),
            'percentage' => $this->decimal(5, 2)->notNull()->comment('เปอร์เซ็นต์คะแนนที่ได้'),
            'is_passed' => $this->tinyInteger(1)->notNull()->comment('1=ผ่านเกณฑ์, 0=ไม่ผ่านเกณฑ์'),
            'created_at' => $this->datetime()->notNull()->comment('วันเวลาที่ส่ง'),
        ], $tableOptions);

        $this->createIndex('idx-hr_elearning_attempt-emp_id', '{{%hr_elearning_attempt}}', 'emp_id');
        $this->createIndex('idx-hr_elearning_attempt-course_id', '{{%hr_elearning_attempt}}', 'course_id');

        $this->addForeignKey(
            'fk-hr_elearning_attempt-emp',
            '{{%hr_elearning_attempt}}',
            'emp_id',
            '{{%employees}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-hr_elearning_attempt-course',
            '{{%hr_elearning_attempt}}',
            'course_id',
            '{{%hr_elearning_course}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%hr_elearning_attempt}}');
        $this->dropTable('{{%hr_elearning_progress}}');
        $this->dropTable('{{%hr_elearning_answer}}');
        $this->dropTable('{{%hr_elearning_question}}');
        $this->dropTable('{{%hr_elearning_material}}');
        $this->dropTable('{{%hr_elearning_course}}');
    }
}
