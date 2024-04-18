<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%asset_borrow}}`.
 */
class m231220_134932_create_asset_borrow_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%asset_borrow}}', [
            'id' => $this->primaryKey(),
            'ref'  => $this->string(255),
            'wathed_date' => $this->string(255)->comment('วันที่ต้องการ'),
            'depreq_id' => $this->integer()->comment('หน่วยงานร้องขอ'),
            'deplend_id' => $this->integer()->comment('หน่วยงานให้ยืม'),
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
        $this->dropTable('{{%asset_borrow}}');
    }
}
