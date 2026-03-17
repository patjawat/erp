<?php

namespace app\modules\am\models;

use yii\db\ActiveRecord;

/**
 * Fiscal year closing — locks depreciation records for that year.
 * @property int $id
 * @property int $fiscal_year
 * @property string $closed_at
 * @property int|null $closed_by
 * @property string|null $remark
 */
class AmDepreciationClosing extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%am_depreciation_closings}}';
    }
}
