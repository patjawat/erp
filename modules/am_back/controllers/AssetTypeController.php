<?php

namespace app\modules\am\controllers;

use app\modules\sm\models\AssetType;
use app\modules\sm\models\AssetTypeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use Yii;
use yii\helpers\ArrayHelper;
use app\models\Categorise;

/**
 * AssetTypeController implements the CRUD actions for AssetType model.
 */
class AssetTypeController extends Controller
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
     * Lists all AssetType models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AssetTypeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'asset_type']);
        $dataProviderGroup = $searchModel->search($this->request->queryParams);
        $dataProviderGroup->query->andFilterWhere(['name' => 'asset_type', 'active' => true]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProviderGroup' => $dataProviderGroup,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {

        $model = $this->findModel($id);
        $searchModel = new AssetTypeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'asset_type', 'active' => true, 'category_id' => $model->code]);
        $dataProviderGroup = $searchModel->search($this->request->queryParams);
        $dataProviderGroup->query->where(['name' => 'asset_type','category_id' => $model->category_id, 'active' => true]);


        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'dataProvider' => $dataProvider,
                    'dataProviderGroup' => $dataProviderGroup,
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'dataProvider' => $dataProvider,
                'dataProviderGroup' => $dataProviderGroup,

            ]);
        }
        // $small_model = Fsn::find()->where(['name' => 'asset_name','category_id'=>$model->code])->all();
        return $this->render('view', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'btn' => true,

        ]);
    }

    /**
     * Creates a new AssetType model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new AssetType([
            'name' => 'asset_type'
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return [
                    'status' => 'success',
                    'container' => '#sm-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('create',[
                'model' => $model,
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10)
            ])
        ];
    }

    /**
     * Updates an existing AssetType model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return [
                'status' => 'success',
                'container' => '#sm-container',
            ];
        }

        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('create',[
                'model' => $model,
                'ref' => $model->ref == '' ? substr(Yii::$app->getSecurity()->generateRandomString(), 10) : $model->ref
            ])
        ];

    }

    /**
     * Deletes an existing AssetType model.
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
     * Finds the AssetType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return AssetType the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AssetType::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


        /**
     * Displays a single Assetitem model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionViewType($id)
    {

        $title = $this->request->get('title');
        $model = $this->findModel($id);
        $searchModel = new AssetTypeSearch();

        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'asset_item', 'active' => true, 'category_id' => $model->code]);
        $dataProviderGroup = $searchModel->search($this->request->queryParams);
        $dataProviderGroup->query->where(['name' => 'asset_type','category_id' => $model->category_id, 'active' => true]);


        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('view_type', [
                    'model' => $model,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'dataProvider' => $dataProvider,
                    'dataProviderGroup' => $dataProviderGroup,
                ]),
            ];
        } else {
            return $this->render('view_type', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'dataProvider' => $dataProvider,
                'dataProviderGroup' => $dataProviderGroup,

            ]);
        }
        // $small_model = Fsn::find()->where(['name' => 'asset_name','category_id'=>$model->code])->all();
        return $this->render('view_type', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'title' => $title ,

        ]);
    }


    
}
