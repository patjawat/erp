<?php

namespace app\modules\am\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use app\components\AppHelper;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetSearch;
use app\modules\am\services\ReportExportService;
use app\modules\am\services\AnnualRemainingReportService;
use app\modules\am\services\MonthlyDepreciationService;
use app\modules\am\models\AssetType;
use app\components\SiteHelper;
use app\components\ThaiDateHelper;

class ReportController extends \yii\web\Controller
{
    public function actionIndex()
    {

        $searchModel = new AssetSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        if ($searchModel->q_year != '' && $searchModel->q_month != '') {
            $d1 = ($searchModel->q_year - 543) . '-' . $searchModel->q_month . '-01';
            $queryMonth = Yii::$app->db->createCommand('SELECT LAST_DAY(:d1)')
                ->bindValue(':d1', $d1)
                ->queryScalar();
            $searchModel->q_lastDay = $queryMonth;
        } else {
            $queryMonth = Yii::$app->db->createCommand('SELECT LAST_DAY(now())')
                ->queryScalar();
            $searchModel->q_lastDay = $queryMonth;
        }
        $sql = "SELECT x5.*,
                                               SUM(IF(x5.date_number > (x5.service_life * 12),1,(x5.x_total + x5.month_price))) as price_last_month,
                                               SUM(x5.x_total) as total
                                               FROM(
                                               SELECT x4.*,
                                               IF((x4.price - total_price) < 1,1,ROUND((x4.price - total_price),2)) as x_total
                                               FROM (
                                               SELECT x3.*,
                                               IF(x3.count_days > 15, ROUND(x3.date_number * ((x3.price / x3.service_life)/12),2),0) as total_price,
                                                      (x3.days_x2 * price_days) as total_price2
                                                  FROM (
                                                      SELECT x2.*,

                                                  ROUND(x2.price  /  (DATEDIFF(DATE_FORMAT(receive_date + INTERVAL x2.service_life YEAR,'%Y-%m-%d'),receive_date)),2) as price_days,
                                                  DATEDIFF(x2.date,x2.receive_date) as days_x2,
                                                  IF(x2.date_number = 1, DATEDIFF(date,receive_date),x2.days_of_month) as count_days

                                                  FROM (select x1.*,

                                                   DAYOFMONTH(LAST_DAY(DATE_FORMAT(date, '%Y-%m-%d'))) as days_of_month,
                                                                          ((TIMESTAMPDIFF(MONTH,receive_date,LAST_DAY(date))+1)) as date_number
                                                                          FROM (
                                                                          SELECT
                                                                          a.id,
                                                                          a.asset_name,
                                                                          i.title,
                                                                          a.code,
                                                                          asset_type.title as type_name,
                                                                          asset_type.code as type_code,
                                                                          a.data_json->'$.service_life' as service_life,
                                                                          CAST(a.data_json->'$.depreciation'as DECIMAL(4,2)) as depreciation,
                                                                          asset_group_id,
                                                                          receive_date,
                                                                          ('" . $searchModel->q_lastDay . "') as date,
                                                                          price,
                                                                            asset_status,
                                                                          (DATEDIFF(DATE_FORMAT(receive_date + INTERVAL JSON_EXTRACT(a.data_json, '$.service_life') YEAR,'%Y-%m-%d'),receive_date)) as all_days,
                                                                          (price/CAST(a.data_json->'$.service_life' as UNSIGNED)) as price_year,
                                                                          ROUND((price/CAST(a.data_json->'$.service_life' as UNSIGNED) / 12),2) as month_price

                                                                          FROM asset a
                                                                          LEFT JOIN categorise i ON i.code = a.asset_item_id
                                                                          LEFT JOIN categorise asset_type ON i.category_id = asset_type.code AND asset_type.name = 'asset_type'
                                                                          WHERE asset_type.code IS NOT NULL ) as x1) as x2) as x3 WHERE   x3.receive_date <= x3.date AND x3.receive_date <= x3.date AND x3.asset_status = 1) as x4) as x5 GROUP BY x5.type_code";
        $querys = Yii::$app->db->createCommand($sql)->queryAll();
        // ->bindValue(':q_date',$d1)
        // ->queryScalar();
        $data = [];
        foreach ($querys as $query) {
            $data[] = [
                'total' => $query['total'],
            ];
        }
        $count = count(Yii::$app->db->createCommand($sql)->queryAll());

        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'totalCount' => $count,
        ]);

        $totalPrice = 0;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            // 'querys' => $querys,
            'queryMonth' => $queryMonth,
            'totalPrice' => $totalPrice,
        ]);
    }

    /**
     * Annual remaining asset report. format=csv or xlsx for export.
     */
    public function actionRegister()
    {
        $thaiYear = (int) $this->request->get('year', AppHelper::YearBudget());
        $reportData = AnnualRemainingReportService::getReportData($thaiYear);

        if ($this->request->get('format') === 'xlsx') {
            $output = AnnualRemainingReportService::saveXlsx($thaiYear);
            return Yii::$app->response->sendFile($output['filePath'], $output['fileName']);
        }

        if ($this->request->get('format') === 'csv') {
            $rows = [];
            $headers = ['หมวด', 'รหัส', 'วันที่รับ', 'รายการ', 'ราคาทุน', 'ค่าเสื่อมสะสม', 'คงเหลือ', 'อายุการใช้งาน'];
            foreach ($reportData['rows'] as $row) {
                $rows[] = [
                    'หมวด' => $row['bucket'] ?? '',
                    'รหัส' => $row['code'] ?? '',
                    'วันที่รับ' => $row['receive_date'] ?? '',
                    'รายการ' => $row['name'] ?? '',
                    'ราคาทุน' => $row['cost'] ?? '',
                    'ค่าเสื่อมสะสม' => $row['accumulated_current'] ?? '',
                    'คงเหลือ' => $row['remaining_current'] ?? '',
                    'อายุการใช้งาน' => $row['useful_life'] ?? '',
                ];
            }
            ReportExportService::sendCsv('annual-remaining-report-' . $thaiYear . '.csv', $rows, $headers);
        }

        return $this->render('register', [
            'thaiYear' => $thaiYear,
            'reportData' => $reportData,
        ]);
    }

    /**
     * Depreciation report (new: useful_life, residual_value). format=csv for export.
     */
    public function actionDepreciationReport()
    {
        $fiscalYear = (int) $this->request->get('year', date('Y') + 543) - 543;
        $query = Asset::find()
            ->andWhere('deleted_at IS NULL')
            ->andWhere(['not', ['useful_life' => null]])
            ->andWhere(['>', 'useful_life', 0])
            ->andWhere(['not', ['receive_date' => null]]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 100],
            'sort' => ['defaultOrder' => ['code' => SORT_ASC]],
        ]);

        if ($this->request->get('format') === 'csv') {
            $rows = [];
            $headers = ['รหัส', 'ชื่อ', 'ราคาทุน', 'มูลค่าซาก(มาตรฐาน)', 'อายุ(ปี)', 'ค่าเสื่อม/ปี', 'วันที่รับ'];
            foreach ($dataProvider->getModels() as $a) {
                $annual = \app\modules\am\services\AssetDepreciationService::getAnnualDepreciationForAsset($a);
                $rows[] = [
                    'รหัส' => $a->code ?? '',
                    'ชื่อ' => $a->asset_name ?? $a->AssetitemName() ?? '',
                    'ราคาทุน' => $a->price ?? '',
                    'มูลค่าซาก(มาตรฐาน)' => 1,
                    'อายุ(ปี)' => $a->useful_life ?? '',
                    'ค่าเสื่อม/ปี' => $annual !== null ? $annual : '',
                    'วันที่รับ' => $a->receive_date ? date('d/m/Y', strtotime($a->receive_date)) : '',
                ];
            }
            ReportExportService::sendCsv('depreciation-report-' . date('Y-m-d') . '.csv', $rows, $headers);
        }

        return $this->render('depreciation-report', [
            'dataProvider' => $dataProvider,
            'fiscalYear' => $fiscalYear,
        ]);
    }

    /**
     * Asset movement report (from am_asset_transactions). format=csv for export.
     */
    public function actionMovementReport()
    {
        $schema = Yii::$app->db->getSchema()->getTableSchema('{{%am_asset_transactions}}', true);
        if ($schema === null) {
            return $this->render('movement-report', ['dataProvider' => null, 'tableExists' => false]);
        }

        $sql = 'SELECT t.id, t.asset_id, a.code AS asset_code, t.transaction_type, t.from_location, t.to_location, t.from_department, t.to_department, t.remark, t.created_at
                FROM {{%am_asset_transactions}} t
                LEFT JOIN {{%asset}} a ON a.id = t.asset_id AND a.deleted_at IS NULL
                ORDER BY t.created_at DESC';
        $countSql = 'SELECT COUNT(*) FROM {{%am_asset_transactions}}';
        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'totalCount' => (int) Yii::$app->db->createCommand($countSql)->queryScalar(),
            'pagination' => ['pageSize' => 50],
        ]);

        if ($this->request->get('format') === 'csv') {
            $rows = [];
            $headers = ['รหัสครุภัณฑ์', 'ประเภท', 'จากสถานที่', 'ถึงสถานที่', 'จากหน่วยงาน', 'ถึงหน่วยงาน', 'หมายเหตุ', 'วันที่'];
            foreach ($dataProvider->getModels() as $row) {
                $rows[] = [
                    'รหัสครุภัณฑ์' => $row['asset_code'] ?? '',
                    'ประเภท' => $row['transaction_type'] ?? '',
                    'จากสถานที่' => $row['from_location'] ?? '',
                    'ถึงสถานที่' => $row['to_location'] ?? '',
                    'จากหน่วยงาน' => $row['from_department'] ?? '',
                    'ถึงหน่วยงาน' => $row['to_department'] ?? '',
                    'หมายเหตุ' => $row['remark'] ?? '',
                    'วันที่' => $row['created_at'] ?? '',
                ];
            }
            ReportExportService::sendCsv('asset-movement-' . date('Y-m-d') . '.csv', $rows, $headers);
        }

        return $this->render('movement-report', [
            'dataProvider' => $dataProvider,
            'tableExists' => true,
        ]);
    }

    /**
     * Asset survey report (from am_asset_survey_items). format=csv for export.
     * Survey data lives in amSurvey module; this report reads am_asset_survey_items.
     */
    public function actionSurveyReport()
    {
        $schema = Yii::$app->db->getSchema()->getTableSchema('{{%am_asset_survey_items}}', true);
        if ($schema === null) {
            return $this->render('survey-report', ['dataProvider' => null, 'tableExists' => false, 'surveys' => []]);
        }

        $surveyId = (int) $this->request->get('survey_id', 0);
        $sql = 'SELECT i.id, i.survey_id, s.survey_name, i.scanned_asset_number, i.found_status, i.location_match, i.department_match, i.survey_method, i.scanned_at
                FROM {{%am_asset_survey_items}} i
                LEFT JOIN {{%am_asset_surveys}} s ON s.id = i.survey_id
                WHERE 1=1';
        $params = [];
        if ($surveyId > 0) {
            $sql .= ' AND i.survey_id = :sid';
            $params[':sid'] = $surveyId;
        }
        $sql .= ' ORDER BY i.scanned_at DESC';
        $countSql = 'SELECT COUNT(*) FROM {{%am_asset_survey_items}} i WHERE 1=1' . ($surveyId > 0 ? ' AND i.survey_id = :sid' : '');
        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $params,
            'totalCount' => (int) Yii::$app->db->createCommand($countSql, $params)->queryScalar(),
            'pagination' => ['pageSize' => 50],
        ]);

        if ($this->request->get('format') === 'csv') {
            $rows = [];
            $headers = ['โครงการสำรวจ', 'หมายเลขที่สแกน', 'สถานะ', 'สถานที่ตรง', 'หน่วยงานตรง', 'วิธีสำรวจ', 'วันเวลาสำรวจ'];
            foreach ($dataProvider->getModels() as $row) {
                $rows[] = [
                    'โครงการสำรวจ' => $row['survey_name'] ?? '',
                    'หมายเลขที่สแกน' => $row['scanned_asset_number'] ?? '',
                    'สถานะ' => $row['found_status'] ?? '',
                    'สถานที่ตรง' => isset($row['location_match']) ? ($row['location_match'] ? 'ใช่' : 'ไม่') : '',
                    'หน่วยงานตรง' => isset($row['department_match']) ? ($row['department_match'] ? 'ใช่' : 'ไม่') : '',
                    'วิธีสำรวจ' => $row['survey_method'] ?? '',
                    'วันเวลาสำรวจ' => $row['scanned_at'] ?? '',
                ];
            }
            ReportExportService::sendCsv('asset-survey-' . date('Y-m-d') . '.csv', $rows, $headers);
        }

        $surveys = Yii::$app->db->createCommand('SELECT id, survey_name FROM {{%am_asset_surveys}} ORDER BY survey_year DESC')->queryAll();
        return $this->render('survey-report', [
            'dataProvider' => $dataProvider,
            'tableExists' => true,
            'surveys' => $surveys,
        ]);
    }

    /**
     * Monthly depreciation report: preview and PDF for government submission.
     * Supports filter by asset_type_id and summary by type.
     */
    public function actionMonthlyDepreciation()
    {
        $fiscalYear = (int) ($this->request->get('fiscal_year') ?: date('Y'));
        $month = (int) ($this->request->get('month') ?: date('n'));
        $month = max(1, min(12, $month));
        $assetTypeId = $this->request->get('asset_type_id');

        $records = MonthlyDepreciationService::getRecordsForMonth($fiscalYear, $month);

        if ($assetTypeId !== null && $assetTypeId !== '') {
            $assetTypeId = (string) $assetTypeId;
            $records = array_values(array_filter($records, function ($r) use ($assetTypeId) {
                $a = $r->asset;
                $id = $a->asset_type_id;
                if ($id === null) {
                    return false;
                }
                return (string) $id === $assetTypeId;
            }));
        }

        $totalDepreciation = 0;
        $summaryByType = [];
        foreach ($records as $r) {
            $totalDepreciation += (float) $r->depreciation_amount;
            $typeLabel = $r->asset->assetType->title ?? $r->asset->AssetTypeName() ?? '-';
            if (!isset($summaryByType[$typeLabel])) {
                $summaryByType[$typeLabel] = [
                    'count' => 0,
                    'beginning_value' => 0,
                    'depreciation_amount' => 0,
                    'accumulated_depreciation' => 0,
                    'remaining_value' => 0,
                ];
            }
            $summaryByType[$typeLabel]['count']++;
            $summaryByType[$typeLabel]['beginning_value'] += (float) $r->beginning_value;
            $summaryByType[$typeLabel]['depreciation_amount'] += (float) $r->depreciation_amount;
            $summaryByType[$typeLabel]['accumulated_depreciation'] += (float) $r->accumulated_depreciation;
            $summaryByType[$typeLabel]['remaining_value'] += (float) $r->remaining_value;
        }
        ksort($summaryByType, SORT_FLAG_CASE | SORT_NATURAL);

        $thaiMonths = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
        ];
        $periodLabel = $thaiMonths[$month] . ' ' . ($fiscalYear + 543);

        $assetTypes = AssetType::find()
            ->where(['name' => 'asset_type'])
            ->orderBy(['title' => SORT_ASC])
            ->all();

        if ($this->request->get('format') === 'pdf') {
            return $this->renderMonthlyDepreciationPdf($records, $totalDepreciation, $fiscalYear, $month, $periodLabel, $summaryByType);
        }

        $schema = Yii::$app->db->getSchema()->getTableSchema('{{%am_asset_depreciation_monthly}}', true);
        return $this->render('monthly-depreciation', [
            'records' => $records,
            'totalDepreciation' => $totalDepreciation,
            'summaryByType' => $summaryByType,
            'fiscalYear' => $fiscalYear,
            'month' => $month,
            'assetTypeId' => $assetTypeId,
            'assetTypes' => $assetTypes,
            'periodLabel' => $periodLabel,
            'thaiMonths' => $thaiMonths,
            'tableExists' => $schema !== null,
        ]);
    }

    /**
     * Generate and send monthly depreciation PDF (A4 portrait, Thai).
     */
    protected function renderMonthlyDepreciationPdf($records, $totalDepreciation, $fiscalYear, $month, $periodLabel, $summaryByType = [])
    {
        $info = SiteHelper::getInfo();
        $orgName = $info['company_name'] ?? 'หน่วยงาน';
        $printDate = ThaiDateHelper::formatThaiDate(date('Y-m-d')) . ' ' . date('H:i') . ' น.';

        $html = $this->renderPartial('monthly-depreciation-pdf', [
            'records' => $records,
            'totalDepreciation' => $totalDepreciation,
            'summaryByType' => $summaryByType,
            'fiscalYear' => $fiscalYear,
            'month' => $month,
            'orgName' => $orgName,
            'periodLabel' => $periodLabel,
            'printDate' => $printDate,
        ]);

        $fontPathTh = Yii::getAlias('@webroot/fonts');
        $ttfR = $fontPathTh . DIRECTORY_SEPARATOR . 'THSarabunNew.ttf';
        $ttfB = $fontPathTh . DIRECTORY_SEPARATOR . 'THSarabunNew Bold.ttf';
        $ttfBAlt = $fontPathTh . DIRECTORY_SEPARATOR . 'THSarabunNew-Bold.ttf';
        $config = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 15,
            'margin_bottom' => 18,
        ];
        if (is_dir($fontPathTh) && file_exists($ttfR)) {
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $defaultFont = (new \Mpdf\Config\FontVariables())->getDefaults();
            $config['fontDir'] = array_merge($defaultConfig['fontDir'], [$fontPathTh]);
            $config['fontdata'] = array_merge($defaultFont['fontdata'], [
                'thsarabun' => [
                    'R' => 'THSarabunNew.ttf',
                    'B' => file_exists($ttfB) ? 'THSarabunNew Bold.ttf' : (file_exists($ttfBAlt) ? 'THSarabunNew-Bold.ttf' : 'THSarabunNew.ttf'),
                ],
            ]);
            $config['default_font'] = 'thsarabun';
        }

        // กำหนดโฟลเดอร์ชั่วคราวให้ mPDF ใช้ @runtime/mpdf (แก้ปัญหา temp dir เขียนไม่ได้บน server)
        $tmpDir = Yii::getAlias('@runtime/mpdf');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0777, true);
        }
        $config['tempDir'] = $tmpDir;

        $mpdf = new \Mpdf\Mpdf($config);
        $mpdf->SetTitle('รายงานค่าเสื่อมรายเดือน - ' . $periodLabel);
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        $filename = 'Monthly_Depreciation_Report_' . $fiscalYear . '-' . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . '.pdf';

        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
        Yii::$app->response->content = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
        return Yii::$app->response;
    }
}
