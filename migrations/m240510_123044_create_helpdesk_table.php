<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%helpdesk}}`.
 */
class m240510_123044_create_helpdesk_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%helpdesk}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'code' => $this->string(255),
            'date_start' => $this->date(),   
            'date_end' => $this->date(),   
            'name' => $this->string(255)->comment('ชื่อการเก็บข้อมูล'),
            'title' => $this->string(255)->comment('รายการ'),
            'data_json' => $this->json()->comment('การเก็บข้อมูลชนิด JSON'),
            'ma_items' => $this->json()->comment('การบำรุงรักษา'),
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
        $this->dropTable('{{%helpdesk}}');
    }
}
