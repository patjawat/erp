<?php

use yii\db\Migration;

/**
 * เพิ่ม snapshot เกณฑ์ค่าเสื่อมที่ทรัพย์สินใช้จริงลงตาราง asset (ตารางหลักชื่อ "asset", PK=id)
 *
 * Reuse คอลัมน์เดิมที่มีอยู่แล้ว (ไม่สร้างซ้ำ):
 *   - depreciation_method  (varchar 50)      → method snapshot
 *   - useful_life          (int, ปี)          → snapshot อายุ (ปี)
 *   - depreciation_rate    (decimal 6,2)      → rate snapshot
 *   - residual_value       (decimal 12,2)     → salvage_value snapshot
 *
 * เพิ่มใหม่:
 *   - depreciation_profile_id      อ้างอิงเกณฑ์ต้นทาง
 *   - useful_life_months           อายุเป็นเดือน (รองรับอายุไม่เต็มปี)
 *   - depreciation_start_date      วันเริ่มคิดค่าเสื่อม
 *   - depreciation_end_date        วันสิ้นสุดคิดค่าเสื่อม
 *   - depreciation_source_type     asset_type|asset_category|asset_item|asset
 *   - depreciation_source_id       id ของแหล่งเกณฑ์
 *   - depreciation_status          สถานะค่าเสื่อมของทรัพย์สิน
 *
 * Backward compatible: ทุกคอลัมน์ใหม่เป็น null ได้
 */
class m260712_100003_add_depreciation_snapshot_to_asset extends Migration
{
    private $columns = [
        'depreciation_profile_id' => 'integer',
        'useful_life_months' => 'integer',
        'depreciation_start_date' => 'date',
        'depreciation_end_date' => 'date',
        'depreciation_source_type' => 'string_20',
        'depreciation_source_id' => 'integer',
        'depreciation_status' => 'string_20',
    ];

    public function safeUp()
    {
        $table = '{{%asset}}';
        $existing = $this->db->getSchema()->getTableSchema($table, true)->columns;

        $defs = [
            'depreciation_profile_id' => $this->integer()->null()->comment('FK depreciation_profiles.id (เกณฑ์ต้นทาง)'),
            'useful_life_months' => $this->integer()->null()->comment('อายุการใช้งาน (เดือน) snapshot'),
            'depreciation_start_date' => $this->date()->null()->comment('วันเริ่มคิดค่าเสื่อม'),
            'depreciation_end_date' => $this->date()->null()->comment('วันสิ้นสุดคิดค่าเสื่อม'),
            'depreciation_source_type' => $this->string(20)->null()->comment('แหล่งเกณฑ์: asset_type|asset_category|asset_item|asset'),
            'depreciation_source_id' => $this->integer()->null()->comment('id ของแหล่งเกณฑ์'),
            'depreciation_status' => $this->string(20)->null()->comment('สถานะค่าเสื่อมของทรัพย์สิน'),
        ];

        foreach ($defs as $name => $type) {
            if (!array_key_exists($name, $existing)) {
                $this->addColumn($table, $name, $type);
            }
        }

        $this->createIndex('idx_asset_depreciation_profile', $table, 'depreciation_profile_id');
    }

    public function safeDown()
    {
        $table = '{{%asset}}';
        $existing = $this->db->getSchema()->getTableSchema($table, true)->columns;

        try {
            $this->dropIndex('idx_asset_depreciation_profile', $table);
        } catch (\Throwable $e) {
        }

        foreach (array_keys($this->columns) as $name) {
            if (array_key_exists($name, $existing)) {
                $this->dropColumn($table, $name);
            }
        }
    }
}
