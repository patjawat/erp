<?php

use yii\db\Migration;

/**
 * เพิ่มคอลัมน์ title สำหรับชื่อขั้นอนุมัติ แยกจาก label ที่ใช้เป็นข้อความลงความเห็น
 */
class m260506_100000_add_title_to_approve_level_setting extends Migration
{
    public function safeUp()
    {
        $table = '{{%approve_level_setting}}';
        $schema = $this->db->getSchema()->getTableSchema($table, true);

        if ($schema !== null && $schema->getColumn('title') !== null) {
            return;
        }

        $this->addColumn($table, 'title', $this->string(255)->null()->comment('ชื่อขั้นอนุมัติ'));

        // ค่าเริ่มต้นของ leave เดิมมีชื่อขั้นที่ชัดเจนอยู่แล้ว ให้เติมไว้เพื่อใช้งานได้ทันที
        $this->update($table, ['title' => 'หัวหน้างาน'], ['system' => 'leave', 'level' => 1]);
        $this->update($table, ['title' => 'หัวหน้ากลุ่มงาน'], ['system' => 'leave', 'level' => 2]);
        $this->update($table, ['title' => 'เจ้าหน้าที่ตรวจสอบ'], ['system' => 'leave', 'level' => 3]);
        $this->update($table, ['title' => 'ผู้อำนวยการ'], ['system' => 'leave', 'level' => 4]);
    }

    public function safeDown()
    {
        $table = '{{%approve_level_setting}}';
        $schema = $this->db->getSchema()->getTableSchema($table, true);

        if ($schema !== null && $schema->getColumn('title') !== null) {
            $this->dropColumn($table, 'title');
        }
    }
}
