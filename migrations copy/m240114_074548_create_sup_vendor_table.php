<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%sup_vendor}}`.
 */
class m240114_074548_create_sup_vendor_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%sup_vendor}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'vendor_name' => $this->string(255)->comment('ตัวแทนจำหน่าย'),
            'vendor_tel' => $this->string(255)->comment('เบอร์โทร'),
            'vendor_add' => $this->string(600)->comment('ที่อยู่'),
            'vendor_contact' => $this->string(255)->comment('ผู้ติดต่อ'),
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
        $this->dropTable('{{%sup_vendor}}');
    }
}
