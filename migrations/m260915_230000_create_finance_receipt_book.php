<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ระบบรับ-จ่ายเงินบำรุง (mophcash) — ทะเบียนคุมใบเสร็จรับเงิน (เฟส A)
 * งาน C690915  •  /finance/receipt
 *
 * คุมเล่มใบเสร็จ: รับเล่มเข้า → เบิกจ่ายให้เจ้าหน้าที่ → นำเลขไปใช้บันทึกรายรับ
 * ยอด "ใช้แล้ว/คงเหลือ/เลขซ้ำ-ข้าม" ดึงจาก finance_cash_txn (IN) doc_no = "เล่ม/เลข"
 * ไม่เก็บซ้ำ (source of truth = รายการรับที่บันทึกไว้)
 */
final class m260915_230000_create_finance_receipt_book extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%finance_receipt_book}}', [
            'id' => $this->primaryKey(),
            'book_no' => $this->string(32)->notNull()->comment('เลขที่เล่ม (เช่น 606, 00704)'),
            'number_from' => $this->integer()->notNull()->comment('เลขที่เริ่ม'),
            'number_to' => $this->integer()->notNull()->comment('เลขที่สิ้นสุด'),
            'receipt_type' => $this->string(120)->null()->comment('ประเภทใบเสร็จ'),
            'received_date' => $this->date()->null()->comment('วันที่รับเล่มเข้า'),
            'issued_to_emp_id' => $this->integer()->null()->comment('เบิกให้ (employees.id)'),
            'issued_date' => $this->date()->null()->comment('วันที่เบิก'),
            'status' => $this->string(16)->notNull()->defaultValue('received')->comment('received | issued | completed | cancelled'),
            'note' => $this->string(255)->null(),
            'ref' => $this->string(64)->notNull(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('uq-fin_receipt_book-ref', '{{%finance_receipt_book}}', 'ref', true);
        $this->createIndex('idx-fin_receipt_book-no', '{{%finance_receipt_book}}', 'book_no');
        $this->createIndex('idx-fin_receipt_book-emp', '{{%finance_receipt_book}}', 'issued_to_emp_id');
        $this->createIndex('idx-fin_receipt_book-status', '{{%finance_receipt_book}}', 'status');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%finance_receipt_book}}');
    }
}
