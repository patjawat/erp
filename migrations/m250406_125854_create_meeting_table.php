<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%meeting}}`.
 */
class m250406_125854_create_meeting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%meeting}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'code' => $this->string(255)->comment('รหัส'),
            'room_id' => $this->string(255)->notNull()->comment('รหัส'),
            'title' => $this->string()->notNull()->comment('หัวข้อการประชุ'),
            'date_start' => $this->date()->notNull()->comment('เริ่มวันที่'),
            'date_end' => $this->date()->comment('ถึงวันที่'),
            'time_start' => $this->string()->notNull()->comment('เริ่มเวลา'),
            'time_end' => $this->string()->notNull()->comment('ถึงเวลา'),
            'thai_year' => $this->integer(255)->notNull()->comment('ปีงบประมาณ'),
            'document_id' => $this->integer()->comment('ตามหนังสือ'),
            'emp_number' => $this->integer()->comment('จำนวนผู้เข้าร่วมประชุม'),
            'urgent' => $this->string()->notNull()->comment('ความเร่งด่วน'),
            'status' => $this->string()->notNull()->notNull()->comment('สถานะ'),
            'emp_id' => $this->string()->notNull()->notNull()->comment('ผู้ขอ'),
            'data_json' => $this->json()->comment('json'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
            'deleted_at' => $this->dateTime()->comment('วันที่ลบ'),
            'deleted_by' => $this->integer()->comment('ผู้ลบ')
        ]);

        $sql1 = Yii::$app->db->createCommand("select * from categorise where name = 'meeting_status'")->queryAll();
        if(count($sql1) < 1){
            $this->insert('categorise',['name'=>'meeting_status','code' =>'Pending','title'=>'รอการอนุมัติ','active' => 1]);
            $this->insert('categorise',['name'=>'meeting_status','code' =>'Pass','title'=>'อนุมัติแล้ว','active' => 1]);
            $this->insert('categorise',['name'=>'meeting_status','code' =>'Approve','title'=>'ผอ.อนุมัติ','active' => 1]);
            $this->insert('categorise',['name'=>'meeting_status','code' =>'Reject','title'=>'ปฏิเสธ','active' => 1]);
            $this->insert('categorise',['name'=>'meeting_status','code' =>'Cancel','title'=>'ยกเลิก','active' => 1]);
        } 

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%meeting}}');
    }
}
