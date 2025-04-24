<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%development_detail}}`.
 */
class m250418_053735_create_development_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%development_detail}}', [
            'id' => $this->primaryKey(),
            'development_id' => $this->integer()->notNull()->comment('ID ของการพัฒนา'),
            'name' => $this->string()->notNull()->comment('ชื่อของการเก็บข้อมูล'),
            'emp_id' => $this->string()->notNull()->notNull()->comment('รหัสบุคลากร'),
            'qty' => $this->integer()->comment('จํานวน'),
            'price' => $this->double()->comment('ราคา'),
            'data_json' => $this->json()->comment('ยานพาหนะ'),
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
        $this->dropTable('{{%development_detail}}');
    }
}
