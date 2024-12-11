<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%approve}}`.
 */
class m241209_060107_create_approve_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%approve}}', [
            'id' => $this->primaryKey(),
            'from_id' => $this->string() ->comment('รหัสการขออนุญาติ'),
            'name' => $this->string()->comment('ชื่อการอนุญาติ'),
            'title' => $this->text()->comment('ชื่อ'),
            'data_json' => $this->json(),
            'emp_id' => $this->integer()->comment('ผู้คตรวจสอลและอนุมัติ'),
            'status' => $this->string()->comment('ความเห็น Y ผ่าน N ไม่ผ่าน'),
            'level' => $this->integer()->comment('ลำดับการอนุมติ'),
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
        $this->dropTable('{{%approve}}');
    }
}
