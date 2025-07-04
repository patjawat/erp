<?php

namespace app\modules\inventory\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\sm\models\Product;
use yii\web\NotFoundHttpException;
use app\modules\approve\models\Approve;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockOut;
use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockEventSearch;

/**
 * StockOutController implements the CRUD actions for StockOut model.
 */
class StockOrderController extends Controller
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all StockOut models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $warehouse = \Yii::$app->session->get('warehouse');
        if (!$warehouse) {
            return $this->redirect(['/inventory']);
        }

        $searchModel = new StockEventSearch([
            // 'warehouse_id' => $warehouse['warehouse_id'],
            'from_warehouse_id' => 8,
            // 'transaction_type' => 'OUT'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', new Expression("JSON_EXTRACT(data_json, '\$.asset_type_name')"), $searchModel->asset_type_name]);
        $dataProvider->query->andFilterWhere(['name' => 'order']);
        // $dataProvider->query->andFilterWhere(['transaction_type' => $searchModel->transaction_type]);
        $dataProvider->query->andFilterWhere(['transaction_type' => 'OUT']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'code', $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '\$.vendor_name')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '\$.pq_number')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '\$.po_number')"), $searchModel->q],
        ]);
        // $dataProvider->query->andwhere(['name' => 'order', 'transaction_type' => 'OUT', 'from_warehouse_id' => $warehouse['warehouse_id']]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // เลือกคลังที่จะทำงาน
    protected function setWarehouse($id)
    {
        $model = Warehouse::find()->where(['id' => $id])->One();
        \Yii::$app->session->set('warehouse', [
            'id' => $model->id,
            'warehouse_id' => $model->id,
            'warehouse_code' => $model->warehouse_code,
            'warehouse_name' => $model->warehouse_name,
            'warehouse_type' => $model->warehouse_type,
            'category_id' => $model->category_id,
            'checker' => isset($model->data_json['checker']) ? $model->data_json['checker'] : '',
            'checker_name' => isset($model->data_json['checker_name']) ? $model->data_json['checker_name'] : '',
        ]);
        // Yii::$app->session->set('warehouse_name', $model->warehouse_name);
    }

    public function actionRequest()
    {
        $warehouse = \Yii::$app->session->get('warehouse');
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        // $dataProvider->query->andwhere(['name' => 'order','transaction_type' => 'IN', 'warehouse_id' => $warehouse['warehouse_id']]);
        $dataProvider->query->andwhere(['name' => 'order', 'transaction_type' => 'OUT', 'from_warehouse_id' => $warehouse['warehouse_id']]);
        $dataProvider->query->andwhere(['<>', 'warehouse_id', $warehouse['warehouse_id']]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single StockOut model.
     *
     * @param int $id ID
     *
     * @return string
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // \Yii::$app->response->format = Response::FORMAT_JSON;
        // return $model;
        $warehouse = Yii::$app->session->get('warehouse');
        $model = StockEvent::findOne($id);
        if (!$warehouse) {
            $this->setWarehouse($model->warehouse_id);
        }
        if ($model) {
            // $this->checkUpdateQty($id);
            return $this->render('view', [
                'model' => $model,
            ]);
        } else {
            return $this->redirect(['/inventory/stock-order']);
        }
    }

    protected function checkUpdateQty($id)
    {
        $model = StockEvent::findOne($id);

        foreach ($model->getItems() as $item) {
            if ($item->qty > $item->SumlotQty()) {
                $item->qty = $item->SumlotQty();
                $item->save(false);
            }
        }
    }


    public function actionShowOrderItem($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = StockEvent::findOne($id);
        $btnSave = false;
        $checkBalanced = 0;
        //ถ้ายังไม่บันทึกจ่ายและยกเลิกให้ updatelot ที่จะจ่าย
        // if(!in_array($model->order_status, ['success','cancel'])){

        $data = [];
        foreach ($model->getItems() as $stockItem) {
            $checkStock = Stock::find()->andWhere(['warehouse_id' => $stockItem->warehouse_id, 'asset_item' => $stockItem->asset_item])->andWhere(['>', 'qty', 0])->one();
            if ($checkStock) {
                if (!isset($stockItem->data_json['copy'])) {
                    $stockItem->lot_number = $checkStock->lot_number;
                    $stockItem->unit_price = $checkStock->unit_price;
                }

                $stockItem->save();
            } else {
                $stockItem->order_status = 'cancel';
                $stockItem->qty = 0;
                $stockItem->save();
            }
        }


        // }

        // if($model->checkBalance()  == 0 && $stockItem->qty ==0 && !in_array($model->order_status, ['success','cancel']) && $model->countNullQty() == 0){
        // if($model->checkBalance()  == 0 && $stockItem->qty ==0 && !in_array($model->order_status, ['success','cancel']) && $model->countNullQty() == 0){
        //     $btnSave = true;
        // }else{
        //     $btnSave = false;

        // }


        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('show_order_items', [
                'model' => $model,
            ]),
            'balance' => $model->checkBalance(),
            'sumPrice' => number_format($model->getTotalOrderPrice(), 2),
        ];
    }
    public function actionViewCode($id)
    {
        $model = StockEvent::findOne(['code' => $id, 'name' => 'order']);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new StockOut model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $formWarehouse = \Yii::$app->session->get('warehouse');
        $toWarehouse = \Yii::$app->session->get('select-warehouse');
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
                // สร้างรหัสรับเข้า
                if ($model->name == 'order') {
                    $model->code = \mdm\autonumber\AutoNumber::generate('REQ-' . substr(AppHelper::YearBudget(), 2) . '????');
                }

                $model->thai_year = AppHelper::YearBudget();
                $model->order_status = 'pending';
                $model->warehouse_id = $toWarehouse['warehouse_id'];
                $model->from_warehouse_id = $formWarehouse['warehouse_id'];

                if ($model->save(false)) {
                    if ($model->name == 'order') {
                        $this->saveCartItem($model);
                        \Yii::$app->cart->checkOut(false);

                        return $this->redirect(['/inventory/stock-order/request']);
                    } else {
                        \Yii::$app->response->format = Response::FORMAT_JSON;

                        return [
                            'status' => 'success',
                            'container' => '#inventory-container',
                        ];
                    }
                } else {
                    $model->loadDefaultValues();
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
        $cart = \Yii::$app->cart;

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
                'qty' => $item->getQuantity(),
                'data_json' => [
                    'req_qty' => $item->getQuantity(),
                ],
                'order_status' => 'pending',
            ]);
            $item->save(false);
        }

        return true;
    }

    // ตรวจสอบความถูกต้อง
    public function actionRecipientValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new StockEvent();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            if (isset($model->data_json['recipient_date'])) {
                preg_replace('/\D/', '', $model->data_json['recipient_date']) == '' ? $model->addError('data_json[recipient_date]', 'ลงวันที่ต้องระบุ') : null;
            }
            if (isset($model->data_json['recipient_time'])) {
                preg_replace('/\D/', '', $model->data_json['recipient_time']) == '' ? $model->addError('data_json[recipient_time]', 'เวลาต้องระบุ') : null;
            }

            if (isset($model->data_json['recipient'])) {
                $model->data_json['recipient'] == '' ? $model->addError('data_json[recipient]', $requiredName) : null;
            }
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    // ผู้รับวัสดุ
    public function actionRecipient($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $oldObj = $model->data_json;
        if ($this->request->isPost && $model->load($this->request->post())) {
            $newObj = [
                'recipient_date' => AppHelper::convertToGregorian($model->data_json['recipient_date']),
            ];

            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json, $newObj);
            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
            $model->save(false);

            return [
                'status' => 'success',
                'container' => '#inventory-container',
                'data' => $model,
            ];
        } else {
            $model->loadDefaultValues();

            $oldObj = $model->data_json;
            $model->data_json = [
                'recipient_date' => AppHelper::convertToThai(isset($model->data_json['recipient_date']) ? $model->data_json['recipient_date'] : date('Y-m-d')),
            ];
            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
        }

        if ($this->request->isAJax) {
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_recipient', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form_recipient', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing StockOut model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id ID
     *
     * @return string|Response
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldObj = $model->data_json;
        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
            $model->save(false);

            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionCancelOrder($id)
    {
        $model = $this->findModel($id);
        $model->order_status = 'cancel';
        $oldObj = $model->data_json;
        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
            if ($model->save(false)) {
                StockEvent::updateAll(['order_status' => 'cancel'], ['name' => 'order_item', 'category_id' => $model->id]);
                return $this->redirect(['/inventory/warehouse/order-request']);
            }
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_cancel', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form_cancel', [
                'model' => $model,
            ]);
        }
    }


    public function actionUpdateQty($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $qty = $this->request->get('qty');
        $model = StockEvent::findOne($id);
        $checkStock = Stock::findOne(['lot_number' => $model->lot_number, 'warehouse_id' => $model->warehouse_id]);
        if ($qty <= -1) {
            return [
                'status' => 'error',
                'container' => '#inventory-container',
                'code' => '$qty <= -1',
            ];
        }

        $model->qty = $qty;
        $model->save(false);
        \Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'status' => 'success',
            'container' => '#inventory-container',
            'data' => $model,
        ];
    }

    public function actionUpdateLot($id)
    {
        $model = StockEvent::findOne($id);
        $model->qty = $model->data_json['req_qty'];
        if ($this->request->isPost && $model->load($this->request->post())) {
            $lot = Stock::findOne(['lot_number' => $model->lot_number]);
            $model->unit_price = $lot->unit_price;
            $model->total_price = ($lot->unit_price * $model->qty);
            $model->save(false);

            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'status' => 'success',
                'container' => '#inventory-container',
            ];
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $model->product->Avatar(),
                'content' => $this->renderAjax('_form_update_lot', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form_update_lot', [
                'model' => $model,
            ]);
        }
    }

    // ตรวจสอบความถูกต้อง
    public function actionUpdateLotValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new StockEvent();

        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            $checkStock = Stock::findOne(['lot_number' => $model->lot_number]);

            if (isset($model->lot_number)) {
                $model->lot_number == '' ? $model->addError('lot_number', $requiredName) : null;
            }

            $model->qty == '' ? $model->addError('qty', $requiredName) : null;
            if ($model->qty > $checkStock->qty) {
                $model->addError('qty', 'วัสดุไม่พอจ่าย');
            }
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    // ตรวจสอบความถูกต้อง
    public function actionCancelOrderValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new StockEvent();

        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            if (isset($model->data_json['cancel_note'])) {
                $model->data_json['cancel_note'] == '' ? $model->addError('data_json[cancel_note]', $requiredName) : null;
            }
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    public function actionSaveOrder($id)
    {
        $model = StockEvent::findOne($id);
        $model->order_status = 'pending';
        $model->save(false);

        foreach ($model->getItems() as $item) {
            $item->order_status = 'pending';
            $item->save(false);
        }

        return $this->redirect(['/inventory/stock-order']);
    }

    public function actionCheckOut($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = StockEvent::findOne($id);

        // ตรวจสอบว่า stock พอจ่ายหรือไม่
        $checkBalanced = 0;
        foreach ($model->getItems() as $item) {
            $item->qty =  (is_numeric($item->SumStockQty()) && $item->SumStockQty() > 0) ? max(0, (int)$item->qty) : 0;
            $item->movement_date = Date('Y-m-d H:i:s');
            $item->save();
            if ($item->SumStockQty() <= 0 && $item->qty > 0) {
                ++$checkBalanced;
            }

            if ($item->qty > $item->SumStockQty()) {
                ++$checkBalanced;
            }
        }

        // ถ้ามีรายการที่ไม่พอจ่าย
        // if ($checkBalanced >= 1) {
        //     return [
        //         'status' => 'error',
        //         'message' => 'ไม่พอจ่าย',
        //         'container' => '#inventory-container',
        //     ];
        // }
        // ตรวจสอบความเรียบร้อยก่อนบันทึก
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // บันทึกข้อมูล Order
            $model = StockEvent::findOne($id);
            $userCreate = UserHelper::GetEmployee();
            $jsonDate = ['player' => $userCreate->id, 'player_date' => date('Y-m-d H:i:s'), 'receive_date' => date('Y-m-d'), 'user_req' => $model->created_by];
            $model->order_status = 'success';
            $oldObj = $model->data_json;
            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json, $jsonDate);

            // ถ้าหากผิดพลาด
            if (!$model->save(false)) {
                throw new \Exception('ไม่สามารถบันทึกข้อมูล Order ได้');
            }

            // สร้างรายการคำสั่งเข้าเข้าคลังที่ขอเบิก
            $newStockModel = new StockEvent();
            $newStockModel->name = 'order';
            $newStockModel->thai_year = AppHelper::YearBudget();
            $newStockModel->order_status = 'success';
            $newStockModel->code = \mdm\autonumber\AutoNumber::generate('IN-' . substr(AppHelper::YearBudget(), 2) . '????');
            $newStockModel->from_warehouse_id = $model->warehouse_id;
            $newStockModel->warehouse_id = $model->from_warehouse_id;
            $newStockModel->transaction_type = 'IN';
            $newStockModel->category_id = $model->code;
            $newStockModel->data_json = $jsonDate;

            // ถ้าหากผิดพลาด
            if (!$newStockModel->save(false)) {
                throw new \Exception('ไม่สามารถบันทึกข้อมูล Order ได้');
            }

            foreach ($model->getItems() as $item) {

                $logQty = [
                    'balance' => $item->SumStockQty(),
                    'balance_lot_number' => $item->lot_number,
                ];
                $oriJson = $item->data_json;
                $item->data_json = ArrayHelper::merge($oriJson,  $logQty);
                $item->movement_date = Date('Y-m-d H:i:s');
                $item->save();


                $checkStock = Stock::findOne(['id' => $item->id, 'lot_number' => $item->lot_number]);

                // if ($item->SumStockQty() != 0 && $item->qty > 0) {
                $item->order_status = 'success';
                if ($item->qty > 0) {

                    $newStockItem = new StockEvent();
                    $newStockItem->order_status = 'success';
                    $newStockItem->thai_year = AppHelper::YearBudget();
                    $newStockItem->name = 'order_item';
                    $newStockItem->code = $newStockModel->code;
                    $newStockItem->asset_item = $item->asset_item;
                    $newStockItem->lot_number = $item->lot_number;
                    // $newStockItem->qty = ($item->SumStockQty() > 0) ? $item->qty : 0;
                    $newStockItem->qty = (is_numeric($item->SumStockQty()) && $item->SumStockQty() > 0) ? max(0, (int)$item->qty) : 0;
                    $newStockItem->unit_price = $item->unit_price;
                    $newStockItem->transaction_type = 'IN';
                    $newStockItem->warehouse_id = $model->from_warehouse_id;
                    $newStockItem->category_id = $newStockModel->id;
                    $newStockItem->data_json = $item->data_json;
                    if (!$newStockItem->save(false)) {
                        throw new \Exception('ไม่สามารถบันทึกข้อมูล OrderItem ได้');
                    }
                }
                // } else {
                //     $item->order_status = 'cancel';
                // }

                // ถ้าหากผิดพลาด
                if (!$item->save(false)) {
                    throw new \Exception('ไม่สามารถบันทึกข้อมูล OrderItem ได้');
                }

                // //     // UpdateNewStock ที่ขอเบิก
                // if ($item->SumStockQty() != '0') {
                if ((int)$item->SumStockQty() !== 0) {
                    $checkToStock = Stock::findOne(['asset_item' => $item->asset_item, 'warehouse_id' => $model->from_warehouse_id, 'lot_number' => $item->lot_number]);
                    if ($checkToStock) {
                        $toStock = $checkToStock;
                    } else {
                        $toStock = new Stock();
                    }
                    $toStock->asset_item = $item->asset_item;
                    $toStock->thai_year = AppHelper::YearBudget();
                    $toStock->lot_number = $item->lot_number;
                    $toStock->warehouse_id = $model->from_warehouse_id;
                    $toStock->unit_price = $item->unit_price;
                    $toStock->qty = $toStock->qty + $item->qty;

                    // ถ้าหากผิดพลาด
                    if (!$toStock->save(false)) {
                        throw new \Exception('ไม่สามารถ UpdateNewStock ที่ขอเบิก ได้');
                    }

                    // ตัด stock และทำการ update
                    // $checkStock = Stock::findOne(['asset_item' => $item->asset_item, 'lot_number' => $item->lot_number, 'warehouse_id' => $model->warehouse_id]);
                    // $checkStock->qty = $checkStock->qty - $item->qty;
                    // // ถ้าหากผิดพลาด
                    // if (!$checkStock->save(false)) {
                    //     throw new \Exception('ไม่สามารถตัด stock และทำการ update ได้');
                    // }

                    $checkStock = Stock::findOne([
                        'asset_item' => $item->asset_item,
                        'lot_number' => $item->lot_number,
                        'warehouse_id' => $model->warehouse_id
                    ]);

                    if ($checkStock) {
                        $checkStock->qty = max(0, $checkStock->qty - $item->qty);
                        if (!$checkStock->save(false)) {
                            throw new \Exception('ไม่สามารถตัด stock และทำการ update ได้');
                        }
                    } else {
                        throw new \Exception('ไม่พบ stock ที่ต้องการตัด');
                    }
                }
            }

            // ถ้าไม่มีข้อผิดพลาด ทำการ commit
            $transaction->commit();


            // return ['status' => 'success', 'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว'];
            return $this->redirect(['/inventory/warehouse']);
        } catch (\Exception $e) {
            $transaction->rollBack();

            // ถ้ามีข้อผิดพลาด ทำการ rollback
            return [
                'status' => 'error',
                'container' => '#inventory-container',
                'message' => $e->getMessage()
            ];
        }
        // จบ
    }

    public function actionAddNewItem($id)
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;
        $order = $this->findModel($id);
        $model = new StockEvent();

        if ($this->request->isPost && $model->load($this->request->post())) {
            $item = Stock::findOne(['lot_number' => $model->lot_number]);
            $model->category_id = $order->id;
            $model->thai_year = AppHelper::YearBudget();
            $model->name = 'order_item';
            $model->asset_item = $item->asset_item;
            $model->unit_price = $item->unit_price;
            $model->transaction_type = 'OUT';
            $model->warehouse_id = $order->warehouse_id;

            \Yii::$app->response->format = Response::FORMAT_JSON;
            $checkStock = Stock::findOne(['lot_number' => $model->lot_number]);

            if ($model->qty > $checkStock->qty) {
                return [
                    'status' => 'error',
                ];
            }

            $model->save(false);

            return [
                'status' => 'success',
                'container' => '#inventory-container',
            ];
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => 'เพิ่มรายการใหม่',
                'content' => $this->renderAjax('_add_new_item', [
                    'model' => $model,
                    'order' => $order,
                ]),
            ];
        } else {
            return $this->render('_add_new_item', [
                'model' => $model,
                'order' => $order,
            ]);
        }
    }

    // update รายการ Items ที่บันทึกใหม่ให้เป็น success
    public function actionConfirmOrder($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        // $model = StockOut::updateAll(['order_status' => 'success'], ['category_id' => $id]);
        return $this->updateStock($id);

        return $this->redirect(['/inventory/stock-out']);

        if ($model) {
            return [
                'status' => 'success',
                'container' => '#inventory-container',
            ];
        }
    }

    protected function updateStock($id)
    {
        $order = $this->findModel($id);
        // return $order->listItems();
        foreach ($order->listItems() as $item) {
            $model = StockEvent::findOne(['lot_number' => $item->lot_number]);
            $model->asset_item = $item->asset_item;
            $model->qty = $model->qty - $item->qty;
            $model->warehouse_id = $item->warehouse_id;
            $model->save(false);

            $stock = Stock::findOne(['asset_item' => $model->asset_item]);
            $stock->qty = $stock->qty - $item->qty;
            $stock->save(false);
        }
    }

    public function actionCopyItem()
    {
        $id = $this->request->get('id');
        $lot_number = $this->request->get('lot_number');
        \Yii::$app->response->format = Response::FORMAT_JSON;


        // $order = StockEvent::findOne($id);
        $order = StockEvent::findOne($id);
        $lastLotNumber = Stock::find()
            ->where(['>', 'lot_number', $order->lot_number])
            ->andWhere(['warehouse_id' => $order->warehouse_id, 'asset_item' => $order->asset_item])
            ->orderBy(['lot_number' => SORT_ASC])
            ->one();

        if ($lastLotNumber) {
            $qty = ($order->data_json['req_qty'] - $order->SumlotQty());
            $model = new StockEvent([
                'name' => 'order_item',
                'category_id' => $order->category_id,
                'transaction_type' => 'OUT',
                'lot_number' => $lastLotNumber->lot_number,
                'warehouse_id' => $order->warehouse_id,
                'order_status' => 'pending',
                'thai_year' => $order->thai_year,
                'unit_price' => $lastLotNumber->unit_price,
                'asset_item' => $lastLotNumber->asset_item,
                'qty' => $qty,
                'data_json' => [
                    'req_qty' => $qty,
                    'copy' => true
                ],
            ]);

            if ($model->save(false)) {
                return [
                    'status' => 'success',
                    'container' => '#inventory-container',
                ];
            }
        } else {
            return [
                'status' => 'error',
                'massage' => 'เกิดข้อผิดพลาด',
                'container' => '#inventory-container',
            ];
        }
    }


    public function actionShowStock()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $asset_item = $this->request->get('asset_item');
        $catebory_id = $this->request->get('catebory_id');
        $product = Product::find()->where(['code' => $asset_item])->one();
        return [
            'title' => $product->Avatar(),
            'content' => $this->renderAjax('show_stock', [
                'asset_item' => $asset_item,
                'catebory_id' => $catebory_id
                // 'lot_number' => $lot_number
            ])
        ];
    }


    //เพิ่มสินค้าจาก lot number ที่เลิอก
    public function actionAddToOrder()
    {
        $id = $this->request->get('id');
        $catebory_id = $this->request->get('catebory_id');
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'category_id' => $catebory_id,
            'id' => $id
        ];
    }
    
    // เจ้าหน้าที่คลังอนุมัติเห็นชอบแทนหัวหน้า Level 1
    public function actionApproveFormStore($id)
    {
        $me = UserHelper::GetEmployee();
        $model = Approve::findOne(['id' => $id, 'name' => 'main_stock']);
        if ($this->request->isPost) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $status = $this->request->post('status');
             // ระบบอนุมัติเบิกคลัง
             $old = $model->data_json;
             $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
             $model->data_json = ArrayHelper::merge($old, $model->data_json, $approveDate);
             $model->status = $status;
              $model->emp_id = $me->id;
             //ถ้าบันทุกเรียบร้อย
             if($model->save(false))
             {
                // update ส่วน stock
                $oldStockObj = $model->stock->data_json;
                $checkData = $model->stock->empChecker;
                $checkerData = [
                    'checker_confirm_date' => date('Y-m-d H:i:s'),
                    'checker_name' => $checkData->fullname,
                    'checker_position' => $checkData->positionName(),
                    'checker_confirm' => ($model->status == 'Pass' ? 'Y' : 'N')
                ];
                
                if ($model->status == 'Pass') {
                    $model->stock->order_status = 'pending';
                }

                if ($model->status == 'Reject') {
                    $model->stock->order_status = 'cancel';
                }
                $model->stock->data_json = ArrayHelper::merge($oldStockObj, $model->stock->data_json, $checkerData);
               
                $model->stock->save(false);

                }
                
                return [
                    'status' => 'success'
                ];
                
        }
        
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-circle-check"></i> อนุมัติเห็นชอบแทนหัวหน้า',
                'content' => $this->renderAjax('_approve_form_store', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_approve_form_store', [
                'model' => $model,
            ]);
        }

    }
    /**
     * Deletes an existing StockOut model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id ID
     *
     * @return Response
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $container = $this->request->get('container');
        $url = $this->request->get('url');
        $model = $this->findModel($id);
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model->delete();

        return $this->redirect(['/inventory/stock-order/view', 'id' => $model->category_id]);
        return [
            'status' => 'success',
            'container' => '#inventory-container',
            'url' => $url,
        ];
    }

    /**
     * Finds the StockOut model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id ID
     *
     * @return StockOut the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StockEvent::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
