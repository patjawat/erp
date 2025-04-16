<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%team_group}}`.
 */
class m250414_084858_create_team_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%team_group}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull()->comment('ชื่อกลุ่ม'),
            'description' => $this->text()->comment('รายละเอียด'),
            'data_json' => $this->json()->comment('json'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
            'status' => $this->smallInteger()->comment('สถานะ'),
            'deleted_at' => $this->smallInteger()->comment('ลบ'),
            'deleted_by' => $this->integer()->comment('ลบโดย'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%team_group}}');
    }
}
