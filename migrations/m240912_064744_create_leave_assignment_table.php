<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%leave_assignment}}`.
 */
class m240912_064744_create_leave_assignment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%leave_assignment}}', [
            'id' => $this->primaryKey(),
          
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%leave_assignment}}');
    }
}
