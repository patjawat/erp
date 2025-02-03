<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%booking_detail}}`.
 */
class m250203_145231_create_booking_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%booking_detail}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'bookig_id' => $this->string()->comment('เชื่อมกับตารางหลัก'),
            'name' => $this->string()->comment('ชื่อการเก็บข้อมุล,ผู้ร่วมเดินทาง,บุคคลอื่นร่วมเดินทาง,งานมอบหมาย'),
            'data_json' => $this->json()->comment('ยานพาหนะ'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
            'deleted_at' => $this->dateTime()->comment('วันที่ลบ'),
            'deleted_by' => $this->integer()->comment('ผู้ลบ')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%booking_detail}}');
    }
}
