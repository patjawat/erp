<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockMovement;
use app\modules\inventory\models\StockMovementSearch;
use app\modules\purchase\models\Order;
use app\modules\purchase\models\OrderSearch;
use app\modules\sm\models\Product;
use app\modules\sm\models\ProductSearch;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Yii;

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

    /**
     * Lists all ir models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new StockMovementSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterwhere(['name' => 'receive']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
        Yii::$app->session->set('receive',$model);
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

    public function actionCreate()
    {
        $model = new StockMovement([
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
    public function actionListOrderByPo()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $po_number = $this->request->get('po_number');
        // $po_number = 'PO-670005';
        $models = Order::find()->where(['name' => 'order','status' => '5'])->all();
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('list_order_by_po', [
                'models' => $models,
            ])
        ];
    }

    // แสดงรายการสินค้าจากใบ po
    public function actionListProductOrder()
    {
        
        $id = $this->request->get('id');
        $model = $this->findModel($id);
        $order = Order::find()->where(['name' => 'order', 'po_number' => $model->po_number])->one();
        
        if($this->request->isAjax){
            
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_product_order', [
                    'model' => $model,
                    'order' => $order
                    ])
                ];
            }else{
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
        $dataProvider->query->andFilterWhere(['IN','name',['product_item','asset_item']]);
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
    }else{
        return $this->render('list_product', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }
    }
    // เพิ่มคณะกรรมการ ตรวจรับ

    public function actionAddCommittee()
    {
        $model = new Order([
            'category_id' => $this->request->get('category_id'),
            'name' => $this->request->get('name')
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => $this->request->get('title'),
                    'status' => 'success',
                    'container' => '#' . $model->name,
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_commitee', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form_commitee', [
                'model' => $model,
            ]);
        }
    }

    //เพิ่มรายการวสดุรับเข้า
    public function actionAddItem()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $receive = Yii::$app->session->get('receive');
        $id = $this->request->get('id');
  
        $product = Product::findOne($id);

        $order = new Order();
        $StockMovement =  StockMovement::findOne($receive->id);

        $model = new StockMovement([
            'po_number' => $order->po_number,
            'rc_number' => $StockMovement->rc_number,
            'to_warehouse_id' => $StockMovement->to_warehouse_id,
            'name' => 'receive_item',
            'product_id' => $product->id,
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
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        // ตรวจสอบ order จาก ID
        $order = Order::findOne($id);
        // ถ้ามีรายการเดิมที่ยังรับเข้าไม่หมด
        $StockMovement = StockMovement::find()->where(['name' => 'receive', 'po_number' => $order->po_number,'order_status' => 'pending'])->One();
        // ค้นหารายการสินค่าจาก product_id ที่เก็บไว้ใน Order po_number
        $product = Product::findOne($order->product_id);

        $model = new StockMovement([
            'po_number' => $order->po_number,
            'rc_number' => $StockMovement->rc_number,
            'to_warehouse_id' => $StockMovement->to_warehouse_id,
            'name' => 'receive_item',
            'product_id' => $product->id,
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

    public function actionUpdateItem($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = StockMovement::findOne(['id' => $id]);

        //เมื่อเป็นการรับเข้าจากการสั่งซื้อให้ตรวจสอบจำนวนด้วย
        if($model->movement_type == 'po_receive'){
            $model->qty_check = $model->QtyCheck();
        }
        

        $product = Product::findOne($model->product_id);

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
        $id = $this->request->get('id');
        $model = $this->findModel($id);

        $updateStock = StockMovement::updateAll(['order_status' => 'success'], ['rc_number' => $model->rc_number]);

        if($model->OrderSuccess()['status'] == true){
            $order = Order::findOne(['po_number' => $model->po_number]);
            $order->status = 6;
            $order->save(false);
        }

        if ($updateStock) {
            return [
                'status' => 'success',
                'container' => '#inventory',
            ];
        }

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
        if (($model = StockMovement::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new StockMovement();

        if ($this->request->isPost && $model->load($this->request->post())) {
            $requiredName = 'ต้องระบุ';
            // ตรวจสอบตำแหน่ง
            if ($model->name == 'receive_item') {
                $model->qty == '' ? $model->addError('qty', $requiredName) : null;
                $model->lot_number == '' ? $model->addError('lot_number', $requiredName) : null;
                // $model->data_json['position_name'] == '' ? $model->addError('data_json[position_name]', $requiredName) : null;
                // $model->data_json['position_number'] == '' ? $model->addError('data_json[position_number]', $requiredName) : null;
                if($model->po_number){
                    $model->qty > $model->qty_check ? $model->addError('qty', 'เกินจำนวนที่สั่งซื้อ('.$model->qty_check.')') : null;
                }
            }

            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }
}
