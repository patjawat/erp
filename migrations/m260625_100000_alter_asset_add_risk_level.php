<?php

use yii\db\Migration;

/**
 * Add canonical risk_level column to asset table (H|M|L) and migrate
 * existing values from data_json (1|2|3 categorise codes) to letters.
 *
 * Old -> New mapping:
 *   data_json.risk_level '1' (ต่ำ)  -> risk_level 'L'
 *   data_json.risk_level '2' (กลาง) -> risk_level 'M'
 *   data_json.risk_level '3' (สูง)  -> risk_level 'H'
 *
 * Also updates asset_detail.data_json.risk_level (calibration records) in place
 * so the whole system speaks one language (H/M/L), and removes the now-redundant
 * key from asset.data_json after backfill.
 */
class m260625_100000_alter_asset_add_risk_level extends Migration
{
    public function safeUp()
    {
        // 1. Column + index
        $this->addColumn(
            '{{%asset}}',
            'risk_level',
            $this->string(1)->null()->after('asset_status')
                ->comment('ระดับความเสี่ยง: H|M|L (สูง|กลาง|ต่ำ)')
        );
        $this->createIndex('idx_asset_risk_level', '{{%asset}}', 'risk_level');

        // 2. Backfill asset.risk_level from data_json (1/2/3 -> L/M/H)
        $this->execute(<<<'SQL'
UPDATE {{%asset}}
SET risk_level = CASE JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.risk_level'))
    WHEN '1' THEN 'L'
    WHEN '2' THEN 'M'
    WHEN '3' THEN 'H'
    ELSE NULL
END
WHERE JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.risk_level')) IN ('1','2','3')
SQL);

        // 3. Migrate asset_detail (calibration) data_json values 1/2/3 -> L/M/H
        $this->execute(<<<'SQL'
UPDATE {{%asset_detail}}
SET data_json = JSON_SET(
    data_json,
    '$.risk_level',
    CASE JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.risk_level'))
        WHEN '1' THEN 'L'
        WHEN '2' THEN 'M'
        WHEN '3' THEN 'H'
        ELSE JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.risk_level'))
    END
)
WHERE name = 'calibration'
  AND JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.risk_level')) IN ('1','2','3')
SQL);

        // 4. Remove asset.data_json.risk_level (column is now canonical).
        //    Keep audit metadata keys (risk_level_source, risk_level_synced_at,
        //    risk_level_synced_from_id) for provenance.
        $this->execute(<<<'SQL'
UPDATE {{%asset}}
SET data_json = JSON_REMOVE(data_json, '$.risk_level')
WHERE JSON_CONTAINS_PATH(data_json, 'one', '$.risk_level')
SQL);
    }

    public function safeDown()
    {
        $this->dropIndex('idx_asset_risk_level', '{{%asset}}');
        $this->dropColumn('{{%asset}}', 'risk_level');
        // We do not restore data_json.risk_level on down.
        // The asset_detail values stay as H/M/L; if a true revert is needed,
        // create a separate down-migration to map them back to 1/2/3.
    }
}
