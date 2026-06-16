<?php


namespace app\modules\am\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\models\Categorise;
use app\components\AppHelper;
use app\components\DateHelper;
use app\models\UploadCsvForm;
use app\modules\am\models\Asset;
use app\components\ProductHelper;
use app\modules\inventory\models\Product;
use app\modules\am\models\AssetImportForm;
use app\modules\inventory\models\StockEvent;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Google\Service\AdExchangeBuyerII\Date;

class ImportController extends Controller
{
    /**
     * ดาวน์โหลดเทมเพลต CSV สำหรับนำเข้าข้อมูลครุภัณฑ์
     * หัวคอลัมน์และแถวตัวอย่างโหลดจาก modules/am/data/equip_import_columns.php
     */
    public function actionDownloadTemplate()
    {
        $config = require \Yii::getAlias('@app/modules/am/data/equip_import_columns.php');
        $headers = $config['headers'];
        $example = $config['sample'];

        $bom = "\xEF\xBB\xBF";
        $fp = fopen('php://temp', 'r+');
        fwrite($fp, $bom);
        fputcsv($fp, $headers);
        fputcsv($fp, $example);
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        $filename = 'template_import_ครุภัณฑ์_' . date('Ymd') . '.csv';
        \Yii::$app->response->sendContentAsFile($csv, $filename, [
            'mimeType' => 'text/csv',
            'inline' => false,
        ]);
        \Yii::$app->end();
    }

    /**
     * ดาวน์โหลดเทมเพลต Excel สำหรับอัปเดต GFMIS จากรหัสครุภัณฑ์
     */
    public function actionDownloadGfmisTemplate()
    {
        $config = require \Yii::getAlias('@app/modules/am/data/gfmis_update_columns.php');
        $spreadsheet = $this->buildGfmisTemplateSpreadsheet($config);
        $this->sendSpreadsheetAsFile(
            $spreadsheet,
            'template_update_gfmis_' . date('Ymd') . '.xlsx'
        );
    }

    /**
     * หน้าอัปโหลด Excel สำหรับอัปเดต GFMIS
     */
    public function actionGfmis()
    {
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title') ?: 'นำเข้าอัปเดต GFMIS',
                'content' => $this->renderAjax('gfmis'),
            ];
        }

        return $this->render('gfmis');
    }

    /**
     * AJAX: แสดงตัวอย่างไฟล์ Excel สำหรับอัปเดต GFMIS
     */
    public function actionPreviewGfmis()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $file = UploadedFile::getInstanceByName('xlsxFile');
        if (!$file) {
            return ['status' => 'error', 'message' => 'ไม่พบไฟล์'];
        }

        $ext = strtolower((string) $file->extension);
        if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            return ['status' => 'error', 'message' => 'รองรับเฉพาะไฟล์ Excel (.xlsx, .xls) หรือ CSV'];
        }

        $filePath = Yii::getAlias('@runtime') . '/gfmis_update_' . time() . '_' . Yii::$app->security->generateRandomString(8) . '.' . $ext;
        $file->saveAs($filePath);

        $parsed = $this->parseGfmisImportFile($filePath);
        if (($parsed['status'] ?? null) !== 'success') {
            return $parsed;
        }

        $parsed['filePath'] = $filePath;
        return $parsed;
    }

    /**
     * POST: อัปเดตค่า GFMIS ลง asset ที่มีรหัสตรงกัน
     */
    public function actionImportGfmis()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $filePath = Yii::$app->request->post('filePath');

        if (!$filePath || !is_file($filePath)) {
            return ['status' => 'error', 'message' => 'ไม่พบไฟล์'];
        }

        $parsed = $this->parseGfmisImportFile($filePath);
        if (($parsed['status'] ?? null) !== 'success') {
            return $parsed;
        }

        if (!empty($parsed['errors'])) {
            return [
                'status' => 'error',
                'message' => 'พบข้อผิดพลาดในไฟล์ กรุณาแก้ไขก่อนนำเข้า',
                'errors' => $parsed['errors'],
            ];
        }

        $updated = 0;
        $skipped = 0;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($parsed['rows'] as $row) {
                $code = trim((string) ($row['code'] ?? ''));
                if (($row['status_key'] ?? '') === 'same') {
                    $skipped++;
                    continue;
                }
                $asset = null;
                if ($code !== '') {
                    $asset = Asset::find()
                        ->where(['code' => $code])
                        ->andWhere('deleted_at IS NULL')
                        ->one();
                }
                if ($asset === null) {
                    throw new \RuntimeException('ไม่พบหมายเลขครุภัณฑ์ ' . ($code !== '' ? $code : '-'));
                }
                $asset->gfmis = $row['new_gfmis'];
                if (!$asset->save(false)) {
                    throw new \RuntimeException('ไม่สามารถอัปเดต GFMIS ของหมายเลขครุภัณฑ์ ' . ($code !== '' ? $code : '-'));
                }
                $updated++;
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($e->getMessage(), __METHOD__);
            return ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดระหว่างบันทึกข้อมูล'];
        }

        return [
            'status' => 'success',
            'message' => "อัปเดต GFMIS เรียบร้อย {$updated} รายการ" . ($skipped > 0 ? " (ไม่เปลี่ยนแปลง {$skipped} รายการ)" : ''),
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    /**
     * หน้าอัปโหลด CSV
     */
    public function actionIndex($order_id = null)
    {
        $model = new AssetImportForm();

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax(
                    'index',
                    [
                        'model' => $model
                    ]
                ),
            ];
        } else {
            return $this->render('index', ['model' => $model]);
        }
    }


    // ตรวจสอบความถูกต้อง
    public function actionValidate()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new AssetImportForm();
        $result = [];

        if ($this->request->isPost && $model->load($this->request->post())) {

            // ตรวจสอบค่า required
            if (empty($model->asset_type_id)) {
                $model->addError('asset_type_id', 'ต้องระบุ');
            }

            // ตรวจสอบรหัสซ้ำ (ตัวอย่าง)
            if (!empty($model->code)) {
                $exists = Asset::find()->where(['code' => $model->code])->exists();
                if ($exists) {
                    $model->addError('code', 'รหัสซ้ำ');
                }
            }

            // เก็บ errors ในรูปแบบที่ ActiveForm ต้องการ
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
        }

        return $result; // ถ้าไม่มี error → จะส่ง empty array → JS รู้ว่า valid
    }


    /**
     * AJAX: แสดงตัวอย่าง CSV
     */
    public function actionPreview()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new AssetImportForm();
        $model->csvFile = UploadedFile::getInstanceByName('csvFile');
        if ($model) {
            // บันทึกไฟล์ชั่วคราว
            $filePath = Yii::getAlias('@runtime') . '/import_' . time() . '.' . $model->csvFile->extension;
            $model->csvFile->saveAs($filePath);

            // อ่าน CSV — ไม่แจ้งเตือนรหัสซ้ำใน preview; การตรวจหมายเลขครุภัณฑ์ซ้ำทำตอนนำเข้าจริง
            $previewData = [];

            if (($handle = fopen($filePath, "r")) !== false) {
                $row = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $previewData[] = $data;
                    $row++;
                }
                fclose($handle);
                return [
                    'status' => 'success',
                    'preview' => $previewData,
                    'duplicates' => [], // ยกเลิกการแจ้งเตือนรหัสซ้ำใน preview
                    'filePath' => $filePath,
                ];
            } else {
                return [
                    'status' => 'error',
                    'errors' => $model->getErrors(),
                ];
            }
        }
    }

    /**
     * POST: นำเข้าข้อมูลจริง
     */
    public function actionImportCsv()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $filePath = Yii::$app->request->post('filePath');
        $postData = Yii::$app->request->post();

        if (!$filePath || !file_exists($filePath)) {
            return ['status' => 'error', 'message' => 'ไม่พบไฟล์'];
        }

        $rowsByCode = []; // รหัสครุภัณฑ์ซ้ำในไฟล์ → แถวหลังแทนที่แถวก่อน (อัปเดตล่าสุด)
        $rowsNoCode = [];
        $errorRows = [];
        $rowNumber = 0;

        if (($handle = fopen($filePath, "r")) !== false) {
            $columnIndexes = null;
            while (($data = fgetcsv($handle, 0, ",")) !== false) {
                $rowNumber++;
                if ($rowNumber == 1) {
                    $columnIndexes = $this->equipImportColumnIndexes($data);
                    continue; // ข้าม header
                }

                $code = trim((string) ($data[0] ?? ''));

                $model = null;
                if ($code !== '') {
                    $existing = Asset::find()->where(['code' => $code])->one();
                    if ($existing !== null) {
                        $gid = $existing->asset_group_id;
                        if ($gid != 4 && (string) $gid !== '4') {
                            $errorRows[] = [
                                'row' => $rowNumber,
                                'code' => $code,
                                'errors' => ['code' => ['รหัสนี้มีในระบบเป็นประเภทอื่น — นำเข้าครุภัณฑ์อัปเดตได้เฉพาะรายการในกลุ่มครุภัณฑ์']],
                            ];
                            continue;
                        }
                        $model = $existing;
                    }
                }
                if ($model === null) {
                    $model = new Asset();
                }

                $ci = $columnIndexes ?? $this->equipImportColumnIndexes([]);
                $usefulLifeIdx = $ci['useful_life'];
                $orderIdx = $ci['order_number'];
                $noteIdx = $ci['note'];

                $depreciationParsed = ['value' => null, 'error' => null];
                if ($ci['depreciation'] !== null) {
                    $depreciationParsed = $this->parseDepreciationRateForImport($data[$ci['depreciation']] ?? '');
                    if ($depreciationParsed['error'] !== null) {
                        $errorRows[] = [
                            'row' => $rowNumber,
                            'code' => $code,
                            'errors' => ['depreciation' => [$depreciationParsed['error']]],
                        ];
                        continue;
                    }
                }

                $model->asset_type_id = $postData['asset_type_id'];
                $model->asset_category_id = $postData['asset_category_id'];
                $model->code = $data[0];
                $model->fsn_number = $data[1]; // หมายเลข FSN ซ้ำได้
                $model->asset_name = $data[2];
                $incomingJson = [
                    'brand' => $data[3],
                    'asset_model' => $data[4],
                    'color_name' => $data[5],
                    'unit' => $data[6],
                    'serial_number' => $data[7],
                    'budget_type' => $this->resolveBudgetTypeFromImport($data[9] ?? ''),
                    'inspection_date' => $this->normalizeDateForDb($data[11] ?? ''),
                    'expire_date' => $this->normalizeDateForDb($data[14] ?? ''),
                    'location' => $data[16],
                    'fsn_old' => $data[0],
                    'vendor_id' => $this->resolveVendorFromImport($data[18] ?? '', $data[19] ?? ''),
                    'vendor_name' => trim((string) ($data[19] ?? '')),
                    'order_number' => trim((string) ($data[$orderIdx] ?? '')),
                    'note' => trim((string) ($data[$noteIdx] ?? '')),
                ];
                $baseJson = $model->isNewRecord ? [] : $this->assetImportDataJsonAsArray($model->data_json);
                $model->data_json = array_merge($baseJson, $incomingJson);
                if ($depreciationParsed['value'] !== null) {
                    $model->depreciation_rate = $depreciationParsed['value'];
                }
                $model->price = $data[8];
                $model->purchase = $this->resolvePurchaseFromImport($data[10] ?? '');
                $model->receive_date = $this->normalizeDateForDb($data[13] ?? '');
                $model->on_year = $data[12];
                if (!empty($data[15])) {
                    $model->on_year = $data[15]; // ปีงบประมาณ (ซ้ำ) ถ้ามีให้ใช้ค่านี้แทน
                }
                $model->license_plate = $data[17];
                $model->useful_life = (int) ($data[$usefulLifeIdx] ?? 0); // อายุการใช้งาน (ปี)
                if ($model->isNewRecord) {
                    $model->asset_status = 'active';
                    $model->asset_condition = 'good';
                } else {
                    if (empty($model->asset_status) || !in_array((string) $model->asset_status, ['active', 'borrowed', 'repair', 'wait_dispose', 'disposed'], true)) {
                        $model->asset_status = 'active';
                    }
                    if (empty($model->asset_condition)) {
                        $model->asset_condition = 'good';
                    }
                }
                $model->asset_group_id = 4;

                // validate ทั้งหมด
                $model->validate();

                // ลบ error ของ price และ csvFile ออก
                $errors = $model->getErrors();
                unset($errors['price'], $errors['csvFile']);

                if (!empty($errors)) {
                    $errorRows[] = [
                        'row' => $rowNumber,
                        'code' => $data[0],
                        'errors' => $errors
                    ];
                } else {
                    if ($code !== '') {
                        $rowsByCode[$code] = $model;
                    } else {
                        $rowsNoCode[] = $model;
                    }
                }
            }
            fclose($handle);

            // ถ้ามี error แถวไหน → return ไม่บันทึก
            if (!empty($errorRows)) {
                return [
                    'status' => 'error',
                    'message' => 'พบข้อผิดพลาดใน CSV',
                    'errors' => $errorRows
                ];
            }

            $rowsData = array_merge(array_values($rowsByCode), $rowsNoCode);

            $imported = 0;
            $created = 0;
            $updated = 0;
            foreach ($rowsData as $model) {
                $wasNew = $model->isNewRecord;
                if ($model->save(false)) {
                    $imported++;
                    if ($wasNew) {
                        $created++;
                    } else {
                        $updated++;
                    }
                }
            }

            $msg = "นำเข้าข้อมูลเรียบร้อย {$imported} แถว";
            if ($created > 0 || $updated > 0) {
                $msg .= " (สร้างใหม่ {$created}, อัปเดต {$updated})";
            }

            return [
                'status' => 'success',
                'message' => $msg,
                'created' => $created,
                'updated' => $updated,
            ];
        }

        return ['status' => 'error', 'message' => 'ไม่สามารถเปิดไฟล์ CSV ได้'];
    }

    /**
     * สร้าง workbook ตัวอย่างสำหรับอัปเดต GFMIS
     *
     * @param array{sheet_name?: string, headers: array<int, string>, sample?: array<int, array<int, mixed>>} $config
     */
    protected function buildGfmisTemplateSpreadsheet(array $config): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheetName = (string) ($config['sheet_name'] ?? 'GFMIS Update');
        $sheet->setTitle(mb_substr($sheetName, 0, 31));

        $headers = array_values($config['headers'] ?? []);
        $sampleRows = array_values($config['sample'] ?? []);

        foreach ($headers as $colIndex => $header) {
            $cell = Coordinate::stringFromColumnIndex($colIndex + 1) . '1';
            $sheet->setCellValueExplicit($cell, (string) $header, DataType::TYPE_STRING);
        }

        $startRow = 2;
        foreach ($sampleRows as $rowOffset => $sampleRow) {
            foreach ($sampleRow as $colIndex => $value) {
                $cell = Coordinate::stringFromColumnIndex($colIndex + 1) . ($startRow + $rowOffset);
                $sheet->setCellValueExplicit($cell, (string) $value, DataType::TYPE_STRING);
            }
        }

        $lastColumn = Coordinate::stringFromColumnIndex(max(1, count($headers)));
        $lastRow = max(1, $startRow + count($sampleRows) - 1);

        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '93C5FD'],
                ],
            ],
        ]);

        if ($lastRow >= 2) {
            $sheet->getStyle('A2:' . $lastColumn . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E2E8F0'],
                    ],
                ],
            ]);
        }

        foreach (['A', 'B'] as $col) {
            $sheet->getStyle($col . ':' . $col)->getNumberFormat()->setFormatCode('@');
        }

        $sheet->getColumnDimension('A')->setWidth(26);
        $sheet->getColumnDimension('B')->setWidth(26);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:' . $lastColumn . '1');
        $sheet->getRowDimension(1)->setRowHeight(24);

        return $spreadsheet;
    }

    /**
     * ส่ง workbook เป็นไฟล์ดาวน์โหลด
     */
    protected function sendSpreadsheetAsFile(Spreadsheet $spreadsheet, string $filename): void
    {
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = (string) ob_get_clean();
        $spreadsheet->disconnectWorksheets();
        unset($writer, $spreadsheet);

        \Yii::$app->response->sendContentAsFile($content, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline' => false,
        ]);
        \Yii::$app->end();
    }

    /**
     * อ่านไฟล์ Excel/CSV เป็นแถวข้อมูล
     *
     * @return array<int, array{row:int, cells: array<int, string>}>
     */
    protected function readSpreadsheetRows(string $filePath): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        if (method_exists($reader, 'setReadDataOnly')) {
            $reader->setReadDataOnly(true);
        }

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = max(1, (int) $sheet->getHighestDataRow());
        $highestColumn = $sheet->getHighestDataColumn();
        $highestColumnIndex = max(1, Coordinate::columnIndexFromString($highestColumn));

        $rows = [];
        for ($rowNumber = 1; $rowNumber <= $highestRow; $rowNumber++) {
            $cells = [];
            for ($columnIndex = 1; $columnIndex <= $highestColumnIndex; $columnIndex++) {
                $cellRef = Coordinate::stringFromColumnIndex($columnIndex) . $rowNumber;
                $value = $sheet->getCell($cellRef)->getFormattedValue();
                if (is_bool($value)) {
                    $value = $value ? '1' : '0';
                }
                $cells[] = trim((string) ($value ?? ''));
            }
            $rows[] = [
                'row' => $rowNumber,
                'cells' => $cells,
            ];
        }

        $spreadsheet->disconnectWorksheets();
        unset($sheet, $spreadsheet);

        return $rows;
    }

    /**
     * นำไฟล์อัปเดต GFMIS ไปแปลงเป็นข้อมูลใช้งาน
     *
     * @return array{
     *   status: string,
     *   headers?: array<int, string>,
     *   rows?: array<int, array<string, mixed>>,
     *   errors?: array<int, array<string, mixed>>,
     *   summary?: array<string, int>,
     *   message?: string
     * }
     */
    protected function parseGfmisImportFile(string $filePath): array
    {
        try {
            $rows = $this->readSpreadsheetRows($filePath);
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return ['status' => 'error', 'message' => 'ไม่สามารถอ่านไฟล์ Excel ได้'];
        }

        $rows = array_values(array_filter($rows, static function (array $row): bool {
            foreach ($row['cells'] as $cell) {
                if (trim((string) $cell) !== '') {
                    return true;
                }
            }
            return false;
        }));

        if (empty($rows)) {
            return ['status' => 'error', 'message' => 'ไม่พบข้อมูลในไฟล์'];
        }

        $config = require \Yii::getAlias('@app/modules/am/data/gfmis_update_columns.php');
        $aliases = $config['aliases'] ?? [];
        $headerInfo = $this->detectGfmisHeaderInfo($rows, $aliases);

        $dataRows = array_slice($rows, $headerInfo['start_index']);
        if (empty($dataRows)) {
            return ['status' => 'error', 'message' => 'ไม่พบแถวข้อมูลสำหรับอัปเดต'];
        }

        $codeCounts = [];
        foreach ($dataRows as $row) {
            $code = trim((string) ($row['cells'][$headerInfo['code_index']] ?? ''));
            if ($code === '') {
                continue;
            }
            $codeCounts[$code] = ($codeCounts[$code] ?? 0) + 1;
        }

        $codes = array_keys($codeCounts);
        $assetMap = [];
        if (!empty($codes)) {
            $assetMap = Asset::find()
                ->where(['code' => $codes])
                ->andWhere('deleted_at IS NULL')
                ->indexBy('code')
                ->all();
        }

        $parsedRows = [];
        $errors = [];
        $ready = 0;
        $same = 0;

        foreach ($dataRows as $row) {
            $rowNumber = (int) $row['row'];
            $cells = $row['cells'];
            $code = trim((string) ($cells[$headerInfo['code_index']] ?? ''));
            $gfmis = trim((string) ($cells[$headerInfo['gfmis_index']] ?? ''));

            $rowErrors = [];
            if ($code === '') {
                $rowErrors['code'][] = 'ต้องระบุรหัสครุภัณฑ์';
            }
            if ($gfmis === '') {
                $rowErrors['gfmis'][] = 'ต้องระบุ GFMIS';
            }
            if ($code !== '' && ($codeCounts[$code] ?? 0) > 1) {
                $rowErrors['code'][] = 'รหัสครุภัณฑ์ซ้ำในไฟล์';
            }

            $asset = $code !== '' ? ($assetMap[$code] ?? null) : null;
            if ($code !== '' && $asset === null) {
                $rowErrors['code'][] = 'ไม่พบรหัสครุภัณฑ์นี้ในระบบ';
            }

            $assetName = $asset ? trim((string) ($asset->asset_name ?: $asset->AssetitemName() ?: '')) : '';
            $currentGfmis = $asset ? trim((string) ($asset->gfmis ?? '')) : '';
            $statusKey = 'error';
            $statusLabel = 'ไม่พร้อมนำเข้า';
            if (empty($rowErrors) && $asset !== null) {
                if ($currentGfmis === $gfmis) {
                    $statusKey = 'same';
                    $statusLabel = 'ค่าเดิม';
                    $same++;
                } else {
                    $statusKey = 'update';
                    $statusLabel = 'พร้อมอัปเดต';
                    $ready++;
                }
            }

            if (!empty($rowErrors)) {
                $errors[] = [
                    'row' => $rowNumber,
                    'code' => $code,
                    'errors' => $rowErrors,
                ];
            }

            $parsedRows[] = [
                'row' => $rowNumber,
                'code' => $code,
                'asset_id' => $asset?->id,
                'asset_name' => $assetName,
                'current_gfmis' => $currentGfmis,
                'new_gfmis' => $gfmis,
                'status_key' => $statusKey,
                'status_label' => $statusLabel,
                'errors' => $rowErrors,
            ];
        }

        return [
            'status' => 'success',
            'headers' => ['#', 'รหัสครุภัณฑ์', 'ชื่อรายการ', 'GFMIS ปัจจุบัน', 'GFMIS ใหม่', 'สถานะ'],
            'rows' => $parsedRows,
            'errors' => $errors,
            'summary' => [
                'total' => count($parsedRows),
                'ready' => $ready,
                'same' => $same,
                'error' => count($errors),
            ],
        ];
    }

    /**
     * ค้นหาตำแหน่งคอลัมน์จาก alias ที่กำหนด
     */
    protected function detectGfmisHeaderInfo(array $rows, array $aliases): array
    {
        $codeAliases = array_map([$this, 'normalizeImportText'], $aliases['code'] ?? []);
        $gfmisAliases = array_map([$this, 'normalizeImportText'], $aliases['gfmis'] ?? []);

        foreach ($rows as $index => $row) {
            $normalized = array_map([$this, 'normalizeImportText'], $row['cells']);
            $codeIndex = $this->findHeaderIndex($normalized, $codeAliases);
            $gfmisIndex = $this->findHeaderIndex($normalized, $gfmisAliases);
            if ($codeIndex !== null && $gfmisIndex !== null) {
                return [
                    'start_index' => $index + 1,
                    'code_index' => $codeIndex,
                    'gfmis_index' => $gfmisIndex,
                    'header_row' => $row['row'],
                ];
            }
        }

        return [
            'start_index' => 0,
            'code_index' => 0,
            'gfmis_index' => 1,
            'header_row' => null,
        ];
    }

    /**
     * @param array<int, string> $normalizedRow
     * @param array<int, string> $normalizedAliases
     */
    protected function findHeaderIndex(array $normalizedRow, array $normalizedAliases): ?int
    {
        foreach ($normalizedRow as $index => $value) {
            if ($value === '') {
                continue;
            }
            if (in_array($value, $normalizedAliases, true)) {
                return $index;
            }
        }

        return null;
    }

    /**
     * ทำความสะอาดข้อความสำหรับเทียบหัวคอลัมน์
     */
    protected function normalizeImportText($value): string
    {
        $value = mb_strtolower(trim((string) $value), 'UTF-8');
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/[^\p{L}\p{N}]+/u', '', $value);

        return is_string($value) ? $value : '';
    }

    /**
     * @param mixed $dataJson ค่า data_json จาก Asset (array หรือ JSON string)
     * @return array<string, mixed>
     */
    protected function assetImportDataJsonAsArray($dataJson): array
    {
        if (is_array($dataJson)) {
            return $dataJson;
        }
        if (is_string($dataJson) && $dataJson !== '') {
            $decoded = json_decode($dataJson, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * แมปดัชนีคอลัมน์จากแถวหัวตาราง (รองรับเทมเพลตเก่าที่ไม่มีคอลัมน์อัตราค่าเสื่อม)
     *
     * @return array{useful_life: int, depreciation: int|null, order_number: int, note: int}
     */
    protected function equipImportColumnIndexes(array $headerRow): array
    {
        $norm = array_map(static fn ($h) => trim((string) $h), $headerRow);
        $find = static function (string $name) use ($norm): ?int {
            $i = array_search($name, $norm, true);

            return $i === false ? null : (int) $i;
        };
        $idxDep = $find('อัตราค่าเสื่อม');
        $idxOrder = $find('เลขที่ใบกำกับ/ใบส่งของ');
        $idxNote = $find('หมายเหตุ');
        $idxUseful = $find('อายุการใช้งาน');

        return [
            'useful_life' => $idxUseful ?? 20,
            'depreciation' => $idxDep,
            'order_number' => $idxOrder ?? ($idxDep !== null ? $idxDep + 1 : 21),
            'note' => $idxNote ?? ($idxDep !== null ? $idxDep + 2 : 22),
        ];
    }

    /**
     * อัตราค่าเสื่อมจาก CSV — ว่างได้, ตัวเลขทศนิยม 2 ตำแหน่ง (รองรับจุดทศนิยมหรือจุลภาค)
     *
     * @return array{value: float|null, error: string|null}
     */
    protected function parseDepreciationRateForImport($raw): array
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return ['value' => null, 'error' => null];
        }
        $normalized = str_replace(["\xc2\xa0", ' '], '', $raw);
        $normalized = str_replace(',', '.', $normalized);
        if ($normalized === '' || !is_numeric($normalized)) {
            return ['value' => null, 'error' => 'อัตราค่าเสื่อมต้องเป็นตัวเลข (ทศนิยมได้สูงสุด 2 ตำแหน่ง)'];
        }
        $v = round((float) $normalized, 2);
        if ($v < 0) {
            return ['value' => null, 'error' => 'อัตราค่าเสื่อมต้องไม่ติดลบ'];
        }

        return ['value' => $v, 'error' => null];
    }

    /**
     * แปลงค่าจาก CSV เป็นวันที่รูปแบบ DB (Y-m-d) หรือ null ถ้าวันที่ invalid
     * รองรับหลายรูปแบบ:
     * - ปี ค.ศ.-เดือน-วัน (Y-m-d): 2025-01-01, 2025/1/1
     * - ปี พ.ศ.-เดือน-วัน: 2568-01-01, 2568/1/1
     * - วัน/เดือน/ปี ค.ศ. (d-m-y): 01-01-2025, 1/1/2025
     * - วัน/เดือน/ปี พ.ศ.: 01-01-2568, 1/1/2568
     */
    protected function normalizeDateForDb($dateStr)
    {
        if ($dateStr === null || $dateStr === '') {
            return null;
        }
        $dateStr = trim((string) $dateStr);
        $delimiter = (strpos($dateStr, '/') !== false) ? '/' : '-';
        $parts = array_map('trim', explode($delimiter, $dateStr));
        if (count($parts) !== 3) {
            return null;
        }
        $p0 = $parts[0];
        $p1 = $parts[1];
        $p2 = $parts[2];
        // keep only digits for numeric checks
        $d0 = preg_replace('/\D+/', '', (string) $p0);
        $d1 = preg_replace('/\D+/', '', (string) $p1);
        $d2 = preg_replace('/\D+/', '', (string) $p2);
        $n0 = (int) $d0;
        $n1 = (int) $d1;
        $n2 = (int) $d2;

        // Fix malformed years e.g. "20925" -> "2025"
        $fixYear = function ($yearDigits) {
            $y = preg_replace('/\D+/', '', (string) $yearDigits);
            if ($y === '') {
                return null;
            }
            if (strlen($y) === 5 && str_starts_with($y, '20')) {
                return (int) ('20' . substr($y, -2));
            }
            if (strlen($y) > 4) {
                return (int) substr($y, -4);
            }
            return (int) $y;
        };

        // ปี ค.ศ.-เดือน-วัน (Y-m-d): ส่วนแรกเป็นปี ค.ศ. 4 หลัก 1900-2100
        $y0 = $fixYear($p0);
        if ($y0 !== null && $y0 >= 1900 && $y0 <= 2100 && strlen($d0) >= 4) {
            $y = $y0;
            $m = $n1;
            $d = $n2;
            if (checkdate($m, $d, $y)) {
                return sprintf('%04d-%02d-%02d', $y, $m, $d);
            }
            return null;
        }
        // ปี พ.ศ.-เดือน-วัน: ส่วนแรกเป็นปี พ.ศ. 4 หลัก (ประมาณ 2400-2600)
        $y0 = $fixYear($p0);
        if ($y0 !== null && $y0 >= 2400 && $y0 <= 2600 && strlen($d0) >= 4) {
            $y = $y0 - 543;
            $m = $n1;
            $d = $n2;
            if (checkdate($m, $d, $y)) {
                return sprintf('%04d-%02d-%02d', $y, $m, $d);
            }
            return null;
        }
        // วัน/เดือน/ปี ค.ศ. (d-m-y): ส่วนท้ายเป็นปี ค.ศ. 4 หลัก
        $y2 = $fixYear($p2);
        if ($y2 !== null && $y2 >= 1900 && $y2 <= 2100 && strlen($d2) >= 4) {
            $y = $y2;
            $m = $n1;
            $d = $n0;
            if (checkdate($m, $d, $y)) {
                return sprintf('%04d-%02d-%02d', $y, $m, $d);
            }
            return null;
        }
        // วัน/เดือน/ปี พ.ศ. (d-m-y BE): ส่วนท้ายเป็นปี พ.ศ. 4 หลัก
        $y2 = $fixYear($p2);
        if ($y2 !== null && $y2 >= 2400 && $y2 <= 2600 && strlen($d2) >= 4) {
            $y = $y2 - 543;
            $m = $n1;
            $d = $n0;
            if (checkdate($m, $d, $y)) {
                return sprintf('%04d-%02d-%02d', $y, $m, $d);
            }
            return null;
        }
        // Fallback: ใช้ DateHelper (d-m-y อาจเป็น 2 หลักหรือรูปแบบอื่น)
        $dbDate = DateHelper::convertToDatabaseDate($dateStr);
        if ($dbDate === null) {
            return null;
        }
        if (!is_string($dbDate) || strpos($dbDate, '-') === false) {
            return null;
        }
        $p = explode('-', $dbDate);
        if (count($p) !== 3) {
            return null;
        }
        $y = (int)$p[0];
        $m = (int)$p[1];
        $d = (int)$p[2];
        if ($y < 1900 || $y > 2100 || !checkdate($m, $d, $y)) {
            return null;
        }
        return $dbDate;
    }

    public function findPurchase($tite = null)
    {
        $model = Categorise::find()->where(['name' => 'purchase', 'title' => $tite])->one();
        if ($model) {
            return $model->code;
        } else {
            return '';
        }
    }

    /**
     * นำชื่อหรือรหัส (จาก CSV) ไปค้นใน categorise (name=budget_type) แล้วคืน code
     */
    protected function resolveBudgetTypeFromImport($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        $cat = Categorise::find()
            ->andWhere(['name' => 'budget_type'])
            ->andWhere(['or', ['code' => $value], ['title' => $value]])
            ->one();
        return $cat ? (string) $cat->code : '';
    }

    /**
     * นำชื่อหรือรหัส (จาก CSV) ไปค้นใน categorise (name=purchase) แล้วคืน code
     */
    protected function resolvePurchaseFromImport($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        $cat = Categorise::find()
            ->andWhere(['name' => 'purchase'])
            ->andWhere(['or', ['code' => $value], ['title' => $value]])
            ->one();
        return $cat ? (string) $cat->code : '';
    }

    /**
     * ดึงรหัสผู้แทนจำหน่ายถัดไป ตามกฎผู้แทนจำหน่าย (V001, V002, V003 ...)
     * @return string
     */
    protected function getNextVendorCode()
    {
        $last = Categorise::find()
            ->where(['name' => 'vendor'])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        if (!$last || !$last->code) {
            return 'V001';
        }
        if (preg_match('/^V(\d+)$/i', trim($last->code), $m)) {
            $next = (int) $m[1] + 1;
            return 'V' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        }
        return 'V001';
    }

    public function findVendor($tite = null)
    {
        $model = Categorise::find()->where(['name' => 'vendor', 'title' => $tite])->one();
        if (!$model) {
            $newVender = new Categorise(['name' => 'vendor', 'title' => $tite]);
            $newVender->code = $this->getNextVendorCode();
            $newVender->save(false);
            return $newVender->code;
        }
        return $model->code;
    }

    /**
     * รหัส/ชื่อผู้ขายจาก CSV → vendor code ใน categorise (name='vendor') โครงสร้างเดียวกับ /sm/vendor
     * - มีรหัสและพบในระบบ: คืน code (อัปเดตชื่อถ้าส่งชื่อมาและไม่ตรง)
     * - มีรหัสแต่ไม่พบในระบบ: ไม่บันทึกรหัสจาก CSV — ถ้ามีชื่อให้ค้น/สร้างจากชื่อ (findVendor) มิฉะนั้นคืนค่าว่าง
     * - รหัสว่างแต่มีชื่อ: ค้น/สร้างผู้ขายอัตโนมัติจากชื่อ (findVendor)
     */
    protected function resolveVendorFromImport($vendorCode = '', $vendorName = '')
    {
        $vendorCode = trim((string) $vendorCode);
        $vendorName = trim((string) $vendorName);

        if ($vendorCode !== '') {
            $byCode = Categorise::find()->where(['name' => 'vendor', 'code' => $vendorCode])->one();
            if ($byCode) {
                // รหัสมีในระบบแล้ว — อัปเดต title เฉพาะเมื่อ CSV ส่งชื่อมาและไม่ตรง
                if ($vendorName !== '' && $byCode->title !== $vendorName) {
                    $byCode->title = $vendorName;
                    $byCode->save(false);
                }
                return $byCode->code;
            }
            // ไม่พบรหัสในระบบ — ไม่สร้างผู้ขายด้วยรหัสจาก CSV
            if ($vendorName !== '') {
                return $this->findVendor($vendorName);
            }

            return '';
        }

        // รหัสว่างแต่มีชื่อ: นำเข้า/สร้างผู้ขายอัตโนมัติจากชื่อ (categorise name=vendor เหมือน /sm/vendor)
        if ($vendorName !== '') {
            return $this->findVendor($vendorName);
        }

        return '';
    }


    protected function findProduct($code = null, $title = null, $categoryId = null, $unit = null)
    {
        $product = Categorise::find()->where(['name' => 'asset_item', 'category_id' => $categoryId, 'title' => $title])->one();
        if ($product) {
            return [
                'status' => 'success',
                'msg' => 'ตรวจพบวัสดุที่มีอยู่แล้ว',
                'data' => $product
            ];
        } else {
            // ถ้าไม่มีทำการสร้างใหม่
            //ตรวจสอบว่ารหัสที่จะสร้างใหม่ด้วยระบบอัตโนมัติจะซ้ำหรือไม่
            $checkCodeDuplicate = ProductHelper::checkCodeDuplicate($categoryId, $code);
            //ถ้าหากซ้ำกัน
            if ($checkCodeDuplicate['status'] == false) {
                return [
                    'status' => 'error',
                    'msg' => 'รหัสซ้ำ ==' . $checkCodeDuplicate['data']['code'] . ' ชื่อรายการ == ' . $checkCodeDuplicate['data']['title'],
                    'data' => $checkCodeDuplicate['data']
                ];
            } else {
                //ถ้าไม่ซ้ำให้สาร้างใหม่
                $newProduct = new Product;
                $newProduct->group_id = 4;
                $newProduct->name = 'asset_item';
                $newProduct->category_id = $categoryId;
                $newProduct->title = $title;
                $newProduct->code  = \mdm\autonumber\AutoNumber::generate($categoryId . '-?');

                $newProduct->data_json = [
                    'unit' => $unit,
                    'asset_type' => $categoryId
                ];
                $newProduct->save(false);
                $this->UpdateUnit($newProduct);
                return [
                    'status' => 'success',
                    'msg' => 'Yes',
                    'data' => $newProduct
                ];
            }
        }
    }

    protected function UpdateUnit($model)
    {
        $unit  = Categorise::findOne(['name' => 'unit', 'title' => $model->data_json['unit']]);
        if (!$unit) {
            $newUnit = new Categorise(['name' => 'unit', 'title' => $model->data_json['unit']]);
            $newUnit->save(false);
        }
    }
}
