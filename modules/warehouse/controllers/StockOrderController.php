<?php

namespace app\modules\warehouse\controllers;

use app\modules\warehouse\models\StockOrder;
use app\modules\warehouse\models\StockOrderSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * StockOrderController implements the CRUD actions for StockOrder model.
 */
class StockOrderController extends Controller
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
     * Lists all StockOrder models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new StockOrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single StockOrder model.
     * @param int $stock_order_id Stock Order ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($stock_order_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($stock_order_id),
        ]);
    }

    /**
     * Creates a new StockOrder model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new StockOrder();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'stock_order_id' => $model->stock_order_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing StockOrder model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $stock_order_id Stock Order ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($stock_order_id)
    {
        $model = $this->findModel($stock_order_id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'stock_order_id' => $model->stock_order_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing StockOrder model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $stock_order_id Stock Order ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($stock_order_id)
    {
        $this->findModel($stock_order_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the StockOrder model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $stock_order_id Stock Order ID
     * @return StockOrder the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($stock_order_id)
    {
        if (($model = StockOrder::findOne(['stock_order_id' => $stock_order_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
