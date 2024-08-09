<?php

namespace app\modules\purchase\controllers;

use app\models\Categorise;
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
        $dataProvider->query->andFilterWhere(['name' => 'order']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'pr_number', $searchModel->q],
            ['like', 'pq_number', $searchModel->q],
            ['like', 'po_number', $searchModel->q],
        ]);
        // $dataProvider->query->andFilterWhere([
        //     'or',
        //     ['like', 'user.doctor_id', $searchModel->q_doctor],
        //     ['like', 'user.fullname', $searchModel->q_doctor],
        //     ['like', 'doctor_consult', $searchModel->q_doctor],
        //     ['like', 'doctor_consult_name', $searchModel->q_doctor],
        //     ['like', 'ipd_admit.vn', $searchModel->q_doctor],
        //     ['like', 'opd_visit.pcc_vn', $searchModel->q_doctor],
        // ]);
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
        $model =  $this->findModel($id);
        \Yii::$app->session->set('order', $model);

        return $this->render('view', [
            'model' => $model,
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
            $order->data_json = ArrayHelper::merge($old,$newObj);
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }
    public function actionDiscount($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        $old = $model->data_json;
        if ($this->request->isPost && $model->load($this->request->post())) {
            // $model->data_json = ArrayHelper::merge($model->data_json,$old);
            $model->data_json = ArrayHelper::merge($old,$model->data_json);
            $model->save();
            return [
                'status' => 'success',
                'container' => '#purchase-container',
                'model' => $model
            ];
        }
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('_form_discount', [
                'model' => $model,
            ]),
        ];

    }

    public function actionFormVat($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        $old = $model->data_json;
        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->data_json = ArrayHelper::merge($old,$model->data_json);
            $model->save();
            return [
                'status' => 'success',
                'container' => '#purchase-container',
                'model' => $model
            ];
        }
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('_form_vat', [
                'model' => $model,
            ]),
        ];

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
            $model->status = $status;
            // if ($model->load($this->request->post())) {
            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
            // if ($model->status == 6) {
            // $model->code = \mdm\autonumber\AutoNumber::generate('PO-' . $thaiYear . '????');
            // }
            //$model->save(false);
            return [
                'status' => 'success',
                'container' => '#purchase-container',
                'model' => $model
            ];
            // } else {
            //     return false;
            // }
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

        $model = Order::findOne($order_id);
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'asset_item']);
        if($model->category_id == ""){
            $dataProvider->query->andFilterWhere(['category_id' => $searchModel->category_id]);
        }else{
            $dataProvider->query->andFilterWhere(['category_id' => $model->category_id]);
            // $dataProvider->query->andFilterWhere(['name' => 'asset_item','category_id' => $order->category_id]);
            // $dataProvider->query->andFilterWhere(['NOT IN' , 'code',$checkOrderItem]);
            
        }

        $dataProvider->pagination->pageSize = 10;

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
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

    // เพิ่มรายการวัสดุ
    public function actionAddItem()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $order_id = $this->request->get('order_id');
        $order = $this->findModel($order_id);
        $asset_item = $this->request->get('asset_item');
        $product = Product::findOne($asset_item);
        
        $model = new Order([
            'group_id' => $product->group_id,
            'category_id' =>  $order_id,
            'name' => 'order_item',
            'asset_item' => $product->code,
            'pr_number' => $order->pr_number,
            'pq_number' => $order->pq_number,
            'po_number' => $order->po_number,
            'data_json' => [
                'asset_item_type_name' => $product->productType->title,
                'asset_item_type_name' => $product->productType->title,
                'asset_item_unit_name' => isset($product->data_json['unit']) ? $product->data_json['unit'] : null,
                'asset_item_name' => $product->title
            ]
        ]);
     

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                if ($model->save()) {
                    // return $order->group_id;
                    //ถ้ายังไม่มีการจัดประเภทของ order
                    if($order->group_id == null){
                        $old = $order->data_json;
                        $newObj = $order->data_json = [
                            'order_type_name' => $product->productType->title
                        ];
                        
                        $order->group_id = $product->group_id;
                        $order->category_id = $product->category_id;
                        
                        $order->data_json = ArrayHelper::merge($old,$newObj);
                        $order->save(false);
                    }

                    return [
                        'status' => 'success',
                        'container' => '#purchase-container',
                    ];
                } else {
                    return $model->getErrors();
                }
            } else {
                return $model->getErrors();
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
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Order::findOne([
            'id' => $id,
            'name' => 'order_item'
        ]);
        $product = Product::findOne(['code' => $model->asset_item]);
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

    public function actionDeleteItem($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        
        if ($model->delete()) {
            $order = Order::findOne($model->category_id);
            if(count($order->ListOrderItems())  == 0){
            $order->data_json =  ArrayHelper::merge($order->data_json, ['order_type_name' => '']);
            $order->group_id = NULL;
            $order->category_id = NULL;
            $order->save();
            }
            return [
                'status' => 'success',
                'container' => '#purchase-container',
            ];
        } else {
            return [
                'status' => 'error',
                'container' => '#purchase-container',
            ];
        }
    }

    /**
     * Deletes an existing Order model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */


      // ตรวจสอบความถูกต้อง
    public function actionCancelOrderValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Order();
        $requiredName = "ต้องระบุเหตุผล";
        if ($this->request->isPost && $model->load($this->request->post())) {

            if (isset($model->data_json['cancel_order_note'])) {
                $model->data_json['cancel_order_note'] == "" ? $model->addError('data_json[cancel_order_note]', $requiredName) : null;
            }

        
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }
    
     public function actionCancelOrder($id){

        $model = $this->findModel($id);

        $oldObj = $model->data_json;
        if ($model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
            $model->save(false);
            return [
                'status' => 'success',
                'container' => '#purchase-container',
                'model' => $model
            ];
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $model->getMe('ยกเลิอกรายการนี้')['avatar'],
                'content' => $this->renderAjax('_form_cancel_order', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form_cancel_order', [
                'model' => $model,
            ]);
        }

        //  $model = $this->findModel($id);
        // $model->deleted_at = Date('Y-m-d H:i:s');
        // $model->deleted_by =Yii::$app->user->id;
        // $model->save();
     }
    public function actionDelete($id)
    {
        $model = $this->findModel($id);


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
