<?php

use yii\db\Migration;

/**
 * เพิ่ม index บน stock_order.ref และ stock_detail.ref
 * เพื่อใช้กับการ sync จาก V1 (เก็บ V1 ID ในฟิลด์ ref) — ทำให้ idempotent
 */
class m260523_160000_add_v1_ref_index_to_v2_tables extends Migration
{
    public function safeUp()
    {
        $this->createIndex('idx_stock_order_ref',  '{{%stock_order}}',  'ref');
        $this->createIndex('idx_stock_detail_ref', '{{%stock_detail}}', 'ref');
    }

    public function safeDown()
    {
        $this->dropIndex('idx_stock_detail_ref', '{{%stock_detail}}');
        $this->dropIndex('idx_stock_order_ref',  '{{%stock_order}}');
    }
}
