<?php

namespace app\modules\am\controllers;

use yii;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\models\Categorise;
use app\models\UploadForm;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\AssetHelper;
use app\modules\am\components\AssetHelper as AmAssetHelper;
use app\modules\am\models\Asset;
use yii\web\NotFoundHttpException;
use app\modules\am\models\AssetSearch;
use app\modules\hr\models\Organization;

/**
 * AssetController implements the CRUD actions for Asset model.
 */
class AssetController extends Controller
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
     * Lists all Asset models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AssetSearch([
            'asset_group_id' => 4
        ]);

        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere('asset.deleted_at IS NULL');
        $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '\$.budget_type')"), $searchModel->budget_type]);
        $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '\$.method_get')"), $searchModel->method_get]);
        $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '\$.po_number')"), $searchModel->po_number]);
        $dataProvider->query->andFilterWhere(['receive_date' => AppHelper::DateToDb($searchModel->q_receive_date)]);

        // ค้นหาคามกลุ่มโครงสร้าง
        $org1 = Organization::findOne($searchModel->q_department);
        // ถ้ามรกลุ่มย่อย
        if (isset($searchModel->q_department) && isset($org1) && $org1->lvl == 1) {
            $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
            FROM tree t1
            JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
            WHERE t2.name = :name;';
            $querys = Yii::$app
                ->db
                ->createCommand($sql)
                ->bindValue(':name', $org1->name)
                ->queryAll();
            $arrDepartment = [];
            foreach ($querys as $tree) {
                $arrDepartment[] = $tree['id'];
            }
            if (count($arrDepartment) > 0) {
                $dataProvider->query->andWhere(['in', 'department', $arrDepartment]);
            }
        } else {
            $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
        }
        // จบการค้นหา

        $dataProvider->query->andFilterWhere(['at.category_id' => $searchModel->asset_type]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['LIKE', 'asset.code', $searchModel->q],
            ['LIKE', new Expression("JSON_EXTRACT(asset.data_json, '\$.asset_name')"), $searchModel->q],
        ]);

        // ค้นหาตามอายุ
        if ($searchModel->price1 && !$searchModel->price2) {
            $dataProvider->query->andWhere(new \yii\db\Expression('price = ' . $searchModel->price1));
        }
        // ค้นหาระหว่างช่วงอายุ
        if ($searchModel->price1 && $searchModel->price2) {
            $dataProvider->query->andWhere(new \yii\db\Expression('price BETWEEN ' . $searchModel->price1 . ' AND ' . $searchModel->price2));
        }

        $dataProvider->setSort([
            'defaultOrder' => [
                'code' => 'SORT_DESC',
                'receive_date' => 'SORT_DESC',
                // 'service_start_time' => SORT_DESC
            ],
        ]);

        if ($this->request->get('view')) {
            SiteHelper::setDisplay($this->request->get('view'));
        }

        return $this->render('index', [
            'tabs' => 'asset',
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Asset();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            $fsnAuto = $this->request->post('Asset');
            // ตรวจระหัสซ้ำ
            $checkCode = Asset::find()
                ->where(['code' => $model->code])
                ->andWhere(['<>', 'ref', $model->ref])
                ->andWhere(['not', ['code' => null]])
                ->andWhere(['not', ['code' => '']])
                ->one();
            //  return $checkCode;

            if ($checkCode) {
                $codeStatus = true;
            } else {
                $codeStatus = false;
            }

            //  return $model;
            // ตรวจสอลการลงปีงบประมาณ
            // return $model;

            if ($model->asset_group_id != 1 && $model->asset_group_id != 2) {  // ถ้าเป็นที่ดินไม่ต้องตรวจสอบปีงบประมาณ
                $model->data_json['budget_type'] == '' ? $model->addError('data_json[budget_type]', $requiredName) : null;
                $model->on_year == '' ? $model->addError('on_year', $requiredName) : null;
                $model->purchase == '' ? $model->addError('purchase', $requiredName) : null;
                $model->data_json['method_get'] == '' ? $model->addError('data_json[method_get]', $requiredName) : null;
                $model->data_json['vendor_id'] == '' ? $model->addError('data_json[vendor_id]', $requiredName) : null;

                $codeStatus ? $model->addError('code', 'หมายเลขครุภัณฑ์ซ้ำ') : null;

                // ถ้าสร้างรหัสอัตโนมัติ
                if (!isset($fsnAuto['fsn_auto']) || $fsnAuto['fsn_auto'] == '1') {
                }
                // ถ้ากำหนดรหัวเอง
                if (isset($fsnAuto['fsn_auto']) && $fsnAuto['fsn_auto'] == '0') {
                    $model->code == '' ? $model->addError('code', $requiredName) : null;
                }
            }
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }

    /**
     * Displays a single Asset model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        // return $model->device_items;
        // $ids = ArrayHelper::getColumn($model->device_items, 'id');

        $searchModel = new AssetSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['in', 'asset.code', $model->device_items != null ? $model->device_items : '']);

        return $this->render('view', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDepreciation($id)
    {
        $model = $this->findModel($id);
        $asset_name = isset($model->data_json['asset_name']) ? 'ค่าเสื่อมราคา' . $model->data_json['asset_name'] : '-';
        $title = $this->request->get('title') . isset($model->data_json['asset_name']) ? $model->data_json['asset_name'] : '-';
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-chart-line"></i> ' . $asset_name,
                'content' => $this->renderAjax('depreciation_list_new', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('depreciation_list_new', [
                'model' => $model,
            ]);
        }
    }

    public function actionDepreciationPdf($id, $number = null, $date = null)
    {
        $model = $this->findModel($id);
        $report = $this->buildDepreciationPdfData($model, $number, $date);
        $html = $this->renderPartial('depreciation_pdf', $report);
        $stylesheet = '';
        if (preg_match('/<style\b[^>]*>(.*?)<\/style>/is', $html, $matches)) {
            $stylesheet = trim((string) $matches[1]);
        }
        $html = trim((string) preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html));

        $fontPath = Yii::getAlias('@webroot/fonts/THSarabunNew');
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $config = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'L',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'fontDir' => array_merge($fontDirs, [$fontPath]),
            'fontdata' => $fontData + [
                'thsarabun' => [
                    'R' => 'THSarabunNew.ttf',
                    'B' => 'THSarabunNew-Bold.ttf',
                    'I' => 'THSarabunNew-Italic.ttf',
                    'BI' => 'THSarabunNew BoldItalic.ttf',
                ],
            ],
            'default_font' => 'thsarabun',
            'autoScriptToLang' => false,
            'autoLangToFont' => false,
        ];

        $tmpDir = Yii::getAlias('@runtime/mpdf');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0777, true);
        }
        $config['tempDir'] = $tmpDir;

        $mpdf = new \Mpdf\Mpdf($config);
        $mpdf->SetTitle('ทะเบียนคุมทรัพย์สิน - ' . ($model->code ?: $model->id));
        $mpdf->SetFooter('||หน้า {PAGENO}');
        if ($stylesheet !== '') {
            $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
        }
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

        $suffix = '';
        if (!empty($date)) {
            $suffix = '_' . $this->sanitizeFileName((string) $date);
        } elseif (!empty($number)) {
            $suffix = '_row_' . (int) $number;
        }
        $filename = 'Asset_Depreciation_' . $this->sanitizeFileName((string) ($model->code ?: $model->id)) . $suffix . '.pdf';

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
        Yii::$app->response->content = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);

        return Yii::$app->response;
    }


    private function buildDepreciationPdfData(Asset $model, $number = null, $date = null): array
    {
        $siteInfo = SiteHelper::getInfo();
        $dataJsonRaw = $model->data_json;
        if (is_string($dataJsonRaw)) {
            $dataJson = Json::decode($dataJsonRaw, true) ?: [];
        } elseif (is_array($dataJsonRaw)) {
            $dataJson = $dataJsonRaw;
        } else {
            $dataJson = [];
        }

        $organizationName = trim((string) ($siteInfo['company_name'] ?? '')) ?: 'ส่วนราชการ';
        $assetTypeTitle = trim((string) ($dataJson['asset_type_text'] ?? $model->assetType?->title ?? $model->AssetTypeName() ?? '')) ?: '-';
        $assetName = trim((string) ($dataJson['asset_name'] ?? $model->asset_name ?? $model->AssetitemName() ?? '')) ?: '-';
        $location = trim((string) ($dataJson['location'] ?? $model->departmentName() ?? '')) ?: '-';
        $vendor = $this->resolveVendorDetails($model, $dataJson);
        $budgetType = trim((string) ($model->budgetTypeName() ?? ($dataJson['budget_type_text'] ?? ''))) ?: '-';
        $purchaseMethod = trim((string) ($model->methodGetName() ?? $dataJson['method_get_text'] ?? '')) ?: '-';
        $docNo = $this->extractDocumentNumber($model, $dataJson);
        $assetCode = trim((string) ($model->code ?? '')) ?: '-';
        $unit = trim((string) ($dataJson['unit'] ?? '')) ?: 'เครื่อง';

        $receiveDate = trim((string) ($model->receive_date ?? ''));
        $price = (float) ($model->price ?? 0);
        $usefulLife = (int) ($model->useful_life ?? 0);
        $rate = $this->normalizeDecimal($model->depreciation_rate ?? ($dataJson['depreciation'] ?? null));
        $annualDep = $usefulLife > 0 ? round($price / $usefulLife, 2) : 0.0;

        $scheduleRows = $this->getDepreciationScheduleRows($model, $number);
        $rows = [
            [
                'type' => 'data',
                'date' => $receiveDate !== '' ? $this->formatThaiDateShortYear($receiveDate) : '-',
                'doc_no' => $docNo,
                'item' => $assetName,
                'qty' => '1 ' . $unit,
                'unit_price' => $price > 0 ? number_format($price, 2) : '',
                'total' => $price > 0 ? number_format($price, 2) : '',
                'life' => $usefulLife > 0 ? $usefulLife . ' ปี' : '',
                'rate' => $rate !== null ? number_format($rate, 2) : '',
                'annual' => $annualDep > 0 ? number_format($annualDep, 2) : '',
                'accumulated' => '',
                'net' => $price > 0 ? number_format($price, 2) : '',
                'note' => '',
            ],
        ];

        foreach ($scheduleRows as $row) {
            $endDate = trim((string) ($row['end_date'] ?? ''));
            $monthLabel = $endDate !== '' ? $this->formatThaiMonthYear($endDate) : '-';
            $monthlyDep = round((float) ($row['price_month'] ?? 0), 2);
            $accumulatedDep = round((float) ($row['total_price'] ?? 0), 2);
            $currentNet = round((float) ($row['total'] ?? $price), 2);

            $rows[] = [
                'type' => 'data',
                'date' => $endDate !== '' ? $this->formatThaiDateShortYear($endDate) : '-',
                'doc_no' => '',
                'item' => $monthLabel !== '-' ? "คำนวณค่าเสื่อมประจำเดือน\n" . $monthLabel : 'คำนวณค่าเสื่อมประจำเดือน',
                'qty' => '',
                'unit_price' => '',
                'total' => '',
                'life' => $this->formatElapsedPeriod((int) ($row['date_number'] ?? 0)),
                'rate' => '',
                'annual' => $monthlyDep > 0 ? number_format($monthlyDep, 2) : '',
                'accumulated' => $accumulatedDep > 0 ? number_format($accumulatedDep, 2) : '',
                'net' => $currentNet > 0 ? number_format($currentNet, 2) : '',
                'note' => '',
            ];
        }

        return [
            'model' => $model,
            'siteInfo' => $siteInfo,
            'organizationName' => $organizationName,
            'assetTypeTitle' => $assetTypeTitle,
            'assetName' => $assetName,
            'assetCode' => $assetCode,
            'location' => $location,
            'vendor' => $vendor,
            'budgetType' => $budgetType,
            'purchaseMethod' => $purchaseMethod,
            'docNo' => $docNo,
            'unit' => $unit,
            'receiveDate' => $receiveDate,
            'price' => $price,
            'usefulLife' => $usefulLife,
            'rate' => $rate,
            'annualDep' => $annualDep,
            'rows' => $rows,
        ];
    }

    private function getDepreciationScheduleRows(Asset $model, $number = null): array
    {
        if (empty($model->useful_life) || empty($model->receive_date)) {
            return [];
        }
        $limit = $number !== null && (int) $number > 0 ? (int) $number : 9999;
        return AmAssetHelper::Depreciation($model->id, $limit);
    }

    private function resolveVendorDetails(Asset $model, array $dataJson = []): array
    {
        $title = '';
        $address = '';
        $phone = '';

        $vendorModel = $model->vendor ?? null;
        if ($vendorModel !== null) {
            $title = trim((string) ($vendorModel->title ?? ''));
            $vendorData = $vendorModel->data_json ?? [];
            if (is_string($vendorData)) {
                $vendorData = Json::decode($vendorData, true) ?: [];
            }
            if (is_array($vendorData)) {
                $address = trim((string) ($vendorData['address'] ?? $vendorData['vendor_address'] ?? ''));
                $phone = trim((string) ($vendorData['phone'] ?? $vendorData['vendor_phone'] ?? ''));
                if ($title === '' && !empty($vendorData['title'])) {
                    $title = trim((string) $vendorData['title']);
                }
            }
        }

        if ($title === '' && !empty($dataJson['vendor']) && is_array($dataJson['vendor'])) {
            $title = trim((string) ($dataJson['vendor']['title'] ?? ''));
            $nestedVendorData = $dataJson['vendor']['data_json'] ?? [];
            if (is_string($nestedVendorData)) {
                $nestedVendorData = Json::decode($nestedVendorData, true) ?: [];
            }
            if (is_array($nestedVendorData)) {
                $address = $address !== '' ? $address : trim((string) ($nestedVendorData['address'] ?? $nestedVendorData['vendor_address'] ?? ''));
                $phone = $phone !== '' ? $phone : trim((string) ($nestedVendorData['phone'] ?? $nestedVendorData['vendor_phone'] ?? ''));
            }
        }

        if ($title === '' && !empty($dataJson['vendor_name'])) {
            $title = trim((string) $dataJson['vendor_name']);
        }
        if ($address === '' && !empty($dataJson['address'])) {
            $address = trim((string) $dataJson['address']);
        }
        if ($phone === '' && !empty($dataJson['phone'])) {
            $phone = trim((string) $dataJson['phone']);
        }

        return [
            'title' => $title !== '' ? $title : '-',
            'address' => $address !== '' ? $address : '-',
            'phone' => $phone !== '' ? $phone : '-',
        ];
    }

    private function extractDocumentNumber(Asset $model, array $dataJson = []): string
    {
        $candidates = [
            $dataJson['invoice_number'] ?? null,
            $dataJson['po_number'] ?? null,
            $model->po_number ?? null,
            $dataJson['document_number'] ?? null,
            $dataJson['bill_number'] ?? null,
            $dataJson['ref_number'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $value = trim((string) $candidate);
            if ($value !== '') {
                return $value;
            }
        }

        return '-';
    }

    private function formatThaiDateShortYear(?string $date): string
    {
        if (empty($date)) {
            return '-';
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return '-';
        }

        $months = [
            1 => 'ม.ค.',
            2 => 'ก.พ.',
            3 => 'มี.ค.',
            4 => 'เม.ย.',
            5 => 'พ.ค.',
            6 => 'มิ.ย.',
            7 => 'ก.ค.',
            8 => 'ส.ค.',
            9 => 'ก.ย.',
            10 => 'ต.ค.',
            11 => 'พ.ย.',
            12 => 'ธ.ค.',
        ];

        $month = (int) date('n', $timestamp);
        $yearShort = substr((string) ((int) date('Y', $timestamp) + 543), -2);

        return date('j', $timestamp) . ' ' . ($months[$month] ?? '') . $yearShort;
    }

    private function formatThaiMonthYear(?string $date): string
    {
        if (empty($date)) {
            return '-';
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return '-';
        }

        $months = [
            1 => 'มกราคม',
            2 => 'กุมภาพันธ์',
            3 => 'มีนาคม',
            4 => 'เมษายน',
            5 => 'พฤษภาคม',
            6 => 'มิถุนายน',
            7 => 'กรกฎาคม',
            8 => 'สิงหาคม',
            9 => 'กันยายน',
            10 => 'ตุลาคม',
            11 => 'พฤศจิกายน',
            12 => 'ธันวาคม',
        ];

        $month = (int) date('n', $timestamp);
        $yearThai = (int) date('Y', $timestamp) + 543;

        return ($months[$month] ?? '') . ' ' . $yearThai;
    }

    private function formatElapsedPeriod(int $months): string
    {
        if ($months <= 0) {
            return '-';
        }

        $years = intdiv($months, 12);
        $remainMonths = $months % 12;

        if ($years > 0 && $remainMonths > 0) {
            return $years . ' ปี ' . $remainMonths . ' เดือน';
        }

        if ($years > 0) {
            return $years . ' ปี';
        }

        return $months . ' เดือน';
    }

    private function normalizeDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = preg_replace('/\s+/u', '', (string) $value);
        $normalized = str_replace(',', '.', $normalized);
        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return round((float) $normalized, 2);
    }

    private function sanitizeFileName(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9\-_]+/', '_', $value);
        $value = trim((string) $value, '_');

        return $value !== '' ? $value : 'asset';
    }

    /**
     * Creates a new Asset model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $id = Yii::$app->request->get('id');

        // สร้าง model ใหม่พร้อมค่าตั้งต้น
        $model = new Asset([
            'asset_group_id' => 4,
            'asset_status' => 0,
            'price' => 0,
            'ref' => substr(Yii::$app->security->generateRandomString(), 10),
        ]);

        // ถ้ามี id แสดงว่าเป็นการ clone
        if ($id) {
            $cloneAsset = $this->findModel($id);

            // คัดลอก attributes ทั้งหมด ยกเว้น primary key
            $model->attributes = $cloneAsset->attributes;

            // ตั้งค่าใหม่ที่ต้องการให้ไม่ซ้ำ
            $model->id = null; // ป้องกัน overwrite
            $model->isNewRecord = true;
            $model->ref = substr(Yii::$app->security->generateRandomString(), 10);
            $model->created_at = date('Y-m-d H:i:s');
            $model->updated_at = null;
            $model->code = AssetHelper::nextAssetCode($model->fsn_number); // สร้างรหัสใหม่
            // แปลง receive_date ถ้ามีค่า
            if (!empty($model->receive_date)) {
                $model->receive_date = AppHelper::convertToThai($model->receive_date);
            }

            // ตรวจสอบ data_json ว่าเป็น array ก่อน
            $dataJson = is_array($model->data_json) ? $model->data_json : [];

            // แปลง expire_date
            $dataJson['expire_date'] = !empty($dataJson['expire_date'])
                ? AppHelper::convertToThai($dataJson['expire_date'])
                : null;

            // แปลง inspection_date
            $dataJson['inspection_date'] = !empty($dataJson['inspection_date'])
                ? AppHelper::convertToThai($dataJson['inspection_date'])
                : null;

            $dataJson['fsn_old'] = !empty($dataJson['fsn_old'])
                ? $dataJson['fsn_old'] = $model->code
                : null;



            $model->data_json = $dataJson;
        }
        $old_data_json = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->receive_date = AppHelper::DateToDb($model->receive_date);

                $convert_date = [
                    'expire_date' => AppHelper::DateToDb($model->data_json['expire_date']),
                    'inspection_date' => AppHelper::DateToDb($model->data_json['inspection_date']),
                ];

                $model->data_json = ArrayHelper::merge($old_data_json, $model->data_json, $convert_date);

                if ($model->save()) {
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    return $model->getErrors();
                }
                // return $this->redirect(['index']);
            }
        } else {
            // return $model->getErrors();
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Asset model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->receive_date = AppHelper::DateFormDb($model->receive_date);
        if ($model->ref == '') {
            $model->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        }
        if (isset($model->data_json['item_options'])) {
            $model->item_options = $model->data_json['item_options'];
        }
        $old_data_json = $model->data_json;

        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model->receive_date = AppHelper::DateToDb($model->receive_date);


            $convert_date = [
                'expire_date' => AppHelper::DateToDb($model->data_json['expire_date']),
                'inspection_date' => AppHelper::DateToDb($model->data_json['inspection_date']),
            ];


            $model->data_json = ArrayHelper::merge($old_data_json, $model->data_json, $convert_date);
            if ($model->save()) {
                $model->updateFsn();
                $this->CheckUpdateData($model);
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $model->getErrors();
            }
        }

        $viewDate = [
                'expire_date' => (isset($model->data_json['expire_date']) ? AppHelper::DateFormDb($model->data_json['expire_date']) : ''),
                'inspection_date' => (isset($model->data_json['inspection_date']) ? AppHelper::DateFormDb($model->data_json['inspection_date']) : ''),
            ];

       $model->data_json = ArrayHelper::merge($old_data_json, $model->data_json, $viewDate);
       $model->data_json = ArrayHelper::merge($old_data_json, $model->data_json, $viewDate);

        return $this->render('@app/modules/am/views/equip/update', [
            'model' => $model,
            // 'group' => $model->asset_group_id
        ]);
    }

    // update Spect ที่เป็น Cmputer
    public function actionUpdateComputer($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $title = $this->request->get('title');
        $model = $this->findModel($id);

        $old_data_json = $model->data_json;

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->data_json = ArrayHelper::merge($old_data_json, $model->data_json);
            // return $model->data_json;
            if ($model->save()) {
                $this->CheckUpdateData($model);
                return [
                    'status' => 'success',
                    'container' => '#am-container',
                    'close' => true
                ];
            } else {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $model->getErrors();
            }
        }
        return [
            'title' => $title,
            'content' => $this->renderAjax('is_computer/_form_computer', ['model' => $model]),
        ];
    }

    private static function CheckUpdateData($model)
    {
        try {
            // บึนทึกยี่ห้ออัตโนมัติ
            $brand = $model->data_json['brand'];
            $modelBrand = Categorise::findOne(['name' => 'brand', 'title' => $brand]);
            if (!$modelBrand) {
                $modelBrandNew = new Categorise(['name' => 'brand', 'code' => $brand, 'title' => $brand]);
                $modelBrandNew->save();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
        try {
            // บึนทึกรุ่นอัตโนมัติ
            $asset_model = $model->data_json['asset_model'];
            $assetModel = Categorise::findOne(['name' => 'asset_model', 'title' => $asset_model]);
            if (!$assetModel) {
                $assetModel = new Categorise(['name' => 'asset_model', 'code' => $asset_model, 'title' => $asset_model]);
                $assetModel->save();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
        try {
            // บึนทึกรุ่นอัตโนมัติ
            $os = $model->data_json['os'];
            $osModel = Categorise::findOne(['name' => 'os', 'title' => $os]);
            if (!$osModel) {
                $osModel = new Categorise(['name' => 'os', 'code' => $os, 'title' => $os]);
                $osModel->save();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        try {

            // บึนทึก CPU
            $cpu = $model->data_json['cpu'];
            $cpuModel = Categorise::findOne(['name' => 'cpu', 'title' => $cpu]);
            if (!$cpuModel) {
                $cpuModel = new Categorise(['name' => 'cpu', 'code' => $cpu, 'title' => $cpu]);
                $cpuModel->save();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function actionQrcode()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $model = $this->findModel($id);
        return [
            'title' => '<i class="fa-solid fa-qrcode"></i> QR-Code',
            'content' => $this->renderAjax('qr-code/view_qrcode', ['model' => $model]),
        ];
    }

    // ตั้งค่าขนาดกระดาษ qr-code
    public function actionQrcodeSetting()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $title = $this->request->get('title');
        return [
            'title' => $title,
            'content' => $this->renderAjax('qr-code/setting_qrcode'),
        ];
    }

    public function actionViewQrPdf($id)
    {
        $model = $this->findModel($id);
        
        // ใช้ view เฉพาะสำหรับ PDF เพื่อแก้ปัญหาภาษาไทยและตัดปุ่มออก
        $html = $this->renderPartial('qr-code/pdf_qrcode', [
            'model' => $model
        ]);

        // กำหนด Path ของฟอนต์ไทยให้ชี้ไปที่โฟลเดอร์ที่มีไฟล์ .ttf จริงๆ
        $fontPath = Yii::getAlias('@webroot/fonts/THSarabunNew');

        // ดึงค่าเริ่มต้นของ mPDF มาเพื่อใช้งานร่วมกับฟอนต์ใหม่
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $config = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 15,
            'margin_bottom' => 18,
            'fontDir' => array_merge($fontDirs, [$fontPath]),
            'fontdata' => $fontData + [
                'thsarabun' => [
                    'R' => 'THSarabunNew.ttf',
                    'B' => 'THSarabunNew-Bold.ttf',
                    'I' => 'THSarabunNew-Italic.ttf',
                    'BI' => 'THSarabunNew BoldItalic.ttf',
                ]
            ],
            'default_font' => 'thsarabun',
            // ตั้งค่าให้ mPDF รองรับภาษาไทยและเลือกฟอนต์ให้อัตโนมัติ
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ];

        $tmpDir = Yii::getAlias('@runtime/mpdf');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0777, true);
        }
        $config['tempDir'] = $tmpDir;

        $mpdf = new \Mpdf\Mpdf($config);
        $mpdf->SetTitle('QR Code - ' . $model->code);
        
        // โหลด CSS สำหรับ mPDF ที่รองรับภาษาไทย
        $cssFile = Yii::getAlias('@webroot/css/kv-mpdf-bootstrap.css');
        if (file_exists($cssFile)) {
            $stylesheet = file_get_contents($cssFile);
            $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
        }
        
        // เขียน HTML ลงใน PDF
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        
        $filename = 'QRCode_' . $model->code . '.pdf';

        return $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
    }


    public function actionNextCode()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Asset();
        $requiredName = 'ต้องระบุ';
        $result = [];

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            // ตรวจสอบค่า
            if ($model->fsn_number === '') {
                $model->addError('fsn_number', 'ต้องระบุ หมายเลข FSN ก่อน');
            }
        }

        foreach ($model->getErrors() as $attribute => $errors) {
            $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if ($result) {
            return [
                'status' => 'error',
                'data' => $result
            ];
        } else {
            return [
                'status' => 'success',
                'data' => AssetHelper::nextAssetCode($model->fsn_number)
            ];
        }
    }


    /**
     * Deletes an existing Asset model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->user->can('admin')) {
            return [
                'status' => 'error',
                'message' => 'ไม่มีสิทธิลบข้อมูล'
            ];
        }
        $model = $this->findModel($id);
        // return $model->deleted_at;
        // ตรวจสอบว่าถูกลบไปแล้วหรือยัง
        if ($model->deleted_at !== null) {
            return [
                'status' => 'error',
                'message' => 'รายการนี้ถูกลบไปแล้ว'
            ];
        }

        // ทำ Soft Delete
        $model->deleted_at = new Expression('NOW()');
        $model->deleted_by = Yii::$app->user->id;

        if ($model->save(false)) {
            return [
                'status' => 'success',
                'url' => Url::to(['/am/asset/index'])
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล'
            ];
        }
    }

    // รายการที่ตกค้างหรือข้อมูลไม่ครบ
    public function actionOmit()
    {
        $sql = "SELECT a.id,g.title as group_name,a.code as asset_code,a.data_json->>'\$.asset_name' as asset_name,t.code as type_code,t.title as type_title,i.code as item_code,i.title as item_title FROM asset a
         LEFT JOIN categorise i ON i.code = a.asset_item AND i.name = 'asset_item'
         LEFT JOIN categorise t ON t.code = i.category_id AND t.name = 'asset_type'
         LEFT JOIN categorise g ON g.code = a.asset_group_id AND g.name = 'asset_group_id'
         WHERE a.asset_group_id <> 1 AND t.code IS NULL
         LIMIT 10000;";

        $models = Yii::$app->db->createCommand($sql)->queryAll();
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'รายการที่ยังไม่สมบรูณ์หรือข้อมูลไม่ครบ <code>' . count($models) . '</code> รายการ',
                'content' => $this->renderAjax('omit', ['models' => $models])
            ];
        } else {
            return $this->render('omit', ['models' => $models]);
        }
    }

    /**
     * Finds the Asset model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Asset the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Asset::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionSetting()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $name = $this->request->get('name');
        $data = $this->request->get('val');
        return $name . $data;
    }

    // เลือกการบันทึกทรัพย์สิน
    public function actionSelectType()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'title' => 'เลือกกลุ่ม',
            'content' => $this->renderAjax('select_type'),
        ];
    }

    public function actionImportCsv()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->csvFile = UploadedFile::getInstance($model, 'csvFile');
            if ($model->validate()) {
                $filePath = 'import-csv/' . $model->csvFile->baseName . '.' . $model->csvFile->extension;
                $model->csvFile->saveAs($filePath);

                // เรียกใช้ฟังก์ชันนำเข้าข้อมูล
                $this->importCsvToDatabase($filePath);
                Yii::$app->session->setFlash('success', 'CSV imported successfully.');
                return $this->render('import_csv', ['model' => $model, 'status' => 'success']);
            }
        }

        return $this->render('import_csv', ['model' => $model, 'status' => false]);
    }

    private function importCsvToDatabase($filePath)
    {
        // สร้าง prefix โดยใช้วันที่และเวลาปัจจุบัน
        $prefix = 'backup_' . date('Y_m_d_His') . '_asset';
        // สร้างคำสั่ง SQL
        $sql = "CREATE TABLE {$prefix} AS SELECT * FROM asset";
        // ดำเนินการคำสั่ง SQL
        Yii::$app->db->createCommand($sql)->execute();

        if (($handle = fopen($filePath, 'r')) !== false) {
            $firstLine = true;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if ($firstLine) {
                    $firstLine = false;  // Skip header row
                    continue;
                }

                // ตรวจสอบว่ามีข้อมูลในฐานข้อมูลหรือไม่ ถ้าไม่มีให้เพิ่มข้อมูลลงไป
                $assetType = isset($data[0]) ? $data[0] : null;
                $assetItemName = isset($data[1]) ? $data[1] : null;
                $assetCode = isset($data[2]) ? $data[2] : null;
                $vendorName = isset($data[4]) ? $data[4] : null;
                $methodGet = isset($data[9]) ? $data[9] : null;
                $purchaseText = isset($data[10]) ? $data[10] : null;
                $budgetType = isset($data[11]) ? $data[11] : null;
                $departmentName = isset($data[7]) ? $data[7] : null;
                $onYear = isset($data[8]) ? $data[8] : null;
                $price = isset($data[12]) ? $data[12] : null;
                $receiveDate = isset($data[6]) ? $data[6] : null;
                $assetStatus = isset($data[17]) ? $data[17] : null;

                $assetItem = AssetHelper::CheckAssetItem($assetType, $assetCode, $assetItemName);

                $checkAsset = $assetCode ? Asset::find()->where(['code' => $assetCode])->one() : null;

                // สมมติว่าคุณมี Model ชื่อ YourModel
                $model = $checkAsset ?? new Asset();
                $model->asset_group_id = 3;
                $model->asset_item = isset($assetItem->code) ? $assetItem->code : null;
                $model->code = $assetCode;
                $model->data_json = [
                    'vendor_id' => $vendorName ? AssetHelper::findByName('vendor', $vendorName) : null,
                    'method_get' => $methodGet ? AssetHelper::findByName('method_get', $methodGet) : null,
                    'budget_type' => $budgetType ? AssetHelper::findByName('budget_type', $budgetType) : null,
                    'asset_type_text' => $assetType,
                    'method_get_text' => $methodGet,
                    'purchase_text' => $purchaseText,
                    'budget_type_text' => $budgetType,
                    'department_name' => $departmentName,
                ];
                $model->purchase = $purchaseText ? AssetHelper::findByName('purchase', $purchaseText) : null;  // วิธีการได้มา
                $model->on_year = $onYear;
                $model->price = $price ? AppHelper::formatNumber($price) : 0;  // ราคา
                $model->receive_date = $receiveDate ? AppHelper::convertToYMD($receiveDate) : null;  // วันที่รับเข้า
                $model->asset_status = $assetStatus ? AssetHelper::findByName('asset_status', $assetStatus) : null;  // วิธีการได้มา

                $org = $departmentName ? Organization::find()->where(['name' => $departmentName])->one() : null;
                $model->department = $org && isset($org->id) ? $org->id : 0;  // หน่วยงานภายในตามโครงสร้าง

                // เพิ่มคอลัมน์ตามไฟล์ CSV
                $model->save(false);
            }

            fclose($handle);
        }
    }


    public function actionImportStatus()
    {
        $data = Yii::$app->session->getFlash('data', []);
        $status = isset($data['status']) ? $data['status'] : false;
        $error = isset($data['error']) ? $data['error'] : [];
        return $this->render('import-status', [
            'status' => $status,
            'error' => $error,
        ]);
    }

    public function actionGetTableDprice()
    {
        return $this->render('extableDprice', [
            // ปี ราคา ค่าเสื่อม
            'data' => AppHelper::GetDepreciation(5, 10000, 40),
        ]);
    }

}
