<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ระบบรับ-จ่ายเงินบำรุง (mophcash) — โอนเงินข้ามบัญชี เฟส 4
 * งาน C690915  •  /finance/account/transfer
 *
 * บันทึกการโอนเงินระหว่างบัญชี เป็น movement สำหรับทะเบียนคุม (audit trail)
 * ไม่ auto-ปรับยอดคงเหลือรายปี (โมเดลคงยอดกรอกเอง)
 */
final class m260915_210000_create_finance_cash_transfer extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%finance_cash_transfer}}', [
            'id' => $this->primaryKey(),
            'from_account_id' => $this->integer()->notNull()->comment('บัญชีต้นทาง'),
            'to_account_id' => $this->integer()->notNull()->comment('บัญชีปลายทาง'),
            'amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'doc_ref' => $this->string(64)->null()->comment('เลขที่เอกสารอ้างอิง'),
            'transfer_date' => $this->date()->notNull()->comment('วันที่โอน'),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'note' => $this->string(255)->null(),
            'ref' => $this->string(64)->notNull(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('uq-fin_cash_transfer-ref', '{{%finance_cash_transfer}}', 'ref', true);
        $this->createIndex('idx-fin_cash_transfer-date', '{{%finance_cash_transfer}}', ['transfer_date']);
        $this->createIndex('idx-fin_cash_transfer-from', '{{%finance_cash_transfer}}', 'from_account_id');
        $this->createIndex('idx-fin_cash_transfer-to', '{{%finance_cash_transfer}}', 'to_account_id');
        $this->addForeignKey('fk-fin_cash_transfer-from', '{{%finance_cash_transfer}}', 'from_account_id', '{{%finance_cash_account}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-fin_cash_transfer-to', '{{%finance_cash_transfer}}', 'to_account_id', '{{%finance_cash_account}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%finance_cash_transfer}}');
    }
}
