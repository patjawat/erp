<?php

use yii\db\Migration;

/**
 * เพิ่ม out_sub_value, out_hosp_value ลง stock_monthly_report
 *
 * เหตุผล:
 *   รายงานสรุปวัสดุคงคลังต้องแสดงเป็น "มูลค่าเงิน" แยกระหว่าง
 *   จ่ายส่วนของ รพ.aq (out_sub_value) และจ่ายส่วนของโรงพยาบาล (out_hosp_value)
 *   มูลค่าคำนวณจาก qty × unit_price ของ IN lot ที่จ่าย (ตาม lot_number)
 *
 *   เดิมมีแต่ total_out_value (รวม) ทำให้แยกออกในรายงานไม่ได้
 */
class m260624_100000_add_out_value_to_monthly_report extends Migration
{
    public function safeUp()
    {
        if (!$this->tableExists('stock_monthly_report')) {
            echo "  [SKIP] ตาราง stock_monthly_report ไม่มีอยู่\n";
            return true;
        }

        $schema = $this->db->getTableSchema('{{%stock_monthly_report}}', true);

        if (!isset($schema->columns['out_sub_value'])) {
            $this->addColumn(
                '{{%stock_monthly_report}}',
                'out_sub_value',
                $this->decimal(15, 2)->defaultValue(0)->after('out_sub_qty')
            );
            echo "  [OK] เพิ่ม out_sub_value\n";
        }

        if (!isset($schema->columns['out_hosp_value'])) {
            $this->addColumn(
                '{{%stock_monthly_report}}',
                'out_hosp_value',
                $this->decimal(15, 2)->defaultValue(0)->after('out_hosp_qty')
            );
            echo "  [OK] เพิ่ม out_hosp_value\n";
        }

        return true;
    }

    public function safeDown()
    {
        if (!$this->tableExists('stock_monthly_report')) {
            return true;
        }
        $schema = $this->db->getTableSchema('{{%stock_monthly_report}}', true);
        if (isset($schema->columns['out_hosp_value'])) {
            $this->dropColumn('{{%stock_monthly_report}}', 'out_hosp_value');
        }
        if (isset($schema->columns['out_sub_value'])) {
            $this->dropColumn('{{%stock_monthly_report}}', 'out_sub_value');
        }
        return true;
    }

    private function tableExists($name)
    {
        return $this->db->getTableSchema($name, true) !== null;
    }
}
