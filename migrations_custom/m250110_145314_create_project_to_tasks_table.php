<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%project_to_tasks}}`.
 */
class m250110_145314_create_project_to_tasks_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%project_to_tasks}}', [
            'project_id' => $this->integer()->notNull(),
            'task_id' => $this->integer()->notNull(),
            'PRIMARY KEY(project_id, task_id)', // กำหนดคีย์หลักแบบ 
        ]);
        // / เพิ่ม Foreign Key สำหรับ project_id
        $this->addForeignKey(
            'fk-project_to_task-project_id',
            '{{%project_to_tasks}}',
            'project_id',
            '{{%projects}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // เพิ่ม Foreign Key สำหรับ task_id
        $this->addForeignKey(
            'fk-project_to_task-task_id',
            '{{%project_to_tasks}}',
            'task_id',
            '{{project_tasks}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%project_to_tasks}}');
    }
}
