<?php

use yii\db\Migration;

/**
 * Creates checkin_location and checkin_record tables for attendance check-in system.
 */
class m260224_100000_create_checkin_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%checkin_location}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->comment('ชื่อจุด/บริเวณ'),
            'lat' => $this->decimal(10, 7)->comment('Latitude ศูนย์กลาง'),
            'lng' => $this->decimal(10, 7)->comment('Longitude ศูนย์กลาง'),
            'radius_m' => $this->integer()->notNull()->defaultValue(0)->comment('รัศมีเมตร 0=ไม่บังคับ'),
            'qr_token' => $this->string(64)->unique()->comment('ค่า QR สำหรับสแกนที่จุดนี้'),
            'active' => $this->tinyInteger(1)->notNull()->defaultValue(1)->comment('1=ใช้งาน'),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);

        $this->createTable('{{%checkin_record}}', [
            'id' => $this->primaryKey(),
            'emp_id' => $this->integer()->notNull()->comment('พนักงาน'),
            'checkin_at' => $this->dateTime()->notNull()->comment('วันเวลาที่ลงเวลา'),
            'method' => $this->string(20)->notNull()->comment('qrcode|photo|manual'),
            'lat' => $this->decimal(10, 7)->null()->comment('Latitude'),
            'lng' => $this->decimal(10, 7)->null()->comment('Longitude'),
            'location_id' => $this->integer()->null()->comment('จุดลงเวลา'),
            'is_in_location' => $this->tinyInteger(1)->notNull()->defaultValue(1)->comment('1=อยู่ในบริเวณ'),
            'out_of_location_reason' => $this->text()->null()->comment('เหตุผลเมื่อนอกบริเวณ'),
            'photo_path' => $this->string(500)->null()->comment('path รูปถ่าย'),
            'qr_token' => $this->string(64)->null()->comment('ค่า QR ที่สแกน'),
            'data_json' => $this->json()->null(),
            'status' => $this->string(20)->notNull()->defaultValue('pending')->comment('pending|approved|rejected'),
            'approved_by' => $this->integer()->null()->comment('ผู้อนุมัติ emp_id'),
            'approved_at' => $this->dateTime()->null(),
            'comment' => $this->text()->null()->comment('ความเห็นผู้อนุมัติ'),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);

        $this->addForeignKey(
            'fk_checkin_record_emp',
            '{{%checkin_record}}',
            'emp_id',
            '{{%employees}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_checkin_record_location',
            '{{%checkin_record}}',
            'location_id',
            '{{%checkin_location}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->createIndex('idx_checkin_record_emp_checkin_at', '{{%checkin_record}}', ['emp_id', 'checkin_at']);
        $this->createIndex('idx_checkin_record_status', '{{%checkin_record}}', ['status']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_checkin_record_location', '{{%checkin_record}}');
        $this->dropForeignKey('fk_checkin_record_emp', '{{%checkin_record}}');
        $this->dropTable('{{%checkin_record}}');
        $this->dropTable('{{%checkin_location}}');
    }
}
