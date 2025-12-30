<?php

use yii\db\Migration;

class m251227_074408_add_plandate_assset_detail extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        return true;
        $table = '{{%asset_detail}}';
        $this->addColumn($table, 'plan_date', $this->date()->comment('วันที่แผน')->after('date_end'));
        $this->addColumn($table, 'actual_date', $this->date()->comment('วันที่ดำเนินการ')->after('plan_date'));
        // $this->addColumn($table, 'provider_type', $this->string(255)->comment('ผู้ให้บริการสอบเทียบ (Provider)')->after('actual_date'));
        // $this->addColumn($table, 'cal_result', $this->string(255)->comment('ผลการสอบเทียบ')->after('actual_date'));
        // $this->addColumn($table, 'is_borrowed', $this->boolean()->defaultValue(0)->after('cal_result'));
        // $this->addColumn($table, 'staff_id', $this->boolean()->defaultValue(0)->comment('ผู้รับคืน')->after('is_borrowed'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
           $table = '{{%asset_detail}}';
            $this->dropColumn($table, 'plan_date');
            $this->dropColumn($table, 'actual_date');
            // $this->dropColumn($table, 'provider_type');
            // $this->dropColumn($table, 'cal_result');
            // $this->dropColumn($table, 'is_borrowed');
            // $this->dropColumn($table, 'staff_id');
    }
}
