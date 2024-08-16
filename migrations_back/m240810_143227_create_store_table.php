<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%store}}`.
 */
class m240810_143227_create_store_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%store}}', [
            'id' => $this->primaryKey()->comment('รหัสการเคลื่อนไหวสินค้า'),
            'name' => $this->string(50)->comment('ชื่อการเก็บของข้อมูล เช่น stock_order, order_item'),
            'asset_item' => $this->string(255)->comment('รหัสสินค้า'),
            'warehouse_id' => $this->integer()->comment('รหัสคลังสินค้าปลายทาง'),
            'stock_qty' => $this->integer()->comment('จำนวนสินค้าที่'),
            'ref' => $this->string(255),
            'thai_year' => $this->integer(255)->comment('ปีงบประมาณ'),
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
        $this->dropTable('{{%store}}');
    }
}
