<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%booking_cars_item}}`.
 */
class m250130_133432_create_booking_cars_items_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%booking_cars_items}}', [
            'id' => $this->primaryKey(),
            'car_type' => $this->string()->comment('ประเภทของรถตามการใช้งาน'),
            'asset_item_id' => $this->integer()->comment('รายการทรัพย์สิน'),
            'license_plate' => $this->string()->comment('เลขทะเบียน'),
            'active' => $this->boolean()->defaultValue(true),
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
        $this->dropTable('{{%booking_cars_items}}');
    }
}
