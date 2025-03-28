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
            'id' => $this->primaryKey()->comment('รหัสคลังสินค้า'),
            'lot_number' => $this->string(50)->comment('หมายเลข Lot'),
            'warehouse_id' => $this->integer()->comment('รหัสคลังสินค้า'),
            'name' => $this->string(50)->comment('ชื่อการเก็บของข้อมูล เช่น order, item'),
            'code' => $this->string(50)->comment('รหัส'),
            'asset_item' => $this->string(255)->comment('รหัสสินค้า'),
            'qty' => $this->float()->comment('จำนวนสินค้าที่เคลื่อนย้าย'),
            'unit_price' => $this->double(255)->comment('ราคาต่อหน่วย'),
            'data_json' => $this->json(),
            'thai_year' => $this->integer(255)->comment('ปีงบประมาณ'),
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
        $this->dropTable('{{%stock}}');
    }
}
