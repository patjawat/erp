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

        $rowsData = [];
        $errorRows = [];
        $rowNumber = 0;
        $codesInFile = []; // หมายเลขครุภัณฑ์ที่เจอในไฟล์นี้ (ไม่ให้ซ้ำในไฟล์) — หมายเลข FSN ซ้ำได้

        if (($handle = fopen($filePath, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $rowNumber++;
                if ($rowNumber == 1) continue; // ข้าม header

                $code = trim((string) ($data[0] ?? ''));
                if ($code !== '') {
                    if (Asset::find()->where(['code' => $code])->exists()) {
                        $errorRows[] = ['row' => $rowNumber, 'code' => $code, 'errors' => ['code' => ['หมายเลขครุภัณฑ์ซ้ำในระบบ']]];
                        continue;
                    }
                    if (isset($codesInFile[$code])) {
                        $errorRows[] = ['row' => $rowNumber, 'code' => $code, 'errors' => ['code' => ['หมายเลขครุภัณฑ์ซ้ำในไฟล์นี้']]];
                        continue;
                    }
                    $codesInFile[$code] = true;
                }

                // มีรหัสผู้ขายแต่ชื่อว่าง และไม่เจอรหัสในฐานข้อมูล → ไม่นำเข้าแถวนี้
                $vendorCode = trim((string) ($data[18] ?? ''));
                $vendorName = trim((string) ($data[19] ?? ''));
                if ($vendorCode !== '' && $vendorName === '') {
                    $vendorExists = Categorise::find()->where(['name' => 'vendor', 'code' => $vendorCode])->exists();
                    if (!$vendorExists) {
                        $errorRows[] = ['row' => $rowNumber, 'code' => $code, 'errors' => ['vendor' => ['ไม่พบรหัสผู้ขาย/ผู้บริจาคในระบบ — ไม่นำเข้าแถวนี้']]];
                        continue;
                    }
                }

                $model = new Asset();
                $model->asset_type_id = $postData['asset_type_id'];
                $model->asset_category_id = $postData['asset_category_id'];
                $model->code = $data[0];
                $model->fsn_number = $data[1]; // หมายเลข FSN ซ้ำได้
                $model->asset_name = $data[2];
                $model->data_json = [
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
                    'order_number' => trim((string) ($data[21] ?? '')),
                    'note' => trim((string) ($data[22] ?? '')),
                ];
                $model->price = $data[8];
                $model->purchase = $this->resolvePurchaseFromImport($data[10] ?? '');
                $model->receive_date = $this->normalizeDateForDb($data[13] ?? '');
                $model->on_year = $data[12];
                if (!empty($data[15])) {
                    $model->on_year = $data[15]; // ปีงบประมาณ (ซ้ำ) ถ้ามีให้ใช้ค่านี้แทน
                }
                $model->license_plate = $data[17];
                $model->useful_life = (int) ($data[20] ?? 0); // อายุการใช้งาน (ปี)
                $model->asset_status = 1;
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
                }

                $rowsData[] = $model;
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

            // บันทึกทุกแถว
            $imported = 0;
            foreach ($rowsData as $model) {
                if ($model->save(false)) $imported++;
            }

            return [
                'status' => 'success',
                'message' => "นำเข้าข้อมูลเรียบร้อย {$imported} แถว"
            ];
        }

        return ['status' => 'error', 'message' => 'ไม่สามารถเปิดไฟล์ CSV ได้'];
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
     * - มีรหัส: ค้น WHERE name='vendor' AND code=รหัส ถ้าเจอคืน code (ไม่แก้ไข) ถ้าไม่เจอสร้างใหม่
     * - รหัสว่างแต่มีชื่อ: ไม่แจ้งเตือน — ค้น/สร้างผู้ขายอัตโนมัติจากชื่อ (findVendor)
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
            // มีรหัสแต่ชื่อว่างและไม่เจอใน DB → caller จะข้ามแถว ไม่สร้าง vendor
            if ($vendorName === '') {
                return '';
            }
            // ไม่พบรหัสในทะเบียนแต่มีชื่อ → สร้างใหม่
            $newVender = new Categorise(['name' => 'vendor', 'title' => $vendorName]);
            $newVender->code = $vendorCode;
            $newVender->save(false);
            return $newVender->code;
        }

        // รหัสว่างแต่มีชื่อ: ไม่แจ้งเตือน — นำเข้า/สร้างผู้ขายอัตโนมัติตามโครงสร้างระบบผู้แทนจำหน่าย (categorise name=vendor เหมือน /sm/vendor)
        if ($vendorName !== '') {
            return $this->findVendor($vendorName); // ค้นจาก title หรือสร้างใหม่ (code อัตโนมัติ)
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
