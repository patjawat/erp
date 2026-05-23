<?php

use yii\db\Migration;

/**
 * เพิ่มคอลัมน์สำหรับการ "ปรับยอด" (Adjustment Mode) ในตาราง stock_monthly_report
 * — เก็บเหตุผล ผู้ปรับ เวลา และค่า closing ก่อนปรับ
 */
class m260523_140000_add_adjustment_to_stock_monthly_report extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%stock_monthly_report}}', 'adjusted_at',
            $this->integer()->null()->comment('เวลาปรับยอดล่าสุด (unix timestamp)'));
        $this->addColumn('{{%stock_monthly_report}}', 'adjusted_by',
            $this->integer()->null()->comment('ผู้ปรับยอด'));
        $this->addColumn('{{%stock_monthly_report}}', 'adjustment_note',
            $this->text()->null()->comment('เหตุผลการปรับยอด'));
        $this->addColumn('{{%stock_monthly_report}}', 'original_closing_qty',
            $this->decimal(12, 2)->null()->comment('ยอดคงเหลือก่อนปรับ (snapshot)'));
        $this->addColumn('{{%stock_monthly_report}}', 'original_closing_value',
            $this->decimal(15, 2)->null()->comment('มูลค่าคงเหลือก่อนปรับ (snapshot)'));

        $this->createIndex('idx_smr_adjusted', '{{%stock_monthly_report}}', 'adjusted_at');
    }

    public function safeDown()
    {
        $this->dropIndex('idx_smr_adjusted', '{{%stock_monthly_report}}');
        $this->dropColumn('{{%stock_monthly_report}}', 'original_closing_value');
        $this->dropColumn('{{%stock_monthly_report}}', 'original_closing_qty');
        $this->dropColumn('{{%stock_monthly_report}}', 'adjustment_note');
        $this->dropColumn('{{%stock_monthly_report}}', 'adjusted_by');
        $this->dropColumn('{{%stock_monthly_report}}', 'adjusted_at');
    }
}
