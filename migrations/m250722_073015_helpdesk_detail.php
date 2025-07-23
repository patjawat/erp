<?php

use yii\db\Migration;

class m250722_073015_helpdesk_detail extends Migration
{
    /**
     * {@inheritdoc}
     */
  public function safeUp()
    {
        $this->createTable('{{%helpdesk_detail}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'helpdesk_id' => $this->integer()->defaultValue(0)->comment('เชื่อมกับ ID หลัก'),
            'emp_id' => $this->string()->comment('เชื่อมกับบุคลากร'),
            'name' => $this->string(255)->comment('ชื่อการเก็บข้อมูล'),
            'code' => $this->string(255),
            'title' => $this->string(255)->comment('รายการ'),
            'data_json' => $this->json()->comment('การเก็บข้อมูลชนิด JSON'),
            'status' => $this->string(255)->comment('สถานะ'),
            'rating' => $this->string(255)->comment('คะแนน'),
            'move_out' => $this->boolean()->comment('จำหน่าย'),
            'thai_year' => $this->integer(255)->comment('ปีงบประมาณ'),
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
        $this->dropTable('{{%helpdesk_detail}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250722_073015_helpdesk_detail cannot be reverted.\n";

        return false;
    }
    */
}
