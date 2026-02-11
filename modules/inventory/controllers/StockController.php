<?php

namespace app\modules\inventory\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\ModalHelper;
use yii\web\NotFoundHttpException;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockSearch;
use app\modules\inventory\models\StockEventSearch;

/**
 * StockController implements the CRUD actions for Stock model.
 */
class StockController extends Controller
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
     * Lists all Stock models.
     *
     * @return string
     */


    public function actionProduct()
    {
        $orderId = $this->request->get('order_id');
        $warehouse = Yii::$app->session->get('warehouse');

        $searchModel = new StockSearch([
            'order_id' => $orderId
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        $dataProvider->query->andFilterWhere([
            'or',
            ['LIKE', 'title', $searchModel->q],
            ['LIKE', 'p.code', $searchModel->q],
        ]);
        $dataProvider->query->andFilterWhere(['p.category_id' => $searchModel->asset_type]);
        $dataProvider->query->andFilterWhere(['warehouse_id' => $warehouse->id]);


        $dataProvider->query->groupBy('asset_item');

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'count' => $dataProvider->getTotalCount(),
                'content' => $this->renderAjax('product/index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('product/index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }


    public function actionInStock()
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;
        $warehouse = Yii::$app->session->get('warehouse');
        if (!$warehouse) {
            return $this->redirect(['/inventory']);
        }
        $searchModel = new StockSearch([
            'warehouse_id' => $warehouse->id
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        $dataProvider->query->andFilterWhere(['p.category_id' => $searchModel->asset_type]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'asset_item', $searchModel->q],
            ['like', 'title', $searchModel->q],
        ]);
        $dataProvider->query->groupBy('asset_item');

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'count' => $dataProvider->getTotalCount(),
                'content' => $this->renderAjax('in_stock', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('in_stock', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }




    public function actionListStock($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $models = Stock::find()
            ->leftJoin('categorise p', 'p.code=stock.asset_item')
            ->where(['warehouse_id' => 1])
            // ->andWhere(['or', ['LIKE', 'title',$q]])
            ->limit(10)
            ->all();
        $data = [['id' => '', 'text' => '']];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->id,
                // 'text' => $model->Avatar(false),
                // 'fullname' => $model->title,
                // 'avatar' => $model->Avatar(false)
            ];
        }
        return $data;
        // return [
        //     'results' => $data,
        //     'items' => $model
        // ];
    }


    /**
     * Displays a single Stock model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {

        $warehouse = Yii::$app->session->get('warehouse');
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionViewStockCard($id)
    {

        $warehouse = Yii::$app->session->get('warehouse');
        if (!$warehouse) {
            return $this->redirect(['/inventory']);
        }
        $model = $this->findModel($id);
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->select([
            't.*',
            'o.category_id AS category_code',
            'w.warehouse_name',
            'o.code',
            'o.data_json',
            // new \yii\db\Expression('@running_total := IF(t.transaction_type = "IN", @running_total + t.qty, @running_total - t.qty) AS total'),
            new \yii\db\Expression('ROUND(@running_total := IF(t.transaction_type = "IN", @running_total + t.qty, @running_total - t.qty), 2) AS total'),
            new \yii\db\Expression('(t.unit_price * t.qty) AS total_price')
        ]);

        $dataProvider->query->from(['t' => 'stock_events'])
            ->leftJoin('warehouses w', 'w.id = t.from_warehouse_id')
            ->leftJoin('stock_events o', 'o.id = t.category_id AND o.name = "order"')
            ->join('JOIN', ['r' => new \yii\db\Expression('(SELECT @running_total := 0)')])
            ->where([
                't.asset_item' => $model->asset_item,
                't.name' => 'order_item',
                't.warehouse_id' => $warehouse->id,
                't.order_status' => 'success',
                'o.order_status' => 'success'
            ])
            ->orderBy(['t.movement_date' => SORT_ASC, 't.id' => SORT_ASC]);

    
          if ($this->request->isAjax) {
    // เตรียม Content ที่จะใช้ทั้งสองกรณี
    $content = $this->renderAjax('view_stock_card-v2', [
        'model' => $model,
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);

    // กรณีที่ 1: ถ้าเรียกผ่าน Pjax (เช่น ตอนบันทึกฟอร์มเสร็จแล้วสั่ง reload)
    if ($this->request->isPjax) {
        return $content; // ส่ง HTML ออกไปตรงๆ
    }

    // กรณีที่ 2: ถ้าเรียกผ่าน AJAX ปกติ (ตอนคลิกเมนูทางซ้าย)
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return [
        'title' => $this->request->get('title'),
        'count' => $dataProvider->getTotalCount(),
        'content' => $content
    ];
} else {
    // กรณีเข้าผ่าน URL ตรงๆ
    return $this->render('view_stock_card-v2', [
        'model' => $model,
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
}

    }

    public function actionUpdateStockCard($id)
    {
        $model= StockEvent::findOne($id);

         if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save(false)) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'url' => Url::to(['/inventory/stock/view-stock-card', 'id' => $model->product->id])
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

          if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_update_stock_card', [
                    'model' => $model,
                ]),
                'footer' => ModalHelper::modalFooterSaveClose()
            ];
        } else {
            return $this->render('_form_update_stock_card', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Creates a new Stock model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Stock();

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
     * Updates an existing Stock model.
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

    public function actionStockCard()
    {
         $warehouse = Yii::$app->session->get('warehouse');
        if (!$warehouse) {
            return $this->redirect(['/inventory']);
        }
        $searchModel = new StockSearch([
            'warehouse_id' => $warehouse->id
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        $dataProvider->query->andFilterWhere(['p.category_id' => $searchModel->asset_type]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'asset_item', $searchModel->q],
            ['like', 'title', $searchModel->q],
        ]);
        $dataProvider->query->groupBy('asset_item');

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'count' => $dataProvider->getTotalCount(),
                'content' => $this->renderAjax('stock_card', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('stock_card', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**
     * Deletes an existing Stock model.
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
     * Finds the Stock model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Stock the loaded model
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
