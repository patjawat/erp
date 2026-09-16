<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ระบบรับ-จ่ายเงินบำรุง (mophcash) — ปิดบัญชี (close batch)
 * งาน C690915  •  /finance/cash
 *
 * 1 งวดปิด = 1 แถว finance_cash_close (ประจำวัน/ประจำปี) เก็บ snapshot ยอดรับ-จ่าย
 * รายการที่ถูกปิดจะ set is_closed=1 + close_batch_id ในตาราง finance_cash_txn / _voucher
 * (คอลัมน์ is_closed / close_batch_id ถูกเตรียมไว้ตั้งแต่ migration แรกแล้ว)
 */
final class m260915_170000_create_finance_cash_close extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%finance_cash_close}}', [
            'id' => $this->primaryKey(),
            'close_type' => $this->string(12)->notNull()->defaultValue('daily')->comment('daily | yearly'),
            'close_date' => $this->date()->null()->comment('วันที่ปิด (ประจำวัน)'),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'total_in' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'total_out' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'in_count' => $this->integer()->notNull()->defaultValue(0),
            'out_count' => $this->integer()->notNull()->defaultValue(0),
            'note' => $this->string(255)->null(),
            'ref' => $this->string(64)->notNull(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('uq-fin_cash_close-ref', '{{%finance_cash_close}}', 'ref', true);
        $this->createIndex('idx-fin_cash_close-date', '{{%finance_cash_close}}', ['close_type', 'close_date']);
        $this->createIndex('idx-fin_cash_close-year', '{{%finance_cash_close}}', 'fiscal_year');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%finance_cash_close}}');
    }
}
