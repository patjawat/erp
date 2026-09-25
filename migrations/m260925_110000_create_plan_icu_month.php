<?php

use yii\db\Migration;

/**
 * ICU 3 มิติ (แบบฟอร์มเขต เมนู 2.3) — ยอดปรับมือรายเดือน
 * ระบบคำนวณเงินบำรุงคงเหลือ/ภาระผูกพันสิ้นเดือนเองจากข้อมูล ERP; ตารางนี้เก็บเฉพาะเดือนที่ผู้ใช้กรอกทับ
 * (เช่น ยอดจากงบทดลอง/รายงานส่งเขตที่รวมรายการนอกระบบ) — ค่า null = ใช้ค่าคำนวณ
 */
class m260925_110000_create_plan_icu_month extends Migration
{
    public function safeUp()
    {
        $tbl = $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        $this->createTable('{{%plan_icu_month}}', [
            'id' => $this->primaryKey(),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'month_no' => $this->tinyInteger()->notNull()->comment('งวดเดือน 1=ต.ค. ... 12=ก.ย.'),
            'cash_balance' => $this->decimal(15, 2)->null()->comment('เงินบำรุงคงเหลือสิ้นเดือน (กรอกทับ)'),
            'commitment' => $this->decimal(15, 2)->null()->comment('ภาระผูกพันสิ้นเดือน (กรอกทับ)'),
            'note' => $this->string(255)->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tbl);
        $this->createIndex('uq-plan_icu_month-year-month', '{{%plan_icu_month}}', ['fiscal_year', 'month_no'], true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%plan_icu_month}}');
    }
}
