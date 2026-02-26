<?php

use yii\db\Migration;

/**
 * เพิ่มประเภทเอกสาร ADJUST สำหรับการปรับยอด stock
 */
class m260219_100000_add_adjust_order_type extends Migration
{
    public function safeUp()
    {
        $tableName = $this->db->getSchema()->getRawTableName('{{%stock_order}}');
        $this->execute("ALTER TABLE {$tableName} MODIFY COLUMN `order_type` ENUM('IN', 'OUT', 'TRANSFER', 'ADJUST') NOT NULL COMMENT 'ทิศทาง: รับ, จ่าย, โอน, ปรับยอด'");
    }

    public function safeDown()
    {
        $tableName = $this->db->getSchema()->getRawTableName('{{%stock_order}}');
        $this->execute("ALTER TABLE {$tableName} MODIFY COLUMN `order_type` ENUM('IN', 'OUT', 'TRANSFER') NOT NULL COMMENT 'ทิศทาง: รับ, จ่าย, โอน'");
    }
}
