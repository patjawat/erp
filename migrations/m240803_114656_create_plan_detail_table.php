<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%plan_detail}}`.
 */
class m240803_114656_create_plan_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%plan_detail}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->comment('ชื่อตารางเก็บข้อมูล'),
            'plan_id' => $this->integer(255)->comment('หัวข้อแผน'),
            'price' => $this->double(255)->comment('ราคา'),
            'qty' => $this->integer(255)->comment('จำนวน'),
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
        $this->dropTable('{{%plan_detail}}');
    }
}
