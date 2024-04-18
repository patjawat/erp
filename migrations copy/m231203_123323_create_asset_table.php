<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%asset_detail}}`.
 */
class m231203_123323_create_asset_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%asset}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'name' => $this->string(255)->comment('แยกประเภทพัสดุ/ครุภัณฑ์'),
            'fsn' => $this->string(255)->comment('ครุภัณฑ์'),
            'fsn_number' => $this->string(255)->comment('หมายเลขครุภัณฑ์'),
            'qty' => $this->integer()->comment('จำนวน'),
            'receive_date' => $this->date()->comment('วันที่รับเข้า'),
            'price' => $this->double()->comment('ราคา'),
            'life' => $this->integer()->comment('อายุการใช้งาน'),
            'dep_id' => $this->integer()->comment('ประจำอยู่หน่วยงาน'),
            'depre_type' => $this->integer()->comment('ประเภทค่าเสื่อมราคา'),
            'budget_year' => $this->integer()->comment('งบประมาณ'),
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
        $this->dropTable('{{%asset}}');
    }
}
