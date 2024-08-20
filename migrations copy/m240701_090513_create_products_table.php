<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%products}}`.
 */
class m240701_090513_create_products_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('products', [
            'product_id' => $this->primaryKey()->comment('รหัสสินค้า'),
            'product_name' => $this->string(100)->notNull()->comment('ชื่อสินค้า'),
            'product_group' => $this->string(100)->notNull()->comment('กลุ่มสินค้า'),
            'product_type' => $this->string(100)->notNull()->comment('ประเภทสินค้า'),
            'description' => $this->text()->comment('รายละเอียดสินค้า'),
            'unit' => $this->string(20)->notNull()->comment('หน่วยนับของสินค้า'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%products}}');
    }
}
