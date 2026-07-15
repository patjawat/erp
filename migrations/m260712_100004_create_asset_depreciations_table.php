<?php

use yii\db\Migration;

/**
 * ผลการคำนวณค่าเสื่อมของทรัพย์สินแต่ละชิ้นในแต่ละงวด (ตารางใหม่ ไม่มี prefix am_)
 *
 * ข้อกำหนดสำคัญ:
 *  - จำนวนเงินใช้ DECIMAL(20,4) ห้าม FLOAT/DOUBLE
 *  - unique (asset_id, accounting_period_id, transaction_type)
 *  - งวดที่ปิดแล้วห้าม update/ลบ (บังคับใช้ระดับ service) — แก้ย้อนหลังด้วย adjustment/reversal
 *  - เก็บ snapshot เกณฑ์ที่ใช้คำนวณเสมอ
 *  - FK ข้อมูลบัญชีเป็น RESTRICT ไม่ cascade delete
 */
class m260712_100004_create_asset_depreciations_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%asset_depreciations}}', [
            'id' => $this->primaryKey(),
            'asset_id' => $this->integer()->notNull()->comment('FK asset.id'),
            'accounting_period_id' => $this->integer()->notNull()->comment('FK accounting_periods.id'),
            'transaction_type' => $this->string(20)->notNull()->defaultValue('normal')->comment('normal|adjustment|reversal'),

            'opening_cost' => $this->decimal(20, 4)->notNull()->defaultValue(0)->comment('ราคาทุน/มูลค่าต้นงวด'),
            'depreciable_base' => $this->decimal(20, 4)->notNull()->defaultValue(0)->comment('ฐานค่าเสื่อม (ทุน - มูลค่าซาก)'),
            'depreciation_amount' => $this->decimal(20, 4)->notNull()->defaultValue(0)->comment('ค่าเสื่อมประจำงวด'),
            'adjustment_amount' => $this->decimal(20, 4)->notNull()->defaultValue(0)->comment('ยอดปรับปรุง'),
            'accumulated_depreciation' => $this->decimal(20, 4)->notNull()->defaultValue(0)->comment('ค่าเสื่อมสะสม (<= depreciable_base)'),
            'closing_net_book_value' => $this->decimal(20, 4)->notNull()->defaultValue(0)->comment('มูลค่าสุทธิปลายงวด (>= มูลค่าซาก)'),

            'calculation_days' => $this->integer()->null()->comment('จำนวนวันที่ใช้คำนวณในงวด'),
            'calculation_months' => $this->decimal(8, 4)->null()->comment('จำนวนเดือนที่ใช้คำนวณในงวด'),

            'depreciation_profile_id' => $this->integer()->null()->comment('FK depreciation_profiles.id'),
            'method_snapshot' => $this->string(30)->null()->comment('วิธีคำนวณ ณ ตอนคำนวณ'),
            'useful_life_months_snapshot' => $this->integer()->null()->comment('อายุ (เดือน) ณ ตอนคำนวณ'),
            'rate_snapshot' => $this->decimal(8, 4)->null()->comment('อัตรา (%) ณ ตอนคำนวณ'),
            'salvage_value_snapshot' => $this->decimal(20, 4)->null()->comment('มูลค่าซาก ณ ตอนคำนวณ'),

            'status' => $this->string(20)->notNull()->defaultValue('draft')->comment('draft|calculated|posted|locked|reversed'),
            'calculated_at' => $this->dateTime()->null(),
            'calculated_by' => $this->integer()->null(),
            'posted_at' => $this->dateTime()->null(),
            'posted_by' => $this->integer()->null(),
            'note' => $this->string(500)->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
        ], $tableOptions);

        $this->createIndex(
            'uq_asset_depreciations_asset_period_type',
            '{{%asset_depreciations}}',
            ['asset_id', 'accounting_period_id', 'transaction_type'],
            true
        );
        $this->createIndex('idx_asset_depreciations_asset', '{{%asset_depreciations}}', 'asset_id');
        $this->createIndex('idx_asset_depreciations_period', '{{%asset_depreciations}}', 'accounting_period_id');
        $this->createIndex('idx_asset_depreciations_status', '{{%asset_depreciations}}', 'status');

        // ข้อมูลบัญชี: RESTRICT ไม่ cascade delete
        $this->addForeignKey(
            'fk_asset_depreciations_asset',
            '{{%asset_depreciations}}',
            'asset_id',
            '{{%asset}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_asset_depreciations_period',
            '{{%asset_depreciations}}',
            'accounting_period_id',
            '{{%accounting_periods}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_asset_depreciations_profile',
            '{{%asset_depreciations}}',
            'depreciation_profile_id',
            '{{%depreciation_profiles}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_asset_depreciations_profile', '{{%asset_depreciations}}');
        $this->dropForeignKey('fk_asset_depreciations_period', '{{%asset_depreciations}}');
        $this->dropForeignKey('fk_asset_depreciations_asset', '{{%asset_depreciations}}');
        $this->dropTable('{{%asset_depreciations}}');
    }
}
