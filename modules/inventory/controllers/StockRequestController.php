<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\StockMovement;
use app\modules\inventory\models\StockMovementSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;
use yii\web\Response;

/**
 * StockRequestController implements the CRUD actions for StockMovement model.
 */
class StockRequestController extends Controller
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
     * Lists all StockMovement models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $searchModel = new StockMovementSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['to_warehouse_id' => $warehouse['warehouse_id']]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single StockMovement model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new StockMovement model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
        public function actionCreate()
        {
            $warehouse = Yii::$app->session->get('warehouse');
            $model = new StockMovement([
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                'name' => 'request',
                'to_warehouse_id' => $warehouse['warehouse_id'],
            ]);
    
            if ($this->request->isPost) {
                if ($model->load($this->request->post())) {
                    $thaiYear = substr((date('Y') + 543), 2);
                    if ($model->rq_number == '') {
                        $model->rq_number = \mdm\autonumber\AutoNumber::generate('RQ-' . $thaiYear . '????');
                    }
                    $model->order_status = 'pending';
                    $model->save(false);
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } else {
                $model->loadDefaultValues();
            }
    
            if ($this->request->isAJax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
    
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

        // $model = new StockMovement();

        // if ($this->request->isPost) {
        //     if ($model->load($this->request->post()) && $model->save()) {
        //         return $this->redirect(['view', 'id' => $model->id]);
        //     }
        // } else {
        //     $model->loadDefaultValues();
        // }

        // return $this->render('create', [
        //     'model' => $model,
        // ]);
    }

    /**
     * Updates an existing StockMovement model.
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
     * Deletes an existing StockMovement model.
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
     * Finds the StockMovement model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return StockMovement the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StockMovement::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
