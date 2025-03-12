<?php

namespace app\modules\me\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use app\components\AppHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\Product;
use app\modules\inventory\models\StockOut;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockSearch;
use app\modules\inventory\models\WarehouseSearch;
use app\modules\inventory\models\StockEventSearch;

class StoreV2Controller extends \yii\web\Controller
{
    public function actionIndex()
    {
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('warehouses w', 'w.id=stock_events.warehouse_id');
        $dataProvider->query->andFilterWhere([
            // 'warehouse_type' => 'SUB',
            'stock_events.name' => 'order',
            'transaction_type' => 'OUT',
            'stock_events.created_by' =>  Yii::$app->user->id]);

        return $this->render('index',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // public function actionIndex()
    // {
    //     return $this->render('index');
    // }

    public function actionSelectStore()
    {
        $id = \Yii::$app->user->id;
        $searchModel = new WarehouseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['delete' => null]);

        if (\Yii::$app->user->can('warehouse')) {
            $dataProvider->query->andWhere(new Expression("JSON_CONTAINS(data_json->'\$.officer','\"$id\"')"));
        } else {
            $emp = UserHelper::GetEmployee();
            $dataProvider->query->andWhere(['warehouse_type' => 'SUB']);
            $dataProvider->query->andWhere(['>', new Expression('FIND_IN_SET(' . $emp->department . ', department)'), 0]);
        }
        $dataProvider->query->orderBy(['warehouse_type' => SORT_ASC]);
        $dataProvider->pagination->pageSize = 100;
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('select_store', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('select_store', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    public function actionSetWarehouse($id)
    {
        $warehouse = Yii::$app->session->get('warehouse_id');
        if ($warehouse) {
            Yii::$app->session->set('warehouse_id', $id);
            return $this->redirect(['/me/store-v2/index']);
        }
    }

    public function actionShow()
    {
        $warehouse = Yii::$app->session->get('warehouse_id');
        return $this->render('show');
    }

    // แสดงรายการขอเบิกวัสดุคลังหลัก
    public function actionMainStore()
    {
        $warehouse = Yii::$app->session->get('warehouse_id');
        return $this->render('main_store');
    }

    public function actionCreateOrder()
    {
        $userCreate = UserHelper::GetEmployee();
        $model = new StockEvent([
            'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
            'transaction_type' => 'out',
            'checker' => $userCreate->leaderUser()['leader1'],
        ]);

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->code = \mdm\autonumber\AutoNumber::generate('REQ-' . substr(AppHelper::YearBudget(), 2) . '????');
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('_form_create_order', [
                'model' => $model
            ])
        ];
    }

    public function actionView($id)
    {
        $model = StockEvent::findOne($id);
        $warehouse = Yii::$app->session->set('warehouse_id', $model->warehouse_id);
        return $this->render('view', ['model' => $model]);
    }

    public function actionStore()
    {
        $warehouse = Yii::$app->session->get('warehouse_id');
        $warehouseModel = \app\modules\inventory\models\Warehouse::findOne($warehouse);
        $item = $warehouseModel->data_json['item_type'];
        $searchModel = new StockSearch([
            'warehouse_id' => $warehouse
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        $dataProvider->query->andWhere(['IN', 'p.category_id', $item]);
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

        return $this->render('store', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAddItem($id)
    {
        $item = Stock::findOne($id);
        $model = new StockEvent([
            'asset_item' => $item->asset_item,
            'name' => 'order_item',
                            'thai_year' => AppHelper::YearBudget(),
                            // 'transaction_type' => $model->transaction_type,
                            // 'category_id' => $model->id,
                            // 'warehouse_id' => $model->warehouse_id,
                            // 'from_warehouse_id' => $model->from_warehouse_id,
                            'lot_number' => $item->lot_number,
                            'unit_price' => $item->unit_price,
                            'qty' => $item->getQuantity(),
                            'data_json' => [
                                'req_qty' => $item->getQuantity(),
                            ],
                            'order_status' => 'pending',
        ]);

        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
                'message' => 'บันทึกข้อมูลเรียบร้อย',
            ];
            return $model;
            // $model->code = \mdm\autonumber\AutoNumber::generate('REQ-' . substr(AppHelper::YearBudget(), 2) . '????');
            // $model->save();
            // return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_add_item', [
                    'model' => $model,
                    'item' => $item,
                ])
            ];
        } else {
            return $this->render('_form_add_item', [
                'model' => $model,
                'item' => $item,
            ]);
        }
    }


    public function actionUpdateItem($id)
    {
        $model = StockEvent::findOne($id);
        $item = Stock::findOne($model->asset_item);
        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
                'message' => 'บันทึกข้อมูลเรียบร้อย',
            ];
            return $model;
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_add_item', [
                    'model' => $model,
                    'item' => $item,
                ])
            ];
        } else {
            return $this->render('_form_add_item', [
                'model' => $model,
                'item' => $item,
            ]);
        }
    }
    
}
