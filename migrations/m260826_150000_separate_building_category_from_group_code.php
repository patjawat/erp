<?php

use yii\db\Migration;

/**
 * BLDG identifies the building group. It is not an FSN/registration prefix.
 * Keep depreciation on the building type and require real categories for new rows.
 */
class m260826_150000_separate_building_category_from_group_code extends Migration
{
    public function safeUp()
    {
        $this->update('{{%categorise}}', [
            'active' => 0,
            'title' => 'รหัสกลุ่มอาคาร (ห้ามใช้เป็นเลขทะเบียน)',
        ], [
            'name' => 'asset_category',
            'code' => 'BLDG',
        ]);

        $this->update('{{%asset}}', [
            'asset_category_id' => null,
        ], [
            'asset_group_id' => 2,
            'asset_category_id' => 'BLDG',
        ]);

        $this->update('{{%asset}}', [
            'depreciation_source_type' => 'asset_type',
            'depreciation_source_id' => '1',
        ], [
            'asset_group_id' => 2,
            'depreciation_source_type' => 'asset_category',
        ]);
    }

    public function safeDown()
    {
        echo "This migration separates a group marker from real registration categories and cannot be safely reverted.\n";
        return false;
    }
}
