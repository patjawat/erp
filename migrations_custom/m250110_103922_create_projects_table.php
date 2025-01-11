<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%projects}}`.
 */
class m250110_103922_create_projects_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%projects}}', [
            'id' => $this->primaryKey()->comment('คีย์หลักที่เป็น Auto Increment'),
            'name' => $this->string(255)->notNull()->comment('ชื่อของโปรเจกต์'),
            'status' => $this->string(50)->defaultValue('Pending')->comment('สถานะของโปรเจกต์ เช่น Pending, In Progress, Completed'),
            'dead_line_date' => $this->date()->comment('วันที่ครบกำหนดโปรเจกต์'),
            'start_date' => $this->date()->comment('วันที่เริ่มต้นโปรเจกต์'),
            'end_date' => $this->date()->comment('วันที่สิ้นสุดโปรเจกต์'),
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
        $this->dropTable('{{%projects}}');
    }
}
