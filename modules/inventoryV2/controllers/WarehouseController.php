<?php

namespace app\modules\inventoryV2\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\inventoryV2\models\Warehouse;
use app\modules\inventoryV2\models\WarehouseSearch;

/**
 * Warehouse CRUD ใน inventoryV2 (ย้ายมาจาก modules/inventory)
 * เมื่อเลือกคลังแล้ว redirect ไป main-stock/dashboard
 */
class WarehouseController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST']],
            ],
        ]);
    }

    /**
     * รายการคลัง หรือ redirect ไป dashboard ถ้าเลือกคลังแล้ว
     */
    public function actionIndex()
    {

        $searchModel = new WarehouseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['delete' => null]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * เลือกคลัง (set session) แล้ว redirect ไป dashboard
     */
    public function actionView($id)
    {
        $this->setWarehouse($id);
        if ($this->request->isAjax && $this->request->get('format') === 'json') {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => 'success', 'redirect' => \yii\helpers\Url::to(['/inventory-v2/main-stock/dashboard'])];
        }
        return $this->redirect(['/inventory-v2/main-stock/dashboard']);
    }

    /**
     * AJAX: set warehouse ใน session แล้ว return JSON
     */
    public function actionSetWarehouse($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $this->setWarehouse($id);
        return ['status' => 'success', 'container' => '#pjax-warehouse'];
    }

    public function actionList()
    {
        $searchModel = new WarehouseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['delete' => null]);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        }
        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Warehouse(['ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10)]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save(false)) {
                return ['status' => 'success', 'container' => '#pjax-warehouse'];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', ['model' => $model]),
            ];
        }
        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load($this->request->post()) && $model->save(false)) {
                return ['status' => 'success', 'container' => '#pjax-warehouse'];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', ['model' => $model]),
            ];
        }
        return $this->render('update', ['model' => $model]);
    }

    /**
     * ล้างคลังที่เลือก
     */
    public function actionClear()
    {
        Yii::$app->session->remove('warehouse');
        return $this->redirect(['index']);
    }

    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $model->delete = date('Y-m-d H:i:s');
        $model->save(false);
        return ['status' => 'success', 'container' => '#pjax-warehouse'];
    }

    protected function setWarehouse($id)
    {
        $model = Warehouse::find()->where(['id' => $id])->one();
        if ($model) {
            Yii::$app->session->set('warehouse', $model);
        }
        return ['status' => 'success', 'container' => '#pjax-warehouse'];
    }

    protected function findModel($id)
    {
        if (($model = Warehouse::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
