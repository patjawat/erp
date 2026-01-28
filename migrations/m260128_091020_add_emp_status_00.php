<?php

use yii\db\Migration;

class m260128_091020_add_emp_status_00 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 1. เช็คก่อนว่ามีข้อมูลนี้อยู่แล้วหรือยัง
        $exists = (new \yii\db\Query())
            ->from('categorise')
            ->where(['name' => 'emp_status', 'code' => 'CANCEL'])
            ->exists();

        // 2. ถ้ายังไม่มี ให้ทำการ Insert
        if (!$exists) {
            $this->insert('categorise', [
                'name' => 'emp_status',
                'title' => 'ยกเลิก',
                'code' => 'CANCEL',
                // ใส่ column อื่นๆ ที่จำเป็นตรงนี้
            ]);
        }
        $this->alterColumn('{{%employees}}', 'status', $this->string(20));
    }

    public function safeDown()
    {
        $this->delete('categorise', ['name' => 'emp_status', 'code' => 'CANCEL']);
        $this->delete('categorise', ['name' => 'emp_status', 'code' => 'CANCEL']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260128_091020_add_emp_status_00 cannot be reverted.\n";

        return false;
    }
    */
}
