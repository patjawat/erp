<?php

use yii\db\Migration;

class m251221_052520_add_hash_cid extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
            $table = '{{%user}}';
        $this->addColumn($table, 'hash_cid', $this->string(100)->comment('เข้ารัหัสบัตรประชาชน')->after('password_reset_token')); 
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
       $table = '{{%user}}';
        $this->dropColumn($table, 'hash_cid');
    }

}
