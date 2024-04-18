<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%sup_register}}`.
 */
class m240109_141548_create_sup_register_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%sup_register}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'regisnumber' => $this->string(50)->comment('เลขทะเบียนคุม'),
            'start_date' => $this->date()->comment('วันที่'),
            'price' => $this->string(255)->comment('มูลค่า'),
            'status' => $this->string(255)->comment('สถานะ'),
            'dep_code' => $this->string(255)->comment('หน่วยงาน'),
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
        $this->dropTable('{{%sup_register}}');
    }
}
