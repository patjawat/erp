<?php

namespace app\modules\booking\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\bootstrap5\Html;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\modules\am\models\Asset;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use app\modules\booking\models\BookingCarItems;
use app\modules\booking\models\BookingCarItemsSearch;

/**
 * BookingCarItemsController implements the CRUD actions for BookingCarItems model.
 */
class BookingCarItemController extends Controller
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
     * Lists all BookingCarItems models.
     *
     * @return string
     */
    public function actionIndex()
    {
        // $searchModel = new BookingCarItemsSearch();
        // $dataProvider = $searchModel->search($this->request->queryParams);

        // return $this->render('index', [
        //     'searchModel' => $searchModel,
        //     'dataProvider' => $dataProvider,
        // ]);
    }

    // เลือกประเภทของการใช้งานรถ
    public function actionListCars()
    {
        $array = BookingCarItems::find()->all();
        $notIn = ArrayHelper::getColumn($array,'asset_item_id');
        // \Yii::$app->response->format = Response::FORMAT_JSON;
      
        
        $dataProvider = Asset::find()
            ->leftJoin('categorise', 'categorise.code = asset.asset_item')
            ->where([
                'asset.asset_group' => '3',
                'categorise.category_id' => '4'
            ])
            ->andWhere(['not in','asset.id',$notIn])
            ->orderBy([
                'asset.code' => SORT_DESC,
                'asset.receive_date' => SORT_ASC
            ])
            ->all();

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_cars', [
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('list_cars', [
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**
     * Displays a single BookingCarItems model.
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
     * Creates a new BookingCarItems model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new BookingCarItems();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'container' => '#booking'
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing BookingCarItems model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
                'container' => '#booking'
            ];
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing BookingCarItems model.
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
     * Finds the BookingCarItems model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return BookingCarItems the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BookingCarItems::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionGetImg($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Asset::findOne($id);
        return Html::img($model->showImg(), ['class' => 'card-img-top p-2 rounded border border-2 border-secondary-subtle']);
    }
}
