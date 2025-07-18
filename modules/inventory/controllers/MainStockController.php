<?php

namespace app\modules\inventory\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use app\components\LineMsg;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\approve\models\Approve;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockSearch;
use app\modules\inventory\models\StockEventSearch;

class MainStockController extends Controller
{
    public function actionIndex()
    {
        $warehouse = \Yii::$app->session->get('warehouse');

        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        // $dataProvider->query->andwhere(['name' => 'order','transaction_type' => 'IN', 'warehouse_id' => $warehouse['warehouse_id']]);
        $dataProvider->query->andwhere(['name' => 'order', 'transaction_type' => 'OUT', 'from_warehouse_id' => $warehouse['warehouse_id']]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $formWarehouse = \Yii::$app->session->get('warehouse');
        $toWarehouse = \Yii::$app->session->get('selectMainWarehouse');

        // หากหม่มีการเลือกคลัง .ให้ redirec ไปที่ Dashbroad
        if (!isset($toWarehouse['warehouse_id']) && !isset($formWarehouse['warehouse_id'])) {
            return $this->redirec(['/inventory']);
        }

        $cart = \Yii::$app->cartMain;
        $userCreate = UserHelper::GetEmployee();
        $name = $this->request->get('name');
        $order_id = $this->request->get('order_id');
        $order = StockEvent::findOne($order_id);
        $type = $this->request->get('type');

        $model = new StockEvent([
            'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
            'warehouse_id' => $toWarehouse['warehouse_id'],
            'category_id' => $order_id,
            'name' => $name,
            'transaction_type' => $order ? $order->transaction_type : $type,
            'code' => $order ? $order->code : '',
            'checker' => $userCreate->leaderUser()['leader1'],
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;

                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // $transaction->commit();
                    // สร้างรหัสขอเบิก
                    if ($model->name == 'order') {
                        $model->code = \mdm\autonumber\AutoNumber::generate('REQ-' . substr(AppHelper::YearBudget(), 2) . '????');
                    }

                    // ถ้าผู้ขอกับผู้อนุมิติเป็นคนเดียวกันให้อนุมติได้เลย
                    // if ($userCreate->id == $model->checker) {
                    //     $newObj = $model->data_json = ['checker_confirm' => 'Y'];
                    //     $model->data_json = ArrayHelper::merge($model->data_json, $newObj);
                    // }
                    
                  
                    

                    $model->thai_year = AppHelper::YearBudget();
                    $model->order_status = 'pending';
                    $model->warehouse_id = $toWarehouse['warehouse_id'];
                    $model->from_warehouse_id = $formWarehouse['warehouse_id'];
                    if (!$model->save(false)) {
                        throw new \Exception('ไม่สามารถบันทึกข้อมูล Order ได้');
                    }

                    foreach ($cart->getItems() as $item) {
                        $newItem = new StockEvent([
                            'name' => 'order_item',
                            'thai_year' => AppHelper::YearBudget(),
                            'transaction_type' => $model->transaction_type,
                            'category_id' => $model->id,
                            'warehouse_id' => $model->warehouse_id,
                            'from_warehouse_id' => $model->from_warehouse_id,
                            'asset_item' => $item->asset_item,
                            'lot_number' => $item->lot_number,
                            'unit_price' => $item->unit_price,
                            'qty' => $item->getQuantity(),
                            // 'qty' => $item->SumLotQty(), //ระบุจำนวนจริงตาม lot ที่เหลือ
                            'data_json' => [
                                'req_qty' => $item->getQuantity(),
                            ],
                            'order_status' => 'pending',
                        ]);
                        if (!$newItem->save(false)) {
                            throw new \Exception('ไม่สามารถบันทึกข้อมูล Order ITems ได้');
                        }
                    }
                    $cart->checkOut(false);
                    \Yii::$app->session->remove('selectMainWarehouse');
                    // ถ้าไม่มีข้อผิดพลาด ทำการ commit
                    $transaction->commit();

                      // สร้างการอนุมัติ new feture
                      $approve = new Approve;

                      $approve->from_id = $model->id;
                      $approve->name = 'main_stock';
                      $approve->emp_id = $model->checker;
                      $approve->status = 'Pending';
                      $approve->title = 'อนุมัติ';
                      $approve->data_json = ['label' => 'อนุมัติ'];
                      $approve->save(false);
                      // try {
                          //ส่ง massage
                          $userId = $model->empChecker->user->line_id;
                          $message = 'ขออนุมัติเบิกวัสดุ';
                          LineMsg::sendMsg($userId, $message);
                      // } catch (\Throwable $th) {
                      //     //throw $th;
                      // }
                    
                      
                    return $this->redirect(['/inventory/main-stock']);
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    return ['status' => 'error', 'message' => $e->getMessage()];
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

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

    protected function saveCartItem($model)
    {
        $cart = \Yii::$app->cartMain;

        foreach ($cart->getItems() as $item) {
            $item = new StockEvent([
                'name' => 'order_item',
                'thai_year' => AppHelper::YearBudget(),
                'transaction_type' => $model->transaction_type,
                'category_id' => $model->id,
                'warehouse_id' => $model->warehouse_id,
                'asset_item' => $item->asset_item,
                'lot_number' => $item->lot_number,
                'unit_price' => $item->unit_price,
                'qty' => $item->SumLotQty(),  // ระบุจำนวนจริงตาม lot ที่เหลือ
                'data_json' => [
                    'req_qty' => $item->getQuantity(),
                ],
                'order_status' => 'pending',
            ]);
            $item->save(false);
        }

        return true;
    }

    public function actionStore()
    {
        $getWarehouse = \Yii::$app->session->get('selectMainWarehouse');
        $warehouse = Yii::$app->session->get('warehouse');
            $warehouseModel = \app\modules\inventory\models\Warehouse::findOne($warehouse['warehouse_id']);
            $item = $warehouseModel->data_json['item_type'];
            $product = ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type','category_id' => 4])->andWhere(['IN', 'code', $item])->all(), 'code', 'title');

        $searchModel = new StockSearch([
            'warehouse_id' => isset($getWarehouse['warehouse_id']) ? $getWarehouse['warehouse_id'] : '',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        // $dataProvider->query->leftJoin('categorise', 'categorise.code = stock.asset_item AND categorise.name = :name', [':name' => 'asset_item'])
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        $dataProvider->query->andWhere(['IN', 'p.category_id', $item]);
        $dataProvider->query->andFilterWhere(['warehouse_id' => ($getWarehouse ? $getWarehouse['warehouse_id'] : $searchModel->warehouse_id)]);
        $dataProvider->query->andFilterWhere(['p.category_id' => $searchModel->asset_type]);
        // $dataProvider->query->andFilterWhere(['p.category_id' =>$product]);
        
        // ->where(['categorise.category_id' => ['M1', 'M2']])
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
                'content' => $this->renderAjax('store', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('store', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }



    public function actionStore2()
    {
        $getWarehouse = \Yii::$app->session->get('selectMainWarehouse');
        $searchModel = new StockSearch([
            'warehouse_id' => isset($getWarehouse['warehouse_id']) ? $getWarehouse['warehouse_id'] : '',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        $dataProvider->query->andFilterWhere(['warehouse_id' => ($getWarehouse ? $getWarehouse['warehouse_id'] : $searchModel->warehouse_id)]);
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
                'content' => $this->renderAjax('store2', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('store2', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }
    
    public function actionViewCart()
    {
        $cart = \Yii::$app->cartMain;
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'content' => $this->renderAjax('view_cart'),
                'countItem' => $cart->getCount(),
            ];
        } else {
            return $this->render('view_cart');
        }
    }

    public function actionShowCart()
    {
        $cart = \Yii::$app->cartMain;
        $warehouseSelect = \Yii::$app->session->get('select-warehouse');
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => '<h6><i class="bi bi-ui-checks"></i> ขอเบิก <span class="badge rounded-pill text-bg-primary countMainItem">' . $cart->getCount() . ' </span> รายการ</h6>',
                'content' => $this->renderAjax('show_cart'),
                'countItem' => $cart->getCount(),
            ];
        } else {
            return $this->render('show_cart');
        }
    }

    public function actionAddToCart($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $cart = \Yii::$app->cartMain;

        $model = Stock::findOne($id);

        $getWarehouse = \Yii::$app->session->get('selectMainWarehouse');
        if (!$getWarehouse) {
            $warehouse = Warehouse::find()->where(['id' => $model->warehouse_id])->One();
            \Yii::$app->session->set('selectMainWarehouse', [
                'warehouse_id' => $warehouse->id,
                'warehouse_name' => $warehouse->warehouse_name,
            ]);
        }
        $cart->create($model, 1);
        $totalCount = $cart->getCount();

        return [
            'status' => 'success',
            'container' => '#inventory-container',
            'totalCount' => $totalCount,
        ];
    }

    public function actionUpdateCart()
    {
        $cart = \Yii::$app->cartMain;
        $id = $this->request->get('id');
        $quantity = $this->request->get('quantity');
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Stock::findOne($id);
        $checkStock = Stock::findOne($id);
        $cart->update($model, $quantity);

        return [
            'container' => '#inventory-container',
            'status' => 'success',
        ];
    }

    public function actionDeleteItem($id)
    {
        $cart = \Yii::$app->cartMain;
        $product = Stock::findOne($id);
        if ($product) {
            $cart->delete($product);
            if ($cart->getCount() < 1) {
                \Yii::$app->session->remove('selectMainWarehouse');
            }
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'container' => '#inventory-container',
                'status' => 'success',
                'countItem' => $cart->getCount(),
            ];
        }
    }

    public function actionClearWarehouse()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        \Yii::$app->session->remove('selectMainWarehouse');
        $cart = \Yii::$app->cartMain;

        return $this->redirect(['/inventory/main-stock']);
    }
}
