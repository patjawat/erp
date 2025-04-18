<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%development}}`.
 */
class m250418_051009_create_development_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%development}}', [
            'id' => $this->primaryKey(),
            'document_id' => $this->integer()->comment('ตามหนังสือ'),
            'development_type_id' => $this->string()->notNull()->comment('ประเภทการพัฒนา'),
            'topic' => $this->string()->notNull()->comment('หัวข้อ'),
            'description' => $this->text(),
            'location' => $this->string()->notNull()->comment('สถานที่ไป'),
            'location_org' => $this->string()->notNull()->comment('หน่วยงานที่จัด'),
            'province_name' => $this->string()->notNull()->comment('จังหวัด'),
            'status' => $this->string()->notNull()->notNull()->comment('สถานะ'),
            'vehicle_type' => $this->string()->notNull()->comment('พาหนะเดินทาง'),
            'claim_type' => $this->string()->notNull()->comment('การเบิกเงิน'),
            'time_slot' => $this->time()->comment('ช่วงเวลา'),
            'date_start' => $this->date()->notNull()->comment('ออกเดินทาง'),
            'time_start' => $this->string()->comment('เริ่มเวลา'),
            'date_end' => $this->date()->notNull()->comment('ถึงวันที่'),
            'time_end' => $this->string()->comment('ถึงเวลา'),
            'driver_id' => $this->string()->comment('พนักงานขับ'),
            'leader_id' => $this->string()->notNull()->comment('หัวหน้าฝ่าย'),
            'assigned_to' => $this->integer()->notNull()->comment('มอบหมายงานให้'),
            'emp_id' => $this->string()->notNull()->notNull()->comment('ผู้ขอ'),
            'data_json' => $this->json()->comment('ยานพาหนะ'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
            'deleted_at' => $this->dateTime()->comment('วันที่ลบ'),
            'deleted_by' => $this->integer()->comment('ผู้ลบ')
        ]);

        $sql = Yii::$app->db->createCommand("select * from categorise where name = 'development_type'")->queryAll();
        if(count($sql) < 1){
            $this->insert('categorise',['name'=>'development_type','code' =>'dev1','title'=>'เพื่อประชุมติดตามงาน/รับนโยบาย','active' => 1]);
            $this->insert('categorise',['name'=>'development_type','code' =>'dev2','title'=>'เพื่อประชุมวิชาการ/สัมมนา/ฝีกอบรม','active' => 1]);
            $this->insert('categorise',['name'=>'development_type','code' =>'dev3','title'=>'เพื่อเป็นวิทยากร','active' => 1]);
            $this->insert('categorise',['name'=>'development_type','code' =>'dev4','title'=>'เพื่อนำเสนอผลงาน/จัดนิทรรศการ','active' => 1]);
            $this->insert('categorise',['name'=>'development_type','code' =>'dev5','title'=>'เพื่อศึกษาดูงาน','active' => 1]);
            $this->insert('categorise',['name'=>'development_type','code' =>'dev6','title'=>'อื่นๆ','active' => 1]);
        } 

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%development}}');
    }
}
