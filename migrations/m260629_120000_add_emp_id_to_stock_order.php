<?php

use yii\db\Migration;

/**
 * เพิ่มคอลัมน์ emp_id (ผู้ขอเบิก) ใน stock_order
 * เก็บ employees.id ของผู้ขอเบิก — soft FK (ไม่มี DB constraint)
 * มี index เพื่อ filter/lookup เร็ว
 */
class m260629_120000_add_emp_id_to_stock_order extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            '{{%stock_order}}',
            'emp_id',
            $this->integer()->null()->comment('ผู้ขอเบิก (employees.id) — soft FK')
        );
        $this->createIndex('idx_stock_order_emp_id', '{{%stock_order}}', 'emp_id');
    }

    public function safeDown()
    {
        $this->dropIndex('idx_stock_order_emp_id', '{{%stock_order}}');
        $this->dropColumn('{{%stock_order}}', 'emp_id');
    }
}
