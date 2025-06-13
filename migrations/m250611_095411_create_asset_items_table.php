<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%asset_items}}`.
 */
class m250611_095411_create_asset_items_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%asset_items}}', [
            'id' => $this->string()->comment('รหัสทรัพย์สิน'),
            'ref' => $this->string(255),
            'asset_group_id' => $this->string(255)->comment('กลุ่มทรัพย์สิน'),
            'asset_type_id' => $this->string(255)->comment('ประเภททรัพย์สิน'),
            'asset_category_id' => $this->string(255)->comment('หมวดรัพย์สิน'),
            'title' => $this->text()->comment('ชื่อทรัพย์สิน'),
            'fsn' => $this->string(255)->comment('ทรัพย์สิน (FSN)'),
            'description' => $this->text()->comment('รายละเอียดทรัพย์สิน'),
            'price' => $this->decimal(12, 2)->defaultValue(0)->comment('ราคาทรัพย์สิน'),
            'depreciation' => $this->decimal(5, 2)->defaultValue(0)->comment('อัตราค่าเสื่อม'),
            'service_life' => $this->integer()->defaultValue(0)->comment('อายุการใช้งาน'),
            'status' => $this->string(50)->defaultValue('active')->comment('สถานะทรัพย์สิน'),
            'data_json' => $this->json()->comment('ยานพาหนะ'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
            'deleted_at' => $this->dateTime()->comment('วันที่ลบ'),
            'deleted_by' => $this->integer()->comment('ผู้ลบ')
        ]);
        $this->addPrimaryKey('pk-asset_items-id', '{{%asset_items}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%asset_items}}');
    }
}
