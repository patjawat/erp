<?php

use yii\db\Migration;

/**
 * ทะเบียนหน่วยงานซักฟอก (ตั้งที่เมนูตั้งค่า) — คุมว่าหน่วยงานไหนแสดงเป็นการ์ดในหน้ารับผ้า/ตรวจรับ/จ่าย
 * + ฟิลด์เสริมใน collection_stop: receipt_no (เลขที่รับ) และ round_seq (เลขรอบเก็บของวัน 1,2,3)
 */
class m260918_000001_create_laundry_unit extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        $this->createTable('{{%laundry_unit}}', [
            'id' => $this->primaryKey(),
            'tree_id' => $this->integer()->notNull()->unique()->comment('org unit (tree.id)'),
            'abbr' => $this->string(20)->null()->comment('ชื่อย่อ เช่น CLC/KID'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
        ], $options);
        $this->createIndex('idx_laundry_unit_active_sort', '{{%laundry_unit}}', ['is_active', 'sort_order']);

        // เลขที่รับ + เลขรอบเก็บของวัน (idempotent — ข้ามถ้ามีแล้ว)
        $stop = $this->db->getTableSchema('{{%laundry_collection_stop}}', true);
        if ($stop && $stop->getColumn('receipt_no') === null) {
            $this->addColumn('{{%laundry_collection_stop}}', 'receipt_no', $this->string(40)->null()->after('id'));
        }
        if ($stop && $stop->getColumn('round_seq') === null) {
            $this->addColumn('{{%laundry_collection_stop}}', 'round_seq', $this->integer()->null()->comment('เลขรอบเก็บของวัน'));
        }
    }

    public function safeDown()
    {
        $stop = $this->db->getTableSchema('{{%laundry_collection_stop}}', true);
        if ($stop && $stop->getColumn('round_seq') !== null) {
            $this->dropColumn('{{%laundry_collection_stop}}', 'round_seq');
        }
        if ($stop && $stop->getColumn('receipt_no') !== null) {
            $this->dropColumn('{{%laundry_collection_stop}}', 'receipt_no');
        }
        $this->dropTable('{{%laundry_unit}}');
    }
}
