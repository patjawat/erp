<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%team_group_detail}}`.
 */
class m250414_085545_create_team_group_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%team_group_detail}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->comment('ชื่อ'),
            'category_id' => $this->string()->notNull()->comment('เชื่อมกับข้อมูลกลัก'),
            'title' => $this->string()->comment('ชื่อรายการ'),
            'thai_year' => $this->integer()->notNull()->comment('ปี'),
            'emp_id' => $this->integer()->comment('รหัสพนักงาน'),
            'document_id' => $this->integer()->comment('รหัสเอกสาร'),
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
        $this->dropTable('{{%team_group_detail}}');
    }
}
