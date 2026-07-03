<?php

use yii\db\Migration;

/**
 * แก้ collation mismatch: categorise.code เดิมเป็น utf8mb3_general_ci/utf8mb4_general_ci
 * (ตารางเก่า) แต่ item_code ของตาราง stock_* ใน inventoryV2 (stock_balance, stock_detail,
 * stock_item_warehouse_setting, stock_monthly_report) เป็น utf8mb4_unicode_ci ทำให้ query
 * ที่ JOIN สองคอลัมน์นี้ (มีอยู่ ~30 จุดใน inventoryV2 controllers) throw
 * "Illegal mix of collations" — เกิดขึ้นตั้งแต่ m260622_120000 ยุบ stock_item เข้า
 * categorise แต่ไม่ได้ปรับ collation ให้ตรงกัน
 *
 * category_id ก็ต้องแก้ด้วย เพราะเก็บ "code" ของ parent category แบบ self-reference
 * (cat.code = i.category_id) ใน categorise เอง — ใช้ค่าจาก domain เดียวกับ code
 */
class m260703_164243_fix_categorise_code_collation extends Migration
{
    const TABLE = '{{%categorise}}';
    const COLUMNS = ['code', 'category_id'];
    const TARGET_COLLATION = 'utf8mb4_unicode_ci';

    public function safeUp()
    {
        foreach (self::COLUMNS as $columnName) {
            $this->fixColumnCollation($columnName);
        }

        return true;
    }

    public function safeDown()
    {
        echo "  [safeDown] ไม่ revert collation กลับเป็นของเดิม — จะทำให้ join กับ inventoryV2 พังอีกครั้ง\n";
        echo "  ถ้าต้องการ rollback จริง ให้ ALTER TABLE ด้วยมือ\n";
        return true;
    }

    private function fixColumnCollation($columnName)
    {
        $column = $this->getColumn($columnName);
        if ($column === null) {
            echo "  [SKIP] ไม่พบคอลัมน์ categorise.{$columnName}\n";
            return;
        }

        $current = $this->currentCollation($columnName);
        if (stripos($current, self::TARGET_COLLATION) === 0) {
            echo "  [SKIP] categorise.{$columnName} เป็น " . self::TARGET_COLLATION . " อยู่แล้ว\n";
            return;
        }

        $nullSql = $column->allowNull ? 'NULL' : 'NOT NULL';
        $dbType = $column->dbType ?: 'varchar(255)';

        echo "  เปลี่ยน categorise.{$columnName} ({$dbType}) จาก {$current} -> " . self::TARGET_COLLATION . "\n";
        $this->execute(
            'ALTER TABLE ' . self::TABLE . ' MODIFY `' . $columnName . '` ' . $dbType
            . ' CHARACTER SET utf8mb4 COLLATE ' . self::TARGET_COLLATION . ' ' . $nullSql
        );
    }

    private function getColumn($columnName)
    {
        $schema = $this->db->getTableSchema('categorise', true);
        return $schema ? $schema->getColumn($columnName) : null;
    }

    private function currentCollation($columnName)
    {
        return (string) $this->db->createCommand(
            'SELECT COLLATION_NAME FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c',
            [':t' => 'categorise', ':c' => $columnName]
        )->queryScalar();
    }
}
