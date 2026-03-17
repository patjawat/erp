<?php

use yii\db\Migration;

/**
 * Fiscal year closing: closed years lock depreciation records.
 */
class m260318_100001_create_am_depreciation_closings_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%am_depreciation_closings}}', [
            'id' => $this->primaryKey(),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (ค.ศ.)'),
            'closed_at' => $this->dateTime()->notNull()->comment('วันเวลาปิด'),
            'closed_by' => $this->integer()->null()->comment('ผู้ปิด'),
            'remark' => $this->string(500)->null()->comment('หมายเหตุ'),
        ]);

        $this->createIndex('uq_am_depreciation_closings_year', '{{%am_depreciation_closings}}', 'fiscal_year', true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%am_depreciation_closings}}');
    }
}
