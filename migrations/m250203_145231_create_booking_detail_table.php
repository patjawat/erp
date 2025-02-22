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
            'name' => $this->string(255),
            'booking_id' => $this->string()->comment('เชื่อมกับตารางหลัก'),
            'emp_id' => $this->integer()->comment('สมาชิก'),
            'ambulance_type' => $this->string(255)->comment('ประเภทของการรับส่งสำหรับรถพยาบาล'),
            'license_plate' => $this->string()->comment('ทะเบียนยานพาหนะ'),
            'driver_id' => $this->string()->comment('พนักงานขับรถ'),
            'date_start' => $this->date()->comment('เริ่มวันที่'),
            'time_start' => $this->string()->comment('เริ่มเวลา'),
            'date_end' => $this->date()->comment('ถึงวันที่'),
            'time_end' => $this->string()->comment('ถึงเวลา'),
            'mileage_start' => $this->double(255)->comment('เลขไมล์รถก่อนออกเดินทาง'),
            'mileage_end' => $this->double(255)->comment('เลขไมล์หลังเดินทาง'),
            'distance_km' => $this->double(255)->comment('นะยะทาง กม.'),
            'oil_price' => $this->double(255)->comment('น้ำมันที่เติม'),
            'oil_liter' => $this->double(255)->comment('ปริมาณน้ำมัน'),
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
