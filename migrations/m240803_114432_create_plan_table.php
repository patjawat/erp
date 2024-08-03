<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%plan}}`.
 */
class m240803_114432_create_plan_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%plan}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->comment('ชื่อตารางเก็บข้อมูล'),
            'plan_type' => $this->integer(255)->comment('ประเภทของแผน'),
            'budget_type' => $this->string(255)->comment('ประเภทเงินที่ต้องใช้'),
            'plan_status' => $this->string(255)->comment('สถานะ'),
            'thai_year' => $this->integer(255)->comment('ปีงบประมาณ'),
            'data_json' => $this->json(),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%plan}}');
    }
}
