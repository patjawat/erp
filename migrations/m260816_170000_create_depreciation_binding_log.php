<?php

use yii\db\Migration;

/**
 * ประวัติการผูกเกณฑ์ค่าเสื่อมเข้ากับลำดับชั้นทรัพย์สิน
 *
 * การผูกเกณฑ์ที่ระดับประเภท/หมวด/รายการ กระทบทรัพย์สินได้ครั้งละหลักพันชิ้น
 * แต่เดิมไม่มีร่องรอยว่าใครเปลี่ยนอะไรเมื่อไร (ต่างจากการเปลี่ยนเกณฑ์รายชิ้น
 * ที่มีตาราง asset_depreciation_changes อยู่แล้ว)
 */
class m260816_170000_create_depreciation_binding_log extends Migration
{
    public function safeUp()
    {
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%depreciation_binding_logs}}', [
            'id' => $this->primaryKey(),
            'level' => $this->string(30)->notNull()->comment('asset_type | asset_category | asset_item'),
            'code' => $this->string(255)->comment('รหัสของระดับนั้น (ผูกที่ระดับรหัส ไม่ใช่ระดับแถว)'),
            'title' => $this->string(255)->comment('ชื่อ ณ เวลาที่บันทึก'),
            'old_profile_id' => $this->integer()->comment('เกณฑ์เดิม (null = ยังไม่ได้ผูก)'),
            'new_profile_id' => $this->integer()->comment('เกณฑ์ใหม่ (null = ล้างการผูก)'),
            'rows_written' => $this->integer()->defaultValue(0)->comment('จำนวนแถว categorise ที่เขียนจริง'),
            'source' => $this->string(20)->comment('single | bulk | seed'),
            'created_by' => $this->integer(),
            'created_at' => $this->dateTime(),
        ], $tableOptions);

        $this->createIndex('idx_dp_binding_log_level_code', '{{%depreciation_binding_logs}}', ['level', 'code']);
        $this->createIndex('idx_dp_binding_log_created', '{{%depreciation_binding_logs}}', 'created_at');
    }

    public function safeDown()
    {
        $this->dropTable('{{%depreciation_binding_logs}}');
    }
}
