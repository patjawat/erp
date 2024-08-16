<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockSearch;
use app\modules\inventory\models\Store;
use app\modules\inventory\models\StoreSearch;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\modules\sm\models\Product;
use Yii;
use app\modules\inventory\models\Warehouse;
use app\components\UserHelper;
use app\modules\hr\models\Employees;
use yii\helpers\ArrayHelper;

class WithdrawController extends \yii\web\Controller
{
    public function actionIndex()
    {
        Yii::$app->session->remove('category_id');
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'order','movement_type' => 'OUT']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionView($id)
    {
        $model =  $this->findModel($id);
        Yii::$app->session->set('order',$model);
        return $this->render('view', [
            'model' => $model
        ]);
    }

    public function actionListOrderItem($id)
    {
        $model =  $this->findModel($id);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_order_item', [
                    'model' => $model
                ]),
            ];
        } else {
            return $this->render('list_order_item', [
                'model' => $model
            ]);
        }
    }


    public function actionCreate()
    {
        $userCreate = UserHelper::GetEmployee();
        $warehouse = Yii::$app->session->get('warehouse');
        // $warehouseSelect = Yii::$app->session->get('select-warehouse');
        $model = new Stock([
            'name' => 'order',
            'movement_type' => 'OUT',
            'order_status' => 'pending',
            'from_warehouse_id' => $warehouse['warehouse_id'],
            // 'to_warehouse_id' => $warehouseSelect['warehouse_id'],
            'data_json' => [
                'checker' => (int)$userCreate->leaderUser()['leader1_user_id'],
                'checker_name' => $warehouse['checker_name'],
            ]
        ]);
        $cart = \Yii::$app->cart;
        $items = $cart->getItems();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) ) {
                if($model->save()){
                    foreach ($items as $item) {
                       $orderItem = new Stock([
                        'name' => 'order_item',
                        'movement_type' => 'OUT',
                        'category_id' => $model->id,
                        'asset_item' => $item->asset_item,
                        'qty' => $item->qty
                       ]);
                       $orderItem->save(false);
                       \Yii::$app->cart->checkOut(false);
                    }
                }
                return $this->redirect(['/inventory/withdraw/view','id' => $model->id]);
                // return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create',['model' => $model]),
            ];
        } else {
            return $this->render('create',['model' => $model]);
        }
    }

    /**
     * Updates an existing Store model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['/inventory/withdraw/view','id' => $model->id]);
        }

      
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update',['model' => $model]),
            ];
        } else {
            return $this->render('create',['model' => $model]);
        }
    }






public function actionProductList()
{
   
    $warehouse = Yii::$app->session->get('select-warehouse');
    $searchModel = new StoreSearch([
        'warehouse_id' => isset($warehouse['warehouse_id']) ? $warehouse['warehouse_id'] : 0
    ]);
    $dataProvider = $searchModel->search($this->request->queryParams);
    $dataProvider->query->leftJoin('categorise at', 'at.code=store.asset_item');

    if(isset($selectWarehouse)){
        // $dataProvider->query->where(['warehouse_id' => $selectWarehouse['warehouse_id']]);
    }

    // if(isset($selectWarehouse) && $selectWarehouse['warehouse_type'] == 'SUB'){
    //     $dataProvider->query->where(['warehouse_id' => $selectWarehouse['category_id']]);
    // }
    $dataProvider->query->andFilterWhere([
        'or',
        ['LIKE', 'at.title', $searchModel->q],
        // ['LIKE', new Expression("JSON_EXTRACT(asset.data_json, '$.asset_name')"), $searchModel->q],
    ]);

    if ($this->request->isAjax) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' => 'รายการวัสดุในคลัง',
            'content' => $this->renderAjax('product_list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ])
        ];
    } else {
        return $this->render('product_list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}



public function actionAddItem()
{

    $data = Yii::$app->session->get('order');
    
    $id = $this->request->get('id');
    $item = Store::findOne($id);
    $model = new Stock([
        'name' => 'order_item',
       
        
    ]);

        if ($this->request->isPost && $model->load($this->request->post()) ) 
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model->movement_type = 'OUT';
            $model->order_status = 'pending';
            $model->category_id = $data->id;
            $model->asset_item = $item->asset_item;
            $model->save(false);

            return [
                'status' => 'success',
                'title' => '<i class="bi bi-check2-circle"></i> บันทึกสำเร็จ',
                'content' =>$this->renderAjax('_add_item_success')
            ];
        }else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_add_item',['model' => $model,'item' => $item]),
            ];
        } else {
            return $this->render('_add_item',['model' => $model,'item' => $item]);
        }

}


public function actionUpdateItem($id)
{
    $model = Stock::findOne($id);
    $oldObj = $model->data_json;
    if ($this->request->isPost && $model->load($this->request->post())) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model->data_json = ArrayHelper::merge($oldObj,$model->data_json);

        if($model->save()){
            return [
                'status' => 'success',
                'container' => '#inventory'
            ];
        }

    }

    if ($this->request->isAjax) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('_update_item', [
                'model' => $model
            ]),
        ];
    } else {
        return $this->render('_update_item', [
            'model' => $model
        ]);
    }
}



public function actionDeleteItem($id)
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    $this->findModel($id)->delete();

    return [
        'status' => 'success',
        'container' => '#inventory'
    ];
}



    protected function findModel($id)
    {
        if (($model = Stock::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }



}
