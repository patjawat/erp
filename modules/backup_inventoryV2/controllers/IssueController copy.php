<?php
namespace app\modules\inventoryV2\controllers;

use app\modules\inventoryV2\components\InventoryService;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockOrder;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class IssueController extends Controller
{


public function actionIndex()
    {
        return $this->render('index');
    }   

    public function actionCreate()
    {
        $model = new StockOrder();
        $model->order_type = 'OUT'; // กำหนดเป็นประเภทจ่ายออก
        $model->order_date = date('Y-m-d H:i:s');
        $model->status = 'CONFIRMED';

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->load($this->request->post()) && $model->save()) {
                    $details = $this->request->post('StockDetail', []);
                    
                    foreach ($details as $data) {
                        $detail = new StockDetail();
                        if ($detail->load($data, '')) {
                            $detail->stock_order_id = $model->id;
                            //remain_qty ของการเบิกจ่าย (OUT) จะเป็น 0 เสมอ เพราะเราไม่ได้เอาล็อตนี้ไปให้ใครเบิกต่อ
                            $detail->remain_qty = 0; 
                            
                            if ($detail->save()) {
                                // เรียก Service เพื่อหัก StockBalance และ ตัด FIFO ใน StockDetail ล็อตที่รับเข้ามา
                                InventoryService::moveStock(
                                    $detail->item_code,
                                    $model->warehouse_id,
                                    $detail->qty,
                                    'OUT',
                                    $model->id
                                );
                            }
                        }
                    }
                    $transaction->commit();
                    return ['success' => true, 'redirect' => ['view', 'id' => $model->id]];
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }
}