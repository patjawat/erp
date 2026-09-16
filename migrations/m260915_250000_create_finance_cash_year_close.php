<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ระบบรับ-จ่ายเงินบำรุง (mophcash) — ปิดบัญชีประจำปี (worksheet ปิดงบสิ้นปี)
 * งาน C690915  •  /finance/cash/close-yearly
 *
 * เก็บค่าที่ปิดจริงต่อปีงบ: ยอดหมวด (แก้ได้/ซิงค์จาก txn) + reconciliation + composition
 *   finance_cash_year_close       หัว: สะสมยกมา/กองทุน/ภาระผูกพัน/ก่อหนี้พัสดุ + องค์ประกอบเงินคงเหลือ
 *   finance_cash_year_close_item  ยอดปิดต่อหมวด (fiscal_year, category_id, amount)
 * ภาระผูกพัน/ก่อหนี้พัสดุ กรอกเองก่อน (เชื่อมพัสดุทีหลัง)
 */
final class m260915_250000_create_finance_cash_year_close extends Migration
{
    public function safeUp(): void
    {
        $money = fn () => $this->decimal(15, 2)->notNull()->defaultValue(0);

        $this->createTable('{{%finance_cash_year_close}}', [
            'id' => $this->primaryKey(),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'carried_forward' => $money()->comment('บวกเงินคงเหลือสะสมยกมา'),
            'fund_pending' => $money()->comment('กองทุนรอการจัดสรร (4)'),
            'obligation' => $money()->comment('ภาระผูกพัน (5)'),
            'purchase_obligation' => $money()->comment('หักก่อหนี้ภาระผูกพันพัสดุ (6)'),
            'cash_amount' => $money()->comment('เงินสด/เทียบเท่าเงินสด'),
            'treasury_amount' => $money()->comment('เงินฝากคลัง'),
            'bank_fixed' => $money()->comment('เงินฝากธนาคาร ประเภทประจำ'),
            'bank_savings' => $money()->comment('เงินฝากธนาคาร ประเภทออมทรัพย์'),
            'bank_current' => $money()->comment('เงินฝากธนาคาร ประเภทกระแสรายวัน'),
            'note' => $this->string(255)->null(),
            'ref' => $this->string(64)->notNull(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('uq-fin_cash_year_close-ref', '{{%finance_cash_year_close}}', 'ref', true);
        $this->createIndex('uq-fin_cash_year_close-year', '{{%finance_cash_year_close}}', 'fiscal_year', true);

        $this->createTable('{{%finance_cash_year_close_item}}', [
            'id' => $this->primaryKey(),
            'fiscal_year' => $this->integer()->notNull(),
            'category_id' => $this->integer()->notNull(),
            'amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'ref' => $this->string(64)->notNull(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('uq-fin_cash_yc_item-ref', '{{%finance_cash_year_close_item}}', 'ref', true);
        $this->createIndex('uq-fin_cash_yc_item-year_cat', '{{%finance_cash_year_close_item}}', ['fiscal_year', 'category_id'], true);
        $this->addForeignKey('fk-fin_cash_yc_item-cat', '{{%finance_cash_year_close_item}}', 'category_id', '{{%finance_cash_category}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%finance_cash_year_close_item}}');
        $this->dropTable('{{%finance_cash_year_close}}');
    }
}
