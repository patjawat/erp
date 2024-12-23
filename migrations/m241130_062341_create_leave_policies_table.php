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
            'month_of_service' => $this->double()->notNull()->defaultValue(0)->comment('เดือนที่มีสิทธิลา'),
            'year_of_service' => $this->double()->notNull()->defaultValue(0)->comment('ปีที่มีสิทธิลา'),
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
            $this->insert('leave_policies', ['position_type_id' => 'PT1', 'leave_type_id' => 'LT1','month_of_service' => 0,'year_of_service' => 1,'days' => 10,'max_days' => 20,'accumulation' => true,'data_json' => ['position_type_name' => 'ข้าราชการ'], 'additional_rules' => 'สะสมได้ถึง 20 วันเมื่อทำงานครบ 10 ปี']);
            $this->insert('leave_policies', ['position_type_id' => 'PT1', 'leave_type_id' => 'LT1','month_of_service' => 0,'year_of_service' => 10,'days' => 10,'max_days' => 30,'accumulation' => true, 'additional_rules' => '']);
            //ลูกจ้างประจำ = PT6
            $this->insert('leave_policies', ['position_type_id' => 'PT6', 'leave_type_id' => 'LT1','month_of_service' => 0,'year_of_service' => 1,'days' => 10, 'max_days' => 20,'accumulation' => true, 'additional_rules' => '']);
            $this->insert('leave_policies', ['position_type_id' => 'PT6', 'leave_type_id' => 'LT1','month_of_service' => 0,'year_of_service' => 10,'days' => 10, 'max_days' => 30,'accumulation' => true, 'additional_rules' => '']);

            // พนักงานราชการ = PT2
            $this->insert('leave_policies', ['position_type_id' => 'PT2', 'leave_type_id' => 'LT1','month_of_service' => 0,'year_of_service' => 1,'days' => 10, 'max_days' => 15,'accumulation' => true, 'additional_rules' => '']);
            // พนักงานกระทรวง (พกส.) = PT3
            $this->insert('leave_policies', ['position_type_id' => 'PT3', 'leave_type_id' => 'LT1','month_of_service' => 0,'year_of_service' => 0,'days' => 10, 'max_days' => 15,'accumulation' => true, 'additional_rules' => '']);

            // ลูกจ้างชั่วคราวรายเดือน = PT4
            $this->insert('leave_policies', ['position_type_id' => 'PT4', 'leave_type_id' => 'LT1','month_of_service' => 6,'year_of_service' => 0.5,'days' => 10, 'max_days' => 0,'accumulation' => false, 'additional_rules' => '']);
            // จ้างเหมาบริการรายวัน = PT7
            $this->insert('leave_policies', ['position_type_id' => 'PT7', 'leave_type_id' => 'LT1','month_of_service' => 6,'year_of_service' => 0.5,'days' => 10, 'max_days' => 0,'accumulation' => false, 'additional_rules' => '']);
           

        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%leave_policies}}');
    }

    // query หาสิทธิวันลาพักผ่อน
    // $sql = "WITH t AS (SELECT 
    //         e.id,
    //         concat(e.fname,' ',e.lname) as fullname,
    //         lt.title as leave_type_name,
    //         el.leave_type_id,
    //         e.position_type,
    //         pt.title as position_type_name,
    //         TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) AS years_of_service,
    //         lp.days
    //         FROM employees e 
    //         LEFT JOIN leave_policies lp ON lp.position_type_id = e.position_type
    //         LEFT JOIN `leave` el ON e.id = el.emp_id AND el.leave_type_id = 'LT4'
    //         JOIN categorise lt ON el.leave_type_id = lt.code AND lt.name = 'leave_type'
    //         JOIN categorise pt ON e.position_type = pt.code AND pt.name = 'position_type'
    //         AND e.position_type NOT IN('PT5')
    //         GROUP BY e.id  
    //         ORDER BY `e`.`id` ASC) SELECT *,
    //         (SELECT max_days FROM `leave_policies` WHERE position_type_id = t.position_type AND year_of_service <= t.years_of_service ORDER BY year_of_service DESC limit 1) as leave_days FROM t;";


//     $sql2 = "
//     SELECT x4.*,(x4.days-x4.use_leave) as total,
// (CASE 
//   WHEN x4.accumulation = 0 THEN 10
//  WHEN (x4.days-x4.use_leave + 10) > x4.max_days AND x4.accumulation = 1 THEN x4.max_days

//  ELSE 
//  (x4.days-x4.use_leave + 10)

//  END) as froward_days
// FROM(
//     SELECT x3.*,
//     COALESCE((SELECT days FROM leave_entitlements WHERE emp_id = x3.emp_id AND leave_type_id = x3.leave_type_id AND thai_year=2567),0) as days,
//     COALESCE((SELECT sum(total_days) FROM `leave` WHERE emp_id = x3.emp_id AND leave_type_id = x3.leave_type_id AND thai_year=2567),0) as use_leave
//     FROM(
//     SELECT x2.*,
//     (SELECT max_days FROM `leave_policies` WHERE position_type_id = x2.position_type AND year_of_service <= x2.years_of_service ORDER BY year_of_service DESC limit 1) as max_days
//     FROM(
//     select x1.* from (SELECT 
//                 e.id as emp_id,
//                 concat(e.fname,' ',e.lname) as fullname,
//                 lt.title as leave_type_name,
//                 l.leave_type_id,
//                 e.position_type,
//                 pt.title as position_type_name,
//                 lp.accumulation,
//                 TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) AS years_of_service
//                 FROM employees e 
//                 LEFT JOIN leave_policies lp ON lp.position_type_id = e.position_type
//                 LEFT JOIN `leave` l ON e.id = l.emp_id AND l.leave_type_id = 'LT4'
//                 JOIN categorise lt ON l.leave_type_id = lt.code AND lt.name = 'leave_type'
//                 JOIN categorise pt ON e.position_type = pt.code AND pt.name = 'position_type'
//                 AND e.position_type NOT IN('PT5')
//                 GROUP BY e.id  
//                 ORDER BY `e`.`id` ASC) as x1) as x2) as x3) as x4;"
}
