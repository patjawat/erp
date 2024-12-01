<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%leave_role}}`.
 */
class m241130_062341_create_leave_role_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%leave_role}}', [
            'id' => $this->primaryKey(),
            'data_json' => $this->json(),
            'emp_id' => $this->integer()->notNull(),
            'thai_year' => $this->integer()->notNull(),
            'work_year' => $this->integer()->notNull()->comment('อายุงาน'),
            'position_type_id' => $this->string()->comment('ตำแหน่ง'),
            'max_point' => $this->integer()->notNull()->comment('สิทธิสะสมวัน')->notNull(),
            'point' => $this->integer()->notNull()->comment('ยอดยกมา/วันลาคงเหลือ')->notNull(),
            'point_use' => $this->integer()->notNull()->comment('ใช้ไปแล้ว')->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%leave_role}}');
    }
}
