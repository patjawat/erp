<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%stock}}`.
 */
class m240816_071255_create_stock_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%stock}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->comment('ชื่อการเก็บของข้อมูล เช่น order, item'),
            'code' => $this->string(50)->comment('รหัส'),
            'asset_item' => $this->string(255)->comment('รหัสสินค้า'),
            'warehouse_id' => $this->integer()->comment('รหัสคลังสินค้า'),
            'qty' => $this->integer()->comment('จำนวนสินค้าที่เคลื่อนย้าย'),
            'data_json' => $this->json(),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%stock}}');
    }
}
