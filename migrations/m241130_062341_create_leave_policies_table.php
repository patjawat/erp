<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%leave_role}}`.
 */
class m241130_062341_create_leave_policies_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%leave_policies}}', [
            'id' => $this->primaryKey(),
            'position_type_id' => $this->string()->comment('ประเภทตำแหน่ง'),
            'leave_type_id' => $this->string()->comment('ประเภทการลา'),
            'month_of_service' => $this->integer()->notNull()->defaultValue(0)->comment('เดือนที่มีสิทธิลา'),
            'year_of_service' => $this->integer()->notNull()->defaultValue(0)->comment('ปีที่มีสิทธิลา'),
            'days' => $this->integer()->notNull()->comment('สิทธิวันลา'),
            'max_days' => $this->integer()->notNull()->comment('วันลาสะสมสูงสุด'),
            'accumulation' => $this->boolean()->notNull()->comment('สิทธิสะสมวันลา'),
            'additional_rules' => $this->string(255)->comment('กฎเพิ่มเติม เช่น สิทธิสะสมเมื่อครบ 10 ปี'),
            'emp_id' => $this->string(255)->comment('พนักงาน'),
            'leave_before_days' => $this->boolean()->comment('จำนวนวันลาสะสม'),
            'data_json' => $this->json(),
            'thai_year' => $this->integer(255)->comment('ปีงบประมาณ'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
            'deleted_at' => $this->dateTime()->comment('วันที่ลบ'),
            'deleted_by' => $this->integer()->comment('ผู้ลบ')
        ]);

        $sql1 = Yii::$app->db->createCommand('select * from leave_policies')->queryAll();
        if (count($sql1) < 1) {
            // ข้าราชการ = PT1
            $this->insert('leave_policies', ['position_type_id' => 'PT1', 'leave_type_id' => 'LT1','month_of_service' => 0,'year_of_service' => 1, 'days' => 10,'max_days' => 20,'accumulation' => true,'data_json' => ['position_type_name' => 'ข้าราชการ'], 'additional_rules' => 'สะสมได้ถึง 20 วันเมื่อทำงานครบ 10 ปี']);
            $this->insert('leave_policies', ['position_type_id' => 'PT1', 'leave_type_id' => 'LT1','month_of_service' => 0,'year_of_service' => 10,'days' => 10,'max_days' => 30,'accumulation' => true, 'additional_rules' => '']);
            // พนักงานราชการ = PT2
            $this->insert('leave_policies', ['position_type_id' => 'PT1', 'leave_type_id' => 'LT1','month_of_service' => 0,'year_of_service' => 1, 'days' => 10, 'max_days' => 20,'accumulation' => true, 'additional_rules' => '']);
            $this->insert('leave_policies', ['position_type_id' => 'PT1', 'leave_type_id' => 'LT1','month_of_service' => 0,'year_of_service' => 0, 'days' => 10, 'max_days' => 30,'accumulation' => true, 'additional_rules' => '']);
            // พนักงานกระทรวง (พกส.) = PT3
            $this->insert('leave_policies', ['position_type_id' => 'PT1', 'leave_type_id' => 'LT1','month_of_service' => 6,'year_of_service' => 1, 'days' => 10, 'max_days' => 15,'accumulation' => false, 'additional_rules' => '']);
            // ลูกจ้างชั่วคราวรายเดือน = PT4
            $this->insert('leave_policies', ['position_type_id' => 'PT1', 'leave_type_id' => 'LT1','month_of_service' => 6,'year_of_service' => 0.5, 'days' => 10, 'max_days' => 0,'accumulation' => false, 'additional_rules' => '']);
            // จ้างเหมาบริการรายวัน = PT7
            $this->insert('leave_policies', ['position_type_id' => 'PT1', 'leave_type_id' => 'LT1','month_of_service' => 6,'year_of_service' => 0.5, 'days' => 10, 'max_days' => 0,'accumulation' => false, 'additional_rules' => '']);
           

        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%leave_policies}}');
    }
}
