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

        $this->db->createCommand('SET FOREIGN_KEY_CHECKS = 0')->execute();
        $this->dropTableIfExists('{{%stock_monthly_report}}');
        $this->dropTableIfExists('{{%stock_balance}}');
        $this->dropTableIfExists('{{%stock_detail}}');
        $this->dropTableIfExists('{{%stock_order}}');
        $this->dropTableIfExists('{{%stock_item}}');
        $this->db->createCommand('SET FOREIGN_KEY_CHECKS = 1')->execute();

        // 1. ตารางรายการพัสดุ (Stock Item)
        $this->createTable('{{%stock_item}}', [
            'id' => $this->primaryKey(),
            'item_code' => $this->string(50)->notNull()->unique()->comment('รหัสพัสดุ'),
            'item_name' => $this->string(255)->notNull()->comment('ชื่อพัสดุ'),
            'category_id' => $this->string(255)->comment('รหัสประเภทวัสดุ เชื่อม categorise.code ชื่อ name=asset_type'),
            'min_qty' => $this->decimal(10, 2)->defaultValue(0)->comment('จุดสั่งซื้อขั้นต่ำ'),
            'max_qty' => $this->decimal(10, 2)->defaultValue(0)->comment('จุดสั่งซื้อขั้นสูง'),
            'is_asset' => $this->boolean()->defaultValue(false)->comment('เป็นครุภัณฑ์หรือไม่ (0=วัสดุ, 1=ครุภัณฑ์)'),
            'is_innovation' => $this->boolean()->defaultValue(false)->comment('เป็นบัญชีนวัตกรรมหรือไม่ (0=ไม่เป็น, 1=เป็น)'),
            'is_active' => $this->boolean()->defaultValue(true)->comment('สถานะ (0=ปิด, 1=เปิด)'),
            'ref' => $this->string(255)->comment('ฟิลด์อ้างอิงภายนอก'),
            'data_json' => $this->json()->comment('เก็บข้อมูลเสริมอื่นๆ รูปแบบ JSON'),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $tableOptions);

        // 2. ตารางหัวเอกสาร รับ-จ่าย-โอน (Stock Order)
        $this->createTable('{{%stock_order}}', [
            'id' => $this->primaryKey(),
            'order_no' => $this->string(100)->notNull()->unique()->comment('เลขที่เอกสาร'),
            'order_type' => "ENUM('IN', 'OUT', 'TRANSFER') NOT NULL COMMENT 'ทิศทาง: รับ, จ่าย, โอน'",
            'source_type' => $this->string(50)->comment('ประเภทการรับ/จ่าย เช่น NORMAL, PO, INITIAL, FREE_GIFT, DONATE, REQUEST'),
            'order_date' => $this->dateTime()->notNull()->comment('วันที่ทำรายการ'),
            'main_warehouse_id' => $this->integer()->comment('คลังสินค้าต้นทาง/คลังหลัก/คลังจ่าย'),
            'sub_warehouse_id' => $this->integer()->comment('คลังปลายทาง/คลังรับ'),
            'contact_id' => $this->integer()->comment('ID ผู้ขาย หรือ ผู้เบิก/แผนก'),
            'status' => "ENUM('DRAFT', 'CONFIRMED', 'CANCELLED') DEFAULT 'DRAFT' COMMENT 'สถานะเอกสาร'",
            'ref' => $this->string(255),
            'data_json' => $this->json(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $tableOptions);

        // 3. ตารางรายละเอียด (Stock Detail)
        $this->createTable('{{%stock_detail}}', [
            'id' => $this->primaryKey(),
            'stock_order_id' => $this->integer()->notNull(),
            'item_code' => $this->string(50)->notNull()->comment('เชื่อม stock_item.item_code'),
            'qty' => $this->decimal(10, 2)->notNull(),
            'remain_qty' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ยอดคงเหลือ Lot สำหรับ FIFO'),
            'unit_price' => $this->decimal(15, 2)->comment('ราคาทุนต่อหน่วย'),
            'lot_number' => $this->string(100)->notNull(),
            'expiry_date' => $this->date()->comment('วันหมดอายุ'),
            'ref' => $this->string(255),
            'data_json' => $this->json(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $tableOptions);

        // 4. ตารางยอดคงเหลือปัจจุบัน (Stock Balance)
        $this->createTable('{{%stock_balance}}', [
            'id' => $this->primaryKey(),
            'item_code' => $this->string(50)->notNull()->comment('เชื่อม stock_item.item_code'),
            'warehouse_id' => $this->integer()->notNull()->comment('คลังหลัก/คลังย่อย จาก warehouses.id'),
            'lot_number' => $this->string(100)->notNull(),
            'balance_qty' => $this->decimal(10, 2)->defaultValue(0)->comment('จำนวนคงเหลือที่ใช้ได้จริง'),
            'ref' => $this->string(255),
            'data_json' => $this->json(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $tableOptions);

        // 5. รายงานประจำเดือน
        $this->createTable('{{%stock_monthly_report}}', [
            'id' => $this->primaryKey(),
            'report_year' => $this->integer()->notNull(),
            'report_month' => $this->smallInteger()->notNull(),
            'warehouse_id' => $this->integer()->notNull(),
            'item_code' => $this->string(50)->notNull(),
            'unit_name' => $this->string(100),
            'opening_qty' => $this->decimal(12, 2)->defaultValue(0),
            'opening_value' => $this->decimal(15, 2)->defaultValue(0),
            'in_qty' => $this->decimal(12, 2)->defaultValue(0),
            'in_value' => $this->decimal(15, 2)->defaultValue(0),
            'out_sub_qty' => $this->decimal(12, 2)->defaultValue(0),
            'out_hosp_qty' => $this->decimal(12, 2)->defaultValue(0),
            'total_out_qty' => $this->decimal(12, 2)->defaultValue(0),
            'total_out_value' => $this->decimal(15, 2)->defaultValue(0),
            'closing_qty' => $this->decimal(12, 2)->defaultValue(0),
            'closing_value' => $this->decimal(15, 2)->defaultValue(0),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
        ], $tableOptions);

        // --- สร้าง Index ---
        $this->createIndex('idx-report-period', '{{%stock_monthly_report}}', ['report_year', 'report_month', 'warehouse_id']);
        $this->createIndex('idx-report-item-code', '{{%stock_monthly_report}}', 'item_code'); // แก้จาก item_id
        $this->createIndex('idx-detail-item-code', '{{%stock_detail}}', 'item_code');
        $this->createIndex('idx-balance-item-code', '{{%stock_balance}}', 'item_code');

        // --- สร้าง Foreign Keys ---

        // Detail -> Order
        $this->addForeignKey('fk_stock_detail_order', '{{%stock_detail}}', 'stock_order_id', '{{%stock_order}}', 'id', 'CASCADE');

        // Detail -> Item (เชื่อมด้วย item_code)
        $this->addForeignKey('fk_stock_detail_item_code', '{{%stock_detail}}', 'item_code', '{{%stock_item}}', 'item_code', 'RESTRICT', 'CASCADE');

        // Balance -> Item (เชื่อมด้วย item_code)
        $this->addForeignKey('fk_stock_balance_item_code', '{{%stock_balance}}', 'item_code', '{{%stock_item}}', 'item_code', 'RESTRICT', 'CASCADE');

        // Report -> Item (เชื่อมด้วย item_code)
        $this->addForeignKey('fk_report_item_code', '{{%stock_monthly_report}}', 'item_code', '{{%stock_item}}', 'item_code', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
        // ลบตามลำดับย้อนกลับ
        $this->dropTable('{{%stock_monthly_report}}');
        $this->dropTable('{{%stock_balance}}');
        $this->dropTable('{{%stock_detail}}');
        $this->dropTable('{{%stock_order}}');
        $this->dropTable('{{%stock_item}}');
    }

    protected function dropTableIfExists($table)
    {
        if ($this->db->getTableSchema($table, true) !== null) {
            $this->dropTable($table);
        }
    }
}
