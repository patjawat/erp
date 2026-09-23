<?php

use yii\db\Migration;

/**
 * หมวดประเภทผ้า — แยกผ้าของโรงพยาบาล กับผ้าจากหน่วยงานภายนอก
 * (เช่น รพ.จังหวัด Refer ผู้ป่วยแล้วส่งผ้ากลับมา) เวลาตรวจนับจะแสดงแยกหมวด ไม่ปนกัน
 *
 * laundry_item.category_id = NULL หมายถึงยังไม่จัดหมวด (แสดงท้ายสุด)
 */
class m260923_140000_create_laundry_item_category extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        $this->createTable('{{%laundry_item_category}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(150)->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
        ], $options);

        $this->addColumn('{{%laundry_item}}', 'category_id', $this->integer()->null()->after('item_name'));
        $this->createIndex('idx_laundry_item_category', '{{%laundry_item}}', 'category_id');

        $now = date('Y-m-d H:i:s');
        $this->insert('{{%laundry_item_category}}', ['name' => 'ผ้าของโรงพยาบาล', 'sort_order' => 1, 'is_active' => 1, 'created_at' => $now]);
        $ownId = (int) $this->db->getLastInsertID();
        $this->insert('{{%laundry_item_category}}', ['name' => 'ผ้าจากหน่วยงานภายนอก (Refer ส่งกลับ)', 'sort_order' => 2, 'is_active' => 1, 'created_at' => $now]);

        // ประเภทผ้าเดิมทั้งหมดเป็นผ้าของโรงพยาบาล
        $this->update('{{%laundry_item}}', ['category_id' => $ownId], ['category_id' => null]);
    }

    public function safeDown()
    {
        $this->dropIndex('idx_laundry_item_category', '{{%laundry_item}}');
        $this->dropColumn('{{%laundry_item}}', 'category_id');
        $this->dropTable('{{%laundry_item_category}}');
    }
}
