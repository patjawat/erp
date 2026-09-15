<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ระบบรับ-จ่ายเงินบำรุง (พอร์ตจาก mophcash) — เฟส 1 : โครงตาราง
 * งาน C690915  •  /finance/cash
 *
 * 3 ตารางหลัก
 *   finance_cash_category  ผังบัญชี 3 ระดับ self-ref (กลุ่ม→หมวด→หัวข้อบัญชี) แยก IN/OUT
 *                          ใช้ร่วมทั้งฝั่งแผนและฝั่งบันทึกจริง
 *   finance_cash_txn       รายการรับ-จ่ายจริง บันทึกละเอียดทีละใบ (ไม่ใช่ยอดก้อนเดียว)
 *                          ของจริงมี ~34k แถว → index (fiscal_year, txn_type, doc_date)
 *   finance_cash_plan      ยอดแผนต่อหมวด/ปีงบ (multi-year) — UI ทำจริงเฟส 3 แต่วางตารางไว้เลย
 *
 * คอนเวนชันเดียวกับชุดเงินยืม: ref(22) + created/updated (datetime) + created_by/updated_by
 * ปีงบประมาณเก็บเป็น int (พ.ศ.) ตรง ๆ, จำนวนเงิน decimal(15,2)
 *
 * ผังบัญชีมาตรฐาน "ป้อนภายหลัง" ผ่าน seed action ในหน้าจัดการหมวด ไม่ใส่ในไฟล์ migration
 * (กันปัญหา migration drift รพ. vs ฐานทดสอบ)
 */
final class m260915_100000_create_finance_cash_tables extends Migration
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

        // 1) ผังบัญชีรับ-จ่าย (chart of accounts) 3 ระดับ ---------------------------
        $this->createTable('{{%finance_cash_category}}', array_merge([
            'id' => $this->primaryKey(),
            'txn_type' => $this->string(3)->notNull()->comment('IN = รายรับ, OUT = รายจ่าย'),
            'parent_id' => $this->integer()->null()->comment('หมวดแม่ (null = กลุ่มบนสุด)'),
            'level' => $this->string(16)->notNull()->defaultValue('group')->comment('group | category | account'),
            'code' => $this->string(32)->null()->comment('รหัสบัญชี (ถ้ามี)'),
            'name' => $this->string(500)->notNull()->comment('ชื่อกลุ่ม/หมวด/หัวข้อบัญชี'),
            'description' => $this->text()->null()->comment('คำอธิบายหมวด'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
        ], $audit()));
        $this->createIndex('uq-fin_cash_category-ref', '{{%finance_cash_category}}', 'ref', true);
        $this->createIndex('idx-fin_cash_category-tree', '{{%finance_cash_category}}', ['txn_type', 'parent_id', 'sort_order']);
        $this->createIndex('idx-fin_cash_category-active', '{{%finance_cash_category}}', ['is_active', 'sort_order']);
        $this->addForeignKey('fk-fin_cash_category-parent', '{{%finance_cash_category}}', 'parent_id', '{{%finance_cash_category}}', 'id', 'SET NULL', 'CASCADE');

        // 2) รายการรับ-จ่ายจริง (บันทึกละเอียดทีละใบ) -----------------------------
        $this->createTable('{{%finance_cash_txn}}', array_merge([
            'id' => $this->primaryKey(),
            'txn_type' => $this->string(3)->notNull()->comment('IN = รับ, OUT = จ่าย'),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'category_id' => $this->integer()->notNull()->comment('หัวข้อบัญชี (leaf ของ finance_cash_category)'),
            'money_account_id' => $this->integer()->null()->comment('บัญชีเงิน/แหล่งเงิน — สำรองไว้เปิดใช้เฟส 4'),
            'doc_date' => $this->date()->notNull()->comment('วันที่ ณ วันออกใบเสร็จ/เอกสาร'),
            'doc_no' => $this->string(64)->null()->comment('เลขที่ใบเสร็จ/เอกสาร'),
            'pay_method' => $this->string(16)->null()->comment('cash | transfer | cheque | credit'),
            'amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'party_id' => $this->integer()->null()->comment('คู่ค้า/ผู้รับ-จ่าย (master data — เฟสหลัง)'),
            'party_name' => $this->string(255)->null()->comment('รับจาก/จ่ายให้ (ข้อความ)'),
            'note' => $this->text()->null(),
            'is_closed' => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('อยู่ในงวดที่ปิดบัญชีแล้ว → ล็อกแก้ไข'),
            'close_batch_id' => $this->integer()->null()->comment('อ้างงวดปิดบัญชี — สำรองไว้เฟส 4'),
        ], $audit()));
        $this->createIndex('uq-fin_cash_txn-ref', '{{%finance_cash_txn}}', 'ref', true);
        $this->createIndex('idx-fin_cash_txn-main', '{{%finance_cash_txn}}', ['fiscal_year', 'txn_type', 'doc_date']);
        $this->createIndex('idx-fin_cash_txn-cat', '{{%finance_cash_txn}}', 'category_id');
        $this->createIndex('idx-fin_cash_txn-doc', '{{%finance_cash_txn}}', 'doc_no');
        $this->createIndex('idx-fin_cash_txn-closed', '{{%finance_cash_txn}}', ['is_closed', 'close_batch_id']);
        $this->addForeignKey('fk-fin_cash_txn-cat', '{{%finance_cash_txn}}', 'category_id', '{{%finance_cash_category}}', 'id', 'RESTRICT', 'CASCADE');

        // 3) ยอดแผนรับ-จ่าย ต่อหมวด/ปีงบ (multi-year) -----------------------------
        $this->createTable('{{%finance_cash_plan}}', array_merge([
            'id' => $this->primaryKey(),
            'txn_type' => $this->string(3)->notNull()->comment('IN | OUT'),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณของแผน (พ.ศ.)'),
            'category_id' => $this->integer()->notNull()->comment('หมวด/หัวข้อบัญชี ที่ตั้งแผน'),
            'amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ยอดแผนตั้งไว้'),
            'note' => $this->string(255)->null(),
        ], $audit()));
        $this->createIndex('uq-fin_cash_plan-ref', '{{%finance_cash_plan}}', 'ref', true);
        $this->createIndex('uq-fin_cash_plan-year_cat', '{{%finance_cash_plan}}', ['fiscal_year', 'category_id'], true);
        $this->createIndex('idx-fin_cash_plan-year', '{{%finance_cash_plan}}', ['fiscal_year', 'txn_type']);
        $this->addForeignKey('fk-fin_cash_plan-cat', '{{%finance_cash_plan}}', 'category_id', '{{%finance_cash_category}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%finance_cash_plan}}');
        $this->dropTable('{{%finance_cash_txn}}');
        $this->dropTable('{{%finance_cash_category}}');
    }
}
