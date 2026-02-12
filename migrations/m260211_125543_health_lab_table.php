<?php

use yii\db\Migration;

class m260211_125543_health_lab_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%health_lab}}', [
             'id' => $this->primaryKey(),
            'lab_code' => $this->string(255)->notNull()->unique()->comment('รหัสห้องปฏิบัติการ'),
            'lab_name' => $this->string(255)->notNull()->comment('ชื่อห้องปฏิบัติการ'),
            'lab_price' => $this->decimal(10,2)->notNull()->comment('ราคาห้องปฏิบัติการ'),
            'lab_type' => $this->string(255)->comment('ประเภทห้องปฏิบัติการ'),
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
         $this->dropTable('{{%health_lab}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260211_125543_health_lab_table cannot be reverted.\n";

        return false;
    }
    */
}
