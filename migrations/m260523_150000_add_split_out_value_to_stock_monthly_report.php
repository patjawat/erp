<?php

use yii\db\Migration;

/**
 * เพิ่ม out_sub_value และ out_hosp_value ใน stock_monthly_report
 * เพื่อแยกมูลค่าจ่ายออกตามคลังปลายทาง (BRANCH = รพ.สต., SUB = โรงพยาบาล)
 * สอดคล้องกับโครงรายงานของ /inventory/report ที่แยก price_out / branch_price_out
 */
class m260523_150000_add_split_out_value_to_stock_monthly_report extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%stock_monthly_report}}', 'out_sub_value',
            $this->decimal(15, 2)->defaultValue(0)->comment('มูลค่าจ่ายส่วนของ รพ.สต. (BRANCH)'));
        $this->addColumn('{{%stock_monthly_report}}', 'out_hosp_value',
            $this->decimal(15, 2)->defaultValue(0)->comment('มูลค่าจ่ายส่วนของโรงพยาบาล (SUB)'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%stock_monthly_report}}', 'out_hosp_value');
        $this->dropColumn('{{%stock_monthly_report}}', 'out_sub_value');
    }
}
