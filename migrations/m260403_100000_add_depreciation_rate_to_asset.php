<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * เพิ่มคอลัมน์อัตราค่าเสื่อม (%) ในตาราง asset และอัปเดตจาก data_json->depreciation ที่มีอยู่
 */
class m260403_100000_add_depreciation_rate_to_asset extends Migration
{
    public function safeUp()
    {
        $table = '{{%asset}}';
        $schema = $this->db->getSchema()->getTableSchema($table, true);
        if ($schema !== null && $schema->getColumn('depreciation_rate') !== null) {
            return;
        }

        $this->addColumn($table, 'depreciation_rate', $this->decimal(6, 2)->null()->comment('อัตราค่าเสื่อม (% ต่อปี, ทศนิยม 2 ตำแหน่ง)'));

        foreach ((new Query())->from($table)->orderBy(['id' => SORT_ASC])->each(200, $this->db) as $row) {
            $dj = $row['data_json'] ?? null;
            if (is_string($dj)) {
                $dj = json_decode($dj, true);
            }
            if (!is_array($dj) || !array_key_exists('depreciation', $dj)) {
                continue;
            }
            $raw = $dj['depreciation'];
            if ($raw === null || $raw === '') {
                continue;
            }
            $normalized = str_replace(',', '.', preg_replace('/\s+/u', '', (string) $raw));
            if ($normalized === '' || !is_numeric($normalized)) {
                continue;
            }
            $v = round((float) $normalized, 2);
            if ($v < 0) {
                continue;
            }
            $this->update($table, ['depreciation_rate' => $v], ['id' => $row['id']]);
        }
    }

    public function safeDown()
    {
        $table = '{{%asset}}';
        $schema = $this->db->getSchema()->getTableSchema($table, true);
        if ($schema !== null && $schema->getColumn('depreciation_rate') !== null) {
            $this->dropColumn($table, 'depreciation_rate');
        }
    }
}
