<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%vehicle}}`.
 */
class m250405_094239_create_vehicle_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%vehicle}}', [
           'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'code' => $this->string(255)->notNull()->comment('รหัส'),
            'thai_year' => $this->integer(255)->notNull()->comment('ปีงบประมาณ'),
            'car_type_id' => $this->string()->notNull()->comment('ประเภทของรถ general หรือ ambulance'),
            'refer_type' => $this->string()->notNull()->comment('ประเภทของการ refer รถพยาบาล refer,ems,normal'),
            'go_type' => $this->integer()->notNull()->comment('ประเภทการเดินทาง 1 = ไปกลับ, 2 = ค้างคืน'),
            'oil_price' => $this->double(255)->comment('น้ำมันที่เติม'),
            'oil_liter' => $this->double(255)->comment('ปริมาณน้ำมัน'),
            'document_id' => $this->integer()->comment('ตามหนังสือ'),
            'owner_id' => $this->integer()->comment('ผู้ดูแลห้องประชุม'),
            'urgent' => $this->string()->notNull()->comment('ความเร่งด่วน'),
            'license_plate' => $this->string()->comment('ทะเบียนยานพาหนะ'),
            'location' => $this->string()->notNull()->comment('สถานที่ไป'),
            'reason' => $this->string()->notNull()->comment('เหตุผล'),
            'status' => $this->string()->notNull()->notNull()->comment('สถานะ'),
            'date_start' => $this->date()->notNull()->comment('เริ่มวันที่'),
            'time_start' => $this->string()->notNull()->comment('เริ่มเวลา'),
            'date_end' => $this->date()->notNull()->comment('ถึงวันที่'),
            'time_end' => $this->string()->notNull()->comment('ถึงเวลา'),
            'driver_id' => $this->string()->comment('พนักงานขับ'),
            'leader_id' => $this->string()->notNull()->comment('หัวหน้างานรับรอง'),
            'emp_id' => $this->string()->notNull()->notNull()->comment('ผู้ขอ'),
            'data_json' => $this->json()->comment('ยานพาหนะ'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
            'deleted_at' => $this->dateTime()->comment('วันที่ลบ'),
            'deleted_by' => $this->integer()->comment('ผู้ลบ')
        ]);

        $sql1 = Yii::$app->db->createCommand("select * from categorise where name = 'vehicle_status'")->queryAll();
        if(count($sql1) < 1){
            $this->insert('categorise',['name'=>'vehicle_status','code' =>'Pending','title'=>'รอดำเนินการ','active' => 1]);
            $this->insert('categorise',['name'=>'vehicle_status','code' =>'Pass','title'=>'จัดสรร','active' => 1]);
            $this->insert('categorise',['name'=>'vehicle_status','code' =>'Approve','title'=>'ผอ.อนุมัติ','active' => 1]);
            $this->insert('categorise',['name'=>'vehicle_status','code' =>'Reject','title'=>'ปฏิเสธ','active' => 1]);
            $this->insert('categorise',['name'=>'vehicle_status','code' =>'Cancel','title'=>'ยกเลิก','active' => 1]);
        } 
        $sql2 = Yii::$app->db->createCommand("select * from categorise where name = 'refer_type'")->queryAll();
        if(count($sql2) < 1){
            $this->insert('categorise',['name'=>'refer_type','code' =>'normal','title'=>'รับ-ส่ง [ไม่ฉุกเฉิน]','active' => 1]);
            $this->insert('categorise',['name'=>'refer_type','code' =>'refer','title'=>'EMS','active' => 1]);
            $this->insert('categorise',['name'=>'refer_type','code' =>'ems','title'=>'REFER','active' => 1]);
        } 

        $sql3 = Yii::$app->db->createCommand("select * from categorise where name = 'vehicle_detail_status'")->queryAll();
        if(count($sql3) < 1){
            $this->insert('categorise',['name'=>'vehicle_detail_status','code' =>'Pass','title'=>'จัดสรร','active' => 1]);
            $this->insert('categorise',['name'=>'vehicle_detail_status','code' =>'Success','title'=>'สําเร็จ','active' => 1]);
        } 


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%vehicle}}');
    }
}
