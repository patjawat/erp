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
