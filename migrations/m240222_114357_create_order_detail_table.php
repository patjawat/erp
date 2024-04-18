<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order_detail}}`.
 */
class m240222_114357_create_order_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order_detail}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'order_id' => $this->string(100)->comment('รหัส order'),
            'product_id' => $this->string(255)->comment('รหัสทรัพย์สิน'),
            'price' => $this->string()->comment('ราคาต่อหน่วย'),
            'amount' => $this->integer()->comment('จำนวน'),
            'data_json' => $this->json(),
            'updated_at' => $this->dateTime(),
            'created_at' => $this->dateTime(),   
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);
        // สร้าง index จาก column `order_id`
        $this->createIndex(
            'idx-order_detail-order_id',
            'order_detail',
            'order_id'
        );

        // เพิ่ม foreign key จากตาราง  `order`
        $this->addForeignKey(
            'fk-order_detail-order_id',
            'order_detail',
            'order_id',
            'order',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%order_detail}}');
    }
}
