<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%leave_types}}`.
 */
class m240911_074740_create_leave_types_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // $this->createTable('{{%leave_types}}', [
        //     'id' => $this->primaryKey(),
        //     'name' =>  $this->string(255)->comment('ชื่อการลา'),
        //     'max_days' =>  $this->string(255)->comment('จำนวนวันที่สามารถลาได้สูงสุด'),
        //     'data_json' => $this->json(),
        //     'active' => $this->boolean(),
        //     'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
        //     'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
        //     'created_by' => $this->integer()->comment('ผู้สร้าง'),
        //     'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
        //     'deleted_at' => $this->dateTime()->comment('วันที่ลบ'),
        //     'deleted_by' => $this->integer()->comment('ผู้ลบ')
        // ]);

        $sql1 = Yii::$app->db->createCommand("select * from categorise where name = 'leave_type'")->queryAll();
        if(count($sql1) < 1){
            $this->insert('categorise',['name'=>'leave_type','code' => 'LT1','title'=>'ลาป่วย','active' => 1]);
            $this->insert('categorise',['name'=>'leave_type','code' => 'LT2','title'=>'ลาคลอดบุตร','active' => 1]);
            $this->insert('categorise',['name'=>'leave_type','code' => 'LT3','title'=>'ลากิจ','active' => 1]);
            $this->insert('categorise',['name'=>'leave_type','code' => 'LT4','title'=>'ลาพักผ่อน','active' => 1]);
            $this->insert('categorise',['name'=>'leave_type','code' => 'LT5','title'=>'ลาอุปสมบท','active' => 1]);
            $this->insert('categorise',['name'=>'leave_type','code' => 'LT6','title'=>'ลาช่วยภริยาคลอด','active' => 1]);
            $this->insert('categorise',['name'=>'leave_type','code' => 'LT7','title'=>'ลาเกณฑ์ทหาร','active' => 1]);
            $this->insert('categorise',['name'=>'leave_type','code' => 'LT8','title'=>'ลาศึกษา ฝึกอบรม','active' => 1]);
            $this->insert('categorise',['name'=>'leave_type','code' => 'LT9','title'=>'ลาทำงานต่างประเทศ','active' => 1]);
            $this->insert('categorise',['name'=>'leave_type','code' => 'LT10','title'=>'ลาติดตามคู่สมรส','active' => 1]);
            $this->insert('categorise',['name'=>'leave_type','code' => 'LT11','title'=>'ลาฟื้นฟูอาชีพ','active' => 1]);
            $this->insert('categorise',['name'=>'leave_type','code' => 'LT12','title'=>'ลาออก','active' => 1]);
        }   

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // $this->dropTable('{{%leave_types}}');
    }
}
