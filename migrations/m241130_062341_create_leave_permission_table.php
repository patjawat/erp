<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%leave_role}}`.
 */
class m241130_062341_create_leave_permission_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%leave_permission}}', [
            'id' => $this->primaryKey(),
            'emp_id' => $this->string(255)->comment('พนักงาน'),
            'leave_days' => $this->boolean()->comment('สิทธิวันลาที่ได้'),
            'leave_before_days' => $this->boolean()->comment('จำนวนวันลาสะสม'),
            'leave_max_days' => $this->boolean()->comment('วันลาสะสมสูงสุด'),
            'leave_sum_days' => $this->boolean()->comment('วันลาสะสม'),
            'year_of_service' => $this->integer()->notNull()->comment('อายุงาน'),
            'position_type_id' => $this->string()->comment('ตำแหน่ง'),
            'leave_type_id' => $this->string()->comment('ประเภทการขอลา'),
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
