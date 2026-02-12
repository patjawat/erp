<?php

use yii\db\Migration;

class m260211_051719_create_inventory_system_stock_order_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
   $this->createTable('{{%stock_order}}', [
            'order_id' => $this->primaryKey(),
            'doc_no' => $this->string(50)->notNull()->unique()->comment('เลขที่เอกสาร (เช่น REQ-67001)'),
            'order_type' => "ENUM('ISSUE', 'DISBURSE', 'RECEIVE', 'RETURN', 'ADJUST') DEFAULT 'ISSUE' COMMENT 'ประเภทธุรกรรม: จ่ายให้คลังย่อย, ตัดใช้จริง, รับเข้า, ส่งคืน, ปรับยอด'",
            'from_warehouse_id' => $this->integer()->comment('คลังต้นทางที่จ่ายของออก'),
            'to_warehouse_id' => $this->integer()->comment('คลังปลายทางที่รับของเข้า'),
            'ref' => $this->string(255)->comment('อ้างอิงภายนอก เช่น เลขใบซ่อม, ชื่อโครงการ'),
            'data_json' => $this->json()->comment('เก็บข้อมูลยืดหยุ่นเพิ่มเติม (เช่น ชื่อคนไข้, เลข Asset Tag)'),
            'status' => "ENUM('PENDING', 'IN-TRANSIT', 'COMPLETED', 'CANCELLED') DEFAULT 'PENDING' COMMENT 'สถานะใบสั่ง: รอคลังหลัก, อยู่ระหว่างส่ง, รับเข้าคลังย่อยแล้ว, ยกเลิก'",
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer()->comment('ID ผู้ใช้งานที่สร้างเอกสาร'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
         $this->dropTable('{{%stock_order}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260211_051719_create_inventory_system_stock_order_tables cannot be reverted.\n";

        return false;
    }
    */
}
