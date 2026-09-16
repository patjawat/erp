<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ระบบรับ-จ่ายเงินบำรุง (mophcash) — บัญชีธนาคาร (Master Data) เฟส 4
 * งาน C690915  •  /finance/account
 *
 * เสริมฟิลด์ให้ finance_cash_account (ธนาคาร/สาขา/ประเภทฝาก/การรับเงิน)
 * + ตารางยอดคงเหลือรายปี finance_cash_account_balance (กรอกเองต่อปีงบ ยกไปปีถัดไป
 *   เพราะปิดงบปีแล้วตั้งยอดใหม่ให้ตรง — ไม่ใช่เดินยอดอัตโนมัติจาก txn)
 */
final class m260915_190000_finance_cash_account_master extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%finance_cash_account}}', 'bank_name', $this->string(120)->null()->after('name')->comment('ธนาคาร'));
        $this->addColumn('{{%finance_cash_account}}', 'branch', $this->string(120)->null()->after('bank_name')->comment('สาขา'));
        $this->addColumn('{{%finance_cash_account}}', 'deposit_type', $this->string(24)->null()->after('branch')->comment('ประเภทบัญชี: ออมทรัพย์/กระแสรายวัน/ฝากประจำ'));
        $this->addColumn('{{%finance_cash_account}}', 'is_promptpay', $this->tinyInteger(1)->notNull()->defaultValue(0)->after('deposit_type')->comment('บัญชีพร้อมเพย์'));
        $this->addColumn('{{%finance_cash_account}}', 'is_credit', $this->tinyInteger(1)->notNull()->defaultValue(0)->after('is_promptpay')->comment('บัญชีรับเงินบัตรเครดิต'));

        $this->createTable('{{%finance_cash_account_balance}}', [
            'id' => $this->primaryKey(),
            'account_id' => $this->integer()->notNull(),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.) ของยอด'),
            'amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ยอดคงเหลือ ณ สิ้นปีงบ (ยกไปปีถัดไป)'),
            'note' => $this->string(255)->null(),
            'ref' => $this->string(64)->notNull(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('uq-fin_cash_acc_bal-ref', '{{%finance_cash_account_balance}}', 'ref', true);
        $this->createIndex('uq-fin_cash_acc_bal-acc_year', '{{%finance_cash_account_balance}}', ['account_id', 'fiscal_year'], true);
        $this->addForeignKey('fk-fin_cash_acc_bal-acc', '{{%finance_cash_account_balance}}', 'account_id', '{{%finance_cash_account}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%finance_cash_account_balance}}');
        $this->dropColumn('{{%finance_cash_account}}', 'is_credit');
        $this->dropColumn('{{%finance_cash_account}}', 'is_promptpay');
        $this->dropColumn('{{%finance_cash_account}}', 'deposit_type');
        $this->dropColumn('{{%finance_cash_account}}', 'branch');
        $this->dropColumn('{{%finance_cash_account}}', 'bank_name');
    }
}
