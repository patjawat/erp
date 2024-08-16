<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\StockOut;
use app\modules\inventory\models\StockOutSearch;
use app\modules\sm\models\Product;
use app\components\AppHelper;
use app\modules\inventory\models\Warehouse;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * StockOutController implements the CRUD actions for StockOut model.
 */
class StockOutController extends Controller
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
     * Lists all StockOut models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new StockOutSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'order']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single StockOut model.
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
     * Creates a new StockOut model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $name = $this->request->get('name');
        $order_id = $this->request->get('order_id');
        $model = new StockOut([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'category_id' => $order_id,
            'name' => $name
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                // สร้างรหัสรับเข้า
                if ($model->name == 'order') {
                    $model->code = \mdm\autonumber\AutoNumber::generate('RC-' . (substr((date('Y') + 543), 2)) . '????');
                }

                if ($model->name == 'order_item') {
                    $convertDate = [
                        'mfg_date' =>  AppHelper::convertToGregorian($model->data_json['mfg_date']),
                        'exp_date' =>  AppHelper::convertToGregorian($model->data_json['exp_date']),
                    ];
                    $model->data_json =  ArrayHelper::merge($model->data_json, $convertDate);

                    if ($model->auto_lot == "1") {
                        $model->lot_number  = \mdm\autonumber\AutoNumber::generate('LOT' . (substr((date('Y') + 543), 2)) . '-?????');
                    } else {

                    }

                }

                $model->order_status = 'pending';
                $model->warehouse_id = $warehouse['warehouse_id'];

                if ($model->save(false)) {
                    if ($model->name == 'order') {
                        return $this->redirect(['view', 'id' => $model->id]);
                    } else {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [
                            'status' => 'success',
                            'container' => '#inventory',
                        ];
                    }
                } else {
                    $model->loadDefaultValues();
                }
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
    }


    /**
     * Updates an existing StockOut model.
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
     * Deletes an existing StockOut model.
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
     * Finds the StockOut model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return StockOut the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StockOut::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
