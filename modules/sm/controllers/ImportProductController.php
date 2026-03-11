<?php


namespace app\modules\sm\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\models\Categorise;
use app\components\AppHelper;
use app\models\UploadCsvForm;
use app\components\ProductHelper;
use app\modules\inventory\models\Product;
use app\models\Employee; // ตัวอย่าง Model
use app\modules\inventory\models\StockEvent;

class ImportProductController extends Controller
{
    /**
     * หน้าอัปโหลด CSV
     */
    public function actionIndex($order_id = null)
    {
        $model = new Product();

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax(
                    'index',
                    [
                        'model' => $model,
                    ]
                ),
            ];
        } else {
            return $this->render('index', ['model' => $model]);
        }
    }

    /**
     * AJAX: แสดงตัวอย่าง CSV
     */
    public function actionPreview()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new UploadCsvForm();
        $model->csvFile = UploadedFile::getInstanceByName('csvFile');

        if ($model && $model->validate()) {
            // บันทึกไฟล์ชั่วคราว
            $filePath = Yii::getAlias('@runtime') . '/import_' . time() . '.' . $model->csvFile->extension;
            $model->csvFile->saveAs($filePath);

            // อ่าน CSV แถวแรก 10 แถว
            $previewData = [];
            if (($handle = fopen($filePath, "r")) !== false) {
                $row = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $previewData[] = $data;
                    $row++;
                    // if ($row >= 10) break;
                }
                fclose($handle);
            }

            return [
                'status' => 'success',
                'preview' => $previewData,
                'filePath' => $filePath,
            ];
        }

        return [
            'status' => 'error',
            'errors' => $model->getErrors(),
        ];
    }

    /**
     * POST: นำเข้าข้อมูลจริง
     */
    public function actionImportCsv()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $filePath = Yii::$app->request->post('filePath');
        $categoryId = Yii::$app->request->post('category_id');

        if (!$filePath || !file_exists($filePath)) {
            return ['status' => 'error', 'message' => 'ไม่พบไฟล์'];
        }

        $imported = 0;
        $msg = [];
        if (($handle = fopen($filePath, "r")) !== false) {
            $row = 0;
            $error = 0;

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $row++;
                if ($row == 1) continue; // ข้าม header
               
                $result = $this->findProduct($data[0], $data[1], $categoryId, $data[2]);
                if ($result['status'] == 'success') {
                    $imported++;
                } else {
                    $error++;
                     $msg[] = $data[1];
                    // คุณอาจเก็บ log ของ error แถวนี้ได้
                }
            }
            fclose($handle);
            if ($error >= 1) {
                return [
                    'status' => 'error',
                    'message' => "xx",
                    'msg' => $msg
                ];
            }
            return [
                'status' => 'success',
                'message' => "นำเข้าข้อมูลเรียบร้อย {$imported} แถว",
                'msg' => $msg
            ];
        }

        // return ['status' => 'success', 'message' => "นำเข้าข้อมูลเรียบร้อย {$imported} แถว"];
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
                // $newProduct->group_id = 4;
                $newProduct->group_id = 'MATER';
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
