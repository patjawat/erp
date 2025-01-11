<?php

namespace app\modules\pm\controllers;

use Yii;
use yii\web\Response;;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\pm\models\ProjectTasks;
use app\modules\pm\models\ProjectTasksSearch;


/**
 * ProjectTasksController implements the CRUD actions for ProjectTasks model.
 */
class ProjectTasksController extends Controller
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
     * Lists all ProjectTasks models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ProjectTasksSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ProjectTasks model.
     * @param int $id คีย์หลักที่เป็น Auto Increment
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
     * Creates a new ProjectTasks model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new ProjectTasks([
            'project_id' => $this->request->get('project_id'),
        ]);
        $model->level = $model->runLevel();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                if ($model->save()) {
                    return ['success' => true];
                } else {
                    return ['success' => false, 'message' => $model->getErrors()];
                }
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
    }

    /**
     * Updates an existing ProjectTasks model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id คีย์หลักที่เป็น Auto Increment
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
     * Deletes an existing ProjectTasks model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id คีย์หลักที่เป็น Auto Increment
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the ProjectTasks model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id คีย์หลักที่เป็น Auto Increment
     * @return ProjectTasks the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProjectTasks::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
