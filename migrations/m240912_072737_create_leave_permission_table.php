<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%leave_permission}}`.
 */
class m240912_072737_create_leave_permission_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%leave_permission}}', [
            'id' => $this->primaryKey(),
            'emp_id' => $this->string(255)->comment('พนักงาน'),
            'service_time' => $this->integer()->comment('อายุงาน'),
            'point' => $this->boolean()->comment('จำนวนที่ลาได้'),
            'last_point' => $this->boolean()->comment('สะสมวันลา'),
            'new_point' => $this->integer()->comment('ลาได้'),
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
        $this->dropTable('{{%leave_permission}}');
    }
}
