<?php

use yii\db\Migration;

class m260409_155642_alter_asset_add_kind extends Migration
{
    public function safeUp()
    {
        // เพิ่มประเภททรัพย์สิน
        $this->addColumn('{{%asset}}', 'asset_kind', $this->string(20)->after('asset_group_id')
            ->comment('LAND|BUILDING|STRUCTURE|EQUIPMENT'));

        // index
        $this->createIndex(
            'idx_asset_kind',
            '{{%asset}}',
            'asset_kind'
        );

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

        // group_id = 1 -> LAND
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
        $this->dropIndex('idx_asset_kind', '{{%asset}}');
        $this->dropColumn('{{%asset}}', 'asset_kind');
    }
}
