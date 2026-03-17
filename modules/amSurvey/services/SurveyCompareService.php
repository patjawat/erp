<?php

namespace app\modules\amSurvey\services;

use Yii;
use app\modules\am\models\Asset;
use app\modules\amSurvey\models\AssetSurveyItem;
use app\modules\amSurvey\models\AssetSurveyLog;

/**
 * Compares scanned/imported asset number with DB and determines found_status, location_match, department_match.
 */
class SurveyCompareService
{
    /**
     * Find asset by code (primary) or fsn_number.
     */
    public static function findAssetByNumber(string $assetNumber): ?Asset
    {
        $assetNumber = trim($assetNumber);
        if ($assetNumber === '') {
            return null;
        }
        return Asset::find()
            ->andWhere([
                'or',
                ['code' => $assetNumber],
                ['fsn_number' => $assetNumber],
            ])
            ->andWhere(['deleted_at' => null])
            ->one();
    }

    /**
     * Compare asset with survey input and return status + match flags.
     * Returns ['asset' => Asset|null, 'found_status' => string, 'location_match' => bool|null, 'department_match' => bool|null].
     */
    public static function compare(
        string $scannedAssetNumber,
        ?int $surveyDepartmentId = null,
        ?string $surveyLocation = null
    ): array {
        $asset = self::findAssetByNumber($scannedAssetNumber);

        if ($asset === null) {
            return [
                'asset' => null,
                'found_status' => AssetSurveyItem::FOUND_STATUS_NOT_FOUND,
                'location_match' => null,
                'department_match' => null,
            ];
        }

        $locationMatch = null;
        if ($surveyLocation !== null && $surveyLocation !== '') {
            $currentLocation = $asset->data_json['location'] ?? '';
            $locationMatch = (trim((string) $currentLocation) === trim($surveyLocation));
        }

        $departmentMatch = null;
        if ($surveyDepartmentId !== null) {
            $departmentMatch = ((int) $asset->department === (int) $surveyDepartmentId);
        }

        $foundStatus = AssetSurveyItem::FOUND_STATUS_FOUND;

        return [
            'asset' => $asset,
            'found_status' => $foundStatus,
            'location_match' => $locationMatch,
            'department_match' => $departmentMatch,
        ];
    }

    /**
     * Create survey item and optionally log location/department change.
     * @param string|null $surveyLocationText For log new_location (e.g. from web form).
     */
    public static function createSurveyItem(
        int $surveyId,
        string $scannedAssetNumber,
        array $compareResult,
        string $surveyMethod,
        ?int $surveyDepartmentId = null,
        ?int $surveyLocationId = null,
        ?string $surveyLocationText = null,
        ?string $remark = null,
        ?int $scannedBy = null
    ): AssetSurveyItem {
        $item = new AssetSurveyItem();
        $item->survey_id = $surveyId;
        $item->scanned_asset_number = $scannedAssetNumber;
        $item->asset_id = $compareResult['asset']->id ?? null;
        $item->found_status = $compareResult['found_status'];
        $item->location_match = $compareResult['location_match'];
        $item->department_match = $compareResult['department_match'];
        $item->survey_department_id = $surveyDepartmentId;
        $item->survey_location_id = $surveyLocationId;
        $item->survey_method = $surveyMethod;
        $item->remark = $remark;
        $item->scanned_by = $scannedBy;
        $item->scanned_at = date('Y-m-d H:i:s');

        $item->save(false);

        if ($item->asset_id && ($item->location_match === false || $item->department_match === false)) {
            $asset = $compareResult['asset'];
            $log = new AssetSurveyLog();
            $log->survey_item_id = $item->id;
            $log->old_location = $asset->data_json['location'] ?? null;
            $log->new_location = $surveyLocationText !== null && $surveyLocationText !== '' ? $surveyLocationText : null;
            $log->old_department = $asset->department;
            $log->new_department = $surveyDepartmentId;
            $log->changed_at = $item->scanned_at;
            $log->changed_by = $scannedBy;
            $log->save(false);
        }

        return $item;
    }
}
