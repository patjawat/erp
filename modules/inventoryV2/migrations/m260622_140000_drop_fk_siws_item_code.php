<?php

use yii\db\Migration;

/**
 * Drop FK fk_siws_item_code ของ stock_item_warehouse_setting
 *
 * เหตุผล:
 *   หลังจาก migration m260622_120000_migrate_stock_item_to_categorise.php
 *   source of truth ของ "รายการวัสดุ" ย้ายไป categorise(name=asset_item, group_id=MATER)
 *   item ใหม่จะถูกเพิ่มใน categorise เท่านั้น ไม่ได้เพิ่มใน stock_item เก่าอีกแล้ว
 *
 *   FK เดิม fk_siws_item_code → stock_item.item_code จึงทำให้ INSERT setting ของ
 *   item ใหม่ล้มเหลวด้วย Integrity constraint violation 1452
 *
 *   ทางออก: drop FK นี้ และพึ่งพา application-level validation
 *   (WarehouseController::actionStockMinMax query จาก categorise โดยตรง)
 *
 *   ไม่สามารถ add FK ใหม่ → categorise(code) เพราะ categorise.code ไม่ unique
 *   (ซ้ำกันได้ข้าม name/group_id หลายชนิด)
 *
 * Safe:
 *   - ตรวจว่า FK มีอยู่จริงก่อน drop (idempotent)
 *   - safeDown จะพยายามคืน FK แต่จะ skip ถ้ามีข้อมูล orphan
 */
class m260622_140000_drop_fk_siws_item_code extends Migration
{
    const TABLE = '{{%stock_item_warehouse_setting}}';
    const FK_NAME = 'fk_siws_item_code';

    public function safeUp()
    {
        if ($this->foreignKeyExists(self::FK_NAME, 'stock_item_warehouse_setting')) {
            $this->dropForeignKey(self::FK_NAME, self::TABLE);
            echo "  [OK] dropped FK " . self::FK_NAME . "\n";
        } else {
            echo "  [SKIP] FK " . self::FK_NAME . " ไม่มีอยู่แล้ว\n";
        }
    }

    public function safeDown()
    {
        if (!$this->tableExists('stock_item')) {
            echo "  [SKIP] table stock_item ไม่มีอยู่แล้ว — ไม่สามารถคืน FK ได้\n";
            return true;
        }

        // ตรวจว่ามี orphan ไหม (item_code ใน setting ที่ไม่ตรงกับ stock_item)
        $orphan = (new \yii\db\Query())
            ->from(['s' => 'stock_item_warehouse_setting'])
            ->leftJoin(['i' => 'stock_item'], 'i.item_code = s.item_code')
            ->where(['i.item_code' => null])
            ->count('*', $this->db);

        if ((int) $orphan > 0) {
            echo "  [SKIP] พบ orphan rows {$orphan} แถวใน stock_item_warehouse_setting — ไม่ปลอดภัยที่จะคืน FK\n";
            return true;
        }

        $this->addForeignKey(
            self::FK_NAME,
            self::TABLE,
            'item_code',
            '{{%stock_item}}',
            'item_code',
            'RESTRICT',
            'CASCADE'
        );
        echo "  [OK] re-added FK " . self::FK_NAME . "\n";
        return true;
    }

    private function tableExists($name)
    {
        return $this->db->getTableSchema($name, true) !== null;
    }

    private function foreignKeyExists($fkName, $tableName)
    {
        // MySQL: ตรวจจาก information_schema
        if ($this->db->driverName !== 'mysql') {
            return true; // เผื่อ driver อื่น — ปล่อยให้ dropForeignKey ขึ้น error เอง
        }
        $count = (new \yii\db\Query())
            ->from('information_schema.TABLE_CONSTRAINTS')
            ->where([
                'CONSTRAINT_SCHEMA' => new \yii\db\Expression('DATABASE()'),
                'TABLE_NAME' => $tableName,
                'CONSTRAINT_NAME' => $fkName,
                'CONSTRAINT_TYPE' => 'FOREIGN KEY',
            ])
            ->count('*', $this->db);
        return (int) $count > 0;
    }
}
