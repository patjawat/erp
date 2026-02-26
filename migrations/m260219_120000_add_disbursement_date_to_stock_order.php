<?php

use yii\db\Migration;

/**
 * เพิ่มคอลัมน์วันที่จ่าย (disbursement_date) ใน stock_order
 * เก็บเป็น Unix timestamp — ใช้เมื่อ status = CONFIRMED
 */
class m260219_120000_add_disbursement_date_to_stock_order extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%stock_order}}', 'disbursement_date', $this->integer()->null()->comment('วันที่จ่าย (Unix timestamp)'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%stock_order}}', 'disbursement_date');
    }
}
