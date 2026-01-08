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

            // อ่าน CSV แถวแรก 10 แถว
            $previewData = [];
            $previewDataDuplicate = [];

            if (($handle = fopen($filePath, "r")) !== false) {
                $row = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $checkCodeDuplicate = Asset::find()->where(['code' => $data[0]])->count();
                    $previewData[] = $data; // เก็บข้อมูลทุกแถว
                    $row++;

                    if ($row != 1 && $checkCodeDuplicate >= 1) {
                        $previewDataDuplicate[] = $data; // เก็บเฉพาะแถวที่ซ้ำ
                    }
                }
                fclose($handle);
                return [
                    'status' => 'success',
                    'preview' => $previewData,          // มี header เสมอ
                    'duplicates' => $previewDataDuplicate, // ไม่มี header
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

        if (($handle = fopen($filePath, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $rowNumber++;
                if ($rowNumber == 1) continue; // ข้าม header

                $model = new Asset();
                $model->asset_type_id = $postData['asset_type_id'];
                $model->asset_category_id = $postData['asset_category_id'];
                $model->code = $data[0];
                $model->fsn_number = $data[1];
                $model->asset_name = $data[2];
                $model->data_json = [
                    'brand' => $data[3],
                    'asset_model' => $data[4],
                    'color_name' => $data[5],
                    'unit' => $data[6],
                    'serial_number' => $data[7],
                    'vendor_id' => $data[10],
                    'budget_type' => $data[9],
                    'inspection_date' => DateHelper::convertToDatabaseDate($data[11]),
                    'receive_date' => DateHelper::convertToDatabaseDate($data[13]),
                    'expire_date' => $data[14],
                    'location' => $data[16],
                    'fsn_old' => $data[0],
                    'vendor_id' => $this->findVendor($data[18]),
                ];
                $model->price = $data[8];
                $model->purchase = $this->findPurchase($data[10]);
                $model->receive_date = DateHelper::convertToDatabaseDate($data[13]);
                $model->on_year = $data[12];
                $model->license_plate = $data[17];
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

public function findPurchase($tite = null)
    {
        $model = Categorise::find()->where(['name' => 'purchase', 'title' => $tite])->one();
        if ($model) {
            return $model->code;
        } else {
            return 0;
        }
    }

    public function findVendor($tite = null)
    {
        $model = Categorise::find()->where(['name' => 'vendor', 'title' => $tite])->one();
        if(!$model){
            $newVender = new Categorise(['name'=>'vendor','title'=>$tite]);
            $newVender-> code = \mdm\autonumber\AutoNumber::generate('vendor-?');
            $newVender-> save(false);
            return $newVender->code;
        }else{
            return $model->code;    

    }
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
