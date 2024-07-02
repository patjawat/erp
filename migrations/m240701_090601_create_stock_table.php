<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%stock}}`.
 */
class m240701_090601_create_stock_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('stock', [
            'id' => $this->primaryKey()->comment('รหัสสต็อก'),
            'product_id' => $this->integer()->notNull()->comment('รหัสสินค้า'),
            'warehouse_id' => $this->integer()->notNull()->comment('รหัสคลังสินค้า'),
            'quantity' => $this->integer()->notNull()->defaultValue(0)->comment('จำนวนสินค้าในสต็อก'),
            'lot_number' => $this->string(50)->comment('หมายเลข Lot'),
            'expiry_date' => $this->date()->comment('วันหมดอายุ'),
            'ref' => $this->string(255),
            'data_json' => $this->json(),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%stock}}');
    }
}
