<?php

namespace app\modules\helpdesk\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockSearch;
use app\modules\inventory\models\StockEventSearch;


class StockController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $getWarehouse = \Yii::$app->session->get('selectMainWarehouse');
        $searchModel = new StockSearch([
            'warehouse_id' => isset($getWarehouse['warehouse_id']) ? $getWarehouse['warehouse_id'] : '',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        $dataProvider->query->andFilterWhere(['warehouse_id' => ($getWarehouse ? $getWarehouse['warehouse_id'] : $searchModel->warehouse_id)]);
        $dataProvider->query->andFilterWhere(['p.category_id' => $searchModel->asset_type]);

        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'asset_item', $searchModel->q],
            ['like', 'title', $searchModel->q],
        ]);
        $dataProvider->setSort([
            'defaultOrder' => [
                'unit_price' => SORT_DESC,
            ],
        ]);
        $dataProvider->query->groupBy('asset_item');
        $dataProvider->pagination->pageSize = 24;
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'count' => $dataProvider->getTotalCount(),
                'content' => $this->renderAjax('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }



}
