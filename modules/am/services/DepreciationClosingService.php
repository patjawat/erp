<?php

namespace app\modules\am\services;

use Yii;
use app\modules\am\models\Asset;
use app\modules\am\models\AmAssetDepreciation;
use app\modules\am\models\AmDepreciationClosing;

/**
 * Fiscal year closing: generate yearly depreciation records and lock them.
 * Straight line: annual = (price - residual_value) / useful_life.
 */
class DepreciationClosingService
{
    /**
     * Close a fiscal year (CE): create am_asset_depreciations for all eligible assets and record closing.
     * Idempotent per asset per year: skips if record exists.
     * @param int $fiscalYear e.g. 2024 (CE)
     * @return array{success: bool, message: string, created: int, skipped: int}
     */
    public static function closeYear(int $fiscalYear): array
    {
        $schema = Yii::$app->db->getSchema()->getTableSchema(AmAssetDepreciation::tableName(), true);
        if ($schema === null) {
            return ['success' => false, 'message' => 'ตาราง am_asset_depreciations ยังไม่มี', 'created' => 0, 'skipped' => 0];
        }

        $exists = AmDepreciationClosing::find()->where(['fiscal_year' => $fiscalYear])->exists();
        if ($exists) {
            return ['success' => true, 'message' => 'ปีนี้ปิดไปแล้ว', 'created' => 0, 'skipped' => 0];
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
        $yearStart = $fiscalYear . '-01-01';
        $yearEnd = $fiscalYear . '-12-31';

        foreach ($assets as $asset) {
            $receiveDate = $asset->receive_date;
            if ($receiveDate === null) {
                continue;
            }
            $receiveYear = (int) date('Y', strtotime($receiveDate));
            if ($fiscalYear < $receiveYear) {
                continue;
            }
            $yearsUsed = $fiscalYear - $receiveYear;
            $usefulLife = (int) $asset->useful_life;
            if ($yearsUsed >= $usefulLife) {
                continue;
            }

            $existing = AmAssetDepreciation::find()
                ->where(['asset_id' => $asset->id, 'fiscal_year' => $fiscalYear])
                ->one();
            if ($existing) {
                $skipped++;
                continue;
            }

            $price = (float) $asset->price;
            $schedule = AssetDepreciationService::generateDepreciationSchedule($asset);
            if (!$schedule['can_calculate'] || empty($schedule['schedule'])) {
                continue;
            }
            $yearIndex = $fiscalYear - $receiveYear;
            if (!isset($schedule['schedule'][$yearIndex])) {
                continue;
            }
            $rowData = $schedule['schedule'][$yearIndex];
            $opening = $rowData['beginning_value'];
            $annual = $rowData['depreciation'];
            $accumulated = $rowData['accumulated_depreciation'];
            $closing = $rowData['remaining_value'];

            $row = new AmAssetDepreciation();
            $row->asset_id = $asset->id;
            $row->fiscal_year = $fiscalYear;
            $row->opening_value = round($opening, 2);
            $row->depreciation_amount = round($annual, 2);
            $row->accumulated_depreciation = round($accumulated, 2);
            $row->closing_value = round(max(0, $closing), 2);
            $row->is_locked = false;
            $row->created_at = date('Y-m-d H:i:s');
            $row->save(false);
            $created++;
        }

        $closingRow = new AmDepreciationClosing();
        $closingRow->fiscal_year = $fiscalYear;
        $closingRow->closed_at = date('Y-m-d H:i:s');
        $closingRow->closed_by = Yii::$app->user->isGuest ? null : Yii::$app->user->id;
        $closingRow->save(false);

        Yii::$app->db->createCommand()->update(
            AmAssetDepreciation::tableName(),
            ['is_locked' => true],
            ['fiscal_year' => $fiscalYear]
        )->execute();

        return [
            'success' => true,
            'message' => "ปิดปี {$fiscalYear} เรียบร้อย สร้าง {$created} รายการ ข้าม {$skipped} รายการ",
            'created' => $created,
            'skipped' => $skipped,
        ];
    }
}
