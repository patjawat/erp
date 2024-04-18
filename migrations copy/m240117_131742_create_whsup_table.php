<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%whsup}}`.
 */
class m240117_131742_create_whsup_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%whsup}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'sup_code' => $this->string(255)->comment('รหัสวัสดุ'),
            'sup_detail' => $this->string(600)->comment('รายละเอียด'),
            'sup_type' => $this->string(255)->comment('ประเภท'),
            'sup_store' => $this->string(255)->comment('คลัง'),
            'sup_unit' => $this->string(20)->comment('คลัง'),
            'data_json' => $this->json(),
            'updated_at' => $this->dateTime()->comment('วันเวลาแก้ไข'),
            'created_at' => $this->dateTime()->comment('วันเวลาสร้าง'), 
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%whsup}}');
    }
}
