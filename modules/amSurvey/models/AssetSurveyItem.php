<?php

namespace app\modules\amSurvey\models;

use Yii;
use app\modules\am\models\Asset;
use app\modules\hr\models\Organization;

/**
 * One survey record per asset scan/import.
 *
 * @property int $id
 * @property int $survey_id
 * @property int|null $asset_id
 * @property string $scanned_asset_number
 * @property string $found_status FOUND|NOT_FOUND|NEW_ASSET
 * @property bool|null $location_match
 * @property bool|null $department_match
 * @property int|null $survey_location_id
 * @property int|null $survey_department_id
 * @property string $survey_method WEB|CSV|QRCODE
 * @property string|null $remark
 * @property int|null $scanned_by
 * @property string|null $scanned_at
 */
class AssetSurveyItem extends \yii\db\ActiveRecord
{
    const FOUND_STATUS_FOUND = 'FOUND';
    const FOUND_STATUS_NOT_FOUND = 'NOT_FOUND';
    const FOUND_STATUS_NEW_ASSET = 'NEW_ASSET';

    const METHOD_WEB = 'WEB';
    const METHOD_CSV = 'CSV';
    const METHOD_QRCODE = 'QRCODE';

    public static function tableName()
    {
        return '{{%am_asset_survey_items}}';
    }

    public function rules()
    {
        return [
            [['survey_id', 'scanned_asset_number', 'found_status', 'survey_method'], 'required'],
            [['survey_id', 'asset_id', 'survey_location_id', 'survey_department_id', 'scanned_by'], 'integer'],
            [['location_match', 'department_match'], 'boolean'],
            [['remark'], 'string'],
            [['scanned_at'], 'safe'],
            [['scanned_asset_number'], 'string', 'max' => 255],
            [['found_status'], 'in', 'range' => [self::FOUND_STATUS_FOUND, self::FOUND_STATUS_NOT_FOUND, self::FOUND_STATUS_NEW_ASSET]],
            [['survey_method'], 'in', 'range' => [self::METHOD_WEB, self::METHOD_CSV, self::METHOD_QRCODE]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'survey_id' => 'โครงการสำรวจ',
            'asset_id' => 'ครุภัณฑ์',
            'scanned_asset_number' => 'หมายเลขที่สแกน',
            'found_status' => 'สถานะ',
            'location_match' => 'สถานที่ตรง',
            'department_match' => 'หน่วยงานตรง',
            'survey_location_id' => 'สถานที่สำรวจ',
            'survey_department_id' => 'หน่วยงานสำรวจ',
            'survey_method' => 'วิธีสำรวจ',
            'remark' => 'หมายเหตุ',
            'scanned_by' => 'ผู้สำรวจ',
            'scanned_at' => 'วันเวลาสำรวจ',
        ];
    }

    public function getSurvey()
    {
        return $this->hasOne(AssetSurvey::class, ['id' => 'survey_id']);
    }

    public function getAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'asset_id']);
    }

    public function getSurveyDepartment()
    {
        return $this->hasOne(Organization::class, ['id' => 'survey_department_id']);
    }

    public function getLogs()
    {
        return $this->hasMany(AssetSurveyLog::class, ['survey_item_id' => 'id']);
    }
}
