<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockSearch;
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
        $model = new Stock();
        $requiredName = "ต้องระบุ";
        if ($this->request->isPost && $model->load($this->request->post())) {

            if (isset($model->data_json['mfg_date'])) {
                preg_replace('/\D/', '', $model->data_json['mfg_date']) == "" ? $model->addError('data_json[mfg_date]', $requiredName.'วันผลิต') : null;
            }
            
            if (isset($model->data_json['exp_date'])) {
                preg_replace('/\D/', '', $model->data_json['exp_date']) == "" ? $model->addError('data_json[exp_date]', $requiredName.'วันหมดอายุ') : null;
            }
            
                if($model->qty != $model->data_json['qty']){
                    $model->addError('qty', 'จำนวนไม่ตรง');
                }

                $model->qty == "" ? $model->addError('qty', $requiredName) : null;
                $model->qty == "0" ? $model->addError('qty', $requiredName.'มากกว่า 0') : null;


        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    // ตรวจสอบความถูกต้อง เก่า
    // public function actionValidator()
    // {
    //     Yii::$app->response->format = Response::FORMAT_JSON;
    //     $model = new Stock();

    //     if ($this->request->isPost && $model->load($this->request->post())) {
    //         $requiredName = 'ต้องระบุ';
    //         // ตรวจสอบตำแหน่ง
    //         if ($model->name == 'receive_item') {
    //             $model->qty == '' ? $model->addError('qty', $requiredName) : null;
    //             $model->lot_number == '' ? $model->addError('lot_number', $requiredName) : null;
    //             // $model->data_json['position_name'] == '' ? $model->addError('data_json[position_name]', $requiredName) : null;
    //             // $model->data_json['position_number'] == '' ? $model->addError('data_json[position_number]', $requiredName) : null;
    //             if ($model->po_number) {
    //                 $model->qty > $model->qty_check ? $model->addError('qty', 'เกินจำนวนที่สั่งซื้อ(' . $model->qty_check . ')') : null;
    //             }
    //         }

    //         foreach ($model->getErrors() as $attribute => $errors) {
    //             $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
    //         }
    //         if (!empty($result)) {
    //             return $this->asJson($result);
    //         }
    //     }
    // }


    /**
     * Lists all ir models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterwhere(['name' => 'receive']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

public function actionFormCommittee()
{
    
    $model = new Stock([
        'category_id' => $this->request->get('id'),
        'name' => $this->request->get('name'),
    ]);

    if ($this->request->isPost) {
        if ($model->load($this->request->post()) && $model->save()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'status' => 'success',
                'container' => '#receive',
            ];
        }
    } else {
        $model->loadDefaultValues();
    }

    if ($this->request->isAjax) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('_form_committee', [
                'model' => $model,
            ]),
        ];
    } else {
        return $this->render('_form_committee', [
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
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $order = Order::findOne(['po_number' => $model->po_number]);
        Yii::$app->session->set('receive', $model);
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('view_order', [
                    'model' => $model,
                    'order' => $order
                ]),
            ];
        } else {
            return $this->render('view_order', [
                'model' => $model,
                'order' => $order
            ]);
        }
    }

    //แสดงรายการรอรับเข้าคลัง
    public function actionViewOrder($id)
    {
        $model = Order::findOne($id);
        
        Yii::$app->session->set('receive', $model);
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('view_order', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('view_order', [
                'model' => $model,
            ]);
        }
    }


    //แก้ไขรายการรับเข้า ระบุบ lot วันหมดอายุ
    public function actionUpdateOrderItem($id)
    {
        $model = $this->findModel($id);
        $oriObj = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                $thaiYear = date('dm').substr((date('Y') + 543), 2);
                // return $model->data_json['lot_number'];
                if($model->auto_lot == "1"){
                    $lotNumber  = \mdm\autonumber\AutoNumber::generate('LOT' . $thaiYear . '-?????');
                }else{
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
                'content' => $this->renderAjax('_form_item', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form_item', [
                'model' => $model,

            ]);
        }
    }





    public function actionCreate()
    {
        $model = new Stock([
            'name' => 'receive',
            'po_number' => $this->request->get('category_id'),
            'receive_type' => $this->request->get('receive_type')
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


    public function actionCreateOrder()
    {
        $thaiYear = substr((date('Y') + 543), 2);
        $po_number  = $this->request->get('category_id');
        $receive_type  = $this->request->get('receive_type');
        $order = Order::findOne(['name' => 'order','po_number' => $po_number]);
        $model = new Stock();
        $model->name = 'receive';
        $model->po_number = $po_number;
        $model->rc_number = \mdm\autonumber\AutoNumber::generate('RC-' . $thaiYear . '????');
        $model->order_status = 'pending';
        
        if($model->save(false)){
            Yii::$app->response->format = Response::FORMAT_JSON;


            foreach ($order->ListOrderItems() as $item){
                $stockItem = new Stock([
                    'category_id' => $model->id,
                    'po_number' => $model->po_number,
                    'name' => 'stock_item',
                    'asset_item' => $item->asset_item,
                    'unit_price' => $item->price,
                    'data_json' => [
                        'qty' => $item->qty
                    ]
                ]);
                $stockItem->save(false);
            }
            return $this->redirect(['view', 'id' => $model->id]);   
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

    //คณะกรรมการตรวจรับพัสดุเข้าคลัง
    public function actionListCommittee($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('list_committee_receive', ['model' => $model]),
        ];
    }


    // เพิ่มคณะกรรมการ ตรวจรับ
    public function actionAddCommittee()
    {
        $model = new Stock([
            'rc_number' => $this->request->get('rc_number'),
            'name' => $this->request->get('name')
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => $this->request->get('title'),
                    'status' => 'success',
                    'container' => '#inventory',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_committee', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form_committee', [
                'model' => $model,
            ]);
        }
    }


    // public function actionUpdateCommittee()
    // {
    //     $model = Stock::findOne($this->request->get('id'));

    //     if ($this->request->isPost) {
    //         if ($model->load($this->request->post()) && $model->save()) {
    //             Yii::$app->response->format = Response::FORMAT_JSON;
    //             return [
    //                 'title' => $this->request->get('title'),
    //                 'status' => 'success',
    //                 'container' => '#inventory',
    //             ];
    //         }
    //     } else {
    //         $model->loadDefaultValues();
    //     }

    //     if ($this->request->isAjax) {
    //         Yii::$app->response->format = Response::FORMAT_JSON;
    //         return [
    //             'title' => $this->request->get('title'),
    //             'content' => $this->renderAjax('_form_committee', [
    //                 'model' => $model,
    //             ]),
    //         ];
    //     } else {
    //         return $this->render('_form_committee', [
    //             'model' => $model,
    //         ]);
    //     }
    // }

    //เพิ่มรายการวสดุรับเข้า
    public function actionAddItem()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $receive = Yii::$app->session->get('receive');
        $id = $this->request->get('id');

        $product = Product::findOne($id);

        $order = new Order();
        $Stock =  Stock::findOne($receive->id);

        $model = new Stock([
            'po_number' => $order->po_number,
            'rc_number' => $Stock->rc_number,
            'to_warehouse_id' => $Stock->to_warehouse_id,
            'name' => 'receive_item',
            'asset_item' => $product->code,
            'movement_type' => 'receive',
            'order_status' => 'pending',
            'data_json' => [
                'po_qty' => $order->qty
            ]
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
                'content' => $this->renderAjax('_add_item', [
                    'model' => $model,
                    'product' => $product,
                    'order' => $order
                ]),
            ];
        } else {
            return $this->render('_add_item', [
                'model' => $model,
                'product' => $product,
                'order' => $order
            ]);
        }
    }
    // เพิ่มรายการวัสดุจาก PO
    public function actionAddPoItem()
    {
        $id = $this->request->get('id');
        // ตรวจสอบ order จาก ID
        $order = Order::findOne($id);
        // ถ้ามีรายการเดิมที่ยังรับเข้าไม่หมด
        $Stock = Stock::find()->where(['name' => 'receive', 'po_number' => $order->po_number, 'order_status' => 'pending'])->One();
        // ค้นหารายการสินค่าจาก asset_item ที่เก็บไว้ใน Order po_number
        // $product = Product::findOne(['code' => $order->asset_item]);

        $model = new Stock([
            'po_number' => $order->po_number,
            'rc_number' => $Stock->rc_number,
            'to_warehouse_id' => $Stock->to_warehouse_id,
            'name' => 'receive_item',
            'asset_item' => $order->asset_item,
            'movement_type' => 'receive',
            'order_status' => 'pending',
            'data_json' => [
                'po_qty' => $order->qty
            ]
        ]);

        $model->qty_check = $model->QtyCheck();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                if ($model->save()) {
                    $order->save(false);
                    // return $model->save();
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
                'content' => $this->renderAjax('_add_item', [
                    'model' => $model,
                    'order' => $order
                ]),
            ];
        } else {
            return $this->render('_add_item', [
                'model' => $model,
                'order' => $order
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
                'title' => 'แก้ไข <i class="fa-solid fa-circle-info text-primary"></i> ' . $product->title,
                'content' => $this->renderAjax('_add_item', [
                    'model' => $model,
                    'product' => $product
                ]),
            ];
        } else {
            return $this->render('_add_item', [
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
        $order = Order::findOne($id);
        $receiveObj =  [
            'recipient_id' => $user['user_id'],
            'recipient_name' => $user['fullname'],
            'recipient_department' => $user['department'],
        ];
        $stock = new Stock([
            'name' => 'receive',
            'po_number' => $order->po_number,
            'data_json' => $receiveObj
        ]);
        $stock->save(false);
        foreach ($order->ListOrderItems() as $item) {
            $stockItem = new Stock([
                'asset_item' => $item->asset_item,
                'category_id' => $stock->id,
                'po_number' => $stock->po_number,
                'name' => 'receive_item',
                'qty' => $item->data_json['qty'],
                'unit_price' => $item->price
            ]);
            $stockItem->save(false);
        }
        $order->data_json =  ArrayHelper::merge($order->data_json,$receiveObj);
        $order->status = 5;
        $order->save(false);
        return $this->redirect(['/inventory/receive']);
        // return [
        //     'stock' => $stock,
        //     'item' => $stockItem
        // ];
        // $model = $this->findModel($id);

        // $updateStock = Stock::updateAll(['order_status' => 'success'], ['rc_number' => $model->rc_number]);

        // if ($model->OrderSuccess()['status'] == true) {
        //     $order = Order::findOne(['po_number' => $model->po_number]);
        //     $order->status = 6;
        //     $order->save(false);
        // }

        // if ($updateStock) {
        //     return [
        //         'status' => 'success',
        //         'container' => '#inventory',
        //     ];
        // }
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
