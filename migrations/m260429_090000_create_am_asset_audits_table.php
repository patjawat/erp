<?php

use yii\db\Migration;

/**
 * Creates table `asset_audits` for annual asset inventory.
 */
class m260429_090000_create_am_asset_audits_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%asset_audits}}', [
            'id' => $this->primaryKey(),
            'audit_no' => $this->string(255)->notNull()->comment('เลขที่ตรวจนับ เช่น ตน.002/2568'),
            'seq_no' => $this->integer()->notNull()->comment('ลำดับรันต่อปี'),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ'),
            'department' => $this->integer()->null()->comment('หน่วยงานที่ตรวจนับ'),
            'emp_id' => $this->text()->null()->comment('ผู้ตรวจนับ'),
            'audit_date' => $this->date()->null()->comment('วันที่ตรวจนับ'),
            'summary_note' => $this->text()->null()->comment('หมายเหตุรวม'),
            'status' => $this->string(20)->notNull()->defaultValue('draft')->comment('สถานะ draft|active|closed'),
            'created_by' => $this->integer()->null()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->null()->comment('ผู้แก้ไข'),
            'created_at' => $this->dateTime()->null()->comment('วันเวลาสร้าง'),
            'updated_at' => $this->dateTime()->null()->comment('วันเวลาแก้ไข'),
        ]);

        $this->createIndex('idx_asset_audits_audit_no', '{{%asset_audits}}', 'audit_no', true);
        $this->createIndex('idx_asset_audits_fiscal_year', '{{%asset_audits}}', 'fiscal_year');
        $this->createIndex('idx_asset_audits_status', '{{%asset_audits}}', 'status');
        $this->createIndex('idx_asset_audits_seq_no', '{{%asset_audits}}', ['fiscal_year', 'seq_no'], true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%asset_audits}}');
    }
}
