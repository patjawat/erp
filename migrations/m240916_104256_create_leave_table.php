<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%leave}}`.
 */
class m240916_104256_create_leave_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%leave}}', [
            'id' => $this->primaryKey(),
            'emp_id' => $this->integer()->comment('รหัสพนักงาน'),
            'leave_type_id' => $this->string()->comment('ประเภทการขอลา'),
            'leave_time_type' => $this->double()->comment('ประเภทการลา'), 
            'days_off' => $this->double()->comment('จำนวนวัน'), 
            'data_json' => $this->json(),
            'date_start' => $this->date()->comment('วันที่ลา'),
            'date_start_type' => $this->double()->comment('ประเภทครึ่งวันเต็มวัน'), 
            'date_end' => $this->date()->comment('ถึงวันที่'),
            'date_end_type' => $this->double()->comment('ประเภทครึ่งวันเต็มวัน'), 
            'status' => $this->string(255)->comment('สถานะ'),
            'ref' => $this->string(255),
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
        $this->dropTable('{{%leave}}');
    }
}
