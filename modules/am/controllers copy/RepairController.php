<?php

namespace app\modules\am\controllers;

use Yii;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\ArrayHelper;

/**
 * RepairController implements the CRUD actions for Asset model.
 */
class RepairController extends Controller
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
     * Lists all Asset models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AssetSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['asset_group' => 3]);
        // $dataProvider->query->andWhere(['not', ['repair' => null]]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Asset model.
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
     * Creates a new Asset model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($id)
    {
        $model = $this->findModel($id);

        $old = $model->repair != NULL ? $model->repair :[] ;
        if ($this->request->isPost) {   
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load($this->request->post())) {
            
                // $model->repair = $datas;
                // return $old;
                // $a = [$old,$model->repair];
                // $b = array_push($old,$a);
                // return $a;
                $model->repair = $old;
                // $model->save();
                return $model->repair;
                return [
                    'status' => 'success',
                    'container' => '#am-container',
                    'save' => $model->save()
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if($this->request->isAJax){
        Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('tilte'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ])
             ];
        }else{
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Asset model.
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
     * Deletes an existing Asset model.
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
     * Finds the Asset model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Asset the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Asset::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
