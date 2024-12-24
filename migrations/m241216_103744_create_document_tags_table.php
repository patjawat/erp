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
            'name' => $this->string()->comment('ชื่อการ tags เอกสาร'),
            'doc_number' => $this->string()->comment('เลขที่หนังสือ'),
            'doc_regis_number' => $this->string()->comment('เลขรับ'),
            'emp_id' => $this->string(),
            'department_id' => $this->string(),
            'document_org_id' => $this->string()->comment('จากหน่วยงาน'),
            'data_json' => $this->json(),
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
