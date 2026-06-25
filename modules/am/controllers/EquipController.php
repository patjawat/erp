<?php

namespace app\modules\am\controllers;

use yii;
use yii\helpers\Url;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\AssetHelper;
use app\modules\am\models\Asset;
use yii\web\NotFoundHttpException;
use app\modules\am\models\AssetSearch;
use app\modules\hr\models\Organization;
use app\modules\am\services\AssetNumberGenerator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * AssetController implements the CRUD actions for Asset model.
 */
class EquipController extends Controller
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
        $q = trim($searchModel->q ?? '');
        $dataProvider->query->andFilterWhere([
            'or',
            ['LIKE', 'asset.code', $q],
            ['LIKE', 'asset.asset_name', $q],
            ['like', 'license_plate', $q],
            ['LIKE', new Expression("JSON_EXTRACT(asset.data_json, '\$.asset_name')"), $q],
        ]);

        // ที่ยังไม่กำหนดหน่วยงาน
        if (!empty($searchModel->no_department)) {
            $dataProvider->query->andWhere(['or', ['asset.department' => null], ['asset.department' => 0], ['asset.department' => '']]);
        }
        // ที่ยังไม่มีผู้รับผิดชอบ
        if (!empty($searchModel->no_owner)) {
            $dataProvider->query->andWhere(['or', ['asset.owner' => null], ['asset.owner' => '']]);
        }
        // ช่วงราคา: ราคาต่ำสุดขึ้นไป / ระหว่าง / ราคาสูงสุดลงมา
        if ($searchModel->price1 !== '' && $searchModel->price1 !== null && $searchModel->price2 !== '' && $searchModel->price2 !== null) {
            $dataProvider->query->andWhere(new \yii\db\Expression('asset.price BETWEEN ' . (float) $searchModel->price1 . ' AND ' . (float) $searchModel->price2));
        } elseif ($searchModel->price1 !== '' && $searchModel->price1 !== null) {
            $dataProvider->query->andWhere(new \yii\db\Expression('asset.price >= ' . (float) $searchModel->price1));
        } elseif ($searchModel->price2 !== '' && $searchModel->price2 !== null) {
            $dataProvider->query->andWhere(new \yii\db\Expression('asset.price <= ' . (float) $searchModel->price2));
        }
        // ราคาต่ำกว่าเกณฑ์
        if ($searchModel->price_below !== '' && $searchModel->price_below !== null) {
            $dataProvider->query->andWhere(new \yii\db\Expression('asset.price < ' . (float) $searchModel->price_below));
        }

        $dataProvider->setSort([
            'defaultOrder' => [
                'receive_date' => SORT_DESC,
                'code' => SORT_DESC,
                'id' => SORT_DESC,
            ],
        ]);

        $dataProvider->query->with(['assetType', 'assetCategory', 'ownerEmployee']);

        $baseQuery = $dataProvider->query;

        $equipStats = [
            // รวมจำนวนทั้งหมด
            'total' => (int) (clone $baseQuery)->count('DISTINCT asset.id'),
            
            // --- นับตาม "สภาพ" (Condition) ---
            // สภาพดี (good) 
            'good' => (int) (clone $baseQuery)->andWhere(['asset.asset_condition' => 'good'])->count('DISTINCT asset.id'),
            
            // สภาพพอใช้ (fair) - *เพิ่มเข้ามาใหม่*
            'fair' => (int) (clone $baseQuery)->andWhere(['asset.asset_condition' => 'fair'])->count('DISTINCT asset.id'),
            
            // สภาพชำรุดและเสื่อมสภาพ (นับรวมกัน หรือจะแยกก็ได้)
            'damaged' => (int) (clone $baseQuery)->andWhere(['asset.asset_condition' => ['damaged', 'worn']])->count('DISTINCT asset.id'),

            // --- (ทางเลือก) หากต้องการนับตาม "สถานะ" (Status) ด้วย ---
            // ตัวอย่าง: ของที่กำลังส่งซ่อม หรือ รอจำหน่าย
            'repairing' => (int) (clone $baseQuery)->andWhere(['asset.asset_status' => 'repair'])->count('DISTINCT asset.id'),
            'waiting_dispose' => (int) (clone $baseQuery)->andWhere(['asset.asset_status' => 'wait_dispose'])->count('DISTINCT asset.id'),

            // มูลค่ารวม
            'total_value' => (float) ((clone $baseQuery)->sum(new Expression('COALESCE(asset.price, 0)'))) ?: 0.0,
        ];

        if ($this->request->get('export') === 'excel') {
            $dataProvider->pagination = false;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set widths
            $sheet->getColumnDimension('A')->setWidth(10);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(35);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);

            $setHeader = 'A1:I1';
            $sheet->getStyle($setHeader)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($setHeader)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($setHeader)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle($setHeader)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle($setHeader)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('8DB4E2');
            $sheet->getStyle($setHeader)->getFont()->setBold(true);

            $sheet->setTitle('ทะเบียนครุภัณฑ์');
            $sheet->setCellValue('A1', 'ลำดับ');
            $sheet->setCellValue('B1', 'รหัสครุภัณฑ์');
            $sheet->setCellValue('C1', 'ชื่อครุภัณฑ์');
            $sheet->setCellValue('D1', 'ประเภท/หมวด');
            $sheet->setCellValue('E1', 'หน่วยงาน');
            $sheet->setCellValue('F1', 'ผู้รับผิดชอบ');
            $sheet->setCellValue('G1', 'วันที่รับ');
            $sheet->setCellValue('H1', 'ราคา');
            $sheet->setCellValue('I1', 'สถานะ');

            $startRow = 2;
            foreach ($dataProvider->getModels() as $key => $a) {
                $numRow = $startRow++;
                $sheet->setCellValue('A' . $numRow, ($key + 1));
                $sheet->setCellValue('B' . $numRow, $a->code ?? '');
                $sheet->setCellValue('C' . $numRow, $a->asset_name ?? $a->AssetitemName() ?? '');
                $sheet->setCellValue('D' . $numRow, $a->assetType->title ?? '');
                $sheet->setCellValue('E' . $numRow, $a->departmentName() ?? '');
                $sheet->setCellValue('F' . $numRow, $a->ownerEmployee->fullname ?? '');
                $sheet->setCellValue('G' . $numRow, $a->receive_date ? \app\components\AppHelper::DateFormDb($a->receive_date) : '');
                
                $price = $a->price ? (float)$a->price : 0;
                $sheet->setCellValue('H' . $numRow, $price);
                $sheet->getStyle('H' . $numRow)->getNumberFormat()->setFormatCode('#,##0.00');
                
                $sheet->setCellValue('I' . $numRow, $a->statusName() ?? '');
            }

            $writer = new Xlsx($spreadsheet);
            
            $dirPath = Yii::getAlias('@webroot') . '/downloads';
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0777, true);
            }
            $filePath = $dirPath . '/ทะเบียนครุภัณฑ์.xlsx';
            
            $writer->save($filePath);

            if (file_exists($filePath)) {
                return Yii::$app->response->sendFile($filePath);
            } else {
                throw new \yii\web\NotFoundHttpException('The file does not exist.');
            }
        }

        if ($this->request->get('view')) {
            SiteHelper::setDisplay($this->request->get('view'));
        }

        return $this->render('@app/modules/am/views/equip/index', [
            'tabs' => 'asset',
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'equipStats' => $equipStats,
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

            if ($checkCode) {
                $codeStatus = true;
            } else {
                $codeStatus = false;
            }

            // ตรวจสอลการลงปีงบประมาณ

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
        $model = $this->findModel($id);
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
        $asset_name = isset($model->data_json['asset_name']) ? 'ค่าเสื่อมราคา ' . $model->data_json['asset_name'] : '-';
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-chart-line"></i> ' . $asset_name,
                'content' => $this->renderAjax('depreciation_list', [
                    'model' => $model,
                ]),
            ];
        }
        return $this->render('view_depreciation', [
            'model' => $model,
        ]);
    }

    /**
     * ค่าเสื่อมชุดใหม่ (อิง useful_life, residual = 1 บาท ตามมาตรฐาน) — เลือกแสดงรายปีหรือรายเดือนได้
     */
    public function actionDepreciationV2($id)
    {
        $model = $this->findModel($id);
        $scheduleData = \app\modules\am\services\DepreciationScheduleService::buildSchedule($model);
        $scheduleDataMonthly = \app\modules\am\services\DepreciationScheduleService::buildMonthlySchedule($model);
        return $this->render('depreciation_v2', [
            'model' => $model,
            'scheduleData' => $scheduleData,
            'scheduleDataMonthly' => $scheduleDataMonthly,
        ]);
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
            'depreciation_method' => 'straight_line',
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


    /**
     * GET /am/equip/next-asset-number?category_id=XXX
     * Returns next asset number for AJAX preview. Consumes sequence.
     */
    public function actionNextAssetNumber()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $categoryId = trim((string) Yii::$app->request->get('category_id', ''));
        if ($categoryId === '') {
            return ['asset_number' => '', 'error' => 'กรุณาระบุ FSN ก่อน'];
        }
        $onYear = trim((string) Yii::$app->request->get('on_year', ''));
        if ($onYear === '') {
            return ['asset_number' => '', 'error' => 'กรุณาระบุปีงบประมาณก่อนสร้างหมายเลข'];
        }
        $assetNumber = AssetNumberGenerator::generate($categoryId, $onYear);
        return ['asset_number' => $assetNumber];
    }

    public function actionNextCode()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Asset();
        $requiredName = 'ต้องระบุ';
        $result = [];

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            if ($model->fsn_number === '') {
                $model->addError('fsn_number', 'ต้องระบุ หมายเลข FSN ก่อน');
            }
            if ($model->on_year === '' || $model->on_year === null) {
                $model->addError('on_year', 'ต้องระบุปีงบประมาณก่อนสร้างหมายเลข');
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
        }
        return [
            'status' => 'success',
            'data' => AssetNumberGenerator::generate($model->fsn_number, $model->on_year)
        ];
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

    public function actionRepairHistory($id)
    {
        $model = $this->findModel($id);
        return $this->render('@app/modules/am/views/equip/_view_repair_history', [
            'model' => $model,
        ]);
    }
    //หนังสือเอกสารคู่มือต่างๆ
    public function actionDocument($id)
    {
        $model = $this->findModel($id);
        return $this->render('@app/modules/am/views/equip/_view_document', [
            'model' => $model,
        ]);
    }

    // การบำรุงรักษา
    public function actionMaintenance($id)
    {
        $model = $this->findModel($id);
        return $this->render('@app/modules/am/views/equip/maintenance', [
            'model' => $model,
        ]);
    }

    // พรบ.ต่อภาษี
    public function actionVehicleTax($id)
    {
        $model = $this->findModel($id);
        return $this->render('@app/modules/am/views/equip/vehicle_tax', [
            'model' => $model,
        ]);
    }

    //การสอลเทียบ
    public function actionCalibration($id)
    {
        $model = $this->findModel($id);
        return $this->render('@app/modules/am/views/equip/calibration', [
            'model' => $model,
        ]);
    }

    //การยืมคืน
    public function actionBorrow($id)
    {
        $model = $this->findModel($id);
        return $this->render('@app/modules/am/views/equip/borrow', [
            'model' => $model,
        ]);
    }

    //การเคลื่อนย้าย
    public function actionMove($id)
    {
        $model = $this->findModel($id);
        return $this->render('@app/modules/am/views/equip/move', [
            'model' => $model,
        ]);
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
}
