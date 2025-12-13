<?php

use yii\db\Migration;

class m251213_121754_add_asset_id_to_asset_detail extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%asset_detail}}', 'asset_id', $this->integer()->after('id')->notNull());
    }
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
       $this->dropColumn('{{%asset_detail}}', 'asset_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251213_121754_add_asset_id_to_asset_detail cannot be reverted.\n";

        return false;
    }
    */
}
