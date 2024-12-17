<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%documents}}`.
 */
class m241212_150029_create_documents_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%documents}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'doc_number' => $this->string()->comment('เลขที่หนังสือ'),
            'topic' => $this->text()->comment('ชื่อเรื่อง'),
            'doc_type_id' => $this->string()->comment('ประเภทหนังสือ'),
            'document_org_id' => $this->string()->comment('จากหน่วยงาน'),
            'thai_year' => $this->string()->comment('ปี พ.ศ.'),
            'doc_regis_number' => $this->string()->comment('เลขรับ'),
            'urgent' => $this->string()->comment('ชั้นความเร็ว'),
            'secret' => $this->string()->comment('ชั้นความลับ'),
            'doc_date' => $this->string()->comment('วันที่หนังสือ'),
            'doc_expire' => $this->string()->comment('วันหมดอายุ'),
            'doc_receive' => $this->string()->comment('ลงวันรับเข้า'),
            'doc_time' => $this->string()->comment('เวลารับ'),
            'data_json' => $this->json(),
        ]);  

        $sql = Yii::$app->db->createCommand("select * from categorise where name = 'document_type'")->queryAll();
        if(count($sql) < 1){
        $this->insert('categorise', ['code' => 'inbox', 'name' => 'document_type', 'title' => 'หนังสือเข้า']);
        $this->insert('categorise', ['code' => 'received', 'name' => 'document_type', 'title' => 'หนังสือรับ']);
        $this->insert('categorise', ['code' => 'send', 'name' => 'document_type', 'title' => 'หนังสือส่ง']);
        $this->insert('categorise', ['code' => 'inside', 'name' => 'document_type', 'title' => 'หนังสือภายใน']);
        }

        $sqlUrgent = Yii::$app->db->createCommand("select * from categorise where name = 'urgent'")->queryAll();
        if(count($sqlUrgent) < 1){
        $this->insert('categorise', ['code' => 'ปกติ', 'name' => 'urgent', 'title' => 'ปกติ']);
        $this->insert('categorise', ['code' => 'ด่วน', 'name' => 'urgent', 'title' => 'ด่วน']);
        $this->insert('categorise', ['code' => 'ด่วนมาก', 'name' => 'urgent', 'title' => 'ด่วนมาก']);
        $this->insert('categorise', ['code' => 'ด่วนที่สุด', 'name' => 'urgent', 'title' => 'ด่วนที่สุด']);
        }

        
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%documents}}');
    }
}
