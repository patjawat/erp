<?php

use yii\db\Migration;

/**
 * เพิ่มคอลัมน์ประเภททรัพย์สิน `asset.asset_kind`
 *
 * เดิม migration นี้อยู่ใน modules/am/migrations/ (โฟลเดอร์ย่อย) ซึ่ง `yii migrate`
 * ไม่ recurse และ deploy script บนเครื่องจริงสแกนแค่ @app/migrations จึงไม่เคยถูกรัน
 * เลย ทำให้ฐานบางเครื่องขาดคอลัมน์นี้ (โมดูล am ทำงานผิดพลาด) ย้ายมาไว้ชั้นเดียว
 * 2026-09-14 และทำให้ idempotent เพื่อให้ปลอดภัยกับทุกสถานะฐาน:
 *   - คอลัมน์มีอยู่แล้ว  -> ข้ามการสร้าง (กัน error duplicate column ตอน deploy)
 *   - ยังไม่มีคอลัมน์    -> สร้าง + backfill ให้
 */
class m260409_155642_alter_asset_add_kind extends Migration
{
    public function safeUp()
    {
        $table = $this->db->getTableSchema('{{%asset}}', true);
        $hasColumn = $table !== null && $table->getColumn('asset_kind') !== null;

        if (!$hasColumn) {
            // เพิ่มประเภททรัพย์สิน
            $this->addColumn('{{%asset}}', 'asset_kind', $this->string(20)->after('asset_group_id')
                ->comment('LAND|BUILDING|STRUCTURE|EQUIPMENT'));
        }

        // index (สร้างเฉพาะเมื่อยังไม่มี)
        $indexes = $this->db->getSchema()->getTableIndexes('{{%asset}}', true);
        $hasIndex = false;
        foreach ($indexes as $index) {
            if ($index->name === 'idx_asset_kind') {
                $hasIndex = true;
                break;
            }
        }
        if (!$hasIndex) {
            $this->createIndex(
                'idx_asset_kind',
                '{{%asset}}',
                'asset_kind'
            );
        }

        // backfill เฉพาะแถวที่ยังว่าง (ปลอดภัยกับข้อมูลเดิม)
        // group_id = 1 -> LAND
        $this->update(
            '{{%asset}}',
            ['asset_kind' => 'LAND'],
            [
                'and',
                ['asset_group_id' => 1],
                ['or', ['asset_kind' => null], ['asset_kind' => '']]
            ]
        );

        // group_id = 4 -> EQUIPMENT
        $this->update(
            '{{%asset}}',
            ['asset_kind' => 'EQUIPMENT'],
            [
                'and',
                ['asset_group_id' => 4],
                ['or', ['asset_kind' => null], ['asset_kind' => '']]
            ]
        );
    }

    public function safeDown()
    {
        $indexes = $this->db->getSchema()->getTableIndexes('{{%asset}}', true);
        foreach ($indexes as $index) {
            if ($index->name === 'idx_asset_kind') {
                $this->dropIndex('idx_asset_kind', '{{%asset}}');
                break;
            }
        }

        $table = $this->db->getTableSchema('{{%asset}}', true);
        if ($table !== null && $table->getColumn('asset_kind') !== null) {
            $this->dropColumn('{{%asset}}', 'asset_kind');
        }
    }
}
