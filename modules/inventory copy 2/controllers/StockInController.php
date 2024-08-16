<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\StockIn;
use app\modules\inventory\models\StockInSearch;
use app\modules\sm\models\Product;
use app\modules\sm\models\ProductSearch;
use app\components\AppHelper;
use app\modules\inventory\models\Warehouse;
use app\modules\purchase\models\Order;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * StockInController implements the CRUD actions for StockIn model.
 */
class StockInController extends Controller
{
    /**
     * @inheritDoc
     */
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
     * Lists all StockIn models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new StockInSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'order']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single StockIn model.
     * @param int $id รหัสการเคลื่อนไหวสินค้า
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new StockIn model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $name = $this->request->get('name');
        $order_id = $this->request->get('order_id');
        $model = new StockIn([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'category_id' => $order_id,
            'name' => $name
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                // สร้างรหัสรับเข้า
                if ($model->name == 'order') {
                    $model->code = \mdm\autonumber\AutoNumber::generate('RC-' . (substr((date('Y') + 543), 2)) . '????');
                }

                if ($model->name == 'order_item') {
                    $convertDate = [
                        'mfg_date' =>  AppHelper::convertToGregorian($model->data_json['mfg_date']),
                        'exp_date' =>  AppHelper::convertToGregorian($model->data_json['exp_date']),
                    ];
                    $model->data_json =  ArrayHelper::merge($model->data_json, $convertDate);

                    if ($model->auto_lot == "1") {
                        $model->lot_number  = \mdm\autonumber\AutoNumber::generate('LOT' . (substr((date('Y') + 543), 2)) . '-?????');
                    } else {

                    }

                }

                $model->order_status = 'pending';
                $model->warehouse_id = $warehouse['warehouse_id'];

                if ($model->save(false)) {
                    if ($model->name == 'order') {
                        return $this->redirect(['view', 'id' => $model->id]);
                    } else {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [
                            'status' => 'success',
                            'container' => '#inventory',
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
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ])
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
        $warehouse = Yii::$app->session->get('warehouse');
        $po_number = $this->request->get('po_number');
        $model = new StockIn([
            'name' => 'order',
            'po_number' =>$order->po_number,
            'vendor_id' =>$order->vendor_id,
            // 'receive_type' => $this->request->get('receive_type'),
            'warehouse_id' => $warehouse['warehouse_id']
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $thaiYear = substr((date('Y') + 543), 2);
                    if ($order) {
                        // $order->status = 5;
                        // $order->save(false);
                        
                         //ถ้าเป็นการรับจ้าใบสั่่งซื้อ


                    }
            
                $model->order_status = 'pending';
                $model->save(false);
                foreach ($order->ListOrderItems() as $item) {
                    $stockItem = new StockIn([
                        'asset_item' => $item->asset_item,
                        'category_id' => $model->id,
                        'po_number' => $model->po_number,
                        'name' => 'order_item',
                        // 'qty' => $item->qty,
                        // 'unit_price' => $item->price,
                        'order_status' => 'pending'
                    ]);
                    $stockItem->save(false);
                }
    
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;


            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Updates an existing StockIn model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id รหัสการเคลื่อนไหวสินค้า
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);


        if ($this->request->isPost && $model->load($this->request->post())) {

            if ($model->name == 'order_item') {
                $convertDate = [
                    'mfg_date' =>  AppHelper::convertToGregorian($model->data_json['mfg_date']),
                    'exp_date' =>  AppHelper::convertToGregorian($model->data_json['exp_date']),
                ];
                $model->data_json =  ArrayHelper::merge($model->data_json, $convertDate);
            }

            if ($model->save(false)) {
                if ($model->name == 'order') {
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'status' => 'success',
                        'container' => '#inventory',
                    ];
                }
            } else {
                $model->loadDefaultValues();
            }
        }
        if ($model->name == 'order_item') {
            try {
            $oldObj = $model->data_json;
            $model->data_json = [
                'mfg_date' =>  AppHelper::convertToThai($model->data_json['mfg_date']),
                'exp_date' =>  AppHelper::convertToThai($model->data_json['exp_date'])
            ];
            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
            } catch (\Throwable $th) {
            }
        }

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ])
            ];
        } else {
            if ($this->request->isAJax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => $this->request->get('title'),
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ])
                ];
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
    }

    /**
     * Deletes an existing StockIn model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id รหัสการเคลื่อนไหวสินค้า
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $model->delete();
        return [
            'status' => 'success',
            'container' => '#inventory',
        ];
    }

    public function actionConfirmOrder($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = StockIn::updateAll(['order_status' => 'success'], ['category_id' => $id]);
        if ($model) {
            return [
                'status' => 'success',
                'container' => '#inventory',
            ];
        }
    }

    // ตรวจสอบความถูกต้อง
    public function actionCreateValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new StockIn();
        $requiredName = "ต้องระบุ";
        if ($this->request->isPost && $model->load($this->request->post())) {

            if ($model->name == 'order_item') {

                if (isset($model->data_json['mfg_date'])) {
                    preg_replace('/\D/', '', $model->data_json['mfg_date']) == "" ? $model->addError('data_json[mfg_date]', $requiredName) : null;
                }
                if (isset($model->data_json['exp_date'])) {
                    preg_replace('/\D/', '', $model->data_json['exp_date']) == "" ? $model->addError('data_json[exp_date]', $requiredName) : null;
                }
            }

            if (isset($model->asset_item)) {
                $model->asset_item == "" ? $model->addError('asset_item', $requiredName) : null;
            }
            
            if (isset($model->data_json['item_type'])) {
                $model->data_json['item_type'] == "" ? $model->addError('data_json[item_type]', $requiredName) : null;
            }
            
            if ($model->auto_lot == "0") {
                $model->lot_number == "" ? $model->addError('lot_number', $requiredName) : null;
            } 

            $model->qty == "" ? $model->addError('qty', $requiredName) : null;
            $model->unit_price == "" ? $model->addError('unit_price', $requiredName) : null;

        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }



    // แสดงรายการส่งซื้อที่รอรับเข้าคลัง
    public function actionListPendingOrder()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $warehouseModel = Warehouse::findOne($warehouse['warehouse_id']);
        $item = $warehouseModel->data_json['item_type'];

        $po_number = $this->request->get('po_number');
        $models = Order::find()
            ->where(['name' => 'order', 'status' => 4])
            // ->andWhere(['IN', new Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.item_type'))"),$item])
            ->andWhere(['IN', 'category_id', $item])
            ->all();
        if ($this->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_pending_order', [
                    'models' => $models,
                ])
            ];
        } else {
            return $this->render('list_pending_order', [
                'models' => $models,
            ]);
        }
    }


    protected function updateStore($stock,$item)
    {
        $store = Stock::findOne(['asset_item' => $item->asset_item,'warehouse_id' => $stock->to_warehouse_id]);
        if($store){
            $storeModel = $store;
        }else{
            $storeModel = new Stock();
        }
        $storeModel->asset_item  = $item->asset_item;
        $storeModel->stock_qty = $storeModel->stock_qty + $item->qty;
        $storeModel->warehouse_id = $stock->to_warehouse_id;
        $storeModel->save(false);
    }



    /**
     * Finds the StockIn model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id รหัสการเคลื่อนไหวสินค้า
     * @return StockIn the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StockIn::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
