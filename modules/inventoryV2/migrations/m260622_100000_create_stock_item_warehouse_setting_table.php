<?php

use yii\db\Migration;

/**
 * Phase 1 ของ feature "ตั้ง min/max ต่อคลัง × ต่อวัสดุ"
 *
 * เก็บค่าจุดสั่งซื้อขั้นต่ำ (min) และขั้นสูง (max) แยกตามคลัง
 * แทนที่ค่าใน stock_item.min_qty / max_qty ที่เป็น global
 *
 * Phase 1: เพิ่มตารางใหม่อย่างเดียว ของเดิมยังใช้งานได้
 * Phase 2: ปรับ critical/report ให้ใช้ตารางใหม่ + drop คอลัมน์เก่า
 */
class m260622_100000_create_stock_item_warehouse_setting_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%stock_item_warehouse_setting}}', [
            'id' => $this->primaryKey(),
            'item_code' => $this->string(50)->notNull()->comment('เชื่อม stock_item.item_code'),
            'warehouse_id' => $this->integer()->notNull()->comment('เชื่อม warehouses.id'),
            'min_qty' => $this->decimal(10, 2)->notNull()->defaultValue(0)->comment('จุดสั่งซื้อขั้นต่ำของคลังนี้'),
            'max_qty' => $this->decimal(10, 2)->notNull()->defaultValue(0)->comment('จุดสั่งซื้อขั้นสูงของคลังนี้'),
            'note' => $this->string(255)->null()->comment('หมายเหตุของ admin'),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'data_json' => $this->json()->null(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $tableOptions);

        $this->createIndex(
            'uk_stock_item_warehouse',
            '{{%stock_item_warehouse_setting}}',
            ['item_code', 'warehouse_id'],
            true
        );
        $this->createIndex(
            'idx_siws_warehouse',
            '{{%stock_item_warehouse_setting}}',
            'warehouse_id'
        );

        $this->addForeignKey(
            'fk_siws_item_code',
            '{{%stock_item_warehouse_setting}}',
            'item_code',
            '{{%stock_item}}',
            'item_code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_siws_warehouse',
            '{{%stock_item_warehouse_setting}}',
            'warehouse_id',
            '{{%warehouses}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%stock_item_warehouse_setting}}');
    }
}
