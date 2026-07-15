<?php

use yii\db\Migration;

/**
 * งวดบัญชี — รองรับการคำนวณ/รายงานรายเดือน รายไตรมาส และปีงบประมาณ
 *
 * ข้อกำหนด:
 *  - ปีงบประมาณไทยเริ่ม 1 ต.ค. สิ้นสุด 30 ก.ย.
 *  - ใช้วันที่จริง (start_date/end_date) เป็นหลัก ไม่ผูกกับเลขเดือนอย่างเดียว
 *  - งวดที่ posted/locked ห้ามคำนวณทับ (บังคับใช้ระดับ service)
 */
class m260712_100002_create_accounting_periods_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%accounting_periods}}', [
            'id' => $this->primaryKey(),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'period_no' => $this->integer()->notNull()->comment('ลำดับงวดในปี (เดือน 1-12, ไตรมาส 1-4, ปี 1)'),
            'period_type' => $this->string(20)->notNull()->comment('ชนิดงวด: month|quarter|fiscal_year|adjustment'),
            'name' => $this->string(100)->null()->comment('ชื่องวด เช่น "ต.ค. 2568"'),
            'start_date' => $this->date()->notNull()->comment('วันเริ่มงวด'),
            'end_date' => $this->date()->notNull()->comment('วันสิ้นงวด'),
            'status' => $this->string(20)->notNull()->defaultValue('open')->comment('สถานะ: open|calculated|posted|locked'),
            'closed_at' => $this->dateTime()->null()->comment('วันเวลาที่ปิดงวด'),
            'closed_by' => $this->integer()->null()->comment('ผู้ปิดงวด'),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
        ], $tableOptions);

        $this->createIndex(
            'uq_accounting_periods_fy_type_no',
            '{{%accounting_periods}}',
            ['fiscal_year', 'period_type', 'period_no'],
            true
        );
        $this->createIndex('idx_accounting_periods_status', '{{%accounting_periods}}', 'status');
        $this->createIndex('idx_accounting_periods_dates', '{{%accounting_periods}}', ['start_date', 'end_date']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%accounting_periods}}');
    }
}
