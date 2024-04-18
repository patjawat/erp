<?php

namespace app\modules\sm\controllers;

use app\modules\sm\models\Order;
use app\modules\sm\models\OrderSearch;
use app\modules\sm\models\OrderDetail;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;
use yii\helpers\ArrayHelper;
use app\modules\am\models\Asset;
use yii\web\Response;

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
            if ($model->load($this->request->post())) {

                //$model->save()
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    
                    $items = Yii::$app->request->post();                     //บันทึกใบ Order
                    $model->save();
                    //var_dump($items['Order']['items']);
                    //$items['Order']['schedule'] = \yii\helpers\Json::encode($items['Order']['schedule']);
                    foreach($items['Order']['schedule'] as $key => $val){ //นำรายการสินค้าที่เลือกมา loop บันทึก
                        $order_detail = new OrderDetail();
                        
                        $order_detail->order_id = strval($model->id); // ใส่ ID ของ Order ที่ถูกสร้างเพิ่มเข้าไป
                        $order_detail->product_id = $val['product_id']; // ชื่อสินค้า
                        $order_detail->price = $val['price']; // ราคาสินค้า
                        $order_detail->amount = $val['amount']; // จำนวนสินค้า
                        $order_detail->save();
                    }

                    $transaction->commit();
                    return $this->redirect(['index']);
                } catch (Exception $e) {
                    $transaction->rollBack();
                    return $this->redirect(['index']);
                }
                return print_r(Yii::$app->db->beginTransaction());
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

    
    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDeteil($id){
        $model = Asset::findOne((['id' => $id]));
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $model;
    }
}
