<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%employee_detail}}`.
 */
class m230910_100543_create_employee_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%employee_detail}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'emp_id' => $this->integer(),
            'name' => $this->string(),
            'data_json' => $this->json(),
            'updated_at' => $this->dateTime(),
            'created_at' => $this->dateTime(),   
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%employee_detail}}');
    }
}
