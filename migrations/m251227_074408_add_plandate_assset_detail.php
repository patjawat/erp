<?php

use yii\db\Migration;

class m251227_074408_add_plandate_assset_detail extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = '{{%asset_detail}}';
        $this->addColumn($table, 'plan_date', $this->date()->comment('วันที่แผน')->after('date_end'));
        $this->addColumn($table, 'actual_date', $this->date()->comment('วันที่ดำเนินการ')->after('plan_date'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
        //    $table = '{{%asset_detail}}';
        //     $this->dropColumn($table, 'plan_date');
        //     $this->dropColumn($table, 'actual_date');
    }
}
