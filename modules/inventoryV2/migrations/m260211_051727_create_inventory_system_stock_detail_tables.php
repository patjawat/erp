<?php

use yii\db\Migration;

class m260211_051727_create_inventory_system_stock_detail_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
   public function safeUp()
    {
        // 1. สร้างตาราง stock_detail (เฉพาะที่ยังไม่มี)
        $this->createTable('{{%stock_detail}}', [
            'detail_id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull()->comment('เชื่อมกับหัวเอกสาร Stock Order'),
            'item_id' => $this->integer()->notNull()->comment('พัสดุที่เบิก'),
            'lot_number' => $this->string(100)->comment('เลขล็อต (คลังหลักจะเป็นคนระบุตอนกดจ่าย)'),
            'qty_requested' => $this->decimal(10, 2)->notNull()->comment('จำนวนที่แผนกขอมาตอนแรก'),
            'qty_issued' => $this->decimal(10, 2)->defaultValue(0)->comment('จำนวนที่คลังจ่ายให้จริง (อาจน้อยกว่าหรือเท่ากับที่ขอ)'),
            'item_status' => "ENUM('NORMAL', 'CANCELLED') DEFAULT 'NORMAL' COMMENT 'สถานะรายรายการ (กรณียกเลิกเฉพาะบางชิ้น)'",
        ]);

        // 2. เพิ่ม Foreign Key เชื่อมตาราง stock (ที่มีอยู่แล้ว) เข้ากับ warehouses (ที่มีอยู่แล้ว)
        // อ้างอิงไปที่ id ของ warehouses ตามที่คุณแจ้งมา
        $this->addForeignKey('fk-stocks-balance-warehouse-ref', '{{%stock}}', 'warehouse_id', '{{%warehouses}}', 'id', 'CASCADE');

        // 3. เพิ่ม Foreign Key เชื่อม stock_detail เข้ากับ stock_order
        $this->addForeignKey('fk-stock-detail-order-ref', '{{%stock_detail}}', 'order_id', '{{%stock_order}}', 'order_id', 'CASCADE');
    }

    public function safeDown()
    {
        // 1. ลบ Foreign Key ก่อน (ต้องลบตามชื่อที่ตั้งไว้ใน safeUp)
        $this->dropForeignKey('fk-stock-detail-order-ref', '{{%stock_detail}}');
        $this->dropForeignKey('fk-stocks-balance-warehouse-ref', '{{%stock}}');

        // 2. ลบตารางที่สร้างขึ้นใหม่ใน Migration นี้
        $this->dropTable('{{%stock_detail}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260211_051727_create_inventory_system_stock_detail_tables cannot be reverted.\n";

        return false;
    }
    */
}
