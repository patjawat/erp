<?php

use yii\db\Migration;

/**
 * เก็บฐานคำนวณและกฎเริ่มคิด ณ วันที่ตรึงเกณฑ์ เพื่อไม่ให้การแก้ Profile ภายหลัง
 * เปลี่ยนผลของทรัพย์สินเดิมโดยไม่มีประวัติ
 */
class m260827_100000_add_depreciation_rule_snapshot_to_asset extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%asset}}', 'depreciation_calculation_basis', $this->string(20)->null()->after('depreciation_end_date'));
        $this->addColumn('{{%asset}}', 'depreciation_start_rule', $this->string(30)->null()->after('depreciation_calculation_basis'));
        $this->update('{{%depreciation_profiles}}', ['start_rule' => 'day_15_cutoff'], [
            'and',
            ['like', 'code', 'STD-%', false],
            ['calculation_basis' => 'monthly'],
            ['start_rule' => 'ready_date'],
        ]);
    }

    public function safeDown()
    {
        $this->update('{{%depreciation_profiles}}', ['start_rule' => 'ready_date'], [
            'and',
            ['like', 'code', 'STD-%', false],
            ['start_rule' => 'day_15_cutoff'],
        ]);
        $this->dropColumn('{{%asset}}', 'depreciation_start_rule');
        $this->dropColumn('{{%asset}}', 'depreciation_calculation_basis');
    }
}
