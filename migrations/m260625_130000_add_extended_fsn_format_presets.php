<?php

use yii\db\Migration;

/**
 * Adds preset patterns that exercise the extended FSN token syntax
 * ({seq:0}, {year:4}, {year:ad:4}). Old presets keep the legacy
 * {year}/{seq} aliases so existing rows render identically.
 */
class m260625_130000_add_extended_fsn_format_presets extends Migration
{
    public function safeUp()
    {
        if ($this->db->getSchema()->getTableSchema('{{%am_asset_number_formats}}') === null) {
            return;
        }

        $presets = [
            ['name' => 'ลำดับ/ปี (ไม่ pad)',  'pattern' => '{category}-{seq:0}/{year:2}'],
            ['name' => 'ปี4-ลำดับ3',          'pattern' => '{category}-{year:4}-{seq:3}'],
            ['name' => 'ปี ค.ศ. 4 หลัก',      'pattern' => '{category}/{year:ad:4}-{seq:3}'],
        ];

        foreach ($presets as $preset) {
            $exists = (new \yii\db\Query())
                ->from('{{%am_asset_number_formats}}')
                ->where(['pattern' => $preset['pattern']])
                ->exists($this->db);

            if (!$exists) {
                $this->insert('{{%am_asset_number_formats}}', [
                    'name' => $preset['name'],
                    'pattern' => $preset['pattern'],
                    'is_active' => 0,
                ]);
            }
        }
    }

    public function safeDown()
    {
        if ($this->db->getSchema()->getTableSchema('{{%am_asset_number_formats}}') === null) {
            return;
        }

        $patterns = [
            '{category}-{seq:0}/{year:2}',
            '{category}-{year:4}-{seq:3}',
            '{category}/{year:ad:4}-{seq:3}',
        ];

        $this->delete('{{%am_asset_number_formats}}', ['pattern' => $patterns]);
    }
}
