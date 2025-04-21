<?php

namespace app\modules\me\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use app\components\LineMsg;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\sm\models\Product;
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
        $warehouse = \Yii::$app->session->get('sub-warehouse');

        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        // $dataProvider->query->andwhere(['name' => 'order','transaction_type' => 'IN', 'warehouse_id' => $warehouse['warehouse_id']]);
        $dataProvider->query->andwhere(['name' => 'order', 'transaction_type' => 'OUT', 'from_warehouse_id' => $warehouse->id]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $submWarehouse = \Yii::$app->session->get('sub-warehouse');
        $mainWarehouse = \Yii::$app->session->get('main-warehouse');
        $assetType = \Yii::$app->session->get('asset_type');

        // หากหม่มีการเลือกคลัง .ให้ redirec ไปที่ Dashbroad
        // if (!isset($toWarehouse['warehouse_id']) && !isset($formWarehouse->id)) {
        // return $this->redirec(['/me']);
        // }

        $cart = \Yii::$app->cartMain;
        $userCreate = UserHelper::GetEmployee();
        $name = $this->request->get('name');
        $order_id = $this->request->get('order_id');
        $type = $this->request->get('type');

        $model = new StockEvent([
            'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
            'checker' => $userCreate->leaderUser()['leader1'],
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;

                try {
                    $transaction = \Yii::$app->db->beginTransaction();
                    // $transaction->commit();
                    // สร้างรหัสขอเบิก
                    $model->code = \mdm\autonumber\AutoNumber::generate('REQ-' . substr(AppHelper::YearBudget(), 2) . '????');
                    $model->name = 'order';
                    $model->transaction_type = 'OUT';

                    $model->thai_year = AppHelper::YearBudget();
                    $model->order_status = 'pending';
                    $model->warehouse_id = $mainWarehouse->id;
                    $model->from_warehouse_id = $submWarehouse->id;

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
                            // 'lot_number' => $item->lot_number,
                            // 'unit_price' => $item->unit_price,
                            'qty' => 0,
                            'qty' => $item->getQuantity(), //ระบุจำนวนจริงตาม lot ที่เหลือ
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
                    // ถ้าไม่มีข้อผิดพลาด ทำการ commit
                    $transaction->commit();

                    // สร้างการอนุมัติ new feture
                    $approve = new Approve;
                    $approve->from_id = $model->id;
                    $approve->level = 1;
                    $approve->name = 'main_stock';
                    $approve->emp_id = $model->checker;
                    $approve->status = 'Pending';
                    $approve->title = 'อนุมัติ';
                    $approve->data_json = ['label' => 'อนุมัติ'];
                    $approve->save(false);
                    try {
                        //   ส่ง massage
                        $userId = $model->empChecker->user->line_id;
                        $message = 'ขออนุมัติเบิกวัสดุ';
                        LineMsg::sendMsg($userId, $message);
                    } catch (\Throwable $th) {
                        // throw $th;
                    }

                    \Yii::$app->session->remove('main-warehouse');
                    \Yii::$app->session->remove('asset_type');
                    return $this->redirect(['/me/main-stock/store']);
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

    // ตรวจสอบความถูกต้อง
    public function actionCreateValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new StockEvent();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->checker == '' ? $model->addError('checker', $requiredName) : null;
            if (isset($model->data_json['note'])) {
                $model->data_json['note'] == '' ? $model->addError('data_json[note]', $requiredName) : null;
            }
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
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
        $mainWarehouse = \Yii::$app->session->get('main-warehouse');
        $warehouse = Yii::$app->session->get('sub-warehouse');
        $warehouseModel = \app\modules\inventory\models\Warehouse::findOne($warehouse->id);
        $item = $warehouseModel->data_json['item_type'];
        
        $product = ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type', 'category_id' => 4])->andWhere(['IN', 'code', $item])->all(), 'code', 'title');

        $searchModel = new StockSearch([
            'warehouse_id' => isset($mainWarehouse->id) ? $mainWarehouse->id : '',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        // $dataProvider->query->leftJoin('categorise', 'categorise.code = stock.asset_item AND categorise.name = :name', [':name' => 'asset_item'])
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        $dataProvider->query->andWhere(['IN', 'p.category_id', $item]);
        $dataProvider->query->andFilterWhere(['warehouse_id' => ($mainWarehouse ? $mainWarehouse->id : $searchModel->warehouse_id)]);
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
        $mainWarehouse = \Yii::$app->session->get('main-warehouse');
        $searchModel = new StockSearch([
            'warehouse_id' => isset($mainWarehouse['warehouse_id']) ? $mainWarehouse['warehouse_id'] : '',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        $dataProvider->query->andFilterWhere(['warehouse_id' => ($mainWarehouse ? $mainWarehouse['warehouse_id'] : $searchModel->warehouse_id)]);
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
        
        if ($cart->getCount() < 1) {
            \Yii::$app->session->remove('main-warehouse');
            \Yii::$app->session->remove('asset_type');
        }

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

        $mainWarehouse = \Yii::$app->session->get('main-warehouse');
        if (!$mainWarehouse) {
            $mainWarehouse = Warehouse::find()->where(['id' => $model->warehouse_id])->One();
            \Yii::$app->session->set('main-warehouse', $mainWarehouse);
            \Yii::$app->session->set('asset_type',$model->product->productType);
        }
        $cart->create($model, 1);
        $totalCount = $cart->getCount();

        return [
            'status' => 'success',
            'container' => '#inventory-container',
            'totalCount' => $totalCount,
            'model' => $model,
            'mainWarehouse' => Yii::$app->session->get('main-warehouse')
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
            'totalCount' => $cart->getCount(),
        ];
    }

    public function actionDeleteItem($id)
    {
        $cart = \Yii::$app->cartMain;
        $product = Stock::findOne($id);
        if ($product) {
            $cart->delete($product);
            if ($cart->getCount() < 1) {
                \Yii::$app->session->remove('main-warehouse');
                \Yii::$app->session->remove('asset_type');
            }
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'container' => '#inventory-container',
                'status' => 'success',
                'totalCount' => $cart->getCount(),
            ];
        }
    }

    public function actionClearWarehouse()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        \Yii::$app->session->remove('main-warehouse');
        \Yii::$app->session->remove('asset_type');
        $cart = \Yii::$app->cartMain;

        return $this->redirect(['/inventory/main-stock']);
    }
}
