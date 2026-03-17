<?php

namespace app\modules\am\models;

use yii\db\ActiveRecord;

/**
 * Yearly depreciation record (Straight Line).
 * @property int $id
 * @property int $asset_id
 * @property int $fiscal_year
 * @property float $opening_value
 * @property float $depreciation_amount
 * @property float $accumulated_depreciation
 * @property float $closing_value
 * @property bool $is_locked
 */
class AmAssetDepreciation extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%am_asset_depreciations}}';
    }

    public function getAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'asset_id']);
    }
}
