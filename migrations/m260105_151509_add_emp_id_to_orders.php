<?php

use yii\db\Migration;

class m260105_151509_add_emp_id_to_orders extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = '{{%orders}}';
        $columnName = 'emp_id';

        // ดึงข้อมูลโครงสร้างตารางปัจจุบัน
        $tableSchema = $this->db->getTableSchema($table);

        // ตรวจสอบว่าในตารางมีคอลัมน์ชื่อนี้อยู่แล้วหรือไม่
        if (!isset($tableSchema->columns[$columnName])) {
            $this->addColumn($table, $columnName, $this->integer()->comment('รหัสบุคลากร')->after('id'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
        // $table = '{{%orders}}';
        // $this->dropColumn($table, 'provider_type');

// update ข้อมูลเดิม
        $sql = "UPDATE `orders` o
LEFT JOIN employees e ON e.user_id = o.created_by
SET o.emp_id = e.id
WHERE o.name = 'order';

";
    }
}

