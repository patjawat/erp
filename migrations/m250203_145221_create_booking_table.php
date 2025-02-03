<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%booking}}`.
 */
class m250203_145221_create_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%booking}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'name' => $this->string(255)->comment('ชื่อกาารเก็บข้อมูล (car,conference)'),
            'thai_year' => $this->integer(255)->comment('ปีงบประมาณ'),
            'car_type' => $this->string()->comment('ประเภทของรถ general หรือ ambulance'),
            'document_id' => $this->integer()->comment('ตามหนังสือ'),
            'urgent' => $this->string()->comment('ความเร่งด่วน'),
            'license_plate' => $this->string()->comment('ทะเบียนยานพาหนะ'),
            'conference_room_id' => $this->string()->comment('ห้องประชุม'),
            'location' => $this->string()->comment('สถานที่ไป'),
            'reason' => $this->string()->comment('เหตุผล'),
            'status' => $this->string()->comment('สถานะ'),
            'date_start' => $this->date()->comment('เริ่มวันที่'),
            'time_start' => $this->string()->comment('เริ่มเวลา'),
            'date_end' => $this->date()->comment('ถึงวันที่'),
            'time_end' => $this->string()->comment('ถึงเวลา'),
            'driver_id' => $this->string()->comment('พนักงานขับ'),
            'leader_id' => $this->string()->comment('หัวหน้างานรับรอง'),
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
        $this->dropTable('{{%booking}}');
    }
}
