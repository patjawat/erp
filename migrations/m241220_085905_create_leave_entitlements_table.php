<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%leave_entitlements}}`.
 */
class m241220_085905_create_leave_entitlements_table extends Migration
{
    /**
     * {@inheritdoc}
     * คำนวนสวิทธิการลาของแต่ละปี
     */
    public function safeUp()
    {
        $this->createTable('{{%leave_entitlements}}', [
            'id' => $this->primaryKey(),
            'emp_id' => $this->string(255)->comment('พนักงาน'),
            'position_type_id' => $this->string()->comment('ประเภทตำแหน่ง'),
            'leave_type_id' => $this->string()->comment('ประเภทการลา'),
            'month_of_service' => $this->integer()->notNull()->comment('อายุงาน(เดือน)'),
            'year_of_service' => $this->integer()->notNull()->comment('อายุงาน(ปี)'),
            'balance' => $this->integer()->notNull()->comment('วันที่ลาพักผ่อนสะสม'),
            'leave_on_year' => $this->integer()->notNull()->comment('วันที่ลาพักผ่อนประจำปี'),
            'days' => $this->float()->notNull()->comment('วันที่ลาได้'),
            'data_json' => $this->json(),
            'thai_year' => $this->integer(255)->comment('ปีงบประมาณ'),
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
