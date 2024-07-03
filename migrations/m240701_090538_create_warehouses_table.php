<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%warehouses}}`.
 */
class m240701_090538_create_warehouses_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('warehouses', [
            'id' => $this->primaryKey()->comment('รหัสคลังสินค้า'),
            'ref' => $this->string(255),
            'warehouse_name' => $this->string(100)->notNull()->comment('ชื่อคลังสินค้า'),
            'warehouse_code' => $this->string(100)->notNull()->defaultValue(false)->comment('รหัสคลัง เช่น รหัส รพ. รพสต.'),
            'warehouse_type' => "ENUM('MAIN', 'SUB', 'BRANCH') NOT NULL COMMENT 'ประเภทการเคลื่อนไหว (MAIN = คลังหลัก, SUB = ตลังย่อย, BRANCH = สาขา รพสต.)'",
            'is_main' => $this->boolean()->notNull()->defaultValue(false)->comment('เป็นคลังหลักหรือไม่ (true = คลังหลัก, false = คลังย่อย)'),
            'delete' => $this->string(100)->comment('เมื่อลบทำการซ่อนโดยใใส่วันที่'),
            'data_json' => $this->json(),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);
        $this->insert('warehouses', ['warehouse_name' => 'คลังหลัก', 'is_main' => 1, 'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10)]);
        $this->insert('warehouses', ['warehouse_name' => 'คลังยา', 'is_main' => 0, 'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10)]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%warehouses}}');
    }
}
