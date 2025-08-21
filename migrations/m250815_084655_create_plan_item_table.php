<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%plan_item}}`.
 */
class m250815_084655_create_plan_item_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%plan_item}}', [
            'id' => $this->primaryKey(),
            'plan_order_id' => $this->integer()->notNull()->comment('รหัสแผน'),
            'title' => $this->string(255)->comment('ชื่อวัสดุ/บุคลากร/ค่าใช้สอย'),
            'asset_code' => $this->string(255)->comment('รหัสครุภัณฑ์'),
            'item_id' => $this->string(255)->comment('รหัสที่ใช้เชื่อมกัน'),
            'item_name' => $this->string(255)->comment('ชื่อการเชื่อมต่อ'),
            'qty' => $this->integer()->defaultValue(1)->comment('จำนวน'),
            'unit_price' => $this->decimal(15, 2)->defaultValue(0)->comment('ราคาต่อหน่วย'),
            'total_price' => $this->decimal(15, 2)->defaultValue(0)->comment('ราคารวม'),
            'data_json' => $this->json()->comment('ยานพาหนะ'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
            'deleted_at' => $this->dateTime()->comment('วันที่ลบ'),
            'deleted_by' => $this->integer()->comment('ผู้ลบ')
        ]);
        //     $this->addForeignKey(
        //     'fk-plan_item-plan_id',
        //     '{{%plan_item}}',
        //     'plan_id',
        //     '{{%plan}}',
        //     'id',
        //     'CASCADE'
        // );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // $this->dropForeignKey('fk-plan_item-plan_id', '{{%plan_item}}');
        $this->dropTable('{{%plan_item}}');
    }
}
