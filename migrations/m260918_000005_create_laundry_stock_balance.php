<?php

use yii\db\Migration;

/**
 * ตารางยอดคงเหลือผ้า (perpetual balance) — กันข้อมูลโต
 * เดิมอ่านยอดด้วย SUM(piece_event) ทั้งประวัติ (ช้าลงเรื่อย ๆ เมื่อ event สะสมทุกวัน)
 * → เก็บยอดคงเหลือต่อ (ประเภทผ้า × ที่อยู่ × หน่วยงาน) อัปเดตทุก write, อ่านตรง (เร็วคงที่)
 * piece_event เก็บเป็นประวัติ/audit อย่างเดียว
 *
 * department_id = 0 หมายถึงไม่ระบุหน่วยงาน (เช่น คลังหลัก CLEAN)
 */
class m260918_000005_create_laundry_stock_balance extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        $this->createTable('{{%laundry_stock_balance}}', [
            'id' => $this->primaryKey(),
            'item_id' => $this->integer()->notNull(),
            'location' => $this->string(30)->notNull(),
            'department_id' => $this->integer()->notNull()->defaultValue(0),
            'qty' => $this->integer()->notNull()->defaultValue(0),
            'updated_at' => $this->dateTime()->null(),
        ], $options);
        $this->createIndex('uq_laundry_balance', '{{%laundry_stock_balance}}', ['item_id', 'location', 'department_id'], true);
        $this->createIndex('idx_laundry_balance_loc', '{{%laundry_stock_balance}}', ['location', 'item_id']);

        // backfill จากประวัติ piece_event (ทำตอนข้อมูลยังน้อย → เบา)
        if ($this->db->driverName === 'mysql') {
            $this->execute("
                INSERT INTO {{%laundry_stock_balance}} (item_id, location, department_id, qty, updated_at)
                SELECT item_id, location, department_id, SUM(delta), NOW() FROM (
                    SELECT item_id, to_location AS location, COALESCE(to_department_id,0) AS department_id, qty AS delta
                    FROM {{%laundry_piece_event}} WHERE status='CONFIRMED' AND to_location IS NOT NULL AND to_location <> ''
                    UNION ALL
                    SELECT item_id, from_location AS location, COALESCE(from_department_id,0) AS department_id, -qty AS delta
                    FROM {{%laundry_piece_event}} WHERE status='CONFIRMED' AND from_location IS NOT NULL AND from_location <> ''
                ) t
                GROUP BY item_id, location, department_id
            ");
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%laundry_stock_balance}}');
    }
}
