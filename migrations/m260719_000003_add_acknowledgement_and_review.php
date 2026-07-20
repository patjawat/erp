<?php

use yii\db\Migration;

/** Adds employee acknowledgement and review-request audit trails. */
class m260719_000003_add_acknowledgement_and_review extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;
        $this->createTable('{{%jd_employee_acknowledgement}}', [
            'id' => $this->primaryKey(),
            'jd_employee_id' => $this->integer()->notNull(),
            'emp_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'employee_name' => $this->string(255)->notNull(),
            'acknowledged_at' => $this->dateTime()->notNull(),
            'ip_address' => $this->string(45)->null(),
            'user_agent' => $this->string(255)->null(),
        ], $options);
        $this->createIndex('uq-jd_ack-jd-emp', '{{%jd_employee_acknowledgement}}', ['jd_employee_id', 'emp_id'], true);
        $this->addForeignKey('fk-jd_ack-jd', '{{%jd_employee_acknowledgement}}', 'jd_employee_id', '{{%jd_employee}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-jd_ack-emp', '{{%jd_employee_acknowledgement}}', 'emp_id', '{{%employees}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%jd_change_request}}', [
            'id' => $this->primaryKey(),
            'jd_employee_id' => $this->integer()->notNull(),
            'emp_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'section_id' => $this->integer()->null(),
            'section_code' => $this->string(40)->null(),
            'section_title' => $this->string(255)->null(),
            'reason' => $this->text()->notNull(),
            'proposed_change' => $this->text()->null(),
            'status' => $this->string(30)->notNull()->defaultValue('submitted'),
            'submitted_at' => $this->dateTime()->notNull(),
            'reviewed_by' => $this->integer()->null(),
            'reviewed_at' => $this->dateTime()->null(),
            'resolution_note' => $this->text()->null(),
            'new_jd_employee_id' => $this->integer()->null(),
        ], $options);
        $this->createIndex('idx-jd_change_request-jd-status', '{{%jd_change_request}}', ['jd_employee_id', 'status']);
        $this->addForeignKey('fk-jd_change_request-jd', '{{%jd_change_request}}', 'jd_employee_id', '{{%jd_employee}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-jd_change_request-emp', '{{%jd_change_request}}', 'emp_id', '{{%employees}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-jd_change_request-section', '{{%jd_change_request}}', 'section_id', '{{%jd_employee_section}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk-jd_change_request-new-jd', '{{%jd_change_request}}', 'new_jd_employee_id', '{{%jd_employee}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%jd_change_request}}');
        $this->dropTable('{{%jd_employee_acknowledgement}}');
    }
}
