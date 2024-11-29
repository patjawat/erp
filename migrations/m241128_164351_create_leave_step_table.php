<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%leave_step}}`.
 */
class m241128_164351_create_leave_step_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%leave_step}}', [
            'id' => $this->primaryKey(),
            'data_json' => $this->json(),
            'leave_id' => $this->integer(),
            'emp_id' => $this->integer()->comment('ผู้คตรวจสอลและอนุมัติ'),
            'status' => $this->string()->comment('ความเห็น Y ผ่าน N ไม่ผ่าน'),
            'level' => $this->integer()->comment('ลำดับการอนุมติ'),
            'title' => $this->text()->comment('ชื่อ'),
            'comment' => $this->text()->comment('ความคิดเห็น'),
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
        $this->dropTable('{{%leave_step}}');
    }
}
