<?php

namespace app\modules\helpdesk\controllers;

use Yii;
use Exception;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\inventory\models\Stock;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockSearch;
use app\modules\inventory\models\StockEventSearch;

class StockController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $getWarehouse = \Yii::$app->session->get('selectMainWarehouse');

        $userid = \Yii::$app->user->id;
        // ตรวจสอบว่า user มีสิทธิ์เข้าถึง คลังไหนบ้าง
        $warehouseModel = Warehouse::find()->andWhere(new Expression("JSON_CONTAINS(data_json->'\$.officer','\"$userid\"')"))->one();
        // ตรวจสอลคลังว่ามีวัสดุอะไรที่สามารถเบิกได้
        $item = $warehouseModel->data_json['item_type'];

        $searchModel = new StockSearch([
            'warehouse_id' => $warehouseModel->id,
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        // $dataProvider->query->andFilterWhere(['warehouse_id' => ($getWarehouse ? $getWarehouse['warehouse_id'] : $searchModel->warehouse_id)]);
        // $dataProvider->query->andFilterWhere(['warehouse_id' => ($getWarehouse ? $getWarehouse['warehouse_id'] : $searchModel->warehouse_id)]);
        $dataProvider->query->andFilterWhere(['p.category_id' => $searchModel->asset_type]);

        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'asset_item', $searchModel->q],
            ['like', 'title', $searchModel->q],
        ]);
        $dataProvider->setSort([
            'defaultOrder' => [
                'unit_price' => SORT_DESC,
            ],
        ]);
        $dataProvider->query->groupBy('asset_item');
        $dataProvider->pagination->pageSize = 24;
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'count' => $dataProvider->getTotalCount(),
                'content' => $this->renderAjax('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    // เพิ่มสินค้าลงใจตะกร้า
    public function actionAddToCart($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $cart = \Yii::$app->cart;

        $model = Stock::findOne($id);

        $cart->create($model, 1);
        $totalCount = $cart->getCount();

        return [
            'status' => 'success',
            'container' => '#helpdesk-cart-container',
            'totalCount' => $totalCount,
        ];
    }

    // แก้ไขจำนวนสินค้าในตะกร้า
    public function actionUpdateCart()
    {
        $cart = \Yii::$app->cart;
        $id = $this->request->get('id');
        $quantity = $this->request->get('quantity');
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Stock::findOne($id);
        $checkStock = Stock::findOne($id);
        $cart->update($model, $quantity);

        return [
            'container' => '#helpdesk-cart-container',
            'status' => 'success',
        ];
    }

    // กำหนดจำนวนที่จ่ายให้
    public function actionUpdateQty()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $qty = $this->request->get('qty');
        $model = Stock::findOne($id);
        \Yii::$app->cart->update($model, $qty);
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'status' => 'success',
            'container' => '#helpdesk-cart-container',
        ];
    }

    // ลบสินค้าออกจากตะกร้า
    public function actionDeleteItem($id)
    {
        $cart = \Yii::$app->cart;
        $product = Stock::findOne($id);
        if ($product) {
            $cart->delete($product);
            if ($cart->getCount() < 1) {
                \Yii::$app->session->remove('selectMainWarehouse');
            }
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'container' => '#helpdesk-cart-container',
                'status' => 'success',
                'countItem' => $cart->getCount(),
            ];
        }
    }

    // บันทเบิกสินค้า
    public function actionCheckOut()
    {
        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $cart = \Yii::$app->cart;
            // ตรวจสอบว่า user มีสิทธิ์เข้าถึง คลังไหนบ้าง
            $userid = \Yii::$app->user->id;
            $warehouseModel = Warehouse::find()->andWhere(new Expression("JSON_CONTAINS(data_json->'\$.officer','\"$userid\"')"))->one();
            $items = $cart->getItems();
            $warehouse = \Yii::$app->session->get('warehouse');
            $id = $this->request->post('id');

            $helpdesk = Helpdesk::findOne($id);
            if(isset($helpdesk->data_json['end_job']) && $helpdesk->data_json['end_job'] == "1"){
                // return $helpdesk->data_json['end_job'];
            }else{
                return [
                    'status' => 'error',
                    'container' => '#helpdesk-container',
                    'msg' => 'ไม่สามารถเบิกสินค้าได้ เนื่องจากงานยังไม่เสร็จสิ้น'
                ];
            }
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $year = AppHelper::YearBudget($helpdesk->data_json['end_job_date']);
                $model = new StockEvent([
                    'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
                    'code' => \mdm\autonumber\AutoNumber::generate('OUT-' . substr($year, 2) . '????'),
                    'helpdesk_id' => $helpdesk->id,
                    'warehouse_id' => $warehouseModel->id,
                    'name' => 'order',
                    'transaction_type' => 'OUT',
                    'order_status' => 'success',
                    'thai_year' => $year
                ]);

                if (!$model->save(false)) {
                    throw new \Exception('ไม่สามารถบันทึกข้อมูล Order ได้');
                }
                // ถ้า Save Order เสร็จ ให้ save Items
                foreach ($cart->getItems() as $item) {
                    $newItem = new StockEvent([
                        'name' => 'order_item',
                        'thai_year' => $year,
                        'transaction_type' => $model->transaction_type,
                        'category_id' => $model->id,
                        'warehouse_id' => $model->warehouse_id,
                        'asset_item' => $item->asset_item,
                        'lot_number' => $item->lot_number,
                        'unit_price' => $item->unit_price,
                        'qty' => $item->getQuantity(),
                        'order_status' => 'success',
                        'data_json' => [
                            'req_qty' => $item->getQuantity(),
                        ],
                    ]);
                    if (!$newItem->save(false)) {
                        throw new Exception('ไม่สามารถบันทึกข้อมูล Order ITems ได้');
                    }
                    // ถ้า save icon เสร็จให้ update stock
                    $stock = Stock::findOne($item->id);
                    if ($stock) {
                        $stock->qty = ($stock->qty - $newItem->qty);
                        if (!$stock->save(false)) {
                            throw new \Exception('ไม่สามารถบันทึกข้อมูล Stock ได้');
                        }
                    }
                }
                $cart->checkOut(false);
                $transaction->commit();
                return [
                    'container' => '#helpdesk-container',
                    'status' => 'success'
                ];
            } catch (\Throwable $e) {
                $transaction->rollBack();

                return ['status' => 'error', 'message' => $e->getMessage()];
            }
        }
    }
}
