<?php

use yii\db\Migration;

/**
 * ลูกหนี้ค่ารักษา (Accounts Receivable) + เงินมัดจำผู้ป่วย
 * (ทะเบียนคุมหมวด 3: 3.1 ลูกหนี้แยกสิทธิ / 3.2 ค้างรับ / 3.3 มัดจำ)
 * แหล่งข้อมูลหลัก: นำเข้าจาก HIS/ระบบเคลม
 *
 *   finance_ar_fund          : สิทธิ/กองทุน (UC/สปส/ข้าราชการ/พรบ/ชำระเอง ...)
 *   finance_ar_invoice       : ลูกหนี้รายก้อน (ตั้งเบิกตามสิทธิ+งวด, อาจรายผู้ป่วยหรือยอดรวม)
 *   finance_ar_settlement    : การรับชำระ/ตัดปรับ/ตัดหนี้สูญ
 *   finance_ar_import_batch  : ชุดนำเข้าจาก HIS/เคลม
 *   finance_patient_deposit  : เงินมัดจำ/เงินรับฝากผู้ป่วย
 */
class m260923_100000_create_finance_ar extends Migration
{
    public function safeUp()
    {
        $t = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%finance_ar_fund}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(32)->notNull()->comment('รหัสสิทธิ'),
            'name' => $this->string(255)->notNull(),
            'settle_days' => $this->integer()->null()->comment('รอบเรียกเก็บ/ระยะรับเงินคาดหมาย (วัน)'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->tinyInteger()->notNull()->defaultValue(1),
            'note' => $this->text()->null(),
        ], $t);

        $this->createTable('{{%finance_ar_import_batch}}', [
            'id' => $this->primaryKey(),
            'ar_fund_id' => $this->integer()->null(),
            'fiscal_year' => $this->integer()->null(),
            'period_month' => $this->integer()->null()->comment('เดือนของงวด 1-12'),
            'source_label' => $this->string(255)->null()->comment('แหล่ง เช่น HIS/e-Claim/NHSO'),
            'file_name' => $this->string(255)->null(),
            'row_count' => $this->integer()->notNull()->defaultValue(0),
            'total_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'note' => $this->text()->null(),
            'created_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
        ], $t);

        $this->createTable('{{%finance_ar_invoice}}', [
            'id' => $this->primaryKey(),
            'ar_fund_id' => $this->integer()->notNull(),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบ พ.ศ.'),
            'period_month' => $this->integer()->null()->comment('เดือนของงวด 1-12'),
            'service_date' => $this->date()->null()->comment('วันที่ให้บริการ/ตั้งลูกหนี้'),
            'doc_no' => $this->string(64)->null()->comment('เลขอ้างอิง/เลขเคลม/REP'),
            'hn' => $this->string(32)->null(),
            'an' => $this->string(32)->null(),
            'patient_name' => $this->string(255)->null(),
            'billed_amount' => $this->decimal(15, 2)->notNull()->comment('ยอดตั้งเบิก'),
            'status' => $this->string(16)->notNull()->defaultValue('billed')->comment('billed|submitted|partial|paid|written_off'),
            'note' => $this->string(500)->null(),
            'import_batch_id' => $this->integer()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $t);

        $this->createTable('{{%finance_ar_settlement}}', [
            'id' => $this->primaryKey(),
            'ar_invoice_id' => $this->integer()->notNull(),
            'settle_date' => $this->date()->notNull(),
            'kind' => $this->string(16)->notNull()->defaultValue('receipt')->comment('receipt|adjust|writeoff'),
            'amount' => $this->decimal(15, 2)->notNull(),
            'doc_no' => $this->string(64)->null(),
            'note' => $this->string(500)->null(),
            'created_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
        ], $t);

        $this->createTable('{{%finance_patient_deposit}}', [
            'id' => $this->primaryKey(),
            'receipt_no' => $this->string(64)->null()->comment('เลขที่ใบรับเงินมัดจำ'),
            'deposit_date' => $this->date()->notNull(),
            'fiscal_year' => $this->integer()->null(),
            'hn' => $this->string(32)->null(),
            'an' => $this->string(32)->null(),
            'patient_name' => $this->string(255)->null(),
            'amount' => $this->decimal(15, 2)->notNull()->comment('ยอดรับฝาก'),
            'used_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('หักชำระค่ารักษาแล้ว'),
            'refunded_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('คืนแล้ว'),
            'status' => $this->string(16)->notNull()->defaultValue('held')->comment('held|partial|closed'),
            'note' => $this->string(500)->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $t);

        // indexes
        $this->createIndex('idx-ar_invoice-fund', '{{%finance_ar_invoice}}', ['ar_fund_id', 'fiscal_year']);
        $this->createIndex('idx-ar_invoice-status', '{{%finance_ar_invoice}}', 'status');
        $this->createIndex('idx-ar_invoice-service', '{{%finance_ar_invoice}}', 'service_date');
        $this->createIndex('idx-ar_settle-invoice', '{{%finance_ar_settlement}}', 'ar_invoice_id');
        $this->createIndex('idx-pdeposit-date', '{{%finance_patient_deposit}}', 'deposit_date');

        // foreign keys
        $this->addForeignKey('fk-ar_invoice-fund', '{{%finance_ar_invoice}}', 'ar_fund_id', '{{%finance_ar_fund}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk-ar_settle-invoice', '{{%finance_ar_settlement}}', 'ar_invoice_id', '{{%finance_ar_invoice}}', 'id', 'CASCADE', 'CASCADE');

        // seed สิทธิมาตรฐาน (ปรับได้ภายหลัง)
        $now = date('Y-m-d H:i:s');
        $this->batchInsert('{{%finance_ar_fund}}', ['code', 'name', 'settle_days', 'sort_order', 'is_active'], [
            ['UC', 'สิทธิหลักประกันสุขภาพ (บัตรทอง)', 90, 1, 1],
            ['OFC', 'ข้าราชการ/รัฐวิสาหกิจ (เบิกจ่ายตรง)', 60, 2, 1],
            ['SSS', 'ประกันสังคม', 90, 3, 1],
            ['LGO', 'พนักงานส่วนท้องถิ่น (อปท.)', 60, 4, 1],
            ['ACC', 'พ.ร.บ. ผู้ประสบภัยจากรถ', 120, 5, 1],
            ['SELF', 'ชำระเงินเอง', 0, 6, 1],
            ['OTHER', 'สิทธิอื่น ๆ', null, 7, 1],
        ]);
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-ar_settle-invoice', '{{%finance_ar_settlement}}');
        $this->dropForeignKey('fk-ar_invoice-fund', '{{%finance_ar_invoice}}');
        $this->dropTable('{{%finance_patient_deposit}}');
        $this->dropTable('{{%finance_ar_settlement}}');
        $this->dropTable('{{%finance_ar_invoice}}');
        $this->dropTable('{{%finance_ar_import_batch}}');
        $this->dropTable('{{%finance_ar_fund}}');
    }
}
