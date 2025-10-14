<?php

use yii\db\Migration;

class m251014_012201_add_index_to_leave_and_employees_tables extends Migration
{
   public function safeUp()
    {
        /** ============================
         *  🟢 ตาราง leave
         *  เพิ่ม index สำหรับฟิลด์ที่ใช้บ่อยใน LeaveSearch
         * ============================ */
        $this->createIndex('idx-leave-emp_id', '{{%leave}}', 'emp_id');
        $this->createIndex('idx-leave-status', '{{%leave}}', 'status');
        $this->createIndex('idx-leave-leave_type_id', '{{%leave}}', 'leave_type_id');
        $this->createIndex('idx-leave-date_start', '{{%leave}}', 'date_start');
        $this->createIndex('idx-leave-date_end', '{{%leave}}', 'date_end');
        $this->createIndex('idx-leave-created_at', '{{%leave}}', 'created_at');

        /** ============================
         *  🟢 ตาราง employees
         *  เพิ่ม index สำหรับการเชื่อมกับ leave และค้นหาพนักงาน
         * ============================ */
        $this->createIndex('idx-employees-user_id', '{{%employees}}', 'user_id');
        $this->createIndex('idx-employees-department', '{{%employees}}', 'department');
    }

    public function safeDown()
    {
        /** rollback: ลบ index ออก */
        // leave
        $this->dropIndex('idx-leave-emp_id', '{{%leave}}');
        $this->dropIndex('idx-leave-status', '{{%leave}}');
        $this->dropIndex('idx-leave-leave_type_id', '{{%leave}}');
        $this->dropIndex('idx-leave-date_start', '{{%leave}}');
        $this->dropIndex('idx-leave-date_end', '{{%leave}}');
        $this->dropIndex('idx-leave-created_at', '{{%leave}}');

        // employees
        $this->dropIndex('idx-employees-user_id', '{{%employees}}');
        $this->dropIndex('idx-employees-department', '{{%employees}}');
    }
}
