<?php


namespace app\modules\inventory\controllers;

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

class ImportController extends Controller
{
    /**
     * หน้าอัปโหลด CSV
     */
    public function actionIndex($order_id = null)
    {
        $model = new UploadCsvForm();

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax(
                    'index',
                    [
                        'model' => $model,
                        'order_id' => $order_id
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
        $id = Yii::$app->request->post('order_id');
        $stockOrder = StockEvent::findOne($id);
        if (!$filePath || !file_exists($filePath)) {
            return ['status' => 'error', 'message' => 'ไม่พบไฟล์'];
        }
        if (!$stockOrder) {
            return ['status' => 'error', 'message' => 'ไม่พบรายการออเดอร์'];
        }

        $imported = 0;
        $demo = [];
        if (($handle = fopen($filePath, "r")) !== false) {
            $row = 0;
            $error = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $row++;
                if ($row == 1) continue; // ข้าม header

                $data = array_pad($data, 6, '');
                $code = trim((string) $data[0]);
                $title = trim((string) $data[1]);
                $unit = trim((string) $data[2]);
                $qty = (float) str_replace(',', '', (string) $data[3]);
                $totalPrice = (float) str_replace(',', '', (string) $data[4]);
                $lotNumber = trim((string) $data[5]);

                // ข้ามบรรทัดว่างเปล่า แต่ถ้าข้อมูลหลักหายให้มองว่าเป็นแถวผิดพลาด
                if ($code === '' && $title === '' && $unit === '' && $qty == 0.0 && $totalPrice == 0.0 && $lotNumber === '') {
                    continue;
                }
                if ($code === '' || $title === '') {
                    $error++;
                    continue;
                }

                $assetType = $stockOrder->asset_type_id;

                $product = $this->findProduct($code, $title, $assetType, $unit);
                if ($product['status'] == 'error') {
                    $error++;
                } else {
                    $assetItem = $product['data']['code'];
                    $checkInOrder = StockEvent::findOne(['name' => 'order_item', 'asset_item' => $assetItem, 'category_id' => $stockOrder->id]);
                    if (!$checkInOrder) {

                        $lotNumber = $lotNumber !== '' ? $lotNumber : \mdm\autonumber\AutoNumber::generate('LOT' . substr(AppHelper::YearBudget(), 2) . '-?????');
                        $unitPrice = $qty > 0 ? ($totalPrice / $qty) : 0;

                        $item = new StockEvent([
                            'name' => 'order_item',
                            'asset_item' => $assetItem,
                            'qty' => $qty,
                            'unit_price' => $unitPrice,
                            'total_price' => $totalPrice,
                            'order_status' => 'pending',
                            'transaction_type' => 'IN',
                            'warehouse_id' => $stockOrder->warehouse_id, // แนบ order_id
                            'code' => $stockOrder->code,
                            'category_id' => $stockOrder->id,
                            'lot_number' => $lotNumber,
                            'data_json' => [
                                "req_qty" =>  "",
                                "exp_date" => "",
                                "mfg_date" => "",
                                "item_type" =>  "ยอดยกมา",
                                "po_number" => "",
                                "pq_number" => "",
                                "vendor_name" => "",
                                "receive_date" => "",
                                "asset_type_name" => "",
                                "employee_fullname" => "",
                                "employee_position" => " ",
                                "employee_department" => ""
                            ]
                        ]);
                        if ($item->save(false)) $imported++;
                    }
                }
                // $demo[] = $product['status'];




            }
            fclose($handle);
            if ($error >= 1) {
                return [
                    'status' => 'error',
                    'message' => "xx",
                    'demo' => $demo
                ];
            }
            return [
                'status' => 'success',
                'message' => "นำเข้าข้อมูลเรียบร้อย {$imported} แถว",
                'demo' => $demo
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
