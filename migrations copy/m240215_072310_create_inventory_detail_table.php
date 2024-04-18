<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%inventory_detail}}`.
 */
class m240215_072310_create_inventory_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%inventory_detail}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'inventory_code' => $this->string(255),
            'code' => $this->string(255),
            'emp_id' => $this->integer(),
            'asset_item' => $this->string(255)->comment('รหัสทรัพย์สิน'),
            'name' => $this->string()->comment('ชื่อการบันทึก'),
            'price' => $this->string()->comment('ราคาต่อหน่วย'),
            'qty' => $this->integer()->comment('จำนวน'),
            'data_json' => $this->json(),
            'updated_at' => $this->dateTime(),
            'created_at' => $this->dateTime(),   
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%inventory_detail}}');
    }
}
