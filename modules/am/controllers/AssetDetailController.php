<?php

namespace app\modules\am\controllers;

use Yii;
use app\components\AppHelper;
use app\modules\am\models\AssetDetail;
use app\modules\am\models\AssetDetailSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * AssetDetailController implements the CRUD actions for AssetDetail model.
 */
class AssetDetailController extends Controller
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
     * Lists all AssetDetail models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $title = $this->request->get('title');
        $name = $this->request->get('name');
        $id = $this->request->get('id');

        $searchModel = new AssetDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => $name]);
   

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $title,
                'content' => $this->renderAjax($name.'/index', [
                    'id' => $id,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('index', [
                'id' => $id,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,

            ]);
        }
    }

    /**
     * Displays a single AssetDetail model.
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
     * Creates a new AssetDetail model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    // public function actionCreate()
    // {
    //     $model = new AssetDetail();

    //     if ($this->request->isPost) {
    //         if ($model->load($this->request->post()) && $model->save()) {
    //             return $this->redirect(['view', 'id' => $model->id]);
    //         }
    //     } else {
    //         $model->loadDefaultValues();
    //     }

    //     return $this->render('create', [
    //         'model' => $model,
    //     ]);
    // }
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $name = $this->request->get('name');
        $category_id = $this->request->get('category_id');
        $title = $this->request->get('title');
        $name = $this->request->get('name');

        $model = new AssetDetail([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'name' => $name,
            // 'code' => $name == "asset_name" ? $category_id : null,
            // 'data_json' => ['title' => $title],
        ]);
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) ) {
                $model->date_start = AppHelper::DateToDb($model->date_start);
                $model->date_end = AppHelper::DateToDb($model->date_end);
                // $model->data_json = ArrayHelper::merge($model_old_data_json,$model->data_json);
                if($model->save()){   
                    return [
                        'status' => 'success',
                        'container' => '#am-container',
                    ];
                }else{
                    return [
                        'status' => 'error',
                        'container' => '#am-container',
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax($name.'/create', [
                'model' => $model,
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            ]),
        ];
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AssetDetail model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->date_start = AppHelper::DateFormDb($model->date_start);
        $model->date_end = AppHelper::DateFormDb($model->date_end);

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->date_start = AppHelper::DateToDb($model->date_start);
            $model->date_end = AppHelper::DateToDb($model->date_end);
                if($model->save()){   
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'container' => '#am-container',
                ];
            }else{
                return [
                    'status' => 'error',
                    'container' => '#am-container',
                ];
            }
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-regular fa-pen-to-square me-1"></i>'.$this->request->get('title'),
                'content' => $this->renderAjax($model->name.'/update', [
                    'model' => $model,
                    'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                ]),
            ];
        }else{
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing AssetDetail model.
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
     * Finds the AssetDetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return AssetDetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AssetDetail::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
