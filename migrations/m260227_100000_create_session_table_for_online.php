<?php

use yii\db\Migration;

/**
 * สร้างตาราง session สำหรับเก็บ session ใน DB
 * ใช้ร่วมกับ config session => ['class' => 'yii\web\DbSession', ...]
 * เพื่อให้ Dashboard แสดง "กำลังออนไลน์" และหน้า /usermanager/session แสดงรายการผู้ใช้ที่ออนไลน์ได้
 */
class m260227_100000_create_session_table_for_online extends Migration
{
    public function safeUp()
    {
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%session}}', [
            'id' => $this->char(40)->notNull()->comment('Session ID'),
            'expire' => $this->integer()->null()->comment('Unix timestamp หมดอายุ'),
            'data' => $this->text()->null()->comment('ข้อมูล session'),
            'user_id' => $this->integer()->null()->comment('รหัส user ที่ล็อกอิน (สำหรับแสดงใครออนไลน์)'),
            'ip_address' => $this->string(45)->null()->comment('IP address'),
            'login_time' => $this->integer()->null()->comment('Unix timestamp ตอนเขียน session'),
        ], $tableOptions);

        $this->addPrimaryKey('pk-session-id', '{{%session}}', 'id');
        $this->createIndex('idx-session-expire', '{{%session}}', 'expire');
        $this->createIndex('idx-session-user_id', '{{%session}}', 'user_id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%session}}');
    }
}
