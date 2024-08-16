<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%stock_out}}`.
 */
class m240816_071251_create_stock_out_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%stock_out}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->comment('ชื่อการเก็บของข้อมูล เช่น order, item'),
            'code' => $this->string(50)->comment('รหัส'),
            'asset_item' => $this->string(255)->comment('รหัสสินค้า'),
            'warehouse_id' => $this->integer()->comment('รหัสคลังสินค้า'),
            'from_warehouse_id' => $this->integer()->comment('รหัสคลังสินค้าปลายทาง'),
            'qty' => $this->integer()->comment('จำนวนสินค้าที่เคลื่อนย้าย'),
            'price' => $this->double(255)->comment('ราคาต่อหน่วย'),
            'movement_date' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('วันที่และเวลาที่เกิดการเคลื่อนไหว'),
            'lot_number' => $this->string(50)->comment('หมายเลข Lot'),
            'category_id' => $this->string(255)->comment('หมวดหมูหลักที่เก็บ'),
            'order_status' => $this->string(255)->comment('สถานะของ order (หัวรายการ)'),
            'ref' => $this->string(255),
            'thai_year' => $this->integer(255)->comment('ปีงบประมาณ'),
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
        $this->dropTable('{{%stock_out}}');
    }
}
