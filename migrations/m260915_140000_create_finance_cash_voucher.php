<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ระบบรับ-จ่ายเงินบำรุง (mophcash) — รายจ่ายแบบใบสำคัญจ่าย (voucher)
 * งาน C690915  •  /finance/cash
 *
 * รายจ่ายของ mophcash เป็น "ใบสำคัญจ่าย" 1 ใบมีหลายบรรทัด + VAT/WHT + จ่ายจากบัญชี
 * (ต่างจากรายรับที่เป็นรายการเดี่ยว) โมเดล: ใช้ finance_cash_txn เป็นบรรทัดเงินกลาง
 * ทั้งรับ-จ่าย (ปิดบัญชี/ภาพรวมอ่านตารางเดียว) แล้วผูก header ฝั่งจ่ายด้วย finance_cash_voucher
 *
 *   finance_cash_account   บัญชีเงิน/แหล่งเงิน (เงินสด/เงินฝากคลัง/บัญชีธนาคาร) — ยอดคงเหลือเฟส 4
 *   finance_cash_voucher   หัวใบสำคัญจ่าย (วิธีจ่าย/เลขเช็ค/บัญชี/จ่ายให้/VAT/WHT/ยอด)
 *   + ALTER finance_cash_txn : voucher_id (บรรทัดจ่ายผูกใบสำคัญ), bc_ref (บค.)
 */
final class m260915_140000_create_finance_cash_voucher extends Migration
{
    public function safeUp(): void
    {
        $audit = fn (): array => [
            'ref' => $this->string(64)->notNull(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ];

        // 1) บัญชีเงิน/แหล่งเงิน ------------------------------------------------
        $this->createTable('{{%finance_cash_account}}', array_merge([
            'id' => $this->primaryKey(),
            'code' => $this->string(32)->null()->comment('เลขที่บัญชี (ถ้ามี)'),
            'name' => $this->string(255)->notNull()->comment('ชื่อบัญชี'),
            'account_type' => $this->string(16)->notNull()->defaultValue('bank')->comment('cash | treasury | bank'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
        ], $audit()));
        $this->createIndex('uq-fin_cash_account-ref', '{{%finance_cash_account}}', 'ref', true);
        $this->createIndex('idx-fin_cash_account-active', '{{%finance_cash_account}}', ['is_active', 'sort_order']);

        // 2) หัวใบสำคัญจ่าย ----------------------------------------------------
        $this->createTable('{{%finance_cash_voucher}}', array_merge([
            'id' => $this->primaryKey(),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'pay_date' => $this->date()->notNull()->comment('วันที่จ่าย'),
            'doc_no' => $this->string(64)->null()->comment('เลขใบสำคัญ'),
            'pay_method' => $this->string(24)->null()->comment('cheque | treasury_deposit | cash | ktb_corporate'),
            'cheque_no' => $this->string(64)->null()->comment('เลขที่เช็ค'),
            'account_id' => $this->integer()->null()->comment('จ่ายจากบัญชี (finance_cash_account)'),
            'payee_id' => $this->integer()->null()->comment('ผู้รับเงิน (master — เฟสหลัง)'),
            'payee_name' => $this->string(255)->null()->comment('จ่ายให้ (ข้อความ)'),
            'subtotal' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('รวมสุทธิที่เสียภาษี (ผลรวมบรรทัด)'),
            'vat_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ภาษีมูลค่าเพิ่ม'),
            'total_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('จำนวนรวมทั้งสิ้น'),
            'wht_type' => $this->string(16)->null()->comment('ประเภทหักภาษี ณ ที่จ่าย (ภ.ง.ด.)'),
            'wht_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ยอดหัก ณ ที่จ่าย'),
            'net_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('จำนวนเงินที่จ่ายจริง'),
            'note' => $this->text()->null(),
            'is_closed' => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('อยู่ในงวดที่ปิดบัญชีแล้ว'),
            'close_batch_id' => $this->integer()->null()->comment('อ้างงวดปิดบัญชี — เฟส 4'),
        ], $audit()));
        $this->createIndex('uq-fin_cash_voucher-ref', '{{%finance_cash_voucher}}', 'ref', true);
        $this->createIndex('idx-fin_cash_voucher-main', '{{%finance_cash_voucher}}', ['fiscal_year', 'pay_date']);
        $this->createIndex('idx-fin_cash_voucher-account', '{{%finance_cash_voucher}}', 'account_id');
        $this->createIndex('idx-fin_cash_voucher-closed', '{{%finance_cash_voucher}}', ['is_closed', 'close_batch_id']);
        $this->addForeignKey('fk-fin_cash_voucher-account', '{{%finance_cash_voucher}}', 'account_id', '{{%finance_cash_account}}', 'id', 'SET NULL', 'CASCADE');

        // 3) ต่อบรรทัดเงินฝั่งจ่ายเข้ากับใบสำคัญ --------------------------------
        $this->addColumn('{{%finance_cash_txn}}', 'voucher_id', $this->integer()->null()->after('money_account_id')->comment('บรรทัดจ่ายผูกใบสำคัญ (null = รายรับ/รายการเดี่ยว)'));
        $this->addColumn('{{%finance_cash_txn}}', 'bc_ref', $this->string(64)->null()->after('doc_no')->comment('บค. (อ้างอิงในบรรทัดจ่าย)'));
        $this->createIndex('idx-fin_cash_txn-voucher', '{{%finance_cash_txn}}', 'voucher_id');
        $this->addForeignKey('fk-fin_cash_txn-voucher', '{{%finance_cash_txn}}', 'voucher_id', '{{%finance_cash_voucher}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-fin_cash_txn-voucher', '{{%finance_cash_txn}}');
        $this->dropIndex('idx-fin_cash_txn-voucher', '{{%finance_cash_txn}}');
        $this->dropColumn('{{%finance_cash_txn}}', 'bc_ref');
        $this->dropColumn('{{%finance_cash_txn}}', 'voucher_id');
        $this->dropTable('{{%finance_cash_voucher}}');
        $this->dropTable('{{%finance_cash_account}}');
    }
}
