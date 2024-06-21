<?php

namespace app\modules\purchase\controllers;

use app\modules\purchase\models\Order;
use app\modules\purchase\models\OrderSearch;
use app\modules\sm\models\Product;
use app\modules\sm\models\ProductSearch;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Yii;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrderController extends Controller
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
     * Lists all Order models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Order model.
     * @param int $id ID
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
     * Creates a new Order model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Order();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Order model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    // อนุมัติตาม status
    public function actionConfirmStatus($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $status = $this->request->get('status');
        $thaiYear = substr((date('Y') + 543), 2);
        $model = $this->findModel($id);
        $oldObj = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
                $model->status = $status;
                if ($model->status == 6) {
                    // $model->code = \mdm\autonumber\AutoNumber::generate('PO-' . $thaiYear . '????');
                }
                $model->save(false);
                return [
                    'status' => 'success',
                    'container' => '#purchase-container',
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
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('confirm_status', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('confirm_status', [
                'model' => $model,
            ]);
        }
    }

    public function actionProductList()
    {
        $order_id = $this->request->get('order_id');
        $order = Order::findOne($order_id);

        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'product_item', 'category_id' => $order->data_json['item_type']]);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('product_list', [
                    'order' => $order,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('product_list', [
                'order' => $order,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    // เพิ่มรายการวัสดุ
    public function actionAddItem()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $order_id = $this->request->get('order_id');
        $order = $this->findModel($order_id);
        $product_id = $this->request->get('product_id');
        $product = Product::findOne($product_id);

        $model = new Order([
            'category_id' => $order_id,
            'name' => 'order_item',
            'item_id' => $product_id
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->save(false);
                return [
                    'status' => 'success',
                    'container' => '#purchase-container',
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
        $model = Order::findOne([
            'id' => $id,
            'name' => 'order_item'
        ]);
        $product = Product::findOne($model->item_id);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->save(false);
                return [
                    'status' => 'success',
                    'container' => '#purchase-container',
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
                'title' => $product->title,
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

    public function actionDocument($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('document', ['model' => $model]),
            ];
        } else {
            return $this->render('document', ['model' => $model]);
        }
    }

    /**
     * Deletes an existing Order model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
