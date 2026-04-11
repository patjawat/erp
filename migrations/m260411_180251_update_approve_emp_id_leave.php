<?php

use yii\db\Migration;

class m260411_180251_update_approve_emp_id_leave extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            UPDATE approve a1
            JOIN approve a2 
                ON a2.from_id = a1.from_id 
                AND a2.level = 2
            SET a1.emp_id = a2.emp_id
            WHERE a1.name = 'leave'
            AND a1.emp_id IS NULL
            AND a1.level = 1
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260411_180251_update_approve_emp_id_leave cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260411_180251_update_approve_emp_id_leave cannot be reverted.\n";

        return false;
    }
    */
}
