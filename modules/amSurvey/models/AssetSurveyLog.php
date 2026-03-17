<?php

namespace app\modules\amSurvey\models;

use Yii;

/**
 * Log of location/department changes detected during survey.
 *
 * @property int $id
 * @property int $survey_item_id
 * @property string|null $old_location
 * @property string|null $new_location
 * @property int|null $old_department
 * @property int|null $new_department
 * @property string|null $changed_at
 * @property int|null $changed_by
 */
class AssetSurveyLog extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%am_asset_survey_logs}}';
    }

    public function rules()
    {
        return [
            [['survey_item_id'], 'required'],
            [['survey_item_id', 'old_department', 'new_department', 'changed_by'], 'integer'],
            [['old_location', 'new_location'], 'string', 'max' => 255],
            [['changed_at'], 'safe'],
        ];
    }

    public function getSurveyItem()
    {
        return $this->hasOne(AssetSurveyItem::class, ['id' => 'survey_item_id']);
    }
}
