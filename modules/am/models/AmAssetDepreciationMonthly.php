<?php

namespace app\modules\am\models;

use yii\db\ActiveRecord;

/**
 * Monthly depreciation record (Straight Line, government standard).
 *
 * @property int $id
 * @property int $asset_id
 * @property int $fiscal_year
 * @property int $month
 * @property int $days_used
 * @property float $beginning_value
 * @property float $depreciation_amount
 * @property float $accumulated_depreciation
 * @property float $remaining_value
 * @property string|null $processed_at
 */
class AmAssetDepreciationMonthly extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%am_asset_depreciation_monthly}}';
    }

    public function getAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'asset_id']);
    }
}
