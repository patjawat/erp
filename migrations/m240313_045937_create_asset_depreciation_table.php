<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%asset_depreciation}}`.
 */
class m240313_045937_create_asset_depreciation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%asset_depreciation}}', [
            'id' => $this->primaryKey(),
            'asset_id' => $this->string(255)->comment('รหัสทรัพย์สิน'),
            'group_type' => $this->string(255)->comment('รายปีหรือรายเดือน'),
            'date_summary' => $this->string(255)->comment('วันที่ เดือน หรือ ปี'),
            'depreciation' => $this->string(255)->comment('ค่าเสื่อม'),
        ]); 
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%asset_depreciation}}');
    }
}
