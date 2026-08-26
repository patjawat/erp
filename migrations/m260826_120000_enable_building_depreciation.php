<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Makes buildings first-class depreciable assets.
 *
 * - prepares the BLDG asset type used by the same taxonomy flow as equipment;
 * - creates/binds the standard 40-year building depreciation profile;
 * - maps legacy building rows to the taxonomy;
 * - snapshots the standard rule for rows that already have cost and receive date.
 */
class m260826_120000_enable_building_depreciation extends Migration
{
    private const TYPE_CODE = '1';
    private const PROFILE_CODE = 'STD-BLDG';

    public function safeUp()
    {
        $now = date('Y-m-d H:i:s');

        $profileId = (new Query())
            ->select('id')
            ->from('{{%depreciation_profiles}}')
            ->where(['code' => self::PROFILE_CODE])
            ->scalar($this->db);

        $profileValues = [
            'name' => 'อาคารถาวร (40 ปี)',
            'method' => 'straight_line',
            'useful_life_months' => 480,
            'annual_rate' => null,
            'salvage_value_type' => 'one_baht',
            'salvage_value' => 1,
            'calculation_basis' => 'monthly',
            'start_rule' => 'ready_date',
            'rounding_scale' => 2,
            'status' => 'active',
            'updated_at' => $now,
        ];

        if ($profileId) {
            $this->update('{{%depreciation_profiles}}', $profileValues, ['id' => (int) $profileId]);
        } else {
            $this->insert('{{%depreciation_profiles}}', array_merge([
                'code' => self::PROFILE_CODE,
                'created_at' => $now,
            ], $profileValues));
            $profileId = (int) $this->db->getLastInsertID();
        }

        $typeRows = (new Query())
            ->from('{{%categorise}}')
            ->where(['name' => 'asset_type', 'group_id' => 'BLDG'])
            ->orderBy(['id' => SORT_ASC])
            ->all($this->db);

        if (!$typeRows) {
            $this->insert('{{%categorise}}', [
                'name' => 'asset_type',
                'group_id' => 'BLDG',
                'category_id' => null,
                'code' => self::TYPE_CODE,
                'title' => 'อาคารถาวร',
                'active' => 1,
                'data_json' => ['depreciation_profile_id' => $profileId],
            ]);
            $typeRows = [(new Query())->from('{{%categorise}}')->where(['id' => $this->db->getLastInsertID()])->one($this->db)];
        } else {
            foreach ($typeRows as $row) {
                $json = $this->decodeJson($row['data_json'] ?? null);
                $json['depreciation_profile_id'] = $profileId;
                $this->update('{{%categorise}}', [
                    'data_json' => $json,
                    'active' => 1,
                ], ['id' => $row['id']]);
            }
        }

        // Buildings had no taxonomy before this migration. Standardise their legacy
        // life/rate columns so old reports agree with the monthly depreciation engine.
        $this->update('{{%asset}}', [
            'asset_type_id' => self::TYPE_CODE,
            'useful_life' => 40,
            'depreciation_rate' => 2.50,
            'depreciation_method' => 'straight_line',
        ], ['asset_group_id' => 2]);

        // Snapshot only complete records. Rows without receive_date remain unsnapped;
        // Asset::beforeSave() will snapshot them once the missing date is supplied.
        $this->update('{{%asset}}', [
            'depreciation_profile_id' => $profileId,
            'useful_life_months' => 480,
            'residual_value' => 1,
            'depreciation_source_type' => 'asset_type',
            'depreciation_source_id' => self::TYPE_CODE,
            'depreciation_status' => 'active',
        ], [
            'and',
            ['asset_group_id' => 2],
            ['not', ['receive_date' => null]],
            ['>', 'price', 0],
        ]);

        $this->execute(
            "UPDATE {{%asset}}
             SET [[depreciation_start_date]] = [[receive_date]],
                 [[depreciation_end_date]] = DATE_SUB(DATE_ADD([[receive_date]], INTERVAL 480 MONTH), INTERVAL 1 DAY)
             WHERE [[asset_group_id]] = 2
               AND [[receive_date]] IS NOT NULL
               AND [[price]] > 0"
        );
    }

    public function safeDown()
    {
        echo "m260826_120000_enable_building_depreciation is a data migration and cannot be safely reverted.\n";
        return false;
    }

    private function decodeJson($value): array
    {
        $decoded = $value;
        for ($i = 0; $i < 3 && is_string($decoded); $i++) {
            $next = json_decode($decoded, true);
            if ($next === null) {
                return [];
            }
            $decoded = $next;
        }
        return is_array($decoded) ? $decoded : [];
    }
}
