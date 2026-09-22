<?php

use yii\db\Migration;

/**
 * โปรแกรมพิมพ์เช็ค — เฟส 0 (ชั้นข้อมูล)
 *
 * 1) finance_cheque_template : แม่แบบพิกัดการพิมพ์ทับฟอร์มเช็ค แยกตามธนาคาร
 *    (พิกัดเก็บเป็น % แบบเดียวกับโมดูล pdfTemplate เพื่อให้ FPDI overlay ใช้ต่อได้)
 * 2) finance_cheque         : ทะเบียนคุมเช็ค (เลขที่/เล่ม/ผู้รับ/ยอด/สถานะ ออก-ยกเลิก-ขึ้นเงิน)
 * 3) เพิ่ม cash_account_id ให้ finance_payable_payment : ผูกบัญชีธนาคารผู้จ่าย (แทนพิมพ์มือ)
 */
class m260922_140000_create_finance_cheque extends Migration
{
    public function safeUp()
    {
        $opts = $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        // 1) แม่แบบเช็ครายธนาคาร
        $this->createTable('{{%finance_cheque_template}}', [
            'id' => $this->primaryKey(),
            'bank_code' => $this->string(20)->null()->comment('รหัสธนาคาร (อ้างอิง FinanceCashAccount)'),
            'bank_name' => $this->string(100)->notNull(),
            'name' => $this->string(150)->notNull()->comment('ชื่อแม่แบบ เช่น เช็คกรุงไทย แบบ ก'),
            'page_width_mm' => $this->decimal(6, 2)->notNull()->defaultValue(178)->comment('ความกว้างแผ่นเช็ค (มม.)'),
            'page_height_mm' => $this->decimal(6, 2)->notNull()->defaultValue(82)->comment('ความสูงแผ่นเช็ค (มม.)'),
            'layout_json' => $this->text()->null()->comment('พิกัดฟิลด์ [{key,x,y,font_size,align,bold,enabled}] หน่วย %'),
            'background_path' => $this->string(255)->null()->comment('ไฟล์สแกนเช็คสำหรับพรีวิว/จัดตำแหน่ง (ไม่ใช้ตอนพิมพ์จริง)'),
            'calibrate_offset_x' => $this->decimal(6, 2)->notNull()->defaultValue(0)->comment('ชดเชยแนวนอน (มม.) ต่อเครื่องพิมพ์'),
            'calibrate_offset_y' => $this->decimal(6, 2)->notNull()->defaultValue(0)->comment('ชดเชยแนวตั้ง (มม.)'),
            'is_active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
            'note' => $this->string(255)->null(),
            'created_at' => $this->integer()->null(),
            'created_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $opts);
        $this->createIndex('idx-fct-bank', '{{%finance_cheque_template}}', 'bank_code');

        // 2) ทะเบียนคุมเช็ค
        $this->createTable('{{%finance_cheque}}', [
            'id' => $this->primaryKey(),
            'payment_id' => $this->integer()->null()->comment('รอบจ่ายเจ้าหนี้ที่ผูก (1 เช็ค = 1 รอบจ่าย)'),
            'cash_account_id' => $this->integer()->null()->comment('บัญชีธนาคารผู้จ่าย'),
            'template_id' => $this->integer()->null()->comment('แม่แบบที่ใช้พิมพ์'),
            'cheque_book_no' => $this->string(50)->null()->comment('เล่มเช็ค'),
            'cheque_no' => $this->string(50)->notNull()->comment('เลขที่เช็ค'),
            'cheque_date' => $this->date()->null()->comment('วันที่สั่งจ่าย'),
            'payee_name' => $this->string(255)->notNull()->comment('จ่ายให้ (ชื่อผู้รับ)'),
            'amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'amount_text' => $this->string(255)->null()->comment('จำนวนเงินตัวอักษร (snapshot)'),
            'is_ac_payee' => $this->tinyInteger(1)->notNull()->defaultValue(1)->comment('ขีดคร่อม A/C PAYEE ONLY'),
            'status' => $this->string(20)->notNull()->defaultValue('draft')->comment('draft/printed/handed/cleared/bounced/void'),
            'printed_at' => $this->integer()->null(),
            'printed_by' => $this->integer()->null(),
            'void_reason' => $this->string(255)->null(),
            'voided_at' => $this->integer()->null(),
            'voided_by' => $this->integer()->null(),
            'note' => $this->string(255)->null(),
            'created_at' => $this->integer()->null(),
            'created_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $opts);
        $this->createIndex('idx-fc-payment', '{{%finance_cheque}}', 'payment_id');
        $this->createIndex('idx-fc-account', '{{%finance_cheque}}', 'cash_account_id');
        $this->createIndex('idx-fc-status', '{{%finance_cheque}}', 'status');
        // กันเลขเช็คซ้ำภายในบัญชีจ่ายเดียวกัน
        $this->createIndex('idx-fc-account-no', '{{%finance_cheque}}', ['cash_account_id', 'cheque_no'], true);

        // FK แบบ soft: ผูกเมื่อมีตารางปลายทาง (บาง deploy อาจยังไม่มี finance_cash_account)
        $this->addForeignKey('fk-fc-payment', '{{%finance_cheque}}', 'payment_id', '{{%finance_payable_payment}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk-fc-template', '{{%finance_cheque}}', 'template_id', '{{%finance_cheque_template}}', 'id', 'SET NULL', 'CASCADE');

        // 3) บัญชีธนาคารผู้จ่าย บนรอบจ่าย
        if (!$this->columnExists('{{%finance_payable_payment}}', 'cash_account_id')) {
            $this->addColumn('{{%finance_payable_payment}}', 'cash_account_id', $this->integer()->null()->after('vendor_id'));
            $this->createIndex('idx-fpp-account', '{{%finance_payable_payment}}', 'cash_account_id');
        }
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-fc-template', '{{%finance_cheque}}');
        $this->dropForeignKey('fk-fc-payment', '{{%finance_cheque}}');
        $this->dropTable('{{%finance_cheque}}');
        $this->dropTable('{{%finance_cheque_template}}');

        if ($this->columnExists('{{%finance_payable_payment}}', 'cash_account_id')) {
            $this->dropColumn('{{%finance_payable_payment}}', 'cash_account_id');
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        $raw = $this->db->schema->getRawTableName($table);
        return $this->db->getTableSchema($raw, true) !== null
            && in_array($column, $this->db->getTableSchema($raw)->columnNames, true);
    }
}
