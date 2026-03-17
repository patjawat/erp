<?php

use yii\db\Migration;

/**
 * Optional format configuration for asset number patterns.
 */
class m260316_100001_create_am_asset_number_formats_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%am_asset_number_formats}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull()->comment('Format name'),
            'pattern' => $this->string(255)->notNull()->comment('e.g. {category}/{year}.{seq}'),
            'is_active' => $this->tinyInteger(1)->notNull()->defaultValue(1)->comment('1=active'),
        ]);
        $this->insert('{{%am_asset_number_formats}}', [
            'name' => 'ปี.ลำดับ (default)',
            'pattern' => '{category}/{year}.{seq}',
            'is_active' => 1,
        ]);
        $this->insert('{{%am_asset_number_formats}}', [
            'name' => 'ลำดับ/ปี',
            'pattern' => '{category}/{seq}/{year}',
            'is_active' => 1,
        ]);
        $this->insert('{{%am_asset_number_formats}}', [
            'name' => 'ปี-ลำดับ',
            'pattern' => '{category}/{year}-{seq}',
            'is_active' => 1,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%am_asset_number_formats}}');
    }
}
