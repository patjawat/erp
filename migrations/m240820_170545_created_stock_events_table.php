<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%d_stock_events}}`.
 */
class m240820_170545_created_stock_events_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%stock_events}}', [
            'id' => $this->primaryKey()->comment('รหัสการเคลื่อนไหวสินค้า'),
            'helpdesk_id' => $this->integer()->comment('รหัสงาน'),
            'name' => $this->string(50)->comment('ชื่อการเก็บของข้อมูล เช่น order, item'),
            'code' => $this->string(50)->comment('รหัส'),
            'transaction_type' => $this->string(255)->comment('ธุรกรรม'),
            'asset_item' => $this->string(255)->comment('รหัสสินค้า'),
            'warehouse_id' => $this->integer()->comment('รหัสคลังสินค้า'),
            'vendor_id' => $this->string(255)->comment('ผู้จำหน่าย ผู้บริจาค'),
            'po_number' => $this->string(255)->comment('จากเลขที่สั่งซื้อ'),
            'from_warehouse_id' => $this->integer()->comment('รหัสคลังสินค้าต้นทาง'),
            'qty' => $this->float()->comment('จำนวนสินค้าที่เคลื่อนย้าย'),
            'total_price' => $this->double(255)->comment('รวมราคา'),
            'unit_price' => $this->double(255)->comment('ราคาต่อหน่วย'),
            'receive_type' => $this->string(255)->comment('วิธีนำเข้า (normal = รับเข้าแบบปกติ, purchase = รับเข้าจาก PO)'),
            'movement_date' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('วันที่และเวลาที่เกิดการเคลื่อนไหว'),
            'lot_number' => $this->string(50)->comment('หมายเลข Lot'),
            'category_id' => $this->string(255)->comment('หมวดหมูหลักที่เก็บ'),
            'order_status' => $this->string(255)->comment('สถานะของ order (หัวรายการ)'),
            'checker' => $this->string(255)->comment('ผู้ตรวจสอบ'),
            'ref' => $this->string(255),
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
        $this->dropTable('{{%stock_events}}');
    }
}
