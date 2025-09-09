<?php

namespace app\modules\inventory\controllers;
use Yii;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockEventSearch;
use app\modules\sm\models\Product;
use yii\web\Response;

class StockEventController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }



     public function actionShowStock()
    {
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $query = (new \yii\db\Query())
        ->select([
            'i.title',
            'i.code as asset_item',
            'qty_in'        => 'SUM(CASE WHEN e.transaction_type = "IN"  THEN e.qty ELSE 0 END)',
            'qty_out'       => 'SUM(CASE WHEN e.transaction_type = "OUT" THEN e.qty ELSE 0 END)',
            'total_price_in'  => 'SUM(CASE WHEN e.transaction_type = "IN"  THEN e.qty * e.unit_price ELSE 0 END)',
            'total_price_out' => 'SUM(CASE WHEN e.transaction_type = "OUT" THEN e.qty * e.unit_price ELSE 0 END)',
            'result_qty'    => '(SUM(CASE WHEN e.transaction_type = "IN"  THEN e.qty ELSE 0 END) - SUM(CASE WHEN e.transaction_type = "OUT" THEN e.qty ELSE 0 END))',
            'result_price'  => '(SUM(CASE WHEN e.transaction_type = "IN"  THEN e.qty * e.unit_price ELSE 0 END) - SUM(CASE WHEN e.transaction_type = "OUT" THEN e.qty * e.unit_price ELSE 0 END))',
        ])
        ->from(['e' => 'stock_events'])
        ->innerJoin(['i' => 'categorise'], 'i.code = e.asset_item AND i.name = "asset_item"')
        ->leftJoin('categorise p', 'p.code = e.asset_item AND p.name = "asset_item"')
        ->groupBy(['i.code', 'i.title']);

    // ✅ ใช้ $where = ['and']
    $where = ['and'];

    if ($searchModel->warehouse_id) {
        $where[] = ['e.warehouse_id' => $searchModel->warehouse_id];
    }

    // if ($this->date_from && $this->date_to) {
    //     $where[] = ['between', 'e.movement_date', $this->date_from, $this->date_to];
    // }

    // if ($this->title) {
    //     $where[] = ['like', 'i.title', $this->title];
    // }

    $query->all();
    $items = $query->where($where)->all();
        return $this->render('show_stock', [
            'items' => $items,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionCancelOrder($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = StockEvent::findOne($id);
        $model->order_status = 'pending';
        $model->save(false);
        $item = StockEvent::updateAll(['order_status' => 'pending'], ['category_id' => $model->id]);
        if($item){
            return [
                'status' => 'success'
            ];
        }
    }



    public function actionProduct($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $models = Product::find()
        ->where(['name' => 'asset_item','group_id' => 4])
        ->andWhere(['!=', 'category_id', 'M25'])
            ->andWhere(['or', ['LIKE', 'title',$q]])
            ->limit(10)
            ->all();
        $data = [['id' => '', 'text' => '']];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->code,
                'text' => $model->Avatar(false),
                'fullname' => $model->title,
                'avatar' => $model->Avatar(false)
            ];
        }
        return [
            'results' => $data,
            'items' => $model
        ];
    }



}
