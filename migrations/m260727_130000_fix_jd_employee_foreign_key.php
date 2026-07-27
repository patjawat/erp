<?php

use yii\db\Migration;

class m260727_130000_fix_jd_employee_foreign_key extends Migration
{
    public function safeUp()
    {
        $this->dropForeignKey('fk-jd_employee-emp', '{{%jd_employee}}');
        $this->addForeignKey(
            'fk-jd_employee-emp',
            '{{%jd_employee}}',
            'emp_id',
            '{{%employees}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-jd_employee-emp', '{{%jd_employee}}');
        $this->addForeignKey(
            'fk-jd_employee-emp',
            '{{%jd_employee}}',
            'emp_id',
            '{{%delete_employees}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }
}
