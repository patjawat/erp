<?php

use yii\db\Migration;

class m260211_125055_health_screen_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%health_screen}}', [
            'id' => $this->primaryKey(),
             'ref' => $this->string(255),
            'thai_year' => $this->integer(4)->notNull()->comment('ปีงบประมาณ'),
            'emp_id' => $this->integer()->notNull()->comment('รหัสพนักงาน'),
            'weight' => $this->decimal(10,2)->notNull()->comment('น้ำหนัก'),
            'height' => $this->decimal(10,2)->notNull()->comment('ส่วนสูง'),
            'bmi' => $this->decimal(10,2)->comment('BMI'),
            'date_checkup' => $this->string(10)->notNull()->comment('วันที่ตรวจสุขภาพ'),
            'checkup_status' => "ENUM('pending', 'wait_doctor', 'complete') DEFAULT 'pending' COMMENT 'สถานะการตรวจสุขภาพ'",
            'data_json' => $this->json()->comment('data_json'),
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
       $this->dropTable('{{%health_screen}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260211_125055_health_screen_table cannot be reverted.\n";

        return false;
    }
    */
}
