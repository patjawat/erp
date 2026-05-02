<?php

namespace app\modules\am\controllers;

use Yii;
use app\modules\am\models\AssetCondition;
use app\modules\am\models\AssetConditionSearch;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * AssetConditionController implements the CRUD actions for AssetCondition model.
 */
class AssetConditionController extends Controller
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
     * Lists all AssetCondition models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AssetConditionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC, 'id' => SORT_ASC]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AssetCondition model.
     * @param string $id good / fair / damaged / worn
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title', 'รายละเอียด Asset Condition'),
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ]),
                'footer' => '',
            ];
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new AssetCondition model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AssetCondition();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                if ($this->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['status' => 'success', 'container' => '#pjax-asset-condition'];
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title', 'Create Asset Condition'),
                'content' => $this->renderAjax('_form', [
                    'model' => $model,
                ]),
                'footer' => '',
            ];
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AssetCondition model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id good / fair / damaged / worn
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            if ($this->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'success', 'container' => '#pjax-asset-condition'];
            }
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title', 'Update Asset Condition'),
                'content' => $this->renderAjax('_form', [
                    'model' => $model,
                ]),
                'footer' => '',
            ];
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing AssetCondition model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id good / fair / damaged / worn
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => 'success', 'container' => '#pjax-asset-condition'];
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the AssetCondition model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id good / fair / damaged / worn
     * @return AssetCondition the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AssetCondition::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
