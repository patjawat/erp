<?php

use yii\db\Migration;

class m260125_144943_add_send_org extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%documents}}', 'send_org', $this->integer()->comment('ส่งหน่วยงานภายนอก')->after('data_json'));
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
        echo "m260125_144943_add_send_org cannot be reverted.\n";

        return false;
    }
    */
}
