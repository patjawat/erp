<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%booking_cars}}`.
 */
class m250130_112214_create_booking_cars_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%booking_cars}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'thai_year' => $this->integer(255)->comment('ปีงบประมาณ'),
            'booking_type' => $this->string()->comment('ประเภทของรถ general หรือ ambulance'),
            'document_id' => $this->integer()->comment('ตามหนังสือ'),
            'urgent' => $this->string()->comment('ความเร่งด่วน'),
            'asset_item_id' => $this->string()->comment('ยานพาหนะ'),
            'location' => $this->string()->comment('สถานที่ไป'),
            'status' => $this->string()->comment('ความเห็น Y ผ่าน N ไม่ผ่าน'),
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
        $this->dropTable('{{%booking_cars}}');
    }
}
