<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%project_details}}`.
 */
class m250110_144930_create_project_details_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%project_details}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->comment('ชื่อของการก็บข้อมูลเพิ่มเติมที่เกี่ยวข้องกับโปรเจกต์'),
            'emp_id' => $this->integer()->comment('ไอดีของผู้ใช้งาน ซึ่งเชื่อมโยงกับตาราง users'),
            'project_id' => $this->integer()->comment('ไอดีของโปรเจกต์ ซึ่งเชื่อมโยงกับตาราง projects'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%project_details}}');
    }
}
