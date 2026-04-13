<?php

use yii\db\Migration;

class m260412_134417_add_telegram_id_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
{
    $this->addColumn('{{%user}}', 'telegram_id', $this->bigInteger()->unique()->after('username'));
}

public function safeDown()
{
    $this->dropColumn('{{%user}}', 'telegram_id');
}

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260412_134417_add_telegram_id_to_user_table cannot be reverted.\n";

        return false;
    }
    */
}
