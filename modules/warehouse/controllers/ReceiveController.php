<?php

namespace app\modules\warehouse\controllers;

use app\modules\purchase\models\Order;
use app\modules\purchase\models\OrderSearch;
use app\modules\sm\models\Product;
use app\modules\warehouse\models\Stock;
use app\modules\warehouse\models\StockOrder;
use app\modules\warehouse\models\StockOrderSearch;
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
        $searchModel = new StockOrderSearch();
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
        $model = new StockOrder([
            'name' => 'receive',
            'po_number' => $this->request->get('category_id')
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $thaiYear = substr((date('Y') + 543), 2);
                if ($model->po_number == '') {
                    $model->rc_number = \mdm\autonumber\AutoNumber::generate('RC-' . $thaiYear . '????');
                }
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
                'title' => $this->request->get('tilte'),
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
        $models = Order::find()->where(['name' => 'order'])->all();
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('list_order_by_po', [
                'models' => $models,
            ])
        ];
    }

    public function actionListItemFormPo()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $po_number = $this->request->get('po_number');
        // $po_number = 'PO-670005';
        $order = Order::find()->where(['name' => 'order', 'po_number' => $po_number])->one();

        return [
            'title' => $this->request->get('tilte'),
            'content' => $this->renderAjax('list_item_form_po', [
                'order' => $order,
            ])
        ];
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

    // เพิ่มรายการวัสดุจาก PO
    public function actionAddItem()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        // ตรวจสอบ order จาก ID
        $order = Order::findOne($id);
        $stockOrder = StockOrder::find()->where(['name' => 'receive', 'po_number' => $order->po_number])->One();
        // ค้นหารายการสินค่าจาก product_id ที่เก็บไว้ใน Order po_number
        $product = Product::findOne($order->product_id);

        $model = new StockOrder([
            'category_id' => $order->id,
            'po_number' => $order->po_number,
            'rc_number' => $stockOrder->rc_number,
            'name' => 'receive_item',
            'product_id' => $product->id,
            'movement_type' => 'IN',
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
                        'container' => '#warehouse',
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
        $model = StockOrder::findOne(['id' => $id]);
        $model->qty_check = $model->QtyCheck();

        $product = Product::findOne($model->product_id);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->save(false);
                return [
                    'status' => 'success',
                    'container' => '#' . $model->name,
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

        $iems = StockOrder::find()->where(['name' => 'receive_item'])->all();
        $data = [];
        foreach ($iems as $key => $item) {
            $stock = new Stock();
            $stock->product_id = $item->product_id;
            $stock->warehouse_id = 1;
            $stock->po_number = $item->po_number;
            $stock->lot_number = $item->lot_number;
            $stock->qty = $item->qty;
            $data[] = [
                'data' => $item->product_id,
                'status' => $stock->save(false)
            ];
            // code...
        }
        return $data;
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
        $container = $this->request->get('container');
        $model = $this->findModel($id);
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model->delete();
        return [
            'status' => 'success',
            'container' => '#' . $model->name,
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
        if (($model = StockOrder::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new StockOrder();

        if ($this->request->isPost && $model->load($this->request->post())) {
            $requiredName = 'ต้องระบุ';
            // ตรวจสอบตำแหน่ง
            if ($model->name == 'receive_item') {
                $model->qty == '' ? $model->addError('qty', $requiredName) : null;
                $model->lot_number == '' ? $model->addError('lot_number', $requiredName) : null;
                // $model->data_json['position_name'] == '' ? $model->addError('data_json[position_name]', $requiredName) : null;
                // $model->data_json['position_number'] == '' ? $model->addError('data_json[position_number]', $requiredName) : null;
                $model->qty > $model->qty_check ? $model->addError('qty', 'เกินจำนวนที่สั่งซื้อ') : null;
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
