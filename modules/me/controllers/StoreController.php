<?php

namespace app\modules\me\controllers;

use app\modules\inventory\models\Product;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockSearch;

class StoreController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionProduct()
    {
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'stock_item']);
        $dataProvider->query->groupBy('asset_item');
        // $dataProvider->pagination->pageSize = 4;

        if ($this->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('product', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('product', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }


}
