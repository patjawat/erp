<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%inventory_v2_tables}}`.
 */
class m260214_174016_create_inventory_v2_tables_table extends Migration
{
public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // 2. ตารางหัวเอกสาร รับ-จ่าย-โอน (Stock Order)
        $this->createTable('{{%stock_order}}', [
            'id' => $this->primaryKey(),
            'order_no' => $this->string(100)->notNull()->unique()->comment('เลขที่เอกสาร (RCV, ISS, TRN)'),
            'order_type' => "ENUM('IN', 'OUT', 'TRANSFER') NOT NULL COMMENT 'ประเภทธุรกรรม'",
            'order_date' => $this->dateTime()->notNull()->comment('วันที่ทำรายการ'),
            'warehouse_id' => $this->integer()->comment('คลังสินค้าต้นทาง/คลังหลัก'),
            'to_warehouse_id' => $this->integer()->comment('คลังสินค้าปลายทาง (กรณีโอน)'),
            'contact_id' => $this->integer()->comment('ID ผู้ขาย หรือ ผู้เบิก/แผนก'),
            'status' => $this->string(50)->defaultValue('DRAFT')->comment('สถานะเอกสาร'),
            
            'ref' => $this->string(255)->comment('อ้างอิงเลขที่ใบ PO หรือ PR'),
            'data_json' => $this->json(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $tableOptions);

        // 3. ตารางรายการรายละเอียด (Stock Detail)
        $this->createTable('{{%stock_detail}}', [
            'id' => $this->primaryKey(),
            'stock_order_id' => $this->integer()->notNull()->comment('เชื่อมกับ stock_order'),
            'item_id' => $this->integer()->notNull()->comment('เชื่อมกับ stock_item'),
            'qty' => $this->decimal(10, 2)->notNull()->comment('จำนวนที่ทำรายการ'),
            'unit_price' => $this->decimal(15, 2)->comment('ราคาทุนต่อหน่วย'),
            'lot_number' => $this->string(100)->comment('เลขล็อตสินค้า'),
            'expiry_date' => $this->date()->comment('วันหมดอายุ'),
            
            'ref' => $this->string(255)->comment('อ้างอิงอื่นๆ รายบรรทัด'),
            'data_json' => $this->json(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $tableOptions);

        // 4. ตารางยอดคงเหลือปัจจุบัน (Stock Balance)
        $this->createTable('{{%stock_balance}}', [
            'id' => $this->primaryKey(),
            'item_id' => $this->integer()->notNull(),
            'warehouse_id' => $this->integer()->notNull(),
            'lot_number' => $this->string(100),
            'balance_qty' => $this->decimal(10, 2)->defaultValue(0)->comment('จำนวนคงเหลือที่ใช้ได้จริง'),
            'ref' => $this->string(255),
            'data_json' => $this->json(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $tableOptions);

        // เพิ่ม Index และ Foreign Keys
        $this->addForeignKey('fk_stock_detail_order', '{{%stock_detail}}', 'stock_order_id', '{{%stock_order}}', 'id', 'CASCADE');
        // $this->addForeignKey('fk_stock_detail_item', '{{%stock_detail}}', 'item_id', '{{%stock_item}}', 'id', 'RESTRICT');
    }

    public function safeDown()
    {
        $this->dropTable('{{%stock_balance}}');
        $this->dropTable('{{%stock_detail}}');
        $this->dropTable('{{%stock_order}}');
    }
}
