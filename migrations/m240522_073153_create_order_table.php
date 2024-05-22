<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order}}`.
 */
class m240522_073153_create_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'name' => $this->string(255)->comment('ชื่อตารางเก็บข้อมูล'),
            'category_id' => $this->string(255)->comment('หมวดหมูหลักที่เก็บ'),
            'code' => $this->string(255)->comment('รหัส'),
            'item_id' => $this->integer(255)->comment('รายการที่เก็บ'),
            'price' => $this->double(255)->comment('ราคา'),
            'amonth' => $this->integer(255)->comment('จำนวน'),
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
        $this->dropTable('{{%order}}');
    }
}
