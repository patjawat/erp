<?php

namespace app\modules\am\services;

use Yii;
use yii\db\Expression;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetDetail;

/**
 * Syncs the risk_level captured on a calibration record (asset_detail.data_json)
 * to the canonical asset.risk_level COLUMN (H|M|L), but only when the given
 * calibration is the most recent one for that asset.
 *
 * Why "latest-only":
 *   Editing an older calibration should not overwrite the asset's current risk
 *   state, since that state reflects the latest assessment. If the latest
 *   calibration is the one being saved (or newly created), then the asset
 *   register should mirror its risk_level.
 *
 * Recency rule: max(COALESCE(date_end, date_start)) then highest id as tiebreak.
 *
 * Provenance: alongside the column write, audit keys are kept in asset.data_json
 *   (risk_level_source, risk_level_synced_at, risk_level_synced_from_id) so the
 *   asset edit form can show "ค่าปัจจุบันมาจากผลสอบเทียบครั้งล่าสุด".
 */
class CalibrationRiskSyncService
{
    /**
     * Push the calibration's risk_level to the asset, but only if this
     * calibration is the latest for that asset code. Safe to call on every save;
     * no-ops when nothing should change.
     *
     * @return array<string,mixed> info about what happened (for logging/debug)
     */
    public static function syncIfLatest(AssetDetail $cal): array
    {
        if (empty($cal->code)) {
            return ['synced' => false, 'reason' => 'missing_code'];
        }
        if ($cal->name !== 'calibration') {
            return ['synced' => false, 'reason' => 'not_calibration'];
        }

        $risk = self::extractRisk($cal);
        if ($risk === null) {
            return ['synced' => false, 'reason' => 'no_risk_set'];
        }

        $asset = Asset::findOne(['code' => $cal->code]);
        if (!$asset) {
            return ['synced' => false, 'reason' => 'asset_not_found'];
        }

        if (!self::isLatest($cal)) {
            return ['synced' => false, 'reason' => 'not_latest'];
        }

        $assetDj = $asset->data_json;
        if (is_string($assetDj)) {
            $assetDj = json_decode($assetDj, true) ?: [];
        }
        if (!is_array($assetDj)) {
            $assetDj = [];
        }

        $previous = $asset->risk_level;
        if ((string) $previous === (string) $risk
            && ($assetDj['risk_level_source'] ?? null) === 'calibration'
            && (int) ($assetDj['risk_level_synced_from_id'] ?? 0) === (int) $cal->id) {
            return ['synced' => false, 'reason' => 'unchanged'];
        }

        // Canonical risk value lives on the column; data_json keeps audit
        // metadata so we can later show "came from calibration X" in the UI.
        $asset->risk_level = (string) $risk;
        $assetDj['risk_level_source'] = 'calibration';
        $assetDj['risk_level_synced_at'] = date('Y-m-d H:i:s');
        $assetDj['risk_level_synced_from_id'] = (int) $cal->id;
        $asset->data_json = $assetDj;

        try {
            $asset->save(false, ['risk_level', 'data_json']);
        } catch (\Throwable $e) {
            Yii::error(
                '[CalibrationRiskSyncService] failed to update asset ' . $asset->id . ': ' . $e->getMessage(),
                __METHOD__
            );
            return ['synced' => false, 'reason' => 'save_failed', 'error' => $e->getMessage()];
        }

        return [
            'synced'   => true,
            'asset_id' => (int) $asset->id,
            'previous' => $previous,
            'current'  => (string) $risk,
        ];
    }

    /**
     * Get a normalized H/M/L risk_level from the calibration's data_json.
     * Tolerates legacy numeric codes (1/2/3) in case the migration missed any rows.
     * Returns null when no valid risk is set.
     */
    private static function extractRisk(AssetDetail $cal): ?string
    {
        $dj = $cal->data_json;
        if (is_string($dj)) {
            $dj = json_decode($dj, true) ?: [];
        }
        if (!is_array($dj)) {
            return null;
        }
        $v = $dj['risk_level'] ?? null;
        if ($v === null || $v === '') {
            return null;
        }
        $v = (string) $v;
        // Legacy code tolerance.
        $legacyMap = ['1' => 'L', '2' => 'M', '3' => 'H'];
        if (isset($legacyMap[$v])) {
            $v = $legacyMap[$v];
        }
        if (!in_array($v, ['L', 'M', 'H'], true)) {
            return null;
        }
        return $v;
    }

    /**
     * True if no other calibration for the same asset code has a more recent
     * effective date (date_end || date_start). Ties broken by highest id.
     */
    public static function isLatest(AssetDetail $cal): bool
    {
        $latestId = AssetDetail::find()
            ->andWhere(['name' => 'calibration', 'code' => $cal->code])
            ->orderBy([
                new Expression('COALESCE(date_end, date_start) DESC'),
                'id' => SORT_DESC,
            ])
            ->select('id')
            ->limit(1)
            ->scalar();

        return (int) $latestId === (int) $cal->id;
    }
}
