<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%sup_request}}`.
 */
class m240114_075634_create_sup_request_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%sup_request}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'req_code' => $this->string(255)->comment('เลขที่ขอ'),
            'req_date' => $this->date()->comment('วันที่ขอ'),
            'req_detail' => $this->string(255)->comment('รายละเอียดการร้องขอ'),
            'req_vendor' => $this->string(255)->comment('บริษัท'),
            'req_amount' => $this->string(50)->comment('มูลค่า'),
            'req_status' => $this->string(255)->comment('สถานะการร้องขอ'),
            'req_dep' => $this->string(255)->comment('หน่วยงานที่ขอ'),
            'data_json' => $this->json(),
            'updated_at' => $this->dateTime()->comment('วันเวลาแก้ไข'),
            'created_at' => $this->dateTime()->comment('วันเวลาสร้าง'), 
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%sup_request}}');
    }
}
