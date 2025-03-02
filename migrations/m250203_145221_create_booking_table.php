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
            'name' => $this->string(255)->comment('ชื่อกาารเก็บข้อมูล (meeting_service,driver_service)'),
            'thai_year' => $this->integer(255)->comment('ปีงบประมาณ'),
            'ambulance_type' => $this->string(255)->comment('ประเภทของการรับส่งสำหรับรถพยาบาล'),
            'mileage_start' => $this->double(255)->comment('เลขไมล์รถก่อนออกเดินทาง'),
            'mileage_end' => $this->double(255)->comment('เลขไมล์หลังเดินทาง'),
            'distance_km' => $this->double(255)->comment('นะยะทาง กม.'),
            'oil_price' => $this->double(255)->comment('น้ำมันที่เติม'),
            'oil_liter' => $this->double(255)->comment('ปริมาณน้ำมัน'),
            'car_type' => $this->string()->comment('ประเภทของรถ general หรือ ambulance'),
            'document_id' => $this->integer()->comment('ตามหนังสือ'),
            'owner_id' => $this->integer()->comment('ผู้ดูแลห้องประชุม'),
            'urgent' => $this->string()->comment('ความเร่งด่วน'),
            'license_plate' => $this->string()->comment('ทะเบียนยานพาหนะ'),
            'room_id' => $this->string()->comment('ห้องประชุม'),
            'location' => $this->string()->comment('สถานที่ไป'),
            'reason' => $this->string()->comment('เหตุผล'),
            'status' => $this->string()->comment('สถานะ'),
            'date_start' => $this->date()->comment('เริ่มวันที่'),
            'time_start' => $this->string()->comment('เริ่มเวลา'),
            'date_end' => $this->date()->comment('ถึงวันที่'),
            'time_end' => $this->string()->comment('ถึงเวลา'),
            'driver_id' => $this->string()->comment('พนักงานขับ'),
            'leader_id' => $this->string()->comment('หัวหน้างานรับรอง'),
            'emp_id' => $this->string()->comment('ผู้ขอ'),
            'private_car' => $this->tinyInteger(0)->notNull()->defaultValue(0)->comment('รถยนต์ส่วนตัว'), // 1 = true, 0 = false
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
