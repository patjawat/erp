<?php

use yii\db\Migration;

class m251104_055049_change_qty_to_decimal_in_stock_event extends Migration
{
    /**
     * {@inheritdoc}
     */
   public function safeUp()
    {
        // ✅ แก้ไขชนิดคอลัมน์ qty เป็น DECIMAL(20,10)
        $this->alterColumn('{{%stock_events}}', 'qty', $this->decimal(20,10)->comment('จำนวนสินค้าที่เคลื่อนย้าย'));
    }

    public function safeDown()
    {
        return true;
        // 🔙 ย้อนกลับเป็น float ถ้าต้องการ rollback
        // $this->alterColumn('{{%stock_events}}', 'qty', $this->float()->comment('จำนวนสินค้าที่เคลื่อนย้าย'));
    }
}
