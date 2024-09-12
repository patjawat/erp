<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%leave_permission}}`.
 */
class m240912_072737_create_leave_permission_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%leave_permission}}', [
            'id' => $this->primaryKey(),
            'leave_type_id' => $this->integer()->comment('ประเภทการลา'),
            'position_type_id' => $this->string(255)->comment('ประเภทการลา'),
            'leave_days' => $this->integer()->comment('วันลา'),
            'leave_days_max' => $this->integer()->comment('สะสมวันลาได้ไม่เกิน'),
            'year_service' => $this->integer()->comment('อายุการทำงาน'),
            'point' => $this->boolean()->comment('สะสมวันลา'),
            'point_days' => $this->integer()->comment('จำนวนวัน'),
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
        $this->dropTable('{{%leave_permission}}');
    }
}
