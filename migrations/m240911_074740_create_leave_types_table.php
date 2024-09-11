<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%leave_types}}`.
 */
class m240911_074740_create_leave_types_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%leave_types}}', [
            'id' => $this->primaryKey(),
            'name' =>  $this->string(255)->comment('ชื่อการลา'),
            'max_days' =>  $this->string(255)->comment('จำนวนวันที่สามารถลาได้สูงสุด'),
            'data_json' => $this->json(),
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
        $this->dropTable('{{%leave_types}}');
    }
}
