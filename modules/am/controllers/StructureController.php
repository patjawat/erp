<?php

namespace app\modules\am\controllers;

use yii;
use yii\web\Response;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetSearch;
use app\modules\hr\models\Organization;
use app\modules\hr\models\Employees;
use app\models\Categorise;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;

class StructureController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $searchModel = new AssetSearch([
            'asset_group_id' => 3
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere('deleted_at IS NULL');
        $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '\$.budget_type')"), $searchModel->budget_type]);
        $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '\$.method_get')"), $searchModel->method_get]);
        $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '\$.po_number')"), $searchModel->po_number]);
        $dataProvider->query->andFilterWhere(['receive_date' => AppHelper::DateToDb($searchModel->q_receive_date)]);


        $dataProvider->query->andFilterWhere(['at.category_id' => $searchModel->asset_type]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['LIKE', 'asset_name', $searchModel->q],
            ['LIKE', 'code', $searchModel->q],
            ['LIKE', 'price', $searchModel->q],
            ['LIKE', 'on_year', $searchModel->q],
            ['LIKE', new Expression("JSON_EXTRACT(asset.data_json, '\$.asset_name')"), $searchModel->q],
            ['LIKE', new Expression("JSON_EXTRACT(asset.data_json, '\$.structure_type_name')"), $searchModel->q],
            ['LIKE', new Expression("JSON_EXTRACT(asset.data_json, '\$.address')"), $searchModel->q],
            ['LIKE', new Expression("JSON_EXTRACT(asset.data_json, '\$.location')"), $searchModel->q],
            ['LIKE', new Expression("JSON_EXTRACT(asset.data_json, '\$.floors')"), $searchModel->q],
            ['LIKE', new Expression("JSON_EXTRACT(asset.data_json, '\$.area')"), $searchModel->q],
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

        // มูลค่ารวม (ตามเงื่อนไขค้นหาปัจจุบัน และไม่รวมรายการที่ถูกลบ)
        /** @var \yii\db\ActiveQuery $sumQuery */
        $sumQuery = clone $dataProvider->query;
        $totalValue = (float) ($sumQuery->select(new Expression('SUM(price)'))->scalar() ?? 0);

        return $this->render('index', [
            'tabs' => 'structure',
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalValue' => $totalValue,
        ]);
    }

    /**
     * Cascade endpoint: คืนลูกภายใต้ node (หมวด/หมวดย่อย) + ค่า default ค่าเสื่อมของ node นั้น
     * รับ POST/GET group_code, ตอบ JSON { items:[{id,text,has_children}], defaults:{useful_life,depreciation_rate} }
     * has_children=true หมายถึง item นั้นยังมีลูกต่อ (ต้อง cascade อีกชั้น) ไม่ใช่ leaf จริง
     */
    public function actionStructureDepdrop()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $groupCode = (string) ($this->request->post('group_code') ?? $this->request->get('group_code') ?? '');

        $model = new Asset();
        $leaves = $model->ListStructureTypeByGroup($groupCode);
        $childCodes = $model->getStructureChildCodes();

        $items = [];
        foreach ($leaves as $code => $title) {
            $items[] = ['id' => $code, 'text' => $title, 'has_children' => in_array($code, $childCodes, true)];
        }

        $defaults = ['useful_life' => null, 'depreciation_rate' => null];
        if ($groupCode !== '') {
            $row = \app\models\Categorise::find()
                ->where(['name' => 'structure_type', 'code' => $groupCode])
                ->one();
            $json = $row && is_array($row->data_json)
                ? $row->data_json
                : ($row && is_string($row->data_json) ? (json_decode($row->data_json, true) ?: []) : []);
            $defaults['useful_life']       = $json['useful_life']       ?? null;
            $defaults['depreciation_rate'] = $json['depreciation_rate'] ?? null;
        }

        return [
            'status'   => 'success',
            'items'    => $items,
            'defaults' => $defaults,
        ];
    }

    /**
     * Endpoint: คืนค่า default ค่าเสื่อมของ leaf (รวม allow_other_note flag)
     * ใช้ตอน user เลือก leaf ใน select
     */
    public function actionStructureLeafDefaults()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $leafCode = (string) ($this->request->post('leaf_code') ?? $this->request->get('leaf_code') ?? '');

        $model = new Asset();
        return [
            'status'   => 'success',
            'defaults' => $model->getStructureDefaults($leafCode),
        ];
    }

    /**
     * ดาวน์โหลดเทมเพลต CSV สำหรับนำเข้าทะเบียนสิ่งปลูกสร้าง
     * คอลัมน์อ้างอิงจาก modules/am/data/structure_import_columns.php (20 คอลัมน์)
     */
    public function actionDownloadTemplate()
    {
        $config = require \Yii::getAlias('@app/modules/am/data/structure_import_columns.php');
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

        $filename = 'template_import_สิ่งปลูกสร้าง_' . date('Ymd') . '.csv';
        \Yii::$app->response->sendContentAsFile($csv, $filename, [
            'mimeType' => 'text/csv',
            'inline' => false,
        ]);
        \Yii::$app->end();
    }

    /**
     * Modal: นำเข้าทะเบียนสิ่งปลูกสร้าง (flow เหมือนนำเข้าครุภัณฑ์)
     */
    public function actionImport()
    {
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-file-import me-1"></i> นำเข้าทะเบียนสิ่งปลูกสร้าง',
                'content' => $this->renderAjax('import', []),
            ];
        }
        return $this->render('import', []);
    }

    /**
     * AJAX: อัปโหลด CSV → preview + ตรวจรหัสซ้ำ
     */
    public function actionPreview()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $file = UploadedFile::getInstanceByName('csvFile');
        if (!$file) {
            return ['status' => 'error', 'message' => 'ไม่พบไฟล์'];
        }
        $ext = strtolower($file->extension);
        if ($ext !== 'csv') {
            return ['status' => 'error', 'message' => 'รองรับเฉพาะไฟล์ .csv'];
        }

        $filePath = \Yii::getAlias('@runtime') . '/structure_import_' . time() . '_' . \Yii::$app->security->generateRandomString(8) . '.csv';
        $file->saveAs($filePath);

        $previewData = [];
        $duplicates = [];
        $row = 0;
        if (($handle = fopen($filePath, 'r')) !== false) {
            // UTF-8 BOM
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                $row++;
                $previewData[] = $data;
                if ($row === 1) {
                    continue;
                }
                $code = trim((string) ($data[0] ?? ''));
                if ($code !== '' && Asset::find()->where(['asset_group_id' => 3, 'code' => $code])->andWhere('deleted_at IS NULL')->exists()) {
                    $duplicates[] = $data;
                }
            }
            fclose($handle);
        } else {
            @unlink($filePath);
            return ['status' => 'error', 'message' => 'ไม่สามารถเปิดไฟล์ได้'];
        }

        return [
            'status' => 'success',
            'preview' => $previewData,
            'duplicates' => $duplicates,
            'filePath' => $filePath,
        ];
    }

    /**
     * POST: นำเข้าข้อมูลจริง (บันทึกเข้า asset_group_id = 3)
     */
    public function actionImportCsv()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $filePath = \Yii::$app->request->post('filePath');
        if (!$filePath || !is_file($filePath)) {
            return ['status' => 'error', 'message' => 'ไม่พบไฟล์'];
        }

        $rowsData = [];
        $errorRows = [];
        $rowNumber = 0;

        if (($handle = fopen($filePath, 'r')) !== false) {
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                $rowNumber++;
                if ($rowNumber === 1) {
                    continue;
                }

                $code = trim((string) ($data[0] ?? ''));
                $name = trim((string) ($data[1] ?? ''));
                if ($code === '' || $name === '') {
                    $errs = [];
                    if ($code === '') $errs['code'][] = 'ต้องระบุหมายเลขครุภัณฑ์';
                    if ($name === '') $errs['asset_name'][] = 'ต้องระบุชื่อสิ่งปลูกสร้าง';
                    $errorRows[] = ['row' => $rowNumber, 'code' => $code, 'errors' => $errs];
                    continue;
                }
                if (Asset::find()->where(['asset_group_id' => 3, 'code' => $code])->andWhere('deleted_at IS NULL')->exists()) {
                    $errorRows[] = ['row' => $rowNumber, 'code' => $code, 'errors' => ['code' => ['รหัสซ้ำในระบบ']]];
                    continue;
                }

                // คอลัมน์อ้างอิงจาก structure_import_columns.php / views/structure/_form.php
                $model = new Asset([
                    'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
                ]);
                $model->asset_group_id = 3;
                $model->useful_life = 50;
                $model->code = $code;
                $model->asset_name = $name;
                $model->price = (float) ($data[6] ?? 0);
                $model->on_year = trim((string) ($data[8] ?? ''));
                $model->receive_date = $this->normalizeDateForDb($data[15] ?? '');

                $statusRaw = trim((string) ($data[18] ?? ''));
                $model->asset_status = $statusRaw !== ''
                    ? $this->resolveCategoriseCodeByName('asset_status', $statusRaw)
                    : '1';
                if ($model->asset_status === '') {
                    $model->asset_status = '1';
                }

                $deptResolved = $this->resolveDepartmentFromImport(trim((string) ($data[9] ?? '')));
                if ($deptResolved !== null) {
                    $model->department = $deptResolved;
                }
                $ownerResolved = $this->resolveOwnerFromImport(trim((string) ($data[17] ?? '')));
                if ($ownerResolved !== null) {
                    $model->owner = $ownerResolved;
                }
                $model->purchase = $this->resolvePurchaseFromImport($data[11] ?? '');

                $vendorNameRaw = trim((string) ($data[13] ?? ''));
                $vendorCodeResolved = $this->resolveVendorFromImport(trim((string) ($data[12] ?? '')), $vendorNameRaw);

                $model->data_json = [
                    'structure_type_name' => trim((string) ($data[2] ?? '')),
                    'location' => trim((string) ($data[3] ?? '')),
                    'area' => trim((string) ($data[4] ?? '')),
                    'asset_options' => trim((string) ($data[5] ?? '')),
                    'budget_type' => $this->resolveBudgetTypeFromImport($data[7] ?? ''),
                    'method_get' => $this->resolveMethodGetFromImport($data[10] ?? ''),
                    'vendor_id' => $vendorCodeResolved,
                    'vendor_name' => $vendorNameRaw,
                    'inspection_date' => $this->normalizeDateForDb($data[14] ?? ''),
                    'expire_date' => $this->normalizeDateForDb($data[16] ?? ''),
                    'note' => trim((string) ($data[19] ?? '')),
                ];

                $model->validate();
                $errors = $model->getErrors();
                unset($errors['csvFile']);
                if (!empty($errors)) {
                    $errorRows[] = [
                        'row' => $rowNumber,
                        'code' => $code,
                        'errors' => $errors,
                    ];
                    continue;
                }
                $rowsData[] = $model;
            }
            fclose($handle);
        } else {
            return ['status' => 'error', 'message' => 'ไม่สามารถเปิดไฟล์ CSV ได้'];
        }

        if (!empty($errorRows)) {
            return [
                'status' => 'error',
                'message' => 'พบข้อผิดพลาดใน CSV',
                'errors' => $errorRows,
            ];
        }

        $db = \Yii::$app->db;
        $tx = $db->beginTransaction();
        try {
            $imported = 0;
            foreach ($rowsData as $m) {
                if ($m->save(false)) {
                    $imported++;
                }
            }
            $tx->commit();
            @unlink($filePath);
            return [
                'status' => 'success',
                'message' => "นำเข้าข้อมูลเรียบร้อย {$imported} แถว",
            ];
        } catch (\Throwable $e) {
            $tx->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * แปลงวันที่จากไฟล์นำเข้าเป็นรูปแบบ DB (Y-m-d) หรือ null
     * (ยึด logic เดียวกับ ImportController)
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

        // Fix malformed years e.g. "20925" -> "2025" (sometimes an extra digit gets inserted)
        // Only apply to year-part candidates (4+ digits)
        $fixYear = function ($yearDigits) {
            $y = preg_replace('/\D+/', '', (string) $yearDigits);
            if ($y === '') {
                return null;
            }
            if (strlen($y) === 5 && str_starts_with($y, '20')) {
                // 20 + last 2 digits => 2025
                return (int) ('20' . substr($y, -2));
            }
            if (strlen($y) > 4) {
                // fallback: take last 4 digits if plausible later
                return (int) substr($y, -4);
            }
            return (int) $y;
        };

        // Y-m-d (CE)
        $y0 = $fixYear($p0);
        if ($y0 !== null && $y0 >= 1900 && $y0 <= 2100 && strlen($d0) >= 4) {
            $y = $y0; $m = $n1; $d = $n2;
            return checkdate($m, $d, $y) ? sprintf('%04d-%02d-%02d', $y, $m, $d) : null;
        }
        // Y-m-d (BE)
        $y0 = $fixYear($p0);
        if ($y0 !== null && $y0 >= 2400 && $y0 <= 2600 && strlen($d0) >= 4) {
            $y = $y0 - 543; $m = $n1; $d = $n2;
            return checkdate($m, $d, $y) ? sprintf('%04d-%02d-%02d', $y, $m, $d) : null;
        }
        // d-m-Y (CE)
        $y2 = $fixYear($p2);
        if ($y2 !== null && $y2 >= 1900 && $y2 <= 2100 && strlen($d2) >= 4) {
            $y = $y2; $m = $n1; $d = $n0;
            return checkdate($m, $d, $y) ? sprintf('%04d-%02d-%02d', $y, $m, $d) : null;
        }
        // d-m-Y (BE)
        $y2 = $fixYear($p2);
        if ($y2 !== null && $y2 >= 2400 && $y2 <= 2600 && strlen($d2) >= 4) {
            $y = $y2 - 543; $m = $n1; $d = $n0;
            return checkdate($m, $d, $y) ? sprintf('%04d-%02d-%02d', $y, $m, $d) : null;
        }
        // fallback: AppHelper
        $dbDate = AppHelper::DateToDb($dateStr);
        if (!is_string($dbDate) || strpos($dbDate, '-') === false) {
            return null;
        }
        $p = explode('-', $dbDate);
        if (count($p) !== 3) {
            return null;
        }
        $y = (int) $p[0]; $m = (int) $p[1]; $d = (int) $p[2];
        if ($y < 1900 || $y > 2100 || !checkdate($m, $d, $y)) {
            return null;
        }
        return $dbDate;
    }

    /**
     * แปลงค่าหน่วยงานจาก CSV เป็น organization id (หรือ null)
     * รองรับ: ค่าว่าง, ตัวเลข (id), ข้อความ (ค้นจากชื่อหน่วยงาน)
     */
    protected function resolveDepartmentFromImport($str)
    {
        if ($str === '') {
            return null;
        }
        if (is_numeric($str)) {
            $org = Organization::findOne((int) $str);
            return $org ? (int) $org->id : null;
        }
        $org = Organization::find()->where(['name' => $str])->one();
        return $org ? (int) $org->id : null;
    }

    /**
     * แปลงค่าผู้รับผิดชอบจาก CSV เป็น employee id (หรือ null)
     * รองรับ: ค่าว่าง, ตัวเลข (id), ข้อความ (ค้นจาก fullname หรือ cid)
     */
    protected function resolveOwnerFromImport($str)
    {
        if ($str === '') {
            return null;
        }
        if (is_numeric($str)) {
            $emp = Employees::findOne((int) $str);
            return $emp ? (int) $emp->id : null;
        }
        $emp = Employees::find()->where(['fullname' => $str])->one();
        if ($emp) {
            return (int) $emp->id;
        }
        $emp = Employees::find()->where(['cid' => $str])->one();
        return $emp ? (int) $emp->id : null;
    }

    protected function resolveCategoriseCodeByName($categoryName, $value)
    {
        $value = trim(preg_replace('/\s+/u', ' ', (string) $value));
        if ($value === '') {
            return '';
        }
        $rows = Categorise::find()->where(['name' => $categoryName])->all();
        foreach ($rows as $row) {
            if ((string) $row->code === $value) {
                return (string) $row->code;
            }
        }
        foreach ($rows as $row) {
            if ((string) $row->title === $value) {
                return (string) $row->code;
            }
        }
        $lower = static function ($s) {
            return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
        };
        $vLower = $lower($value);
        foreach ($rows as $row) {
            if ($lower((string) $row->title) === $vLower) {
                return (string) $row->code;
            }
        }

        return '';
    }

    protected function resolveBudgetTypeFromImport($value)
    {
        return $this->resolveCategoriseCodeByName('budget_type', $value);
    }

    protected function resolveMethodGetFromImport($value)
    {
        return $this->resolveCategoriseCodeByName('method_get', $value);
    }

    protected function resolvePurchaseFromImport($value)
    {
        return $this->resolveCategoriseCodeByName('purchase', $value);
    }

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

    protected function findVendorByTitle($title = null)
    {
        $model = Categorise::find()->where(['name' => 'vendor', 'title' => $title])->one();
        if ($model) {
            return $model->code;
        }
        $newVendor = new Categorise(['name' => 'vendor', 'title' => $title]);
        $newVendor->code = $this->getNextVendorCode();
        $newVendor->save(false);

        return $newVendor->code;
    }

    protected function resolveVendorFromImport($vendorCode = '', $vendorName = '')
    {
        $vendorCode = trim((string) $vendorCode);
        $vendorName = trim((string) $vendorName);

        if ($vendorCode !== '') {
            $byCode = Categorise::find()->where(['name' => 'vendor', 'code' => $vendorCode])->one();
            if ($byCode) {
                if ($vendorName !== '' && $byCode->title !== $vendorName) {
                    $byCode->title = $vendorName;
                    $byCode->save(false);
                }

                return $byCode->code;
            }
            if ($vendorName !== '') {
                return $this->findVendorByTitle($vendorName);
            }

            return '';
        }
        if ($vendorName !== '') {
            return $this->findVendorByTitle($vendorName);
        }

        return '';
    }

    public function actionDelete($id)
    {
        // ให้สอดคล้องกับ AssetController: ลบได้เฉพาะ admin
        if (!\Yii::$app->user->can('admin')) {
            if ($this->request->isAjax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'error', 'message' => 'ไม่มีสิทธิลบข้อมูล'];
            }
            throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิลบข้อมูล');
        }

        $model = $this->findModel($id);
        if ($model->deleted_at !== null) {
            if ($this->request->isAjax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'error', 'message' => 'รายการนี้ถูกลบไปแล้ว'];
            }
            return $this->redirect(['index']);
        }

        $model->deleted_at = new Expression('NOW()');
        $model->deleted_by = \Yii::$app->user->id;
        $model->save(false);

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => 'success', 'url' => Url::to(['/am/structure/index'])];
        }

        return $this->redirect(['index']);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
        ]);
    }
    public function actionCreate()
    {
        $model = new Asset([
            'asset_group_id' => 3,
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
        ]);

        if ($this->request->isPost) {
            $loaded = $model->load($this->request->post());
            if ($loaded) {
                $model->asset_group_id = 3;
                $model->receive_date = AppHelper::DateToDb($model->receive_date);
            }

            if ($loaded && $model->save()) {
                if ($this->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'status' => 'success',
                        'url' => Url::to(['view', 'id' => $model->id]),
                    ];
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }

            if ($this->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'error',
                    'message' => 'กรุณาตรวจสอบข้อมูลที่จำเป็นให้ครบถ้วน',
                    'errors' => $model->getErrors(),
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
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


            $postedData = is_array($model->data_json) ? $model->data_json : [];
            $convert_date = [
                'expire_date' => AppHelper::DateToDb($postedData['expire_date'] ?? null),
                'inspection_date' => AppHelper::DateToDb($postedData['inspection_date'] ?? null),
            ];


            $model->data_json = ArrayHelper::merge($old_data_json, $model->data_json, $convert_date);
            if ($model->save()) {
                return [
                    'status' => 'success',
                    'url' => Url::to(['view', 'id' => $model->id]),
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'กรุณาตรวจสอบข้อมูลที่จำเป็นให้ครบถ้วน',
                    'errors' => $model->getErrors(),
                ];
            }
        }

        $viewDate = [
            'expire_date' => (isset($model->data_json['expire_date']) ? AppHelper::DateFormDb($model->data_json['expire_date']) : ''),
            'inspection_date' => (isset($model->data_json['inspection_date']) ? AppHelper::DateFormDb($model->data_json['inspection_date']) : ''),
        ];

        $model->data_json = ArrayHelper::merge($old_data_json, $model->data_json, $viewDate);
        $model->data_json = ArrayHelper::merge($old_data_json, $model->data_json, $viewDate);

        return $this->render('update', [
            'model' => $model,
            // 'group' => $model->asset_group_id
        ]);
    }


    protected function findModel($id)
    {
        if (($model = Asset::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
}
