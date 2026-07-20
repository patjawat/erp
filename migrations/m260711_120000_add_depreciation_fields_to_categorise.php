<?php

use yii\db\Migration;

/**
 * เพิ่มคอลัมน์ค่าเสื่อมให้ตาราง categorise เพื่อให้หมวดทรัพย์สิน (name=asset_category)
 * เก็บ useful_life / depreciation_rate เป็นคอลัมน์จริง (แทนการเก็บใน data_json)
 * หมวดทรัพย์สินเป็นแหล่งค่าเสื่อมแหล่งเดียว — ชนิดคอลัมน์ตรงกับตาราง asset เพื่อความสอดคล้อง
 * Backward compatible: คอลัมน์ใหม่เป็น null ได้ทั้งหมด
 */
class m260711_120000_add_depreciation_fields_to_categorise extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = '{{%categorise}}';
        $columns = Yii::$app->db->schema->getTableSchema($table)->columns;

        if (!array_key_exists('useful_life', $columns)) {
            $this->addColumn($table, 'useful_life', $this->integer()->null()->after('ma_items')->comment('อายุการใช้งาน (ปี) — ค่าตั้งต้นของหมวดทรัพย์สิน'));
        }
        if (!array_key_exists('depreciation_rate', $columns)) {
            $this->addColumn($table, 'depreciation_rate', $this->decimal(6, 2)->null()->after('useful_life')->comment('อัตราค่าเสื่อม (% ต่อปี) — ค่าตั้งต้นของหมวดทรัพย์สิน'));
        }

        // ย้ายค่าเดิมที่เคยเก็บใน data_json เข้าคอลัมน์ใหม่ (เฉพาะหมวดทรัพย์สิน)
        $this->execute("
            UPDATE {{%categorise}}
            SET useful_life = CAST(JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.useful_life')) AS UNSIGNED)
            WHERE name = 'asset_category'
              AND JSON_EXTRACT(data_json, '$.useful_life') IS NOT NULL
              AND useful_life IS NULL
        ");
        $this->execute("
            UPDATE {{%categorise}}
            SET depreciation_rate = CAST(JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.depreciation_rate')) AS DECIMAL(6,2))
            WHERE name = 'asset_category'
              AND JSON_EXTRACT(data_json, '$.depreciation_rate') IS NOT NULL
              AND depreciation_rate IS NULL
        ");

        // เคลียร์ 2 key นี้ออกจาก data_json — ยกเลิกการเก็บค่าเสื่อมใน data_json
        $this->execute("
            UPDATE {{%categorise}}
            SET data_json = JSON_REMOVE(data_json, '$.useful_life', '$.depreciation_rate')
            WHERE name = 'asset_category'
              AND data_json IS NOT NULL
              AND (JSON_EXTRACT(data_json, '$.useful_life') IS NOT NULL
                   OR JSON_EXTRACT(data_json, '$.depreciation_rate') IS NOT NULL)
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $table = '{{%categorise}}';
        $columns = Yii::$app->db->schema->getTableSchema($table)->columns;

        if (array_key_exists('depreciation_rate', $columns)) {
            $this->dropColumn($table, 'depreciation_rate');
        }
        if (array_key_exists('useful_life', $columns)) {
            $this->dropColumn($table, 'useful_life');
        }
    }
}
