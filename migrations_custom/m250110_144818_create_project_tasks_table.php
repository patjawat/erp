<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%project_tasks}}`.
 */
class m250110_144818_create_project_tasks_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%project_tasks}}', [
            'id' => $this->primaryKey(),
            'task_name' => $this->string(255)->notNull()->comment('ชื่องาน'),
            'data_json' => $this->json(),
            'active' => $this->boolean()->defaultValue(1)->comment('สถานะการใช้งาน')
        ]);
        $this->insert('project_tasks',['id'=>1,'task_name' => 'Todos','active' => 1]);
        $this->insert('project_tasks',['id'=>2,'task_name' => 'Pending','active' => 1]);
        $this->insert('project_tasks',['id'=>3,'task_name' => 'Inprogress','active' => 1]);
        $this->insert('project_tasks',['id'=>4,'task_name' => 'Completed','active' => 1]);
        $this->insert('project_tasks',['id'=>5,'task_name' => 'On-hold','active' => 1]);
        $this->insert('project_tasks',['id'=>6,'task_name' => 'Review','active' => 1]);
        $this->insert('project_tasks',['id'=>7,'task_name' => 'Success','active' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%project_tasks}}');
    }
}
