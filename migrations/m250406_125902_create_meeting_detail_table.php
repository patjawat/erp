<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%meeting_detail}}`.
 */
class m250406_125902_create_meeting_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%meeting_detail}}', [
            'id' => $this->primaryKey(),
            'meeting_id' => $this->integer()->notNull()->comment('ID ของการประชุม'),
            'data_json' => $this->json()->comment('json'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
            'deleted_at' => $this->dateTime()->comment('วันที่ลบ'),
            'deleted_by' => $this->integer()->comment('ผู้ลบ')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%meeting_detail}}');
    }
}
