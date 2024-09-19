<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%calendar}}`.
 */
class m240918_082619_create_calendar_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%calendar}}', [
            'id' => $this->primaryKey(),
            'emp_id' => $this->integer()->comment('พนีกงาน'),
            'name' => $this->string(255)->comment('ชื่อการเก็บ'),
            'title' => $this->string(255)->comment('คำอธิบาย'),
            'date_start' => $this->date()->comment('วันที่ลา'),
            'date_end' => $this->date()->comment('ถึงวันที่'),
            'status' => $this->string(255)->comment('สถานะ'),
            'data_json' => $this->json(),
            'thai_year' => $this->integer(255)->comment('ปีงบประมาณ'),
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
        $this->dropTable('{{%calendar}}');
    }
}
