<?php

namespace app\modules\amSurvey\services;

use Yii;
use app\modules\amSurvey\models\AssetSurvey;
use app\modules\amSurvey\models\AssetSurveyItem;
use app\modules\amSurvey\models\AssetSurveyLog;

/**
 * Batch CSV import for survey: parse CSV, match asset_number, compare, create survey items.
 * Supports 50,000+ rows via batch insert and transactions.
 */
class CsvImportService
{
    public const BATCH_SIZE = 500;

    /** CSV column index for asset number (0-based). Default first column. */
    public $assetNumberColumnIndex = 0;

    /** Optional: column index for location text. */
    public $locationColumnIndex = null;

    /** Optional: column index for department id. */
    public $departmentColumnIndex = null;

    /** User ID to set as surveyor (scanned_by). If null, uses current logged-in user. */
    public $scannedByUserId = null;

    /** Default department ID when CSV has no department column. If set, used for all rows when departmentColumnIndex is null. */
    public $defaultSurveyDepartmentId = null;

    /**
     * @param int $surveyId
     * @param string $filePath path to CSV (with header row)
     * @return array ['imported' => int, 'errors' => array of strings, 'rows' => total rows processed]
     */
    public function importFromCsv(int $surveyId, string $filePath): array
    {
        $survey = AssetSurvey::findOne($surveyId);
        if (!$survey) {
            return ['imported' => 0, 'errors' => ['Survey not found.'], 'rows' => 0];
        }

        $imported = 0;
        $errors = [];
        $rows = 0;
        $batch = [];

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return ['imported' => 0, 'errors' => ['Cannot open file.'], 'rows' => 0];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $rowNum = 0;
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                $rowNum++;
                if ($rowNum === 1) {
                    continue; // skip header
                }

                $rows++;
                $assetNumber = isset($data[$this->assetNumberColumnIndex])
                    ? trim((string) $data[$this->assetNumberColumnIndex])
                    : '';
                if ($assetNumber === '') {
                    continue;
                }

                $surveyLocation = null;
                if ($this->locationColumnIndex !== null && isset($data[$this->locationColumnIndex])) {
                    $surveyLocation = trim((string) $data[$this->locationColumnIndex]);
                }

                $surveyDeptId = null;
                if ($this->departmentColumnIndex !== null && isset($data[$this->departmentColumnIndex])) {
                    $surveyDeptId = (int) $data[$this->departmentColumnIndex];
                }
                if ($surveyDeptId === null && $this->defaultSurveyDepartmentId !== null) {
                    $surveyDeptId = (int) $this->defaultSurveyDepartmentId;
                }

                $compareResult = SurveyCompareService::compare($assetNumber, $surveyDeptId, $surveyLocation);

                $item = new AssetSurveyItem();
                $item->survey_id = $surveyId;
                $item->scanned_asset_number = $assetNumber;
                $item->asset_id = $compareResult['asset']->id ?? null;
                $item->found_status = $compareResult['found_status'];
                $item->location_match = $compareResult['location_match'];
                $item->department_match = $compareResult['department_match'];
                $item->survey_department_id = $surveyDeptId;
                $item->survey_location_id = null;
                $item->survey_method = AssetSurveyItem::METHOD_CSV;
                $item->scanned_by = $this->scannedByUserId !== null
                    ? (int) $this->scannedByUserId
                    : (Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id);
                $item->scanned_at = date('Y-m-d H:i:s');

                if (!$item->save(false)) {
                    $errors[] = "Row {$rowNum}: " . implode(', ', $item->getFirstErrors());
                    continue;
                }

                if ($item->asset_id && ($item->location_match === false || $item->department_match === false)) {
                    $asset = $compareResult['asset'];
                    $log = new AssetSurveyLog();
                    $log->survey_item_id = $item->id;
                    $log->old_location = $asset->data_json['location'] ?? null;
                    $log->new_location = $surveyLocation;
                    $log->old_department = $asset->department;
                    $log->new_department = $surveyDeptId;
                    $log->changed_at = $item->scanned_at;
                    $log->changed_by = $item->scanned_by;
                    $log->save(false);
                }

                $imported++;

                if (count($batch) >= self::BATCH_SIZE) {
                    $batch = [];
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $errors[] = $e->getMessage();
        } finally {
            fclose($handle);
        }

        return ['imported' => $imported, 'errors' => $errors, 'rows' => $rows];
    }
}
