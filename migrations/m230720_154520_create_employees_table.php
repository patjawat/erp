<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%employees}}`.
 */
class m230720_154520_create_employees_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%employees}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'ref' => $this->string(255),
            'avatar' => $this->string(255),
            'photo' => $this->binary(4294967295),
            'phone' => $this->string(20),
            'cid' => $this->string(17)->comment('เลขบัตรประชาชน'),
            'email' => $this->string(255),
            'gender' => $this->string(20)->comment('เพศ'),
            'prefix' => $this->string(20)->comment('คำนำหน้า'),
            'fname' => $this->string(200)->notNull()->comment('ชื่อ'),
            'lname' => $this->string(200)->notNull()->comment('นามสกุล'),
            'fname_en' => $this->string(200)->comment('ชื่อ(TH)'),
            'lname_en' => $this->string(200)->comment('นามสกุล(EN)'),
            'birthday' => $this->date()->comment('วันเกิด'),
            'join_date' => $this->date()->comment('เริ่มงาน'),
            'end_date' => $this->date()->comment('ทำงานวันสุดท้าย'),
            'address' => $this->string()->comment('ที่อยู่'),
            'province' => $this->integer()->comment('จังหวัด'),
            'amphure' => $this->integer()->comment('อำเภอ'),
            'district' => $this->integer()->comment('ตำบล'),
            'zipcode' => $this->integer()->comment('รหัสไปรษณีย์'),
            'position_group' => $this->string(100)->comment('ประเภท/กลุ่มงาน'),
            'expertise' => $this->integer()->comment('ความเชี่ยวชาญ'),
            'position_name' => $this->string(100)->comment('ตำแหน่ง'),
            'position_type' => $this->string(100)->comment('ตำแหน่ง'),
            'position_level' => $this->string(100)->comment('ตำแหน่ง'),
            'position_number' => $this->string(100)->comment('ตำแหน่ง'),
            'position_manage' => $this->integer()->comment('ตำแหน่งบริหาร'),
            'education' => $this->integer()->comment('การศึกษา'),
            'department' => $this->integer()->comment('แผนก/ฝ่าย'),
            'salary' => $this->integer()->comment('เงินเดือน'),
            'status' => $this->integer()->comment('สถานะ'),
            'data_json' => $this->json(),
            'emergency_contact' => $this->json()->comment('ติดต่อในกรณีฉุกเฉิน'),
            'updated_at' => $this->dateTime()->comment('วันเวลาแก้ไข'),
            'created_at' => $this->dateTime()->comment('วันเวลาสร้าง'),  
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%employees}}');
    }
}
