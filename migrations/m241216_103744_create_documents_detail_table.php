<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%document_tags}}`.
 */
class m241216_103744_create_documents_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%documents_detail}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'name' => $this->string()->comment('ชื่อการ tags เอกสาร เช่น  employee,department'),
            'document_id' => $this->string(255)->comment('เอกสาร'),
            'to_id' => $this->string(255)->comment('ถึงบุคลากรหรือหน่วยงาน ระบุเป็นเลข id ของบุคลากรหรือหน่วยงาน'),
            'to_name' => $this->string()->comment('ถึงบุคลากรหรือหน่วยงาน ระบุเป็นชื่อของบุคลากรหรือหน่วยงาน'),
            'to_type' => $this->string()->comment('ถึงบุคลากรหรือหน่วยงาน ระบุเป็นประเภทของบุคลากรหรือหน่วยงาน'),
            'from_id' => $this->string(255)->comment('จากบุคลากรหรือหน่วยงาน ระบุเป็นเลข id ของบุคลากรหรือหน่วยงาน'),
            'from_name' => $this->string()->comment('จากบุคลากรหรือหน่วยงาน ระบุเป็นชื่อของบุคลากรหรือหน่วยงาน'),
            'from_type' => $this->string()->comment('จากบุคลากรหรือหน่วยงาน ระบุเป็นประเภทของบุคลากรหรือหน่วยงาน'),
            'data_json' => $this->json(),
            'tags_department' => $this->json(),
            'tags_employee' => $this->json(),
            'doc_read' => $this->dateTime()->comment('การเปิดอ่าน'),
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
        $this->dropTable('{{%documents_detail}}');
    }
}
