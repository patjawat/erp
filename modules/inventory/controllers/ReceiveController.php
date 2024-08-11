<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockSearch;
use app\modules\inventory\models\Store;
use app\modules\purchase\models\Order;
use app\modules\purchase\models\OrderSearch;
use app\modules\sm\models\Product;
use app\components\AppHelper;
use app\modules\sm\models\ProductSearch;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use Yii;
use yii\db\Expression;
use app\modules\inventory\models\Warehouse;

/**
 * irController implements the CRUD actions for ir model.
 */
class ReceiveController extends Controller
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


    // ตรวจสอบความถูกต้อง
    public function actionCreateValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Stock();
        $requiredName = "ต้องระบุ";
        if ($this->request->isPost && $model->load($this->request->post())) {

            if (isset($model->data_json['to_stock_date'])) {
                preg_replace('/\D/', '', $model->data_json['to_stock_date']) == "" ? $model->addError('data_json[to_stock_date]', $requiredName) : null;
            }

            if (isset($model->data_json['checked_date'])) {
                preg_replace('/\D/', '', $model->data_json['checked_date']) == "" ? $model->addError('data_json[checked_date]', $requiredName) : null;
            }
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }



    // ตรวจสอบความถูกต้อง
    public function actionAddItemValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Order();

       

        $requiredName = "ต้องระบุ";
        if ($this->request->isPost && $model->load($this->request->post())) {

            if (isset($model->data_json['mfg_date'])) {
                preg_replace('/\D/', '', $model->data_json['mfg_date']) == "" ? $model->addError('data_json[mfg_date]', $requiredName . 'วันผลิต') : null;
            }

            if (isset($model->data_json['exp_date'])) {
                preg_replace('/\D/', '', $model->data_json['exp_date']) == "" ? $model->addError('data_json[exp_date]', $requiredName . 'วันหมดอายุ') : null;
            }

            if ($model->qty !== $model->data_json['qty']) {
                $model->addError('data_json[qty]', 'จำนวนไม่ตรง');
            }
            
            $model->data_json['qty'] == "" ? $model->addError('data_json[qty]', $requiredName) : null;
            $model->data_json['qty']  == "0" ? $model->addError('data_json[qty]', $requiredName . 'มากกว่า 0') : null;
        }
        
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }

        // ตรวจสอบควาใถูกต้องรับเข้า Stock
        $stock = new Stock();
        if ($this->request->isPost && $stock->load($this->request->post())) {

            if (isset($stock->data_json['mfg_date'])) {
                preg_replace('/\D/', '', $stock->data_json['mfg_date']) == "" ? $stock->addError('data_json[mfg_date]', $requiredName . 'วันผลิต') : null;
            }

            if (isset($stock->data_json['exp_date'])) {
                preg_replace('/\D/', '', $stock->data_json['exp_date']) == "" ? $stock->addError('data_json[exp_date]', $requiredName . 'วันหมดอายุ') : null;
            }

            $stock->qty == "" ? $stock->addError('qty', $requiredName) : null;
            $stock->qty  == "0" ? $stock->addError('qty', $requiredName . 'มากกว่า 0') : null;
        }

        foreach ($stock->getErrors() as $attribute => $errors) {
            $resultStock[\yii\helpers\Html::getInputId($stock, $attribute)] = $errors;
        }
        if (!empty($resultStock)) {
            return $this->asJson($resultStock);
        }


    }


    /**
     * Lists all ir models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterwhere(['name' => 'order','movement_type' => 'IN','to_warehouse_id' => $warehouse['warehouse_id']]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    

    //แสดงรายการรอรับเข้าคลัง
    public function actionView($id)
    {
        $model =  $this->findModel($id);

        Yii::$app->session->set('receive', $model);
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Displays a single ir model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionViewByPo($id)
    {
        $model = $this->findModel($id);
        if($model->po_number){
            $order = Order::findOne(['po_number' => $model->po_number]);
        }else{
            $order = new Order();
        }
        Yii::$app->session->set('receive', $model);
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('view_by_po', [
                    'model' => $model,
                    'order' => $order
                ]),
            ];
        } else {
            return $this->render('view_by_po', [
                'model' => $model,
                'order' => $order
            ]);
        }
    }


    //แก้ไขรายการรับเข้า ระบุบ lot วันหมดอายุ
    public function actionUpdateOrderItem($id)
    {
        $model = Order::findOne($id);
        $oriObj = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                $thaiYear = date('dm') . substr((date('Y') + 543), 2);
                // return $model->data_json['lot_number'];
                if ($model->auto_lot == "1") {
                    $lotNumber  = \mdm\autonumber\AutoNumber::generate('LOT' . $thaiYear . '-?????');
                } else {
                    $lotNumber = $model->data_json['lot_number'];
                    // return $model->data_json;
                }

                $convertDate = [
                    'mfg_date' =>  AppHelper::convertToGregorian($model->data_json['mfg_date']),
                    'exp_date' =>  AppHelper::convertToGregorian($model->data_json['exp_date']),
                    'lot_number' => $lotNumber
                ];


                $model->data_json =  ArrayHelper::merge($oriObj, $model->data_json, $convertDate);

                if ($model->save()) {
                    return [
                        'status' => 'success',
                        'container' => '#receive',
                        'model' => $model
                    ];
                }
                // return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
            try {
                $oldObj = $model->data_json;

                $model->data_json = [
                    'mfg_date' =>  AppHelper::convertToThai($model->data_json['mfg_date']),
                    'exp_date' =>  AppHelper::convertToThai($model->data_json['exp_date'])
                ];
                $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_po_item', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form_po_item', [
                'model' => $model,

            ]);
        }
    }

    public function actionCreate()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $model = new Stock([
            'name' => 'order',
            'po_number' => $this->request->get('po_number'),
            'movement_type' => 'IN',
            'to_warehouse_id' => $warehouse['warehouse_id']
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
          

                $thaiYear = substr((date('Y') + 543), 2);

                if ($model->rc_number == '') {
                    $model->rc_number = \mdm\autonumber\AutoNumber::generate('RC-' . $thaiYear . '????');
                }
                $model->order_status = 'pending';

                $convertDate = [
                    'to_stock_date' =>  AppHelper::convertToGregorian($model->data_json['to_stock_date']),
                    'checked_date' =>  AppHelper::convertToGregorian($model->data_json['checked_date']),
                ];

                $model->data_json =  ArrayHelper::merge($model->data_json, $convertDate);
                $model->save(false);
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



    // รับเข้าจากการจัดซื้อ
    public function actionCreateByPo()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $model = new Stock([
            'name' => 'receive',
            'po_number' => $this->request->get('po_number'),
            'receive_type' => $this->request->get('receive_type'),
            'to_warehouse_id' => $warehouse['warehouse_id']
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $thaiYear = substr((date('Y') + 543), 2);
                if ($model->rc_number == '') {
                    $model->rc_number = \mdm\autonumber\AutoNumber::generate('RC-' . $thaiYear . '????');
                    $order = Order::findOne(['po_number' => $model->po_number]);
                    if ($order) {
                        $order->status = 5;
                        $order->save(false);
                    }
                }
                $model->order_status = 'pending';
                $model->save(false);
                return $this->redirect(['view-by-po', 'id' => $model->id]);
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




    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save(false)) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
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

    // แสดงรายการสินค้าจากใบ po
    public function actionListProductOrder()
    {

        $id = $this->request->get('id');
        $model = $this->findModel($id);
        $order = Order::find()->where(['name' => 'order', 'po_number' => $model->po_number])->one();

        if ($this->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_product_order', [
                    'model' => $model,
                    'order' => $order
                ])
            ];
        } else {
            return $this->render('list_product_order', [
                'model' => $model,
                'order' => $order
            ]);
        }
    }

    public function actionListProduct()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['IN', 'name', ['product_item', 'asset_item']]);
        $dataProvider->pagination->pageSize = 10;

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_product', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider
                ])
            ];
        } else {
            return $this->render('list_product', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider
            ]);
        }
    }





    public function actionProductList()
    {

        $id = $this->request->get('id');
        $model = $this->findModel($id);
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'asset_item']);
        $dataProvider->pagination->pageSize = 10;

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('product_list', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'model' => $model
                ]),
            ];
        } else {
            return $this->render('product_list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'model' => $model
            ]);
        }
    }


    //เพิ่มรายการวสดุรับเข้า
    public function actionAddItem()
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $asset_item = $this->request->get('asset_item');

        $product = Product::findOne(['code' => $asset_item]);
        // return $product;

        $Stock =  Stock::findOne($id);

        $model = new Stock([

            'rc_number' => $Stock->rc_number,
            'to_warehouse_id' => $Stock->to_warehouse_id,
            'name' => 'order_item',
            'asset_item' => $product->code,
            'movement_type' => 'in',
            'order_status' => 'pending',
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                if ($model->save()) {
                    return [
                        'status' => 'success',
                        'container' => '#inventory',
                    ];
                }
            } else {
                return $model->getErrors();
            }
        } else {
            $model->loadDefaultValues();
        }


        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_item', [
                    'model' => $model,
                    'product' => $product
                ]),
            ];
        } else {
            return $this->render('_form_item', [
                'model' => $model,
                'product' => $product
            ]);
        }
    }


    public function actionUpdateItem($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Stock::findOne(['id' => $id]);

        //เมื่อเป็นการรับเข้าจากการสั่งซื้อให้ตรวจสอบจำนวนด้วย
        if ($model->movement_type == 'po_receive') {
            $model->qty_check = $model->QtyCheck();
        }


        $product = Product::findOne($model->asset_item);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->save(false);
                return [
                    'status' => 'success',
                    'container' => '#inventory',
                ];
            } else {
                return false;
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'แก้ไข <i class="fa-solid fa-circle-info text-primary"></i> ',
                'content' => $this->renderAjax('_form_item', [
                    'model' => $model,
                    'product' => $product
                ]),
            ];
        } else {
            return $this->render('_form_item', [
                'model' => $model,
                'product' => $product
            ]);
        }
    }

    public function actionSaveToStock()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $user = UserHelper::getMe();
        $id = $this->request->get('id');
        $stock = $this->findModel($id);
        // return $stock->to_warehouse_id;

        if($stock->po_number){
            $order = Order::findOne(['name' => 'order','po_number' => $stock->po_number]);   
        }

        $receiveObj =  [
            'recipient_id' => $user['user_id'],
            'recipient_name' => $user['fullname'],
            'recipient_department' => $user['department'],
        ];

        $stock->name  = 'receive';
        $stock->order_status = 'success';
        if($stock->po_number){
            $stock->po_number = $order->po_number;
            $data = [];
        //ถ้าเป็นการรับจ้าใบสั่่งซื้อ
            foreach ($order->ListOrderItems() as $item) {
                $stockItem = new Stock([
                    'asset_item' => $item->asset_item,
                    'category_id' => $stock->id,
                    'po_number' => $stock->po_number,
                    'name' => 'receive_item',
                    'qty' => $item->data_json['qty'],
                    'unit_price' => $item->price,
                    'order_status' => 'success'
                ]);
                $stockItem->save(false);

              $this->updateStore($stock,$item);

            }
     
           
            $order->data_json =  ArrayHelper::merge($order->data_json, $receiveObj);
            $order->status = 5;
             $order->save(false);

        }else{
            //ถ้าเป็นการรับเข้าเอง
            $stocks  = Stock::find()->where(['name' => 'order_item','rc_number' => $stock->rc_number])->all();
            foreach($stocks as $item){
                $item->order_status = 'success';
                $item->save(false);
                $this->updateStore($stock,$item);

            }
        }

        $stock->data_json = $receiveObj;
        // $this->updateStore($stock);
        //$stock->save(false);
        return $this->redirect(['/inventory/receive']);
        
    }


    protected function updateStore($stock,$item)
    {
        $store = Store::findOne(['asset_item' => $item->asset_item,'warehouse_id' => $stock->to_warehouse_id]);
        if($store){
            $storeModel = $store;
        }else{
            $storeModel = new Store();
        }
        $storeModel->asset_item  = $item->asset_item;
        $storeModel->stock_qty = $storeModel->stock_qty + $item->qty;
        $storeModel->warehouse_id = $stock->to_warehouse_id;
        $storeModel->save(false);
    }
    /**
     * Deletes an existing ir model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
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

    /**
     * Finds the ir model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return ir the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Stock::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
