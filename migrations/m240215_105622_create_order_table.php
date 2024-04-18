<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order_}}`.
 */
class m240215_105622_create_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order}}', [
            'id' => $this->string(100)->notNull(),
            'ref' => $this->string(255),
            'name' => $this->string()->comment('ชื่อการบันทึก เช่น purchase_order'),
            'start_date' => $this->date(),   
            'end_date' => $this->date(),   
            'data_json' => $this->json(),
            'updated_at' => $this->dateTime(),
            'created_at' => $this->dateTime(),   
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);
        $this->addPrimaryKey('pk_on_id', '{{%order}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%order}}');
    }
}
