<?php

use yii\db\Migration;

class m260429_110000_create_asset_disposals_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%asset_disposals}}', [
            'id' => $this->primaryKey(),
            'disposal_no' => $this->string(255)->notNull()->comment('เลขที่ใบขอจำหน่าย'),
            'seq_no' => $this->integer()->notNull()->comment('ลำดับ'),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ'),
            'department' => $this->integer()->null()->comment('หน่วยงาน'),
            'disposal_date' => $this->date()->null()->comment('วันที่'),
            'disposal_method' => $this->string(255)->null()->comment('วิธีจำหน่าย'),
            'responsible_emp_id' => $this->integer()->null()->comment('ผู้รับผิดชอบ'),
            'summary_note' => $this->text()->null()->comment('หมายเหตุ'),
            'status' => $this->string(50)->notNull()->defaultValue('pending_approval')->comment('สถานะ'),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
        ]);

        $this->createIndex('ux_asset_disposals_disposal_no', '{{%asset_disposals}}', 'disposal_no', true);
        $this->createIndex('ux_asset_disposals_year_seq', '{{%asset_disposals}}', ['fiscal_year', 'seq_no'], true);
        $this->createIndex('idx_asset_disposals_department', '{{%asset_disposals}}', 'department');
        $this->createIndex('idx_asset_disposals_responsible', '{{%asset_disposals}}', 'responsible_emp_id');
        $this->createIndex('idx_asset_disposals_status', '{{%asset_disposals}}', 'status');


    }

    public function safeDown()
    {
        $this->dropIndex('idx_asset_disposals_status', '{{%asset_disposals}}');
        $this->dropIndex('idx_asset_disposals_responsible', '{{%asset_disposals}}');
        $this->dropIndex('ux_asset_disposals_year_seq', '{{%asset_disposals}}');
        $this->dropIndex('ux_asset_disposals_disposal_no', '{{%asset_disposals}}');
        $this->dropTable('{{%asset_disposals}}');
    }
}
