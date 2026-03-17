<?php

use yii\db\Migration;

/**
 * Adds depreciation-related columns to asset table for future depreciation calculation.
 * Backward compatible: all new columns are nullable or have defaults.
 */
class m260315_100000_add_depreciation_fields_to_asset extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%asset}}', 'useful_life', $this->integer()->null()->comment('อายุการใช้งาน (ปี)'));
        $this->addColumn('{{%asset}}', 'residual_value', $this->decimal(12, 2)->null()->comment('มูลค่าซาก'));
        $this->addColumn('{{%asset}}', 'depreciation_method', $this->string(50)->defaultValue('straight_line')->comment('วิธีคำนวณค่าเสื่อม'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%asset}}', 'useful_life');
        $this->dropColumn('{{%asset}}', 'residual_value');
        $this->dropColumn('{{%asset}}', 'depreciation_method');
    }
}
