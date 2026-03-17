<?php

namespace app\modules\sm\controllers;

use Yii;
use yii\helpers\Json;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\models\Categorise;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use app\modules\sm\models\Vendor;
use ruskid\csvimporter\CSVReader;
use yii\web\NotFoundHttpException;
use ruskid\csvimporter\CSVImporter;
use app\modules\hr\models\UploadCsv;
use app\modules\sm\models\VendorSearch;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use app\modules\sm\services\VendorImportService;
use app\modules\sm\services\VendorImportValidationService;
use ruskid\csvimporter\MultipleImportStrategy;

/**
 * VendorController implements the CRUD actions for Vendor model.
 */
class VendorController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Vendor models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new VendorSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'vendor']);
                $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'title', $searchModel->q],

            ['like', new Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.address'))"), $searchModel->q],
            ['like', new Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.phone'))"), $searchModel->q],
        ]);

        $baseQuery = clone $dataProvider->query;
        $baseQuery->andWhere(['name' => 'vendor']);

        $missingJson = function (string $jsonPath) {
            $expr = "NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_json, '$jsonPath'))), '') IS NULL";
            return new Expression($expr);
        };

        $completeness = [
            'total' => (int) (clone $baseQuery)->count(),
            'missing_code' => (int) (clone $baseQuery)->andWhere(['or', ['code' => null], ['code' => '']])->count(),
            'missing_title' => (int) (clone $baseQuery)->andWhere(['or', ['title' => null], ['title' => '']])->count(),
            'missing_tax_id' => (int) (clone $baseQuery)->andWhere($missingJson('$.tax_id'))->count(),
            'missing_contact_name' => (int) (clone $baseQuery)->andWhere($missingJson('$.contact_name'))->count(),
            'missing_phone' => (int) (clone $baseQuery)->andWhere($missingJson('$.phone'))->count(),
            'missing_email' => (int) (clone $baseQuery)->andWhere($missingJson('$.email'))->count(),
        ];
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'completeness' => $completeness,
        ]);
    }

    /**
     * Displays a single Vendor model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> ' . $model->title,
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Creates a new Vendor model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Vendor([
            'name' => 'vendor',
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return [
                    'status' => 'success',
                    'container' => '#sm-container',
                ];
            } else {
                return false;
            }
        } else {
            $model->loadDefaultValues();
            $model->code = $this->getNextVendorCode();
        }

        if ($this->request->isAjax) {
            return [
                'title' => '<i class="fa-regular fa-pen-to-square"></i> สร้างใหม่',
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Vendor();
        if ($this->request->isPost && $model->load($this->request->post())) {
            $requiredName = 'ต้องระบุ';
            // ตรวจสอบตำแหน่ง
            $model->code == '' ? $model->addError('code', $requiredName) : null;
            $model->title == '' ? $model->addError('title', $requiredName) : null;
            $model->data_json['address'] == '' ? $model->addError('data_json[address]', $requiredName) : null;
            // $model->data_json['phone'] == "" ? $model->addError('data_json[phone]', $requiredName) : null;
            $model->data_json['contact_name'] == '' ? $model->addError('data_json[contact_name]', $requiredName) : null;
            $model->data_json['bank_name'] == '' ? $model->addError('data_json[bank_name]', $requiredName) : null;
            $model->data_json['account_name'] == '' ? $model->addError('data_json[account_name]', $requiredName) : null;
            $model->data_json['account_number'] == '' ? $model->addError('data_json[account_number]', $requiredName) : null;

            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }

    /**
     * Updates an existing Vendor model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            // return $this->redirect(['view', 'id' => $model->id]);
            if ($this->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                $model->validate();
                $result = [];
                // The code below comes from ActiveForm::validate(). We do not need to validate the model
                // again, as it was already validated by save(). Just collect the messages.
                foreach ($model->getErrors() as $attribute => $errors) {
                    $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
                }
                if (!empty($result)) {
                    return $this->asJson($result);
                }

                if ($model->save()) {
                    return [
                        'status' => 'success',
                        'container' => '#sm-container',
                    ];
                }
            }
        } else {
            if ($this->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข',
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                    ]),
                ];
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }
    }

    public function actionImportCsv()
    {
        $model = new UploadCsv([
            'name' => 'vendor',
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
        ]);
        $basePath = Yii::getAlias('@app/web/import-csv/');
        AppHelper::CreateDir($basePath);

        $error = [];
        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            $model->file->saveAs($basePath . $model->file->name);

            $importer = new CSVImporter();
            $filename = $basePath . $model->file->name;
            $importer->setData(new CSVReader([
                'filename' => $filename,
                'tableName' => Vendor::tableName(),
                'fgetcsvOptions' => [
                    'delimiter' => ';',
                ],
            ]));

            for ($x = 1; $x <= count($importer->getData()); $x++) {
                $data_check_error = $importer->getData()[$x][0];
                $data_check_error = explode(',', $data_check_error);
                // if (count($data_check_error) != 13) {
                //     array_push($error, 'ข้อมูลไม่ครบถ้วน');
                // }
                if (null != Vendor::findOne(['code' => $data_check_error[0]])) {
                    array_push($error, 'มีเลขประจำตัวผู้เสียภาษี ' . $data_check_error[0] . ' อยู่ในระบบแล้ว');
                }
                // if (strlen($data_check_error[2]) != 10) {
                //     array_push($error, 'เบอร์โทรศัพท์ ' . $data_check_error[2] . ' ไม่ถูกต้อง (ต้องมี 10 หลัก)');
                // }
            }
            if (empty($error)) {
                $numberRowsAffected = $importer->import(new MultipleImportStrategy([
                    'tableName' => Vendor::tableName(),  // change your model names accordingly
                    'configs' => [
                        [
                            'attribute' => 'name',
                            'value' => function ($data) {
                                return 'vendor';
                            },
                        ],
                        [
                            'attribute' => 'ref',
                            'value' => function ($data) {
                                return substr(Yii::$app->getSecurity()->generateRandomString(), 10);
                            },
                        ],
                        [
                            'attribute' => 'code',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[0];
                            },
                        ],
                        [
                            'attribute' => 'title',
                            'allowEmptyValues' => false,
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[1];
                            },
                        ],
                        [
                            'attribute' => 'data_json',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                $jsonData = [
                                    'fax' => isset($data[7]) ? $data[7] : '',
                                    'phone' => isset($data[2]) ? $data[2] : '',
                                    'email' => isset($data[3]) ? $data[3] : '',
                                    'address' => isset($data[4]) ? $data[4] : '',
                                    'bank_name' => isset($data[10]) ? $data[10] : '',
                                    'account_name' => isset($data[8]) ? $data[8] : '',
                                    'contact_name' => isset($data[6]) ? $data[6] : '',
                                    'account_number' => isset($data[9]) ? $data[9] : '',
                                ];

                                return Json::encode($jsonData);
                            },
                        ],
                    ],
                ]));
                unlink($filename);
                Yii::$app->session->setFlash('data', [
                    'status' => true,
                    'error' => $error,
                ]);
                return $this->redirect(['import-status']);
            } else {
                unlink($filename);
                Yii::$app->session->setFlash('data', [
                    'status' => false,
                    'error' => $error,
                ]);
                return $this->redirect(['import-status']);
            }

            // return var_dump($importer->getData());
        } else {
            return $this->render('import_csv',
                ['model' => $model,
                    'error' => $error,
                    'success' => false]);
        }
    }

    public function actionImportStatus()
    {
        $data = Yii::$app->session->getFlash('data', []);
        $status = isset($data['status']) ? $data['status'] : false;
        $error = isset($data['error']) ? $data['error'] : [];
        return $this->render('import-status', [
            'status' => $status,
            'error' => $error
        ]);
    }

    /**
     * ดาวน์โหลดเทมเพลต CSV/Excel สำหรับนำเข้า Vendor (แบบใหม่)
     * @param string $format csv|xlsx
     */
    public function actionDownloadTemplate($format = 'csv')
    {
        $service = new VendorImportService();
        if ($format === 'xlsx') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = VendorImportService::TEMPLATE_HEADERS;
            foreach ($headers as $c => $h) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($c + 1) . '1', $h);
            }
            $example = ['V001', 'บริษัท ตัวอย่าง จำกัด', 'คุณสมชาย', '02-1234567', 'contact@example.com', '123 ถ.สุขุมวิท กทม.', '1234567890123', 'active', 'บัญชีตัวอย่าง', '123-4-56789-0', 'ธนาคารกรุงเทพ', 'ผู้จัดการ', '02-1234568'];
            foreach ($example as $c => $v) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($c + 1) . '2', $v);
            }
            $filename = 'template_vendor_' . date('Ymd') . '.xlsx';
            $path = Yii::getAlias('@runtime') . '/' . $filename;
            (new Xlsx($spreadsheet))->save($path);
            return Yii::$app->response->sendFile($path, $filename, ['inline' => false]);
        }
        $csv = $service->generateCsvTemplate();
        $filename = 'template_vendor_' . date('Ymd') . '.csv';
        Yii::$app->response->sendContentAsFile($csv, $filename, ['mimeType' => 'text/csv', 'inline' => false]);
        Yii::$app->end();
    }

    /**
     * ส่งออกข้อมูล Vendor เป็น Excel
     */
    public function actionExportVendor()
    {
        $models = Vendor::find()->where(['name' => 'vendor'])->orderBy(['id' => SORT_ASC])->all();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = ['รหัส', 'ชื่อ', 'ผู้ติดต่อ', 'โทรศัพท์', 'อีเมล', 'ที่อยู่', 'ชื่อบัญชี', 'เลขบัญชี', 'ธนาคาร', 'สถานะ'];
        foreach ($headers as $c => $h) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($c + 1) . '1', $h);
        }
        $row = 2;
        foreach ($models as $m) {
            $sheet->setCellValue('A' . $row, $m->code);
            $sheet->setCellValue('B' . $row, $m->title);
            $sheet->setCellValue('C' . $row, $m->contact_name ?? '');
            $sheet->setCellValue('D' . $row, $m->phone ?? '');
            $sheet->setCellValue('E' . $row, $m->email ?? '');
            $sheet->setCellValue('F' . $row, is_array($m->data_json) ? ($m->data_json['address'] ?? '') : '');
            $sheet->setCellValue('G' . $row, $m->account_name ?? '');
            $sheet->setCellValue('H' . $row, $m->account_number ?? '');
            $sheet->setCellValue('I' . $row, $m->bank_name ?? '');
            $sheet->setCellValue('J' . $row, !empty($m->active) ? 'ใช้งาน' : 'ไม่ใช้งาน');
            $row++;
        }
        $filename = 'ข้อมูลผู้แทนจำหน่าย_' . date('Ymd-His') . '.xlsx';
        $path = Yii::getAlias('@runtime') . '/' . $filename;
        (new Xlsx($spreadsheet))->save($path);

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        $response->sendFile($path, $filename, [
            'inline' => false,
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->on(Response::EVENT_AFTER_SEND, function () use ($path) {
            @unlink($path);
        });
        return $response;
    }

    /**
     * Modal นำเข้าข้อมูล Vendor (แบบใหม่ แบบเดียว AM equip)
     */
    public function actionImport()
    {
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-file-import me-1"></i> นำเข้า Vendor (ผู้แทนจำหน่าย)',
                'content' => $this->renderAjax('import', []),
            ];
        }
        return $this->render('import', []);
    }

    /**
     * AJAX: อัปโหลดไฟล์ → แสดง preview + validation
     */
    public function actionPreview()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $file = UploadedFile::getInstanceByName('importFile');
        if (!$file) {
            return ['status' => 'error', 'message' => 'ไม่พบไฟล์'];
        }
        $ext = strtolower($file->extension);
        if (!in_array($ext, ['csv', 'xlsx', 'xls'], true)) {
            return ['status' => 'error', 'message' => 'รองรับเฉพาะ .csv และ .xlsx'];
        }
        $filePath = Yii::getAlias('@runtime') . '/vendor_import_' . time() . '_' . Yii::$app->security->generateRandomString(8) . '.' . $ext;
        $file->saveAs($filePath);

        try {
            $service = new VendorImportService();
            $parsed = $service->parseFile($filePath);
            $rows = $parsed['rows'];
            if (empty($rows)) {
                @unlink($filePath);
                return ['status' => 'error', 'message' => 'ไม่มีข้อมูลในไฟล์'];
            }
            $validated = $service->validateRows($rows);
            $validated['filePath'] = $filePath;
            $validated['status'] = 'success';
            return $validated;
        } catch (\Throwable $e) {
            @unlink($filePath);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * POST: นำเข้าข้อมูลจริง (ใช้ filePath จาก preview)
     */
    public function actionDoImport()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $filePath = $this->request->post('filePath');
        if (!$filePath || !is_file($filePath)) {
            return ['status' => 'error', 'message' => 'ไม่พบไฟล์'];
        }
        try {
            $service = new VendorImportService();
            $parsed = $service->parseFile($filePath);
            $rows = $parsed['rows'];
            $validated = $service->validateRows($rows);
            if ($validated['error'] > 0) {
                return [
                    'status' => 'error',
                    'message' => 'มีข้อมูลไม่ผ่านการตรวจสอบ ' . $validated['error'] . ' แถว กรุณาแก้ไขหรือนำเข้าเฉพาะแถวที่ถูกต้อง',
                ];
            }
            $validRows = array_filter($validated['rows'], function ($r) {
                return !empty($r['valid']);
            });
            $result = $service->import(array_values($validRows));
            @unlink($filePath);
            return [
                'status' => 'success',
                'message' => $result['message'],
                'imported' => $result['imported'],
            ];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * ส่งออกเฉพาะแถวที่ error เป็น CSV
     */
    public function actionExportErrors()
    {
        $filePath = $this->request->post('filePath');
        $rowsJson = $this->request->post('errorRows');
        if (!$filePath || !$rowsJson) {
            return $this->redirect(['index']);
        }
        $errorRows = json_decode($rowsJson, true);
        if (!is_array($errorRows)) {
            return $this->redirect(['index']);
        }
        $service = new VendorImportService();
        $headers = VendorImportService::TEMPLATE_HEADERS;
        $bom = "\xEF\xBB\xBF";
        $fp = fopen('php://temp', 'r+');
        fwrite($fp, $bom);
        fputcsv($fp, array_merge(['ลำดับ', 'ข้อผิดพลาด'], $headers));
        foreach ($errorRows as $item) {
            $row = $item['row'] ?? [];
            $err = isset($item['errors']) ? implode('; ', $item['errors']) : '';
            $line = array_merge(
                [$item['rowNumber'] ?? '', $err],
                array_map(function ($k) use ($row) {
                    return $row[$k] ?? '';
                }, $headers)
            );
            fputcsv($fp, $line);
        }
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);
        $filename = 'vendor_import_errors_' . date('Ymd-His') . '.csv';
        Yii::$app->response->sendContentAsFile($csv, $filename, ['mimeType' => 'text/csv', 'inline' => false]);
        Yii::$app->end();
    }

    /**
     * Deletes an existing Vendor model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Vendor model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Vendor the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    /**
     * ดึงรหัสผู้แทนจำหน่ายถัดไป (เช่น V001 -> V002)
     * @return string
     */
    protected function getNextVendorCode()
    {
        $last = Vendor::find()
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

    protected function findModel($id)
    {
        if (($model = Vendor::findOne(['name' => 'vendor','id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
