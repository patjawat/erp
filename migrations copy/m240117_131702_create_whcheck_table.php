<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%whcheck}}`.
 */
class m240117_131702_create_whcheck_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%whcheck}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'check_code' => $this->string(255)->comment('รหัส'),
            'check_date' => $this->date()->comment('วันที่ตรวจรับ'),
            'check_type' => $this->string(20)->comment('ประเภทการรับ'),
            'check_store' => $this->string(255)->comment('คลัง'),
            'check_from' => $this->string(255)->comment('รับจาก'),
            'check_hr' => $this->string(255)->comment('เจ้าหน้าที่'),
            'check_status' => $this->string(255)->comment('สถานะการตรวจรับ'),
            'data_json' => $this->json(),
            'updated_at' => $this->dateTime()->comment('วันเวลาแก้ไข'),
            'created_at' => $this->dateTime()->comment('วันเวลาสร้าง'), 
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%whcheck}}');
    }
}
