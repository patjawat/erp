<?php

namespace app\modules\inventory\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use yii\web\NotFoundHttpException;
use app\modules\purchase\models\Order;
use app\modules\inventory\models\Stock;
use app\modules\sm\models\ProductSearch;
use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\StockEvent;
use app\modules\purchase\models\OrderSearch;
use app\modules\inventory\models\StockEventSearch;

/**
 * StockEventController implements the CRUD actions for StockEvent model.
 */
class StockInController extends Controller
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
     * Lists all StockEvent models.
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
            'warehouse_id' => $warehouse->id,
            'transaction_type' => 'IN',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['name' => 'order']);
        $q = isset($searchModel->q) ? trim($searchModel->q) : '';
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'code', $q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.vendor_name')"), $q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.pq_number')"), $q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.po_number')"), $q],
        ]);

        //ค้นหาช่วบงวันที่
        $dataProvider->query
            ->andFilterWhere(['>=', 'movement_date', AppHelper::convertToGregorian($searchModel->date_start)])
            ->andFilterWhere(['<=', 'movement_date', AppHelper::convertToGregorian($searchModel->date_end)]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    // แสดงรายการส่งซื้อที่รอรับเข้าคลัง
    public function actionListPendingOrder()
    {
        $warehouse = \Yii::$app->session->get('warehouse');
        $warehouseModel = Warehouse::findOne($warehouse->id);

        if (isset($warehouseModel->data_json['item_type'])) {
            $item = $warehouseModel->data_json['item_type'];
            $item = array_diff($item, ['M25']);
            $searchModel = new OrderSearch();
            $dataProvider = $searchModel->search($this->request->queryParams);
            $dataProvider->query->andWhere(['name' => 'order']);
            $dataProvider->query->andWhere(['status' => 5]);
            $dataProvider->query->andWhere(['IN', 'category_id', $item]);
            $dataProvider->query->andFilterWhere([
                'or',
                ['like', 'pr_number', $searchModel->q],
                ['like', 'po_number', $searchModel->q],
                ['like', 'pq_number', $searchModel->q],
                ['like', new Expression("JSON_EXTRACT(data_json, '$.vendor_name')"), $searchModel->q],
            ]);
            $dataProvider->query->andFilterWhere(['=', new Expression("JSON_EXTRACT(data_json, '$.order_type_name')"), $searchModel->order_type_name]);

            if ($this->request->isAjax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;

                return [
                    'title' => $this->request->get('title'),
                    'content' => $this->renderAjax('list_pending_order', [
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                    ]),
                ];
            } else {
                return $this->render('list_pending_order', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,

                ]);
            }
        } else {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => 'not content',
            ];
        }
    }

    /**
     * Displays a single StockEvent model.
     *
     * @param int $id รหัสการเคลื่อนไหวสินค้า
     *
     * @return string
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                    'ajax' => true
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
                'ajax' => false
            ]);
        }
    }

    /**
     * Creates a new StockEvent model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $warehouse = \Yii::$app->session->get('warehouse');
        $name = $this->request->get('name');
        $type = $this->request->get('type');
        $order_id = $this->request->get('order_id');
        $asset_item = $this->request->get('asset_item');
        $order = StockEvent::findOne($order_id);

        $model = new StockEvent([
            'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),

            'category_id' => $order_id,
            'code' => $order ? $order->code : '',
            'name' => $name,
            'asset_item' => $asset_item ? $asset_item : '',
            'transaction_type' => $order ? $order->transaction_type : $type,
            'data_json' => [
                'item_type' => ($order->data_json['item_type'] ?? '')
            ],
        ]);
        if ($name == 'order') {
            $model->movement_date = AppHelper::convertToThai(date('Y-m-d'));
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                $user = \Yii::$app->user->identity->employee;
                $created = [
                    'employee_fullname' => $user->fullname,
                    'employee_position' => $user->positionName(),
                    'employee_department' => $user->departmentName(),
                ];
                // สร้างรหัสรับเข้า
                if ($model->name == 'order') {
                    $model->code = \mdm\autonumber\AutoNumber::generate('IN-' . substr(AppHelper::YearBudget(), 2) . '????');
                    $model->data_json = ArrayHelper::merge($model->data_json, $created);
                    $model->movement_date =  AppHelper::convertToGregorian($model->movement_date);
                    $model->thai_year =  AppHelper::YearBudget($model->movement_date);
                }

                if ($model->name == 'order_item') {
                    $movementDate = $order->movement_date;
                    $model->thai_year =  AppHelper::YearBudget($movementDate);
                    $convertDate = [
                        'mfg_date' => $model->data_json['mfg_date'] !== '__/__/____' ? AppHelper::convertToGregorian($model->data_json['mfg_date']) : '',
                        'exp_date' => $model->data_json['exp_date'] !== '__/__/____' ? AppHelper::convertToGregorian($model->data_json['exp_date']) : '',
                        'req_qty' => $model->qty
                    ];
                    $model->data_json = ArrayHelper::merge($model->data_json, $convertDate, $created);

                    if ($model->auto_lot == '1') {
                        $model->lot_number = \mdm\autonumber\AutoNumber::generate('LOT' . substr(AppHelper::YearBudget(), 2) . '-?????');
                    } else {
                    }
                };
                $model->order_status = 'pending';
                $model->warehouse_id = $warehouse->id;

                if ($model->save(false)) {
                    if ($model->name == 'order') {
                        return $this->redirect(['view', 'id' => $model->id]);
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

    // รับเข้าจากการจัดซื้อ
    public function actionCreateByPo($id)
    {
        $order = Order::findOne($id);
        $warehouse = \Yii::$app->session->get('warehouse');
        $model = new StockEvent([
            'name' => 'order',
            'category_id' => $order->id,
            'asset_type_id' => $order->category_id,
            'po_number' => $order->po_number,
            'vendor_id' => $order->vendor_id,
            // 'receive_type' => $this->request->get('receive_type'),
            'warehouse_id' => $warehouse->id,
            'data_json' => [
                'item_type' => 'จัดซื้อ',
                'receive_date' => AppHelper::convertToThai(date('Y-m-d')),
                'po_number' => $order->po_number,
                'do_number' => isset($order->data_json['gr_number']) ? $order->data_json['gr_number'] : '',
                'pq_number' => $order->pq_number,
                'asset_type' => $order->assetType->code,
                'asset_type_name' => $order->assetType->title
            ],
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;

                $model->code = \mdm\autonumber\AutoNumber::generate('IN-' . substr(AppHelper::YearBudget(), 2) . '????');
                $model->transaction_type = 'IN';
                $model->order_status = 'pending';
                $model->movement_date = AppHelper::convertToGregorian($model->movement_date);
                $model->thai_year = AppHelper::YearBudget(AppHelper::convertToGregorian($model->movement_date));

                $model->save(false);
                foreach ($order->ListOrderItems() as $item) {
                    $stockItem = new StockEvent([
                        'code' => $model->code,
                        'thai_year' =>  $model->thai_year,
                        'lot_number' => $model->auto_lot == '1' ? \mdm\autonumber\AutoNumber::generate('LOT' . substr(AppHelper::YearBudget(), 2) . '-?????') : '',
                        'asset_item' => $item->asset_item,
                        'transaction_type' => 'IN',
                        'warehouse_id' => $model->warehouse_id,
                        'category_id' => $model->id,
                        'po_number' => $model->po_number,
                        'name' => 'order_item',
                        'qty' => (float)($item->qty ?? 0),
                        'unit_price' => (float)($item->price ?? 0),
                        'total_price' => ((float)($item->qty ?? 0) * (float)($item->price ?? 0)),
                        'order_status' => 'pending',
                        'data_json' => [
                            'item_type' => 'จัดซื้อ',
                            'order_qty' => $item->qty,

                        ],
                    ]);
                    $stockItem->save(false);
                }
                if ($order) {
                    $order->status = 6; //สถานะส่งวัสดุเข้าคลัง
                    $order->save(false);
                    // ถ้าเป็นการรับจ้าใบสั่่งซื้อ
                }


                return $this->redirect(['view', 'id' => $model->id]);
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

    /**
     * Updates an existing StockEvent model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id รหัสการเคลื่อนไหวสินค้า
     *
     * @return string|Response
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->movement_date = AppHelper::convertToThai($model->movement_date);
        $oldObj = $model->data_json;
        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($model->name == 'order_item') {

                $convertDate = [
                    'mfg_date' => $model->data_json['mfg_date'] == '__/__/____' ? '' : AppHelper::convertToGregorian($model->data_json['mfg_date']),
                    'exp_date' => $model->data_json['exp_date'] == '__/__/____' ? '' : AppHelper::convertToGregorian($model->data_json['exp_date']),
                ];
                $model->data_json = ArrayHelper::merge($model->data_json, $convertDate);
            }

            if ($model->name == 'order') {
                $model->movement_date = AppHelper::convertToGregorian($model->movement_date);
            }

            if ($model->name == 'order_item' && $model->auto_lot == '1' && $model->lot_number == '') {
                $model->lot_number = \mdm\autonumber\AutoNumber::generate('LOT' . substr(AppHelper::YearBudget(), 2) . '-?????');
            }
            //ถ้าหากมีการระบุวันที่รับเข้าให้หาปีงบประมาจากวันที่
            if (isset($model->data_json['receive_date'])) {
                // return $model->data_json['receive_date'];
                // return AppHelper::YearBudget($model->data_json['receive_date']);
                $model->thai_year = AppHelper::YearBudget($model->data_json['receive_date']);
            } else {
                // ถ้าไม้ให้เป็นปัจจุบัน
                $model->thai_year = AppHelper::YearBudget();
            }

            \Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->save(false)) {
                // if ($model->name == 'order') {
                //     return $this->redirect(['view', 'id' => $model->id]);
                // } else {
                \Yii::$app->response->format = Response::FORMAT_JSON;

                return [
                    'status' => 'success',
                    'container' => '#inventory-container',
                ];
                // }
            } else {
            }
        } else {
            $model->loadDefaultValues();
            try {
                $model->data_json = [
                    'receive_date' => AppHelper::convertToThai($model->data_json['receive_date']),
                ];
                $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
                //code...
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
        if ($model->name == 'order_item') {
            try {
                $oldObj = $model->data_json;
                $model->data_json = [
                    'mfg_date' => $model->mfgDate,
                    'exp_date' => $model->expDate,
                ];
                $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
            } catch (\Throwable $th) {
            }
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
    }

    /**
     * Deletes an existing StockEvent model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id รหัสการเคลื่อนไหวสินค้า
     *
     * @return Response
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = StockEvent::findOne(['id' => $id]);

        if ($model && $model->delete()) {
            return [
                'status' => 'success',
                'container' => '#inventory-container',
            ];
        }
    }

    public function actionConfirmOrder($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $count = StockEvent::find()
            ->where(['category_id' => $id])
            ->andWhere([
                'or',
                ['lot_number' => null],
                ['lot_number' => '']
            ])
            ->count();
        if ($count > 0) {
            return [
                'status' => 'error',
                'msg' => 'หมายเลขล๊อตไม่ครบ'
            ];
        } else {
            StockEvent::updateAll(['order_status' => 'success'], ['category_id' => $id]);
            $this->updateStock($id);
            return $this->redirect(['/inventory/stock-in']);
        }
    }

    protected function updateStock($id)
    {
        $model = $this->findModel($id);
        $order = Order::findOne(['name' => 'order', 'po_number' => $model->po_number]);
        if ($order) {
            $order->status = 6;
            $order->save(false);
        }
        foreach ($model->getItems() as $item) {
            $store = Stock::findOne(['asset_item' => $item->asset_item, 'id' => $item->lot_number]);
            if ($store) {
                $storeModel = $store;
            } else {
                $storeModel = new Stock();
                $storeModel->lot_number = $item->lot_number;
            }

            $storeModel->thai_year = AppHelper::YearBudget();
            $storeModel->asset_item = $item->asset_item;
            // $storeModel->qty = $storeModel->qty + $item->qty;
            $storeModel->qty += (float) $item->qty;
            $storeModel->unit_price = $item->unit_price;
            $storeModel->warehouse_id = $item->warehouse_id;
            $storeModel->save(false);
        }

        $orderInTime = [
            'checkin_data' => date('Y-m-d H:i:s'),
        ];
        $model->data_json = ArrayHelper::merge($model->data_json, $orderInTime);

        $model->order_status = 'success';
        $model->save(false);
    }

    // ตรวจสอบความถูกต้อง
    public function actionCreateValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new StockEvent();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {

            if ($model->name == 'order') {
                if (isset($model->data_json['receive_date'])) {
                    preg_replace('/\D/', '', $model->data_json['receive_date']) == "" ? $model->addError('data_json[receive_date]', $requiredName) : null;
                }

                if (isset($model->data_json['do_number'])) {
                    $model->data_json['do_number'] == '' ? $model->addError('data_json[do_number]', $requiredName) : null;
                }
                if (isset($model->data_json['item_type'])) {
                    $model->data_json['item_type'] == '' ? $model->addError('data_json[item_type]', $requiredName) : null;
                }

                $model->movement_date == '' ? $model->addError('movement_date', $requiredName) : null;
                $model->vendor_id == '' ? $model->addError('vendor_id', $requiredName) : null;
            }
            if ($model->name == 'order_item') {

                if (isset($model->asset_item)) {
                    $model->asset_item == '' ? $model->addError('asset_item', $requiredName) : null;
                }

                if (isset($model->data_json['item_type'])) {
                    $model->data_json['item_type'] == '' ? $model->addError('data_json[item_type]', $requiredName) : null;
                }

                if ($model->auto_lot == '0') {
                    $model->lot_number == '' ? $model->addError('lot_number', $requiredName) : null;
                }

                $model->qty == '' ? $model->addError('qty', $requiredName) : null;
                $model->unit_price == '' ? $model->addError('unit_price', $requiredName) : null;
            }
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }


    public function actionProductList()
    {
        $id = $this->request->get('id');

        $model = StockEvent::findOne($id);
        $searchModel = new ProductSearch([
            'category_id' => $model->asset_type_id
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'asset_item']);

        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'code', $searchModel->q],
            ['like', 'title', $searchModel->q]
        ]);
        // $dataProvider->pagination->pageSize = false;

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('product_list', [
                    'model' => $model,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('product_list', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    public function actionAddItem()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $asset_item = $this->request->get('asset_item');
        $model = new StockEvent([
            'asset_item' => $asset_item,
            'category_id' => $id,
        ]);

        if ($this->request->isPost && $model->load($this->request->post())) {
        }

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('product_list', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('product_list', [
                'model' => $model,
            ]);
        }
    }
    public function actionDeleteAllItem()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $order_id = $this->request->post('order_id');

        if (!$order_id) {
            return ['status' => 'error', 'message' => 'order_id not provided'];
        }

        StockEvent::deleteAll(['category_id' => $order_id]);

        return ['status' => 'success'];
    }

    //การยกเลิก order พร้อมกับคืนค่า Stock ในกรณีมีการรับเข้าจากใบสั่งซื้อ
    public function actionUndoStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');

        /** @var StockEvents $model */
        $model = $this->findModel($id);
        if (!$model) {
            return [
                'status' => 'error',
                'msg' => 'ไม่พบข้อมูลคำสั่งซื้อนี้'
            ];
        }
        // ตรวจสอบว่ามีการเบิกจ่ายหรือไม่
        $sql = "
        SELECT COUNT(i.id) AS total 
        FROM stock_events i
        INNER JOIN stock_events o 
            ON o.lot_number = i.lot_number 
            AND o.transaction_type = 'OUT'
        WHERE i.category_id = :id
    ";

        $countPayStock = Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $id)
            ->queryScalar();

        if ($countPayStock >= 1) {
            return [
                'status' => 'error',
                'msg' => 'ไม่สามารถยกเลิกได้มีการเนื่องจากมีการเบิกจ่ายวัสดุแล้ว'
            ];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // คืนสถานะหลักเป็น "รอดำเนินการ"
            $model->order_status = 'cancel';
            $model->save(false);

            // คืนสถานะใบสั่งซื้อเป็น "รอดำรับเข้า" (ถ้ามี purchase)
            if ($model->purchase) {
                $model->purchase->status = 5;
                $model->purchase->save(false);
            }

            // คืนสถานะรายการย่อยทั้งหมด
            foreach ($model->ListItems() as $item) {
                $item->order_status = 'cancel';
                Stock::findOne(['lot_number' => $item->lot_number])?->delete();

                $item->save(false);
            }

            $transaction->commit();

            return [
                'status' => 'success',
                'msg' => 'คืนสถานะเรียบร้อยแล้ว'
            ];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            return [
                'status' => 'error',
                'msg' => 'เกิดข้อผิดพลาดระหว่างดำเนินการ: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Finds the StockEvent model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id รหัสการเคลื่อนไหวสินค้า
     *
     * @return StockEvent the loaded model
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
