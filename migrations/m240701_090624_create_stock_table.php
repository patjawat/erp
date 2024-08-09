<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%stock}}`.
 */
class m240701_090624_create_stock_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('stock', [
            'id' => $this->primaryKey()->comment('รหัสการเคลื่อนไหวสินค้า'),
            'name' => $this->string(50)->comment('ชื่อการเก็บของข้อมูล เช่น stock_order, stock_item'),
            'rq_number' => $this->string(50)->comment('รหัสร้องขอ'),
            'rc_number' => $this->string(50)->comment('รหัสใบรับเข้าคลัง'),
            'po_number' => $this->string(50)->comment('รหัสใบสั่งซื้อ'),
            'asset_item' => $this->string(255)->comment('รหัสสินค้า'),
            'from_warehouse_id' => $this->integer()->comment('รหัสคลังสินค้าต้นทาง'),
            'to_warehouse_id' => $this->integer()->comment('รหัสคลังสินค้าปลายทาง'),
            'qty' => $this->integer()->comment('จำนวนสินค้าที่เคลื่อนย้าย'),
            'total_price' => $this->double(255)->comment('รวมราคา'),
            'unit_price' => $this->double(255)->comment('ราคาต่อหน่วย'),
            'movement_type' => "ENUM('IN', 'OUT') NOT NULL COMMENT 'ประเภทการเคลื่อนไหว (IN = รับเข้า, OUT = เบิก)'",
            'receive_type' => $this->string(255)->comment('วิธีนำเข้า (normal = รับเข้าแบบปกติ, purchase = รับเข้าจาก PO)'),
            'movement_date' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('วันที่และเวลาที่เกิดการเคลื่อนไหว'),
            'lot_number' => $this->string(50)->comment('หมายเลข Lot'),
            'expiry_date' => $this->date()->comment('วันหมดอายุ'),
            'category_id' => $this->string(255)->comment('หมวดหมูหลักที่เก็บ'),
            'order_status' => $this->string(255)->comment('สถานะของ order (หัวรายการ)'),
            'ref' => $this->string(255),
            'thai_year' => $this->integer(255)->comment('ปีงบประมาณ'),
            'data_json' => $this->json(),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);

        // $this->createIndex('idx-stock-product_id', 'stock', 'product_id');
        // $this->createIndex('idx-stock-warehouse_id', 'stock', 'warehouse_id');
        // $this->createIndex('idx-stock-lot_number', 'stock', 'lot_number');
        // $this->createIndex('idx-stock-expiry_date', 'stock', 'expiry_date');

        // $this->addForeignKey('fk-stock-product_id', 'stock', 'product_id', 'products', 'product_id', 'CASCADE', 'CASCADE');
        // $this->addForeignKey('fk-stock-warehouse_id', 'stock', 'warehouse_id', 'warehouses', 'warehouse_id', 'CASCADE', 'CASCADE');

        // $this->createIndex('idx-warehouses-warehouse_name', 'warehouses', 'warehouse_name');
        // $this->createIndex('idx-products-product_name', 'products', 'product_name');
        // $this->addForeignKey('fk-stock-product_id', 'stock', 'product_id', 'products', 'product_id', 'CASCADE', 'CASCADE');
        // $this->addForeignKey('fk-stock-from_warehouse_id', 'stock', 'from_warehouse_id', 'warehouses', 'warehouse_id', 'SET NULL', 'CASCADE');
        // $this->addForeignKey('fk-stock-to_warehouse_id', 'stock', 'to_warehouse_id', 'warehouses', 'warehouse_id', 'SET NULL', 'CASCADE');

        // $this->createIndex('idx-stock-product_id', 'stock', 'product_id');
        // $this->createIndex('idx-stock-from_warehouse_id', 'stock', 'from_warehouse_id');
        // $this->createIndex('idx-stock-to_warehouse_id', 'stock', 'to_warehouse_id');
        // $this->createIndex('idx-stock-movement_date', 'stock', 'movement_date');
        // $this->createIndex('idx-stock-lot_number', 'stock', 'lot_number');
        // $this->createIndex('idx-stock-expiry_date', 'stock', 'expiry_date');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%stock}}');
    }
}
