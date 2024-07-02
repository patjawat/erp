<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%stock_movements}}`.
 */
class m240701_090624_create_stock_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('stock_order', [
            'id' => $this->primaryKey()->comment('รหัสการเคลื่อนไหวสินค้า'),
            'name' => $this->string(50)->comment('ชื่อการเก็บของข้อมูล เช่น stock_order, stock_item'),
            'po_number' => $this->string(50)->comment('รหัสใบสั่งซื้อ'),
            'rc_number' => $this->string(50)->comment('รหัสใบรับสินค้า'),
            'product_id' => $this->integer()->comment('รหัสสินค้า'),
            'from_warehouse_id' => $this->integer()->comment('รหัสคลังสินค้าต้นทาง'),
            'to_warehouse_id' => $this->integer()->comment('รหัสคลังสินค้าปลายทาง'),
            'qty' => $this->integer()->comment('จำนวนสินค้าที่เคลื่อนย้าย'),
            'total_price' => $this->double(255)->comment('รวมราคา'),
            'unit_price' => $this->double(255)->comment('ราคาต่อหน่วย'),
            'movement_type' => "ENUM('IN', 'OUT', 'TRANSFER') NOT NULL COMMENT 'ประเภทการเคลื่อนไหว (IN = รับเข้า, OUT = จ่ายออก, TRANSFER = โอนย้าย)'",
            'movement_date' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('วันที่และเวลาที่เกิดการเคลื่อนไหว'),
            'lot_number' => $this->string(50)->comment('หมายเลข Lot'),
            'expiry_date' => $this->date()->comment('วันหมดอายุ'),
            'category_id' => $this->string(255)->comment('หมวดหมูหลักที่เก็บ'),
            'ref' => $this->string(255),
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
        // $this->addForeignKey('fk-stock_movements-product_id', 'stock_movements', 'product_id', 'products', 'product_id', 'CASCADE', 'CASCADE');
        // $this->addForeignKey('fk-stock_movements-from_warehouse_id', 'stock_movements', 'from_warehouse_id', 'warehouses', 'warehouse_id', 'SET NULL', 'CASCADE');
        // $this->addForeignKey('fk-stock_movements-to_warehouse_id', 'stock_movements', 'to_warehouse_id', 'warehouses', 'warehouse_id', 'SET NULL', 'CASCADE');

        // $this->createIndex('idx-stock_movements-product_id', 'stock_movements', 'product_id');
        // $this->createIndex('idx-stock_movements-from_warehouse_id', 'stock_movements', 'from_warehouse_id');
        // $this->createIndex('idx-stock_movements-to_warehouse_id', 'stock_movements', 'to_warehouse_id');
        // $this->createIndex('idx-stock_movements-movement_date', 'stock_movements', 'movement_date');
        // $this->createIndex('idx-stock_movements-lot_number', 'stock_movements', 'lot_number');
        // $this->createIndex('idx-stock_movements-expiry_date', 'stock_movements', 'expiry_date');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%stock_order}}');
    }
}
