<?php

namespace app\modules\am\services;

use Yii;
use app\modules\am\models\Asset;
use app\modules\am\models\AmAssetDepreciationMonthly;

/**
 * Monthly depreciation processing: calculate and store one record per asset per month.
 * Uses existing Straight Line logic (residual 1 baht, inclusive first-month days).
 * Prevents duplicate processing; allows admin regeneration with confirmation.
 */
class MonthlyDepreciationService
{
    /**
     * Run monthly depreciation for all eligible assets for the given month.
     * Each asset has at most one record per (fiscal_year, month).
     *
     * @param int $fiscalYear Calendar year (CE), e.g. 2024
     * @param int $month Month 1-12
     * @param bool $forceRegenerate If true, delete existing records for this month first (admin only)
     * @return array{success: bool, message: string, created: int, skipped: int, deleted: int}
     */
    public static function runForMonth(int $fiscalYear, int $month, bool $forceRegenerate = false): array
    {
        $month = max(1, min(12, $month));
        $schema = Yii::$app->db->getSchema()->getTableSchema(AmAssetDepreciationMonthly::tableName(), true);
        if ($schema === null) {
            return [
                'success' => false,
                'message' => 'ตาราง am_asset_depreciation_monthly ยังไม่มี กรุณารัน migration',
                'created' => 0,
                'skipped' => 0,
                'deleted' => 0,
            ];
        }

        $deleted = 0;
        if ($forceRegenerate) {
            $deleted = AmAssetDepreciationMonthly::deleteAll([
                'fiscal_year' => $fiscalYear,
                'month' => $month,
            ]);
        }

        $assets = Asset::find()
            ->andWhere(['deleted_at' => null])
            ->andWhere(['not', ['useful_life' => null]])
            ->andWhere(['>', 'useful_life', 0])
            ->andWhere(['not', ['receive_date' => null]])
            ->andWhere(['not', ['price' => null]])
            ->all();

        $created = 0;
        $skipped = 0;
        $processedAt = date('Y-m-d H:i:s');

        foreach ($assets as $asset) {
            $existing = AmAssetDepreciationMonthly::find()
                ->where([
                    'asset_id' => $asset->id,
                    'fiscal_year' => $fiscalYear,
                    'month' => $month,
                ])
                ->one();
            if ($existing) {
                $skipped++;
                continue;
            }

            $schedule = AssetDepreciationService::generateMonthlySchedule($asset);
            if (!$schedule['can_calculate'] || empty($schedule['schedule'])) {
                continue;
            }

            $row = null;
            foreach ($schedule['schedule'] as $r) {
                if ((int) $r['year'] === $fiscalYear && (int) $r['month'] === $month) {
                    $row = $r;
                    break;
                }
            }
            if ($row === null) {
                continue;
            }

            $record = new AmAssetDepreciationMonthly();
            $record->asset_id = $asset->id;
            $record->fiscal_year = $fiscalYear;
            $record->month = $month;
            $record->days_used = (int) $row['days_used'];
            $record->beginning_value = round($row['beginning_value'], 2);
            $record->depreciation_amount = round($row['depreciation'], 2);
            $record->accumulated_depreciation = round($row['accumulated_depreciation'], 2);
            $record->remaining_value = round($row['remaining_value'], 2);
            $record->processed_at = $processedAt;
            $record->save(false);
            $created++;
        }

        return [
            'success' => true,
            'message' => "ประมวลผลค่าเสื่อมรายเดือน {$fiscalYear}-" . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . " เรียบร้อย สร้าง {$created} รายการ ข้าม {$skipped} รายการ" . ($deleted > 0 ? " ลบเดิม {$deleted} รายการ" : ''),
            'created' => $created,
            'skipped' => $skipped,
            'deleted' => $deleted,
        ];
    }

    /**
     * Get monthly depreciation records for report/PDF.
     *
     * @param int $fiscalYear
     * @param int $month
     * @return AmAssetDepreciationMonthly[]
     */
    public static function getRecordsForMonth(int $fiscalYear, int $month): array
    {
        return AmAssetDepreciationMonthly::find()
            ->with(['asset', 'asset.assetType'])
            ->where(['fiscal_year' => $fiscalYear, 'month' => $month])
            ->orderBy(['asset_id' => SORT_ASC])
            ->all();
    }
}
