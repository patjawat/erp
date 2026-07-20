<?php

use yii\db\Migration;

class m260718_000004_fix_challenge_timestamps extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%appreciation_challenge}}', 'created_at', $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'));
        $this->alterColumn('{{%appreciation_challenge}}', 'updated_at', $this->dateTime()->null()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'));
    }

    public function safeDown()
    {
        $this->alterColumn('{{%appreciation_challenge}}', 'created_at', $this->dateTime()->notNull());
        $this->alterColumn('{{%appreciation_challenge}}', 'updated_at', $this->dateTime()->null());
    }
}
