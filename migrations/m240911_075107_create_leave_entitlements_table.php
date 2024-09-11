<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%leave_entitlements}}`.
 */
class m240911_075107_create_leave_entitlements_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%leave_entitlements}}', [
            'id' => $this->primaryKey(),
            'emp_id' => $this->integer()->comment('บุคลากร'),
            'leave_type_id' => $this->integer()->comment('ประเภทการลา'),
            'days_available' => $this->integer()->comment('จำนวนวันที่มีสิทธิลา'),
            'data_json' => $this->json(),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
            'deleted_at' => $this->dateTime()->comment('วันที่ลบ'),
            'deleted_by' => $this->integer()->comment('ผู้ลบ')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%leave_entitlements}}');
    }
}
