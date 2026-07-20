<?php

use yii\db\Migration;

/**
 * ลบตารางค่าเสื่อม legacy (prefix am_) — สำหรับรันใน "เฟส 2" พร้อมการถอดโค้ด legacy
 *
 * ⚠️ อย่ารัน migration นี้จนกว่าจะถอด/แทนที่โค้ดที่อ้างอิงตารางเหล่านี้:
 *    - modules/am/models/AmAssetDepreciationMonthly.php, AmAssetDepreciation.php, AmDepreciationClosing.php
 *    - modules/am/services/MonthlyDepreciationService.php, DepreciationClosingService.php
 *    - modules/am/controllers/DepreciationController.php, ReportController.php (line ~429)
 *    - modules/am/views/depreciation/monthly-processing.php, report/monthly-depreciation*.php
 *    - modules/am/menu.php (เมนู "ประมวลผลรายเดือน"/"รายงานค่าเสื่อมรายเดือน")
 *
 * ⚠️ ก่อนรัน ให้สำรองข้อมูลก่อน (ผู้ใช้ยืนยันว่าเป็นข้อมูลทดสอบ แต่คงมาตรฐานความปลอดภัยไว้):
 *    docker exec dansai_db mysqldump -uroot -pdocker --no-tablespaces \
 *      <DB> am_asset_depreciation_monthly am_asset_depreciations am_depreciation_closings \
 *      > backup_legacy_depreciation_<DB>_$(date +%F).sql
 *
 * ลำดับการลบ: ลบ FK ก่อน → ลบตารางลูก → ตารางที่เหลือ
 * safeDown: สร้างโครงสร้างเดิมกลับคืน (โครงสร้างเท่านั้น ข้อมูลต้องกู้จาก backup)
 */
class m260712_900000_drop_legacy_am_depreciation_tables extends Migration
{
    public function safeUp()
    {
        $schema = $this->db->getSchema();

        // รายงานจำนวนข้อมูลก่อนลบ (ตรวจ dependency อีกครั้งในตัว migration)
        foreach (['am_asset_depreciation_monthly', 'am_asset_depreciations', 'am_depreciation_closings'] as $t) {
            $full = $this->db->tablePrefix . $t;
            if ($schema->getTableSchema('{{%' . $t . '}}', true) !== null) {
                $count = (new \yii\db\Query())->from('{{%' . $t . '}}')->count('*', $this->db);
                echo "    > $t มีข้อมูล $count แถว (จะถูกลบ)\n";
            }
        }

        // am_asset_depreciation_monthly (ลูก มี FK ไป asset)
        if ($schema->getTableSchema('{{%am_asset_depreciation_monthly}}', true) !== null) {
            $this->tryDropFk('fk_am_dep_monthly_asset', '{{%am_asset_depreciation_monthly}}');
            $this->dropTable('{{%am_asset_depreciation_monthly}}');
        }

        // am_asset_depreciations (มี FK ไป asset)
        if ($schema->getTableSchema('{{%am_asset_depreciations}}', true) !== null) {
            $this->tryDropFk('fk_am_asset_depreciations_asset', '{{%am_asset_depreciations}}');
            $this->dropTable('{{%am_asset_depreciations}}');
        }

        // am_depreciation_closings (ไม่มี FK)
        if ($schema->getTableSchema('{{%am_depreciation_closings}}', true) !== null) {
            $this->dropTable('{{%am_depreciation_closings}}');
        }
    }

    public function safeDown()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // สร้างโครงสร้างเดิมกลับคืน (ข้อมูลไม่ถูกกู้ — ต้อง restore จาก backup SQL)
        $this->createTable('{{%am_asset_depreciation_monthly}}', [
            'id' => $this->primaryKey(),
            'asset_id' => $this->integer()->notNull(),
            'fiscal_year' => $this->integer()->notNull(),
            'month' => $this->tinyInteger()->notNull(),
            'days_used' => $this->smallInteger()->notNull()->defaultValue(30),
            'beginning_value' => $this->decimal(14, 2)->notNull()->defaultValue(0),
            'depreciation_amount' => $this->decimal(14, 2)->notNull()->defaultValue(0),
            'accumulated_depreciation' => $this->decimal(14, 2)->notNull()->defaultValue(0),
            'remaining_value' => $this->decimal(14, 2)->notNull()->defaultValue(0),
            'processed_at' => $this->dateTime()->null(),
        ], $tableOptions);
        $this->createIndex('idx_am_dep_monthly_asset_id', '{{%am_asset_depreciation_monthly}}', 'asset_id');
        $this->createIndex('idx_am_dep_monthly_fiscal_month', '{{%am_asset_depreciation_monthly}}', ['fiscal_year', 'month']);
        $this->createIndex('uq_am_dep_monthly_asset_year_month', '{{%am_asset_depreciation_monthly}}', ['asset_id', 'fiscal_year', 'month'], true);
        $this->addForeignKey('fk_am_dep_monthly_asset', '{{%am_asset_depreciation_monthly}}', 'asset_id', '{{%asset}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%am_asset_depreciations}}', [
            'id' => $this->primaryKey(),
            'asset_id' => $this->integer()->notNull(),
            'fiscal_year' => $this->integer()->notNull(),
            'opening_value' => $this->decimal(14, 2)->notNull()->defaultValue(0),
            'depreciation_amount' => $this->decimal(14, 2)->notNull()->defaultValue(0),
            'accumulated_depreciation' => $this->decimal(14, 2)->notNull()->defaultValue(0),
            'closing_value' => $this->decimal(14, 2)->notNull()->defaultValue(0),
            'is_locked' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
        ], $tableOptions);
        $this->createIndex('idx_am_asset_depreciations_asset_id', '{{%am_asset_depreciations}}', 'asset_id');
        $this->createIndex('idx_am_asset_depreciations_fiscal_year', '{{%am_asset_depreciations}}', 'fiscal_year');
        $this->createIndex('uq_am_asset_depreciations_asset_year', '{{%am_asset_depreciations}}', ['asset_id', 'fiscal_year'], true);
        $this->addForeignKey('fk_am_asset_depreciations_asset', '{{%am_asset_depreciations}}', 'asset_id', '{{%asset}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%am_depreciation_closings}}', [
            'id' => $this->primaryKey(),
            'fiscal_year' => $this->integer()->notNull(),
            'closed_at' => $this->dateTime()->notNull(),
            'closed_by' => $this->integer()->null(),
            'remark' => $this->string(500)->null(),
        ], $tableOptions);
        $this->createIndex('uq_am_depreciation_closings_year', '{{%am_depreciation_closings}}', 'fiscal_year', true);
    }

    private function tryDropFk(string $name, string $table): void
    {
        try {
            $this->dropForeignKey($name, $table);
        } catch (\Throwable $e) {
            // FK อาจถูกลบไปแล้ว
        }
    }
}
