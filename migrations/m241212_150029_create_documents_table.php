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
            'document_group' => $this->string()->comment('หมวดหนังสือ'),
            'document_type' => $this->string()->comment('ประเภทหนังสือ'),
            'document_org' => $this->string()->comment('จากหน่วยงาน'),
            'thai_year' => $this->string()->comment('ปี พ.ศ.'),
            'doc_regis_number' => $this->string()->comment('เลขรับ'),
            'doc_speed' => $this->string()->comment('ชั้นความเร็ว'),
            'secret' => $this->string()->comment('ชั้นความลับ'),
            'doc_date' => $this->string()->comment('วันที่หนังสือ'),
            'doc_expire' => $this->string()->comment('วันหมดอายุ'),
            'doc_receive_date' => $this->string()->comment('ลงวันรับเข้า'),
            'req_approve' => $this->boolean()->defaultValue(true)->comment('เสนอผู้อำนวยการ'),
            'doc_time' => $this->string()->comment('เวลารับ'),
            'status' => $this->string()->comment('สถานะ'),
            'data_json' => $this->json(),
            'view_json' => $this->json(),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
            'deleted_at' => $this->dateTime()->comment('วันที่ลบ'),
            'deleted_by' => $this->integer()->comment('ผู้ลบ')
        ]);  

        $sql = Yii::$app->db->createCommand("select * from categorise where name = 'document_type'")->queryAll();
        if(count($sql) < 1){
        $this->insert('categorise', ['code' => 'DT1', 'name' => 'document_type', 'title' => 'หนังสือภายนอก']);
        $this->insert('categorise', ['code' => 'DT2', 'name' => 'document_type', 'title' => 'หนังสือภายใน']);
        $this->insert('categorise', ['code' => 'DT3', 'name' => 'document_type', 'title' => 'หนังสือประทับตรา']);
        $this->insert('categorise', ['code' => 'DT4', 'name' => 'document_type', 'title' => 'หนังสือสั่งการ']);
        $this->insert('categorise', ['code' => 'DT5', 'name' => 'document_type', 'title' => 'หนังสือประชาสัมพันธ์']);
        $this->insert('categorise', ['code' => 'DT6', 'name' => 'document_type', 'title' => 'หนังสือที่เจ้าหน้าที่ทำขึ้นหรือรับไว้เป็นหลักฐานในราชการ']);
        $this->insert('categorise', ['code' => 'DT7', 'name' => 'document_type', 'title' => 'หนังสือวิทยุ']);
        $this->insert('categorise', ['code' => 'DT8', 'name' => 'document_type', 'title' => 'หนังสือขอประวัติการรักษาพยาบาล']);
        $this->insert('categorise', ['code' => 'DT9', 'name' => 'document_type', 'title' => 'หนังสือคำสั่ง']);
        }

        $sqlUrgent = Yii::$app->db->createCommand("select * from categorise where name = 'document_group'")->queryAll();
        if(count($sqlUrgent) < 1){
        $this->insert('categorise', ['code' => 'receive', 'name' => 'document_group', 'title' => 'หนังสือรับ']);
        $this->insert('categorise', ['code' => 'send', 'name' => 'document_group', 'title' => 'หนังสือส่ง']);
        }

        $sqlUrgent = Yii::$app->db->createCommand("select * from categorise where name = 'urgent'")->queryAll();
        if(count($sqlUrgent) < 1){
        $this->insert('categorise', ['code' => 'ปกติ', 'name' => 'urgent', 'title' => 'ปกติ']);
        $this->insert('categorise', ['code' => 'ด่วน', 'name' => 'urgent', 'title' => 'ด่วน']);
        $this->insert('categorise', ['code' => 'ด่วนมาก', 'name' => 'urgent', 'title' => 'ด่วนมาก']);
        $this->insert('categorise', ['code' => 'ด่วนที่สุด', 'name' => 'urgent', 'title' => 'ด่วนที่สุด']);
        }

        $sqlUrgent = Yii::$app->db->createCommand("select * from categorise where name = 'document_secret'")->queryAll();
        if(count($sqlUrgent) < 1){
        $this->insert('categorise', ['code' => 'ปกติ', 'name' => 'document_secret', 'title' => 'ปกติ']);
        $this->insert('categorise', ['code' => 'ลับ', 'name' => 'document_secret', 'title' => 'ลับ']);
        $this->insert('categorise', ['code' => 'ลับมาก', 'name' => 'document_secret', 'title' => 'ลับมาก']);
        $this->insert('categorise', ['code' => 'ลับที่สุด', 'name' => 'document_secret', 'title' => 'ลับที่สุด']);
        }

        $sqlUrgent = Yii::$app->db->createCommand("select * from categorise where name = 'document_status'")->queryAll();
        if(count($sqlUrgent) < 1){
        $this->insert('categorise', ['code' => 'DS1', 'name' => 'document_status', 'title' => 'ลงทะเบียนรับ']);
        $this->insert('categorise', ['code' => 'DS2', 'name' => 'document_status', 'title' => 'ส่งหน่วยงาน']);
        $this->insert('categorise', ['code' => 'DS3', 'name' => 'document_status', 'title' => 'เสนอ ผอ.']);
        $this->insert('categorise', ['code' => 'DS4', 'name' => 'document_status', 'title' => 'ผอ.ลงนาม']);
        $this->insert('categorise', ['code' => 'DS5', 'name' => 'document_status', 'title' => 'อ่าน']);
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
