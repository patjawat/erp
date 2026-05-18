<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * เติมค่า license_plate จาก data_json->license_plate เฉพาะรายการที่ช่อง license_plate ว่าง
 */
class m260518_090000_backfill_asset_license_plate_from_data_json extends Migration
{
    public function safeUp()
    {
        $table = '{{%asset}}';
        $schema = $this->db->getSchema()->getTableSchema($table, true);
        if ($schema === null || $schema->getColumn('license_plate') === null || $schema->getColumn('data_json') === null) {
            return;
        }

        $query = (new Query())
            ->from($table)
            ->select(['id', 'license_plate', 'data_json'])
            ->orderBy(['id' => SORT_ASC]);

        foreach ($query->each(200, $this->db) as $row) {
            $currentLicensePlate = trim((string) ($row['license_plate'] ?? ''));
            if ($currentLicensePlate !== '') {
                continue;
            }

            $dataJson = $row['data_json'] ?? null;
            if (is_string($dataJson)) {
                $decoded = json_decode($dataJson, true);
                if (!is_array($decoded)) {
                    continue;
                }
                $dataJson = $decoded;
            }

            if (!is_array($dataJson)) {
                continue;
            }

            $licensePlate = trim((string) ($dataJson['license_plate'] ?? ''));
            if ($licensePlate === '') {
                continue;
            }

            $this->update($table, ['license_plate' => $licensePlate], ['id' => $row['id']]);
        }
    }

    public function safeDown()
    {
        return false;
    }
}
