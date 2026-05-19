<?php

use yii\db\Migration;

/**
 * ปรับ employee_position ให้เป็น master ตำแหน่งอิสระ
 * โดยอนุญาตให้ employee_type_id และ employee_position_group_id เป็น null
 */
class m260519_150000_make_employee_position_independent extends Migration
{
    public function safeUp()
    {
        $schema = $this->db->getTableSchema('{{%employee_position}}', true);
        if ($schema === null) {
            return;
        }

        if (isset($schema->columns['employee_type_id'])) {
            $this->alterColumn(
                '{{%employee_position}}',
                'employee_type_id',
                $this->integer()->null()->comment('ประเภทพนักงาน (ใหม่)')->after('id')
            );
        }

        if (isset($schema->columns['employee_position_group_id'])) {
            $this->alterColumn(
                '{{%employee_position}}',
                'employee_position_group_id',
                $this->integer()->null()->comment('กลุ่มตำแหน่งพนักงาน (ใหม่)')->after('employee_type_id')
            );
        }
    }

    public function safeDown()
    {
        return false;
    }
}
