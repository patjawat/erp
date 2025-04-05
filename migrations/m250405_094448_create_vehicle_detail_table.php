<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%vehicle_detail}}`.
 */
class m250405_094448_create_vehicle_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%vehicle_detail}}', [
            'id' => $this->primaryKey(),
            'vehicle_id' => $this->integer()->notNull()->comment('ID ของรถยนต์'),
            'ref' => $this->string(255),
            'mileage_start' => $this->double(255)->comment('เลขไมล์รถก่อนออกเดินทาง'),
            'mileage_end' => $this->double(255)->comment('เลขไมล์หลังเดินทาง'),
            'distance_km' => $this->double(255)->comment('ระยะทาง กม.'),
            'oil_price' => $this->double(255)->comment('น้ำมันที่เติม'),
            'oil_liter' => $this->double(255)->comment('ปริมาณน้ำมัน'),
            'license_plate' => $this->string()->comment('ทะเบียนยานพาหนะ'),
            'status' => $this->string()->comment('สถานะ'),
            'date_start' => $this->date()->comment('เริ่มวันที่'),
            'time_start' => $this->string()->comment('เริ่มเวลา'),
            'date_end' => $this->date()->comment('ถึงวันที่'),
            'time_end' => $this->string()->comment('ถึงเวลา'),
            'driver_id' => $this->string()->comment('พนักงานขับ'),
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
        $this->dropTable('{{%vehicle_detail}}');
    }
}
