<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%visit_counter}}`.
 */
class m230705_084634_create_visit_counter_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%visit_counter}}', [
            'id' => $this->primaryKey(),
            'counter' => $this->integer(),
            'ip' => $this->string(),
            'vdate' => $this->date(),
            'device' => $this->string(),
            'device_name' => $this->string(),
            'user_id' => $this->integer(),
            'created_at' => $this->timestamp(),   
          'updated_at' => $this->timestamp()->defaultValue(null)->append('ON UPDATE CURRENT_TIMESTAMP'),

        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%visit_counter}}');
    }
}
