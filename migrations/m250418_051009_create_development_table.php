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
            'topic' => $this->string()->notNull()->comment('หัวข้อ'),
            'status' => $this->string()->notNull()->notNull()->comment('สถานะ'),
            'response_status' => $this->string()->comment('การตอบรับเป็นวิทยากร'),
            'thai_year' => $this->integer()->notNull()->comment('ปีงบประมาณ'),
            'date_start' => $this->date()->comment('วันที่เริ่ม'),
            'time_start' => $this->string()->comment('เริ่มเวลา'),
            'date_end' => $this->date()->comment('ถึงวันที่'),
            'time_end' => $this->string()->comment('ถึงเวลา'),
            'development_type_id' => $this->string()->comment('ประเภทการพัฒนา'),
            'vehicle_type_id' => $this->string()->comment('ยานพาหนะ'),
            'vehicle_date_start' => $this->date()->comment('วันออกเดินทาง'),
            'vehicle_date_end' => $this->date()->comment('วันกลับ'),
            'driver_id' => $this->string()->comment('พนักงานขับ'),
            'leader_id' => $this->string()->notNull()->comment('หัวหน้าฝ่าย'),
            'leader_group_id' => $this->string()->comment('หัวหน้ากลุ่มงาน'),
            'assigned_to' => $this->integer()->notNull()->comment('มอบหมายงานให้'),
            'emp_id' => $this->string()->notNull()->notNull()->comment('ผู้ขอ'),
            'data_json' => $this->json()->comment('JSON'),
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
        $sql1 = Yii::$app->db->createCommand("select * from categorise where name = 'development_level'")->queryAll();
        if(count($sql1) < 1){
            $this->insert('categorise',['name'=>'development_level','code' =>'dev_height','title'=>'ผู้บริหารระดับสูง','active' => 1]);
            $this->insert('categorise',['name'=>'development_level','code' =>'dev_medium','title'=>'ผู้บริหารระดับกลาง','active' => 1]);
            $this->insert('categorise',['name'=>'development_level','code' =>'dev_low','title'=>'ผู้บริหาระดับต้น','active' => 1]);
            $this->insert('categorise',['name'=>'development_level','code' =>'dev_none','title'=>'ไม่ระบุ','active' => 1]);
        } 

        $sql2 = Yii::$app->db->createCommand("select * from categorise where name = 'development_go_type'")->queryAll();
        if(count($sql2) < 1){
            $this->insert('categorise',['name'=>'development_go_type','code' =>'devgo1','title'=>'ขอไปเองผ่านการอนุมัติ','active' => 1]);
            $this->insert('categorise',['name'=>'development_go_type','code' =>'devgo2','title'=>'ไปตามแผนโรงพยาบาล','active' => 1]);
            $this->insert('categorise',['name'=>'development_go_type','code' =>'devgo3','title'=>'แผนกระทรวง เขต จังหวัด','active' => 1]);
        }

        $sql3 = Yii::$app->db->createCommand("select * from categorise where name = 'development_claim_type'")->queryAll();
        if(count($sql3) < 1){
            $this->insert('categorise',['name'=>'development_claim_type','code' =>'dev_claim_type1','title'=>'ไม่ประสงค์เบิกค่าใช้จ่าย','active' => 1]);
            $this->insert('categorise',['name'=>'development_claim_type','code' =>'dev_claim_type2','title'=>'เบิกจากเงินงบประมาณ','active' => 1]);
            $this->insert('categorise',['name'=>'development_claim_type','code' =>'dev_claim_type3','title'=>'เบิกจากผู้จัด','active' => 1]);

        }

        $sql4 = Yii::$app->db->createCommand("select * from categorise where name = 'vehicle_type'")->queryAll();
        if(count($sql4) < 1){
            $this->insert('categorise',['name'=>'vehicle_type','code' =>'official','title'=>'รถยนต์ราชการ','active' => 1]);
            $this->insert('categorise',['name'=>'vehicle_type','code' =>'personal','title'=>'รถยนต์ส่วนตัว','active' => 1]);
            $this->insert('categorise',['name'=>'vehicle_type','code' =>'ambulance','title'=>'รถพยาบาล','active' => 1]);
            $this->insert('categorise',['name'=>'vehicle_type','code' =>'charter_vehicle','title'=>'รถจ้างเหมา','active' => 1]);
            $this->insert('categorise',['name'=>'vehicle_type','code' =>'airplane','title'=>'เครื่องบิน','active' => 1]);
            $this->insert('categorise',['name'=>'vehicle_type','code' =>'train','title'=>'รถไฟ','active' => 1]);
            $this->insert('categorise',['name'=>'vehicle_type','code' =>'boat','title'=>'เรือ','active' => 1]);
            $this->insert('categorise',['name'=>'vehicle_type','code' =>'other','title'=>'อื่นๆ','active' => 1]);
        } 
        

        //ประเภทของค่าใช้จ่าย
        $sql5 = Yii::$app->db->createCommand("select * from categorise where name = 'expense_type'")->queryAll();
        if(count($sql5) < 1){
            $this->insert('categorise',['name'=>'expense_type','code' =>'ET1','title'=>'ค่าเบี้ยเลี้ยง','active' => 1]);
            $this->insert('categorise',['name'=>'expense_type','code' =>'ET2','title'=>'ค่าที่พัก','active' => 1]);
            $this->insert('categorise',['name'=>'expense_type','code' =>'ET3','title'=>'ค่ายานพาหนะ','active' => 1]);
            $this->insert('categorise',['name'=>'expense_type','code' =>'ET4','title'=>'ค่าลงทะเบียน','active' => 1]);
            $this->insert('categorise',['name'=>'expense_type','code' =>'ET5','title'=>'ค่าอาหารและอาหารว่าง','active' => 1]);
            $this->insert('categorise',['name'=>'expense_type','code' =>'ET6','title'=>'ค่าวิทยากร','active' => 1]);
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
