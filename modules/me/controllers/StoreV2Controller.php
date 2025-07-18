<?php

namespace app\modules\me\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use yii\db\Expression;
use app\components\LineMsg;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use mdm\autonumber\AutoNumber;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\modules\approve\models\Approve;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\Product;
use app\modules\inventory\models\StockOut;
use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockSearch;
use app\modules\inventory\models\WarehouseSearch;
use app\modules\inventory\models\StockEventSearch;

class StoreV2Controller extends \yii\web\Controller
{
    public function actionSetWarehouse()
    {
        $callbackUrl = Yii::$app->request->get('callback');
        try {
            $emp = UserHelper::GetEmployee();
            $checkWarehouse = Warehouse::find()->andWhere(['warehouse_type' => 'SUB'])->andWhere(['>', new Expression('FIND_IN_SET(' . $emp->department . ', department)'), 0])->one();
            $warehouse = Warehouse::findOne($checkWarehouse->id);
            if (!$checkWarehouse) {
                return $this->render('not_set_warehouse', [
                    'message' => 'ไม่พบการตั้งค่า กำหนดหน่วยงานเบิก ในคลังย่อย',
                ]);
            }
            Yii::$app->session->set('sub-warehouse', $warehouse);
        } catch (\Throwable $th) {
            return $this->render('not_set_warehouse', [
                'message' => 'ไม่พบการตั้งค่า กำหนดหน่วยงานเบิก ในคลังย่อย',
            ]);
        }
        if ($callbackUrl) {
            // return $this->redirect(['/me/main-stock/store']);
            return $this->redirect([$callbackUrl]);
        }else{
            return $this->redirect(['/me/store-v2/index']);
        }
    }

    public function actionIndex()
    {
        $warehouse = Yii::$app->session->get('sub-warehouse');
        if (!$warehouse) {
            return $this->redirect(['/me/store-v2/set-warehouse']);
        }

        $emp = UserHelper::GetEmployee();
        $checkWarehouse = Warehouse::find()->andWhere(['warehouse_type' => 'SUB'])->andWhere(['>', new Expression('FIND_IN_SET(' . $emp->department . ', department)'), 0])->one();

        $item = $warehouse->data_json['item_type'];
        $searchModel = new StockSearch([
            'warehouse_id' => $warehouse->id
        ]);

        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        $dataProvider->query->leftJoin('warehouses w', 'w.id=stock.warehouse_id');
        $dataProvider->query->andWhere(['IN', 'p.category_id', $item]);
        $dataProvider->query->andFilterWhere(['p.category_id' => $searchModel->asset_type]);
        $dataProvider->query->andFilterWhere(['w.id' => $warehouse->id]);
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

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        // } catch (\Throwable $th) {
        //     // return $this->redirect(['/me/store-v2/select-warehouse']);
        // }
    }

    public function actionDashboard()
    {
        $warehouse = Yii::$app->session->get('sub-warehouse');
        if (!$warehouse) {
            return $this->redirect(['/me/store-v2/set-warehouse','callback' => '/me/store-v2/dashboard']);
        }
        $id = \Yii::$app->user->id;
        $searchModel = new WarehouseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['delete' => null]);

        if (!\Yii::$app->user->can('admin')) {
            $dataProvider->query->andWhere(new Expression("JSON_CONTAINS(data_json->'\$.officer','\"$id\"')"));
        } else {
        }

        // หากเลือกคลังแล้วให้แสดง ในคลัง
        if ($warehouse) {
            $searchModel = new StockEventSearch([
                'thai_year' => AppHelper::YearBudget(),
                'warehouse_id' => $warehouse->id
            ]);
            $dataProvider = $searchModel->search($this->request->queryParams);
            $dataProvider->query->andwhere(['name' => 'order', 'transaction_type' => 'OUT', 'warehouse_id' => $warehouse->id]);
            $dataProvider->query->andFilterWhere([
                'or',
                ['like', 'code', $searchModel->q],
                ['like', 'thai_year', $searchModel->q],
                ['like', new Expression("JSON_EXTRACT(data_json, '\$.vendor_name')"), $searchModel->q],
            ]);

            return $this->render('dashboard', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                // 'model' => $this->findModel($warehouse['warehouse_id']),
            ]);
        }
    }

    // แสดงรายการขอเบิกวัสดุคลังหลัก
    public function actionOrderIn()
    {
        // ถ้ามีการเลือกคลังสินค้าแล้ว ให้เก็บค่าไว้ใน session
        if ($order = Yii::$app->session->get('order')) {
            Yii::$app->session->remove('order');
        }

        $id = \Yii::$app->user->id;
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('warehouses tw', 'tw.id=stock_events.warehouse_id');
        $dataProvider->query->leftJoin('warehouses fw', 'fw.id=stock_events.from_warehouse_id');
        // $dataProvider->query->andFilterWhere(new Expression("JSON_CONTAINS(w.data_json->'$.officer','\"$id\"')"));
        $dataProvider->query->andWhere(new Expression("JSON_CONTAINS(fw.data_json->'\$.officer','\"$id\"')"));
        // $dataProvider->query->andWhere(['>', new Expression('FIND_IN_SET('.$emp->department.', department)'), 0]);

        $dataProvider->query->andFilterWhere([
            'stock_events.name' => 'order',
            'transaction_type' => 'OUT',
            // 'stock_events.created_by' => Yii::$app->user->id
        ]);
        // $dataProvider->query->andWhere(['warehouse_type' => 'SUB']);

        return $this->render('order_in', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionOrderOut()
    {
        $warehouse = Yii::$app->session->get('warehouse');

        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('warehouses w', 'w.id=stock_events.warehouse_id');
        $dataProvider->query->andFilterWhere([
            'w.warehouse_type' => 'SUB',
            'stock_events.name' => 'order',
            'transaction_type' => 'OUT',
            'warehouse_id' => $warehouse->id,
            // 'stock_events.created_by' => Yii::$app->user->id
        ]);

        return $this->render('order_out', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = StockEvent::findOne($id);
        Yii::$app->session->set('order', $model);

        return $this->render('view', ['model' => $model]);
    }

    public function actionShow()
    {
        $warehouse = Yii::$app->session->get('warehouse_id');
        return $this->render('show');
    }

    // แสดงรายการขอเบิกวัสดุคลังหลัก
    // public function actionMainStore()
    // {
    //     $warehouse = Yii::$app->session->get('warehouse_id');
    //     return $this->render('main_store');
    // }

    public function actionCreateOrder()
    {
        $userCreate = UserHelper::GetEmployee();
        $model = new StockEvent([
            'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
            'transaction_type' => 'OUT',
            'checker' => $userCreate->leaderUser()['leader1'],
        ]);

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->code = \mdm\autonumber\AutoNumber::generate('REQ-' . substr(AppHelper::YearBudget(), 2) . '????');
            $model->name = 'order';
            $model->order_status = 'none';
            $model->thai_year = AppHelper::YearBudget();
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('_form_create_order', [
                'model' => $model
            ])
        ];
    }

    public function actionStore()
    {
        $order = Yii::$app->session->get('order');

        $warehouse = $order->warehouse_id;
        $warehouseModel = \app\modules\inventory\models\Warehouse::findOne($warehouse);
        $item = $warehouseModel->data_json['item_type'];
        $searchModel = new StockSearch([
            'warehouse_id' => $warehouse
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        $dataProvider->query->andWhere(['IN', 'p.category_id', $item]);
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
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('store', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('store', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    public function actionAddCard($id)
    {
        if ($this->request->isPost) {
            $order = Yii::$app->session->get('order');
            Yii::$app->response->format = Response::FORMAT_JSON;
            $product = Stock::findOne($id);

            $checkCartOrder = StockEvent::find()->where(['asset_item' => $product->asset_item])->andWhere(['category_id' => $order->id, 'lot_number' => $product->lot_number])->one();
            if (!$checkCartOrder) {
                $model = new StockEvent([
                    'asset_item' => $product->asset_item,
                    'name' => 'order_item',
                    'thai_year' => AppHelper::YearBudget(),
                    'transaction_type' => $order->transaction_type,
                    'code' => $order->code,
                    'category_id' => $order->id,
                    'warehouse_id' => $order->warehouse_id,
                    'from_warehouse_id' => $order->from_warehouse_id,
                    'lot_number' => $product->lot_number,
                    'unit_price' => $product->unit_price,
                    'qty' => 1,
                    'data_json' => [
                        'req_qty' => 1,
                    ],
                    'order_status' => 'pending',
                ]);
                $model->save();
            } else {
                $checkCartOrder->qty = $checkCartOrder->qty + 1;
                $checkCartOrder->save();
            }

            return [
                'status' => 'success',
                'message' => 'บันทึกข้อมูลเรียบร้อย',
            ];
            // $model->code = \mdm\autonumber\AutoNumber::generate('REQ-' . substr(AppHelper::YearBudget(), 2) . '????');
            // $model->save();
            // return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_add_item', [
                    'model' => $model,
                    'item' => $item,
                ])
            ];
        } else {
            return $this->render('_form_add_item', [
                'model' => $model,
                'item' => $item,
            ]);
        }
    }

    public function actionUpdateItem($id)
    {
        $model = StockEvent::findOne($id);
        $item = Stock::findOne($model->asset_item);
        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
                'message' => 'บันทึกข้อมูลเรียบร้อย',
            ];
            return $model;
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_add_item', [
                    'model' => $model,
                    'item' => $item,
                ])
            ];
        } else {
            return $this->render('_form_add_item', [
                'model' => $model,
                'item' => $item,
            ]);
        }
    }

    // update Card
    public function actionUpdateQuantity()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;  // กำหนด response เป็น JSON

        $request = Yii::$app->request;
        if (!$request->isPost) {
            throw new BadRequestHttpException('อนุญาตเฉพาะการร้องขอแบบ POST เท่านั้น');
        }

        $id = $request->post('id');
        $qty = (int) $request->post('qty');

        if (!$id || $qty < 1) {
            throw new BadRequestHttpException('ข้อมูลไม่ถูกต้อง');
        }

        // ค้นหาสินค้าในตะกร้า
        $cartItem = StockEvent::findOne($id);
        if (!$cartItem) {
            throw new NotFoundHttpException('ไม่พบสินค้าในตะกร้า');
        }
        // อัปเดตจำนวนสินค้า
        $oldObj = $cartItem->data_json;
        $cartItem->qty = $qty;
        $newData = [
            'req_qty' =>   $qty,
        ];
        $cartItem->data_json =  ArrayHelper::merge($oldObj, $cartItem->data_json, $newData);


        if ($cartItem->save(false)) {
            return ['status' => 'success', 'message' => 'อัปเดตจำนวนสินค้าสำเร็จ'];
        }

        return ['status' => 'false', 'message' => 'เกิดข้อผิดพลาดในการอัปเดตสินค้า'];
    }

    // ส่งใบเบิกให้หัวหน้าอนุมัติ
    public function actionCheckout($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;  // กำหนด response เป็น JSON
        if ($this->request->isPost) {
            $model = StockEvent::findOne($id);
            $model->order_status = 'pending';
            $model->save();

            // สร้างการอนุมัติ new feture
            $check = Approve::find()->where(['from_id' => $model->id])->andWhere(['name' => 'main_stock', 'level' => 1])->one();
            if (!$check) {
                $approve = new Approve;
                $approve->from_id = $model->id;
                $approve->level = 1;
                $approve->name = 'main_stock';
                $approve->emp_id = $model->checker;
                $approve->status = 'Pending';
                $approve->title = 'อนุมัติ';
                $approve->data_json = ['label' => 'อนุมัติ'];
                $approve->save(false);
                // try {
                // ส่ง massage
                // $userId = $model->empChecker->user->line_id;
                // $message = 'ขออนุมัติเบิกวัสดุ';
                // LineMsg::sendMsg($userId, $message);
                // } catch (\Throwable $th) {
                //     //throw $th;
                // }
            }

            return [
                'status' => 'success',
            ];
        }
    }

    // แก้ไขรายการใบเบิก
    public function actionEdit($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;  // กำหนด response เป็น JSON
        if ($this->request->isPost) {
            $model = StockEvent::findOne($id);
            $model->order_status = 'none';
            $model->save();
            return [
                'status' => 'success',
            ];
        }
    }

    public function actionDeleteItem($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;  // กำหนด response เป็น JSON
        if ($this->request->isPost) {
            $model = StockEvent::findOne($id);
            $model->delete();
            return [
                'status' => 'success',
                'url' => Url::to(['/me/store-v2/view', 'id' => $model->category_id]),
            ];
        }
    }

    public function actionDeleteMultipleItems()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;  // กำหนด response เป็น JSON

        $ids = Yii::$app->request->post('ids');  // รับค่าจาก AJAX

        if (empty($ids)) {
            throw new BadRequestHttpException('ไม่มีสินค้าให้ลบ');
        }

        // ค้นหาและลบสินค้าที่มี ID ตรงกับรายการที่ส่งมา
        $deletedCount = StockEvent::deleteAll(['id' => $ids]);

        if ($deletedCount > 0) {
            return ['success' => true, 'message' => "ลบสินค้า $deletedCount รายการสำเร็จ"];
        } else {
            throw new NotFoundHttpException('ไม่พบสินค้าที่ต้องการลบ');
        }
    }
}
