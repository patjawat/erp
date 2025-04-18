<?php

namespace app\modules\inventory\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockOut;;

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
    public function actionIndex()
    {
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionProduct()
    {
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        $dataProvider->query->andFilterWhere(['like', 'title', $searchModel->q]);
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
        if(!$warehouse){
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
        if(!$warehouse){
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

        return $this->render('view_stock_card', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
