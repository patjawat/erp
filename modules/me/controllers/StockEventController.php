<?php

namespace app\modules\me\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockEventSearch;

/**
 * StockEventController implements the CRUD actions for StockEvent model.
 */
class StockEventController extends Controller
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
     * Lists all StockEvent models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    //แสดงรายหารขอเบิกคลังหลัก
    public function actionReuqestOrder()
    {
        $warehouse = Yii::$app->session->get('sub-warehouse');
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'order']);
        $dataProvider->query->andFilterWhere(['transaction_type' => 'OUT']);
        $dataProvider->query->andFilterWhere(['from_warehouse_id' => $warehouse->id]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'code', $searchModel->q],
            ['like', 'thai_year', $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.vendor_name')"), $searchModel->q],
        ]);
        
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
            'title' => '',
            'content' =>    $this->renderAjax('request_order', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]) 
            ];
        }
        return $this->render('request_order', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // รายการจ่ายออกจากคลัง
    public function actionOut()
    {
        $warehouse = Yii::$app->session->get('sub-warehouse');
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'order']);
        $dataProvider->query->andFilterWhere(['transaction_type' => 'OUT']);
        $dataProvider->query->andFilterWhere(['warehouse_id' => $warehouse->id]);
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
            'title' => '',
            'content' =>    $this->renderAjax('stock_out', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]) 
            ];
        }
        return $this->render('stock_out', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    

    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $msg = ($model->data_json['note'] ?? '-') .' | '.$model->viewCreatedAt();
            return [
            'title' => $model->CreateBy($msg)['avatar'],
            'content' =>    $this->renderAjax('view', ['model' => $model]) 
            ];
        }else{
            return $this->render('view',['model' => $model]);
        }
    }

    public function actionViewStockCard($id)
    {

        $warehouse = Yii::$app->session->get('sub-warehouse');
        if(!$warehouse){
            return $this->redirect(['/inventory']);
        }

        $model = Stock::findOne(['id' => $id]);
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->select([
            't.*',
            'o.category_id AS category_code',
            'w.warehouse_name',
            'o.code',
            'o.data_json',
            new \yii\db\Expression('@running_total := IF(t.transaction_type = "IN", @running_total + t.qty, @running_total - t.qty) AS total'),
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
            ->orderBy(['t.created_at' => SORT_ASC, 't.id' => SORT_ASC]);

            if ($this->request->isAJax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                'title' => $model->Avatar(),
                'content' =>    $this->renderAjax('view_stock_card', [ 'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,]) 
                ];
            }else{
                return $this->render('view_stock_card',[ 'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,]);
            }
        
    }
    
    
    /**
     * Deletes an existing StockEvent model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id รหัสการเคลื่อนไหวสินค้า
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the StockEvent model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id รหัสการเคลื่อนไหวสินค้า
     * @return StockEvent the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StockEvent::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
