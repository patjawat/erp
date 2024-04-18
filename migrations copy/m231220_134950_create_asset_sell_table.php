<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%asset_sell}}`.
 */
class m231220_134950_create_asset_sell_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%asset_sell}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'start_date' => $this->date()->comment('วันที่เริ่มโครงการ'),
            'project_name' => $this->string()->comment('ชื่อโครงการ'),
            'price' => $this->double()->comment('มูลค่า'),
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
        $this->dropTable('{{%asset_sell}}');
    }
}
