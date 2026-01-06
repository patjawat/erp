<?php

use yii\db\Migration;

class m260106_100840_add_status_to_asset_detail extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = '{{%asset_detail}}';
        $this->addColumn($table, 'status', $this->string(255)->comment('สถานะ')->after('id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
       

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260106_100840_add_status_to_asset_detail cannot be reverted.\n";

        return false;
    }
    */
}
