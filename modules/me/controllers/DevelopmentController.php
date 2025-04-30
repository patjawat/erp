<?php

namespace app\modules\me\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\Development;
use app\modules\hr\models\DevelopmentDetail;
use app\modules\hr\models\DevelopmentSearch;

/**
 * DevelopmentController implements the CRUD actions for Development model.
 */
class DevelopmentController extends Controller
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
     * Lists all Development models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new DevelopmentSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Development model.
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
     * Creates a new Development model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Development([
            'thai_year' => AppHelper::YearBudget()
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                try {
                    $model->date_start = AppHelper::convertToGregorian($model->date_start);
                    $model->date_end = AppHelper::convertToGregorian($model->date_end);
                    $model->vehicle_date_start = AppHelper::convertToGregorian($model->vehicle_date_start);
                    $model->vehicle_date_end = AppHelper::convertToGregorian($model->vehicle_date_end);
                } catch (\Throwable $th) {
                }
                if($model->save()){
                    $me = UserHelper::GetEmployee();
                    $addMember = new DevelopmentDetail();
                    $addMember->development_id = $model->id;
                    $addMember->name = 'member';
                    $addMember->emp_id = $me->id;
                    $addMember->save(false);
                }
                

                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => false,
                    'errors' => $model->getErrors(),
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', ['model' => $model]),
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Development model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        try {
            $model->date_start = AppHelper::convertToThai($model->date_start);
            $model->date_end = AppHelper::convertToThai($model->date_end);
            $model->vehicle_date_start = AppHelper::convertToThai($model->vehicle_date_start);
            $model->vehicle_date_end = AppHelper::convertToThai($model->vehicle_date_end);
        } catch (\Throwable $th) {
        }
        
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                try {
                    $model->date_start = AppHelper::convertToGregorian($model->date_start);
                    $model->date_end = AppHelper::convertToGregorian($model->date_end);
                    $model->vehicle_date_start = AppHelper::convertToGregorian($model->vehicle_date_start);
                    $model->vehicle_date_end = AppHelper::convertToGregorian($model->vehicle_date_end);
                } catch (\Throwable $th) {
                }
                $model->save();

                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => false,
                    'errors' => $model->getErrors(),
                ];
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
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Development model.
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
     * Finds the Development model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Development the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Development::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    
}
