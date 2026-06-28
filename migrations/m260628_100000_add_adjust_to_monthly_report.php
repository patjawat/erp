<?php

use yii\db\Migration;

/**
 * เพิ่ม adjust_in_qty, adjust_in_value, adjust_out_qty, adjust_out_value ลง stock_monthly_report
 *
 * เหตุผล:
 *   เดิมการปิดเดือนนับเฉพาะ order_type IN/OUT — การปรับยอด (ADJUST) ถูกข้ามทั้งหมด
 *   ทำให้ snapshot ที่ส่งบัญชีไม่ตรงกับ stock_balance จริง
 *   แยกคอลัมน์ ADJUST ออกจาก in/out ปกติเพื่อให้บัญชีเห็น movement ที่เกิดจากการปรับยอด
 *   เป็นรายการต่างหากในรายงาน (ไม่ปะปนกับยอดซื้อ/ยอดจ่ายปกติ)
 */
class m260628_100000_add_adjust_to_monthly_report extends Migration
{
    public function safeUp()
    {
        if (!$this->tableExists('stock_monthly_report')) {
            echo "  [SKIP] ตาราง stock_monthly_report ไม่มีอยู่\n";
            return true;
        }

        $schema = $this->db->getTableSchema('{{%stock_monthly_report}}', true);

        if (!isset($schema->columns['adjust_in_qty'])) {
            $this->addColumn(
                '{{%stock_monthly_report}}',
                'adjust_in_qty',
                $this->decimal(15, 2)->defaultValue(0)->after('in_value')
            );
            echo "  [OK] เพิ่ม adjust_in_qty\n";
        }

        if (!isset($schema->columns['adjust_in_value'])) {
            $this->addColumn(
                '{{%stock_monthly_report}}',
                'adjust_in_value',
                $this->decimal(15, 2)->defaultValue(0)->after('adjust_in_qty')
            );
            echo "  [OK] เพิ่ม adjust_in_value\n";
        }

        if (!isset($schema->columns['adjust_out_qty'])) {
            $this->addColumn(
                '{{%stock_monthly_report}}',
                'adjust_out_qty',
                $this->decimal(15, 2)->defaultValue(0)->after('adjust_in_value')
            );
            echo "  [OK] เพิ่ม adjust_out_qty\n";
        }

        if (!isset($schema->columns['adjust_out_value'])) {
            $this->addColumn(
                '{{%stock_monthly_report}}',
                'adjust_out_value',
                $this->decimal(15, 2)->defaultValue(0)->after('adjust_out_qty')
            );
            echo "  [OK] เพิ่ม adjust_out_value\n";
        }

        return true;
    }

    public function safeDown()
    {
        if (!$this->tableExists('stock_monthly_report')) {
            return true;
        }
        $schema = $this->db->getTableSchema('{{%stock_monthly_report}}', true);
        foreach (['adjust_out_value', 'adjust_out_qty', 'adjust_in_value', 'adjust_in_qty'] as $col) {
            if (isset($schema->columns[$col])) {
                $this->dropColumn('{{%stock_monthly_report}}', $col);
            }
        }
        return true;
    }

    private function tableExists($name)
    {
        return $this->db->getTableSchema($name, true) !== null;
    }
}
