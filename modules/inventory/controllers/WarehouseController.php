<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\WarehouseSearch;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockSearch;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Yii;
use yii\db\Expression;
use app\modules\purchase\models\Order;

/**
 * WarehouseController implements the CRUD actions for Warehouse model.
 */
class WarehouseController extends Controller
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
     * Lists all Warehouse models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $searchModel = new WarehouseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['delete' => null]);

        if ($warehouse) {
            return $this->render('view', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'model' => $this->findModel($warehouse['warehouse_id']),
                'warehouse' => $warehouse
            ]);
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }


    public function actionList()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $searchModel = new WarehouseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['delete' => null]);

        if ($this->request->isAJax) {
        Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

        


    /**
     * Displays a single Warehouse model.
     * @param int $id Warehouse ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Warehouse model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Warehouse([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save(false)) {
                return [
                    'status' => 'success',
                    'container' => '#warehouse',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAJax) {
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Warehouse model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id Warehouse ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
       

        $model = $this->findModel($id);
     
        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load($this->request->post()) && $model->save(false)) {
                return [
                    'status' => 'success',
                    'container' => '#warehouse',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    //เลือกคลังที่จะทำงาน
    public function actionSetWarehouse()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $model = Warehouse::find()->where(['id' => $id])->One();
        Yii::$app->session->set('warehouse',[
            'id' => $model->id,
            'warehouse_id' => $model->id,
            'warehouse_code' => $model->warehouse_code,
            'warehouse_name' => $model->warehouse_name,
            'warehouse_type' => $model->warehouse_type,
            'category_id' => $model->category_id,
        ]);
        return $this->redirect(['index']);
        // Yii::$app->session->set('warehouse_name', $model->warehouse_name);
    }


    //เลือกคลังที่จะดำเนินการเบิก
    public function actionSelectWarehouse()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $model = Warehouse::find()->where(['id' => $id])->One();
        Yii::$app->session->set('select-warehouse', [
            'warehouse_id' => $model->id,
            'warehouse_name' => $model->warehouse_name,
        ]);
        \Yii::$app->cart->checkOut(false);
        return $this->redirect(['/inventory/store']);
        // Yii::$app->session->set('warehouse_name', $model->warehouse_name);
    }
    
    public function actionClearSelectWarehouse()
    {
        Yii::$app->session->remove('select-warehouse');
        \Yii::$app->cart->checkOut(false);
        return $this->redirect(['/inventory/store']);
    }


    public function actionClear()
    {
        Yii::$app->session->remove('warehouse');
        return $this->redirect(['index']);
        // Yii::$app->session->set('warehouse_name', $model->warehouse_name);
    }

    /**
     * Deletes an existing Warehouse model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id Warehouse ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $model->delete = date('Y-m-d H:i:s');
        $model->save(false);

        return [
            'status' => 'success',
            'container' => '#warehouse',
        ];
    }

    public function actionListOrderRequest()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'stock_detail','to_warehouse_id' => $warehouse['warehouse_id']]);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_order_request', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('list_order_request', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }
    
    /**
     * Finds the Warehouse model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id Warehouse ID
     * @return Warehouse the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Warehouse::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
