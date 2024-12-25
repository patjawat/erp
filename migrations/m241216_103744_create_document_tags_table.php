<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%document_tags}}`.
 */
class m241216_103744_create_document_tags_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%document_tags}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'document_id' => $this->string(255),
            'name' => $this->string()->comment('ชื่อการ tags เอกสาร'),
            'doc_number' => $this->string()->comment('เลขที่หนังสือ'),
            'doc_regis_number' => $this->string()->comment('เลขรับ'),
            'emp_id' => $this->string(),
            'status' => $this->string()->comment('สถานะ'),
            'department_id' => $this->string(),
            'document_org_id' => $this->string()->comment('จากหน่วยงาน'),
            'data_json' => $this->json(),
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
        $this->dropTable('{{%document_tags}}');
    }
}
