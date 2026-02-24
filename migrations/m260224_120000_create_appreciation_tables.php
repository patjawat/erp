<?php

use yii\db\Migration;

/**
 * สร้างตารางสำหรับโมดูล พลังแห่งคำขอบคุณ (Appreciation Wall)
 * - appreciation: คำขอบคุณที่ส่งถึงกัน
 * - appreciation_like: การกด like บนคำขอบคุณ
 * - appreciation_challenge: กิจกรรม Challenge เป้าหมายรับรางวัล
 * - appreciation_challenge_progress: ความคืบหน้าของผู้เข้าร่วมแต่ละคน
 */
class m260224_120000_create_appreciation_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        $this->createTable('{{%appreciation}}', [
            'id' => $this->primaryKey(),
            'from_emp_id' => $this->integer()->notNull()->comment('ผู้ส่งคำชม'),
            'to_emp_id' => $this->integer()->notNull()->comment('ผู้รับคำชม'),
            'message' => $this->text()->notNull()->comment('ข้อความคำขอบคุณ'),
            'badge_type' => $this->string(64)->null()->comment('ประเภทคำชม เช่น team_player, problem_solver'),
            'points_given' => $this->integer()->notNull()->defaultValue(50)->comment('คะแนนที่ให้ต่อคำชม'),
            'created_at' => $this->datetime()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx-appreciation-from_emp_id', '{{%appreciation}}', 'from_emp_id');
        $this->createIndex('idx-appreciation-to_emp_id', '{{%appreciation}}', 'to_emp_id');
        $this->createIndex('idx-appreciation-created_at', '{{%appreciation}}', 'created_at');
        $this->addForeignKey(
            'fk-appreciation-from_emp',
            '{{%appreciation}}',
            'from_emp_id',
            '{{%employees}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-appreciation-to_emp',
            '{{%appreciation}}',
            'to_emp_id',
            '{{%employees}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createTable('{{%appreciation_like}}', [
            'id' => $this->primaryKey(),
            'appreciation_id' => $this->integer()->notNull(),
            'emp_id' => $this->integer()->notNull(),
            'created_at' => $this->datetime()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx-appreciation_like-appreciation_id', '{{%appreciation_like}}', 'appreciation_id');
        $this->createIndex('idx-appreciation_like-emp_id', '{{%appreciation_like}}', 'emp_id');
        $this->createIndex('uq-appreciation_like-appreciation-emp', '{{%appreciation_like}}', ['appreciation_id', 'emp_id'], true);
        $this->addForeignKey(
            'fk-appreciation_like-appreciation',
            '{{%appreciation_like}}',
            'appreciation_id',
            '{{%appreciation}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-appreciation_like-emp',
            '{{%appreciation_like}}',
            'emp_id',
            '{{%employees}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createTable('{{%appreciation_challenge}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->comment('ชื่อกิจกรรม'),
            'description' => $this->text()->null()->comment('รายละเอียด'),
            'start_at' => $this->date()->notNull(),
            'end_at' => $this->date()->notNull(),
            'goal_type' => $this->string(32)->notNull()->comment('send_count หรือ receive_count'),
            'goal_value' => $this->integer()->notNull()->comment('เป้าหมาย เช่น 10 ครั้ง'),
            'reward_name' => $this->string(255)->null()->comment('ของรางวัล'),
            'reward_description' => $this->text()->null(),
            'status' => $this->string(16)->notNull()->defaultValue('draft')->comment('draft, active, ended'),
            'created_at' => $this->datetime()->notNull(),
            'updated_at' => $this->datetime()->null(),
        ], $tableOptions);

        $this->createIndex('idx-appreciation_challenge-status', '{{%appreciation_challenge}}', 'status');
        $this->createIndex('idx-appreciation_challenge-dates', '{{%appreciation_challenge}}', ['start_at', 'end_at']);

        $this->createTable('{{%appreciation_challenge_progress}}', [
            'id' => $this->primaryKey(),
            'challenge_id' => $this->integer()->notNull(),
            'emp_id' => $this->integer()->notNull(),
            'current_value' => $this->integer()->notNull()->defaultValue(0)->comment('ค่าปัจจุบัน เช่น จำนวนครั้งที่ส่ง/รับ'),
            'completed_at' => $this->datetime()->null()->comment('เวลาที่ทำครบเป้า'),
            'created_at' => $this->datetime()->notNull(),
            'updated_at' => $this->datetime()->null(),
        ], $tableOptions);

        $this->createIndex('idx-appreciation_challenge_progress-challenge_emp', '{{%appreciation_challenge_progress}}', ['challenge_id', 'emp_id'], true);
        $this->addForeignKey(
            'fk-appreciation_challenge_progress-challenge',
            '{{%appreciation_challenge_progress}}',
            'challenge_id',
            '{{%appreciation_challenge}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-appreciation_challenge_progress-emp',
            '{{%appreciation_challenge_progress}}',
            'emp_id',
            '{{%employees}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%appreciation_challenge_progress}}');
        $this->dropTable('{{%appreciation_challenge}}');
        $this->dropTable('{{%appreciation_like}}');
        $this->dropTable('{{%appreciation}}');
    }
}
