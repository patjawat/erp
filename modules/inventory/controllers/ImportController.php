<?php


namespace app\modules\inventory\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\models\UploadCsvForm;
use app\modules\sm\models\Product;
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
                'content' => $this->renderAjax('index',
                [
                    'model' => $model,
                    'order_id' => $order_id
                ]),
            ];
        } else {
            return $this->render('index',['model' => $model]);
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
                    if ($row >= 10) break;
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
        return ['status'=>'error','message'=>'ไม่พบไฟล์'];
    }

    $imported = 0;
    if (($handle = fopen($filePath, "r")) !== false) {
        $row = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $row++;
            if ($row == 1) continue; // ข้าม header

return Product::createOrUpdate($stockOrder->data_json['asset_type'], $data[1]);
            $item = new StockEvent([
                'name' => 'asset_item',
                'transaction_type' => 'IN',
                'warehouse_id' => $stockOrder->warehouse_id, // แนบ order_id
                'code' => $stockOrder->code,
                'category_id' => $stockOrder->id
            ]);
            $item->asset_item = Product::createOrUpdate($stockOrder->data_json['asset_type'], $data[1]);
            if($item->save(false)) $imported++;
        }
        fclose($handle);
    }

    return ['status'=>'success','message'=>"นำเข้าข้อมูลเรียบร้อย {$imported} แถว"];
}

}
