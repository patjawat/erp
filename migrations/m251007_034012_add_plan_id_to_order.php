<?php

use yii\db\Migration;

class m251007_034012_add_plan_id_to_order extends Migration
{
    /**
     * {@inheritdoc}
     */
       public function safeUp()
    {
        $table = '{{%orders}}';
        $this->addColumn($table, 'plan_group_id', $this->string(100)->comment('ประเภทเผน')->after('id')); 
        $this->addColumn($table, 'plan_type_id', $this->string(100)->comment('ประเภท')->after('plan_group_id')); 
        $this->addColumn($table, 'plan_category_id', $this->string(100)->comment('หมวดของแผน')->after('plan_type_id')); 
        $this->addColumn($table, 'plan_item_id', $this->string(100)->comment('ชื่อของแผน')->after('plan_category_id')); 
        $this->addColumn($table, 'plan_order_id', $this->integer()->comment('เชื่อมกับแผน')->after('plan_item_id')); 
    }
    
    public function safeDown()
    {
        $table = '{{%orders}}';
        $this->dropColumn($table, 'plan_group_id');
        $this->dropColumn($table, 'plan_type_id');
        $this->dropColumn($table, 'plan_category_id');
        $this->dropColumn($table, 'plan_item_id');
        $this->dropColumn($table, 'plan_order_id');
    }

 
}
