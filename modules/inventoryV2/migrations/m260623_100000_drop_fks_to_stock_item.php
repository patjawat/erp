<?php

use yii\db\Migration;

/**
 * Drop FK ทั้งหมดที่ยังชี้ไป stock_item (deprecated)
 *
 * พื้นหลัง:
 *   หลังจาก migration m260622_120000_migrate_stock_item_to_categorise.php
 *   ข้อมูลวัสดุย้ายไป categorise(asset_item, MATER) แล้ว
 *   การ INSERT/UPDATE ผ่าน controller ใหม่จะ reference categorise.code
 *   แต่ FK เก่ายังบังคับให้ค่า item_code ต้องมีใน stock_item.item_code
 *   → INSERT ล้มเหลวด้วย error 1452 ทันทีที่เจอ item ที่อยู่ใน categorise
 *     แต่ไม่ได้ถูก backfill ใน stock_item
 *
 * FK ที่ drop:
 *   - fk_stock_detail_item_code   (stock_detail → stock_item)
 *   - fk_stock_balance_item_code  (stock_balance → stock_item)
 *   - fk_report_item_code         (stock_monthly_report → stock_item)
 *
 * (FK ตัวที่ 4 fk_siws_item_code ของ stock_item_warehouse_setting
 *  ถูก drop ไปแล้วใน m260622_140000)
 *
 * ทดแทน:
 *   ใช้ application-level validation ใน controller ที่ query จาก categorise ตรงๆ
 *   ไม่สามารถ add FK ใหม่ → categorise(code) เพราะ code ไม่ unique
 *
 * Safe:
 *   - ตรวจว่า FK มีอยู่จริงผ่าน information_schema ก่อน drop (idempotent)
 *   - safeDown พยายามคืน FK แต่จะ skip ถ้ามี orphan rows
 */
class m260623_100000_drop_fks_to_stock_item extends Migration
{
    const TARGETS = [
        // [fk_name, table_name (without {{%...}})]
        ['fk_stock_detail_item_code',  'stock_detail'],
        ['fk_stock_balance_item_code', 'stock_balance'],
        ['fk_report_item_code',        'stock_monthly_report'],
    ];

    public function safeUp()
    {
        foreach (self::TARGETS as [$fkName, $tableName]) {
            if (!$this->tableExists($tableName)) {
                echo "  [SKIP] ตาราง {$tableName} ไม่มีอยู่\n";
                continue;
            }
            if ($this->foreignKeyExists($fkName, $tableName)) {
                $this->dropForeignKey($fkName, '{{%' . $tableName . '}}');
                echo "  [OK] dropped FK {$fkName} จาก {$tableName}\n";
            } else {
                echo "  [SKIP] FK {$fkName} ไม่มีอยู่แล้วใน {$tableName}\n";
            }
        }
    }

    public function safeDown()
    {
        if (!$this->tableExists('stock_item')) {
            echo "  [SKIP] table stock_item ไม่มีอยู่แล้ว — ไม่สามารถคืน FK ได้\n";
            return true;
        }
        foreach (self::TARGETS as [$fkName, $tableName]) {
            if (!$this->tableExists($tableName)) continue;

            // ตรวจ orphan ก่อนคืน FK
            $orphan = (new \yii\db\Query())
                ->from(['t' => $tableName])
                ->leftJoin(['i' => 'stock_item'], 'i.item_code = t.item_code')
                ->where(['i.item_code' => null])
                ->andWhere(['IS NOT', 't.item_code', null])
                ->count('*', $this->db);

            if ((int) $orphan > 0) {
                echo "  [SKIP] {$tableName} มี orphan {$orphan} แถว — ไม่ปลอดภัยที่จะคืน FK {$fkName}\n";
                continue;
            }
            $this->addForeignKey(
                $fkName,
                '{{%' . $tableName . '}}',
                'item_code',
                '{{%stock_item}}',
                'item_code',
                'RESTRICT',
                'CASCADE'
            );
            echo "  [OK] re-added FK {$fkName} ใน {$tableName}\n";
        }
        return true;
    }

    private function tableExists($name)
    {
        return $this->db->getTableSchema($name, true) !== null;
    }

    private function foreignKeyExists($fkName, $tableName)
    {
        if ($this->db->driverName !== 'mysql') {
            return true;
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
